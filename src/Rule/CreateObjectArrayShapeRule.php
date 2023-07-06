<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\VerbosityLevel;
use yii\BaseYii;

/**
 * @implements Rule<StaticCall>
 */
final class CreateObjectArrayShapeRule implements Rule {

    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getNodeType(): string {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     */
    public function processNode(Node $node, Scope $scope): array {
        $class = $node->class;
        if (!$class instanceof Node\Name) {
            return [];
        }

        $methodName = $node->name;
        if (!$methodName instanceof Node\Identifier) {
            return [];
        }

        if (!is_a($class->toString(), BaseYii::class, true) || $methodName->toString() !== 'createObject') {
            return [];
        }

        $config = $node->getArgs()[0]->value;
        if (!$config instanceof Node\Expr\Array_) {
            return [];
        }

        $configType = $scope->getType($config);

        /** @var ConstantStringType $class */
        $class = $configType->getOffsetValueType(new ConstantStringType('class'));
        if (count($class->getConstantStrings()) !== 1) {
            /** @var ConstantStringType $class */
            $class = $configType->getOffsetValueType(new ConstantStringType('__class'));
            if (count($class->getConstantStrings()) !== 1) {
                return [];
            }
        }

        $className = $class->getConstantStrings()[0]->getValue();
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        $errors = [];
        foreach ($config->items as $item) {
            if ($item === null || !$item->key instanceof Node\Scalar\String_) {
                continue;
            }

            $propertyName = $item->key->value;
            // Skip class name declaration since it's already readed
            if ($propertyName === 'class' || $propertyName === '__class') {
                continue;
            }

            // Ignore constructors for a moment since it's an another complicated task
            if ($propertyName === '__construct()') {
                continue;
            }

            // Skip behaviors and events attachment
            if (str_starts_with($propertyName, 'as ') || str_starts_with($propertyName, 'on ')) {
                continue;
            }

            if (!$classReflection->hasProperty($propertyName)) {
                $errors[] = RuleErrorBuilder::message(sprintf("The config for %s is wrong: the property %s doesn't exists", $className, $propertyName))->build();
                continue;
            }

            $valueType = $scope->getType($item->value);
            $property = $classReflection->getProperty($propertyName, $scope);
            if (!$property->isPublic()) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Access to %s property %s::$%s.',
                    $property->isPrivate() ? 'private' : 'protected',
                    $property->getDeclaringClass()->getName(),
                    $propertyName,
                ))
                    ->identifier('property.private')
                    ->line($item->getLine())
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
                    ->line($item->getLine())
                    ->build();
                continue;
            }

            $typeFromClass = $property->getWritableType();
            $result = $typeFromClass->acceptsWithReason($valueType, true);
            if (!$result->yes()) {
                $level = VerbosityLevel::getRecommendedLevelByType($typeFromClass, $valueType);

                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Property %s::$%s (%s) does not accept %s.',
                    $property->getDeclaringClass()->getDisplayName(),
                    $propertyName,
                    $typeFromClass->describe($level),
                    $valueType->describe($level),
                ))
                    ->identifier('assign.propertyType')
                    ->acceptsReasonsTip($result->reasons)
                    ->line($item->getLine())
                    ->build();
            }
        }

        return $errors;
    }

}
