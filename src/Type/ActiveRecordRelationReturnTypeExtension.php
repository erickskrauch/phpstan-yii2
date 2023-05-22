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
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
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

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type {
        $arg = $methodCall->args[0];
        if (!$arg instanceof Arg) {
            throw new ShouldNotHappenException(sprintf('Unexpected arg %s during method call %s at line %d', get_class($arg), $methodReflection->getName(), $methodCall->getLine()));
        }

        $argType = $scope->getType($arg->value);
        if (!$argType instanceof ConstantStringType) {
            throw new ShouldNotHappenException(sprintf('Invalid argument provided to method %s' . PHP_EOL . 'Hint: You should use ::class instead of ::className()', $methodReflection->getName()));
        }

        $class = $this->reflectionProvider->getClass($argType->getValue());
        /** @var \PHPStan\Type\ObjectType $type */
        $type = ParametersAcceptorSelector::selectSingle($class->getMethod('find', $scope)->getVariants())->getReturnType();

        return new ActiveQueryObjectType($argType->getValue(), $type->getClassName());
    }

}
