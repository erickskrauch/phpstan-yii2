<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Rule;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\VerbosityLevel;

final class YiiConfig {

    /**
     * @return string[]
     */
    public static function validateArray(ClassReflection $classReflection, ConstantArrayType $config, Scope $scope): array {
        $errors = [];
        /** @var ConstantIntegerType|ConstantStringType $key */
        foreach ($config->getKeyTypes() as $i => $key) {
            /** @var \PHPStan\Type\Type $value */
            $value = $config->getValueTypes()[$i];
            if (!$key instanceof ConstantStringType) {
                // TODO: should introduce an error that all keys must be strings
                continue;
            }

            $propertyName = $key->getValue();
            // Skip class name declaration since it's already readed
            if ($propertyName === 'class' || $propertyName === '__class') {
                continue;
            }

            // Ignore constructors for a moment since it's an another complicated task
            if ($propertyName === '__construct()') {
                if (!$value->isConstantArray()->yes()) {
                    // TODO: reword
                    $errors[] = RuleErrorBuilder::message(sprintf("The config for %s is wrong: the property %s doesn't exists", $classReflection->getName(), $propertyName))->build();
                    continue;
                }

                $errors = array_merge($errors, self::validateConstructorArgs($classReflection, $value->getConstantArrays()[0], $scope));
                continue;
            }

            // Skip behaviors and events attachment
            if (str_starts_with($propertyName, 'as ') || str_starts_with($propertyName, 'on ')) {
                // TODO: if it's not Configurable, than we're in trouble
                continue;
            }

            if (!$classReflection->hasProperty($propertyName)) {
                $errors[] = RuleErrorBuilder::message(sprintf("The config for %s is wrong: the property %s doesn't exists", $classReflection->getName(), $propertyName))->build();
                continue;
            }

            $property = $classReflection->getProperty($propertyName, $scope);
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
            $result = $target->acceptsWithReason($value, true);
            if (!$result->yes()) {
                $level = VerbosityLevel::getRecommendedLevelByType($target, $value);
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Property %s::$%s (%s) does not accept %s.',
                    $property->getDeclaringClass()->getDisplayName(),
                    $propertyName,
                    $target->describe($level),
                    $target->describe($level),
                ))
                    ->identifier('assign.propertyType')
                    ->acceptsReasonsTip($result->reasons)
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    public static function validateConstructorArgs(ClassReflection $classReflection, ConstantArrayType $config, Scope $scope): array {
        /** @var ParameterReflection[] $constructorParams */
        $constructorParams = ParametersAcceptorSelector::selectSingle($classReflection->getConstructor()->getVariants())->getParameters();
        /** @var \PHPStan\Type\Type|null $firstKeyType */
        $firstKeyType = null;
        $errors = [];
        /** @var ConstantIntegerType|ConstantStringType $key */
        foreach ($config->getKeyTypes() as $i => $key) {
            /** @var \PHPStan\Type\Type $value */
            $value = $config->getValueTypes()[$i];

            if ($firstKeyType === null) {
                $firstKeyType = $key;
            } elseif (!$firstKeyType instanceof $key) {
                $errors[] = RuleErrorBuilder::message("Parameters indexed by name and by position in the same array aren't allowed.")
                    ->nonIgnorable()
                    ->build();
                continue;
            }

            if ($key instanceof ConstantIntegerType) {
                if (!isset($constructorParams[$key->getValue()])) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Unknown parameter #%d in call to %s constructor.',
                        $key->getValue() + 1,
                        $classReflection->getName(),
                    ))
                        ->identifier('argument.unknown')
                        ->build();
                    continue;
                }

                $paramIndex = $key->getValue();
                $paramReflection = $constructorParams[$paramIndex];
            } else {
                $paramReflection = null;
                $paramIndex = null;
                foreach ($constructorParams as $j => $constructorParam) {
                    if ($constructorParam->getName() === $key->getValue()) {
                        $paramReflection = $constructorParam;
                        $paramIndex = $j;
                        break;
                    }
                }

                if ($paramReflection === null) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Unknown parameter $%s in call to %s constructor.',
                        $key->getValue(),
                        $classReflection->getName(),
                    ))
                        ->identifier('argument.unknown')
                        ->build();

                    continue;
                }
            }

            $paramType = $paramReflection->getType();
            $result = $paramType->acceptsWithReason($value, true);
            if (!$result->yes()) {
                $level = VerbosityLevel::getRecommendedLevelByType($paramType, $value);
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Parameter #%d %s of class %s constructor expects %s, %s given.',
                    $paramIndex + 1,
                    $paramReflection->getName(),
                    $classReflection->getName(),
                    $paramType->describe($level),
                    $value->describe($level),
                ))
                    ->identifier('argument.type')
                    ->acceptsReasonsTip($result->reasons)
                    ->build();
            }
        }

        return $errors;
    }

}
