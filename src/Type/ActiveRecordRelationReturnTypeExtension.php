<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\db\BaseActiveRecord;

final class ActiveRecordRelationReturnTypeExtension implements DynamicMethodReturnTypeExtension {

    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getClass(): string {
        return BaseActiveRecord::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool {
        return in_array($methodReflection->getName(), ['hasOne', 'hasMany'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type {
        // When method call is invalid - do nothing
        if (!isset($methodCall->args[0]) || !$methodCall->args[0] instanceof Arg) {
            return null;
        }

        $argType = $scope->getType($methodCall->args[0]->value);
        if (!$argType->isClassStringType()->yes()) {
            throw new ShouldNotHappenException(sprintf('Invalid argument provided to method %s' . PHP_EOL . 'Hint: You should use ::class instead of ::className()', $methodReflection->getName()));
        }

        $types = [];
        foreach ($argType->getConstantStrings() as $constantString) {
            $class = $this->reflectionProvider->getClass($constantString->getValue());
            $type = ParametersAcceptorSelector::selectSingle($class->getMethod('find', $scope)->getVariants())->getReturnType();
            if (!$type->isObject()->yes()) {
                throw new ShouldNotHappenException(sprintf('Return type of %s::%s must be an object', $class->getName(), $methodReflection->getName()));
            }

            $classNames = $type->getObjectClassNames();
            foreach ($classNames as $className) {
                $types[] = new ActiveQueryObjectType(new ObjectType($class->getName()), $className);
            }
        }

        return TypeCombinator::union(...$types);
    }

}
