<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Rule;

use ErickSkrauch\PHPStan\Yii2\Helper\RuleHelper;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Internal\SprintfHelper;
use PHPStan\Node\Expr\TypeExpr;
use PHPStan\Rules\Classes\InstantiationRule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;

final class YiiConfigHelper {

    private RuleLevelHelper $ruleLevelHelper;

    private InstantiationRule $instantiationRule;

    public function __construct(
        RuleLevelHelper $ruleLevelHelper,
        InstantiationRule $instantiationRule
    ) {
        $this->ruleLevelHelper = $ruleLevelHelper;
        $this->instantiationRule = $instantiationRule;
    }

    /**
     * @return Type|\PHPStan\Rules\IdentifierRuleError
     */
    public function findObjectType(ConstantArrayType $config) {
        foreach (['__class', 'class'] as $classKey) {
            $classType = $config->getOffsetValueType(new ConstantStringType($classKey));
            // This condition will also filter our invalid type, which should be already reported by PHPStan itself
            if (!$classType->isClassStringType()->yes()) {
                continue;
            }

            return $classType->getClassStringObjectType();
        }

        return RuleErrorBuilder::message('Configuration params array must have "class" or "__class" key')
            ->identifier('yiiConfig.missingClass')
            ->build();
    }

    /**
     * @phpstan-return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function validateArray(Type $object, ConstantArrayType $config, Scope $scope): array {
        $errors = [];
        /** @var ConstantIntegerType|ConstantStringType $key */
        foreach ($config->getKeyTypes() as $i => $key) {
            /** @var Type $value */
            $value = $config->getValueTypes()[$i];
            // @phpstan-ignore-next-line according to getKeyType() typing it is only possible to have those or ConstantIntType
            if (!$key instanceof ConstantStringType) {
                $errors[] = RuleErrorBuilder::message('The object configuration params must be indexed by name')
                    ->identifier('argument.type')
                    ->build();
                continue;
            }

            $propertyName = $key->getValue();
            // Skip class name declaration since it's already readed
            if ($propertyName === 'class' || $propertyName === '__class') {
                continue;
            }

            // TODO: yii\base\Configurable interface
            if ($propertyName === '__construct()') {
                if (!$value->isConstantArray()->yes()) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'The constructor params must be an array, %s given',
                        $value->describe(VerbosityLevel::typeOnly()),
                    ))
                        ->identifier('argument.type')
                        ->build();
                    continue;
                }

                $errors = array_merge($errors, $this->validateConstructorArgs($object, $value->getConstantArrays()[0], $scope));
                continue;
            }

            // Skip behaviors and events attachment
            if (str_starts_with($propertyName, 'as ') || str_starts_with($propertyName, 'on ')) {
                // TODO: if it's not Configurable, than we're in trouble
                continue;
            }

            $typeResult = $this->ruleLevelHelper->findTypeToCheck(
                $scope,
                new TypeExpr($object), // @phpstan-ignore-line @ondrejmirtes said that I can use that method
                sprintf('Access to property $%s on an unknown class %%s.', SprintfHelper::escapeFormatString($propertyName)), // @phpstan-ignore-line @ondrejmirtes said that I can use that method
                static fn(Type $type): bool => $type->canAccessProperties()->yes() && $type->hasProperty($propertyName)->yes(),
            );
            $objectType = $typeResult->getType();
            if ($objectType instanceof ErrorType) {
                return $typeResult->getUnknownClassErrors();
            }

            if (!$objectType->canAccessProperties()->yes() || !$objectType->hasProperty($propertyName)->yes()) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    "The config for %s is wrong: the property %s doesn't exists",
                    $objectType->describe(VerbosityLevel::typeOnly()),
                    $propertyName,
                ))
                    ->identifier('property.notFound')
                    ->build();
                continue;
            }

            $property = $objectType->getProperty($propertyName, $scope);
            if (!$property->isPublic()) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Access to %s property %s::$%s.',
                    $property->isPrivate() ? 'private' : 'protected',
                    $property->getDeclaringClass()->getName(),
                    $propertyName,
                ))
                    ->identifier('property.private')
                    ->build();
                continue;
            }

            if (!$property->isWritable()) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Property %s::$%s is not writable.',
                    $property->getDeclaringClass()->getName(),
                    $propertyName,
                ))
                    ->identifier('assign.propertyReadOnly')
                    ->build();
                continue;
            }

            $target = $property->getWritableType();
            $result = $this->ruleLevelHelper->acceptsWithReason($target, $value, $scope->isDeclareStrictTypes());
            if (!$result->result) {
                $level = VerbosityLevel::getRecommendedLevelByType($target, $value);
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Property %s::$%s (%s) does not accept %s.',
                    $property->getDeclaringClass()->getDisplayName(),
                    $propertyName,
                    $target->describe($level),
                    $value->describe($level),
                ))
                    ->identifier('assign.propertyType')
                    ->acceptsReasonsTip($result->reasons)
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * @phpstan-return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function validateConstructorArgs(Type $object, ConstantArrayType $config, Scope $scope): array {
        $errors = [];
        /** @var \PhpParser\Node\Arg[] $args */
        $args = [];
        /** @var Type|null $firstKeyType */
        $firstKeyType = null;
        foreach ($config->getKeyTypes() as $i => $key) {
            /** @var Type $value */
            $value = $config->getValueTypes()[$i];

            if ($firstKeyType === null) {
                $firstKeyType = $key;
            } elseif (!$firstKeyType instanceof $key) {
                $errors[] = RuleErrorBuilder::message("Parameters indexed by name and by position in the same array aren't allowed.")
                    ->identifier('yiiConfig.constructorParamsMix')
                    ->build();
                continue;
            }

            if ($key instanceof ConstantIntegerType) {
                // @phpstan-ignore-next-line I know about backward compatibility promise
                $args[] = new Node\Arg(new TypeExpr($value));
            } else {
                // @phpstan-ignore-next-line I know about backward compatibility promise
                $args[] = new Node\Arg(new TypeExpr($value), false, false, [], new Node\Identifier($key->getValue()));
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        $classNamesTypes = [];
        foreach ($object->getObjectClassNames() as $className) {
            $classNamesTypes[] = new ConstantStringType($className, true);
        }

        // @phpstan-ignore-next-line I know about backward compatibility promise
        $newNode = new Expr\New_(new TypeExpr(TypeCombinator::union(...$classNamesTypes)), $args);

        // @phpstan-ignore-next-line I know about backward compatibility promise
        $errors = $this->instantiationRule->processNode($newNode, $scope);

        // @phpstan-ignore-next-line it does return the correct type, but some internal PHPStan's magic fails here
        return array_map([RuleHelper::class, 'removeLine'], $errors);
    }

}
