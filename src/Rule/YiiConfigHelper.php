<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Rule;

use PHPStan\Analyser\Scope;
use PHPStan\Internal\SprintfHelper;
use PHPStan\Node\Expr\TypeExpr;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class YiiConfigHelper {

    private RuleLevelHelper $ruleLevelHelper;

    private bool $reportMaybes;

    public function __construct(
        RuleLevelHelper $ruleLevelHelper,
        bool $reportMaybes
    ) {
        $this->ruleLevelHelper = $ruleLevelHelper;
        $this->reportMaybes = $reportMaybes;
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
                new TypeExpr($object),
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
        /** @var \PHPStan\Type\Type|null $firstParamKeyType */
        $firstParamKeyType = null;
        $errors = [];

        /** @var ConstantIntegerType|ConstantStringType $key */
        foreach ($config->getKeyTypes() as $i => $key) {
            /** @var \PHPStan\Type\Type $paramValue */
            $paramValue = $config->getValueTypes()[$i];
            $paramName = $key->getValue();
            $paramStrToReport = is_int($paramName) ? ('#' . ($paramName + 1)) : ('$' . $paramName);

            if ($firstParamKeyType === null) {
                $firstParamKeyType = $key;
            } elseif (!$firstParamKeyType instanceof $key) {
                $errors[] = RuleErrorBuilder::message("Parameters indexed by name and by position in the same array aren't allowed.")
                    ->identifier('yiiConfig.constructorParamsMix')
                    ->build();
                continue;
            }

            /** @var list<\PHPStan\Rules\IdentifierRuleError> $paramSearchErrors */
            $paramSearchErrors = [];
            /** @var \PHPStan\Reflection\ParameterReflection[] $foundParams */
            $foundParams = [];
            /** @var \PHPStan\Reflection\ClassReflection $classReflection */
            foreach ($object->getObjectClassReflections() as $classReflection) {
                $constructorParams = ParametersAcceptorSelector::selectSingle($classReflection->getConstructor()->getVariants())->getParameters();
                $param = null;

                // TODO: prevent direct pass of 'config' param to constructor args (\yii\base\Configurable)

                if (is_int($paramName)) {
                    $param = $constructorParams[$paramName] ?? null;
                } else {
                    foreach ($constructorParams as $constructorParam) {
                        if ($constructorParam->getName() === $paramName) {
                            $param = $constructorParam;
                            break;
                        }
                    }
                }

                if ($param === null) {
                    $paramSearchErrors[] = RuleErrorBuilder::message(sprintf(
                        'Unknown parameter %s in call to %s constructor.',
                        $paramStrToReport,
                        $classReflection->getName(),
                    ))
                        ->identifier('argument.unknown')
                        ->build();
                    continue;
                }

                $foundParams[$classReflection->getName()] = $param;
            }

            if (empty($foundParams)) {
                $paramSearchErrors[] = RuleErrorBuilder::message(sprintf(
                    'Unknown parameter %s in call to %s constructor.',
                    $paramStrToReport,
                    $object->describe(VerbosityLevel::typeOnly()),
                ))
                    ->identifier('argument.unknown')
                    ->build();
                continue;
            }

            if ($this->reportMaybes && !empty($paramSearchErrors)) {
                $errors = array_merge($errors, $paramSearchErrors);
                continue;
            }

            /** @var list<\PHPStan\Rules\IdentifierRuleError> $typeCheckErrors */
            $typeCheckErrors = [];
            $accepted = false;
            foreach ($foundParams as $className => $constructorParam) {
                // TODO: expose param name
                $paramType = $constructorParam->getType();
                $result = $this->ruleLevelHelper->acceptsWithReason($paramType, $paramValue, $scope->isDeclareStrictTypes());
                if (!$result->result) {
                    $level = VerbosityLevel::getRecommendedLevelByType($paramType, $paramValue);
                    $typeCheckErrors[] = RuleErrorBuilder::message(sprintf(
                        'Parameter %s of class %s constructor expects %s, %s given.',
                        $paramStrToReport,
                        $className,
                        $paramType->describe($level),
                        $paramValue->describe($level),
                    ))
                        ->identifier('argument.type')
                        ->acceptsReasonsTip($result->reasons)
                        ->build();
                }

                $accepted |= $result->result;
            }

            if (!$accepted) {
                // TODO: bad decision, need to create more specific error
                $errors[] = $typeCheckErrors[0];
                continue;
            }

            if ($this->reportMaybes && !empty($typeCheckErrors)) {
                $errors = array_merge($errors, $typeCheckErrors);
            }
        }

        return $errors;
    }

}
