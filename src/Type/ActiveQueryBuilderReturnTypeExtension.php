<?php
declare(strict_types=1);

namespace Proget\PHPStan\Yii2\Type;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\db\ActiveQueryInterface;

final class ActiveQueryBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension {

    public function getClass(): string {
        return ActiveQueryInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool {
        // This condition is necessary to process the query builder and not to lose the model during its work
        $type = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        if ((new ObjectType(ActiveQueryInterface::class))->isSuperTypeOf($type)->yes()) {
            return true;
        }

        return in_array($methodReflection->getName(), ['one', 'all'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type {
        $calledOnType = $scope->getType($methodCall->var);
        if (!$calledOnType instanceof ActiveQueryObjectType) {
            return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }

        $methodName = $methodReflection->getName();
        if ($methodName === 'asArray') {
            $argType = isset($methodCall->args[0]) && $methodCall->args[0] instanceof Arg ? $scope->getType($methodCall->args[0]->value) : new ConstantBooleanType(true);
            if (!$argType instanceof ConstantBooleanType) {
                throw new ShouldNotHappenException(sprintf('Invalid argument provided to asArray method at line %d', $methodCall->getLine()));
            }

            return new ActiveQueryObjectType(
                $calledOnType->getModelClass(),
                $calledOnType->getClassName(),
                $argType->getValue(),
                $calledOnType->hasIndexBy(),
            );
        }

        if ($methodName === 'indexBy') {
            $argType = $scope->getType($methodCall->getArgs()[0]->value);

            return new ActiveQueryObjectType(
                $calledOnType->getModelClass(),
                $calledOnType->getClassName(),
                $calledOnType->isAsArray(),
                !$argType instanceof NullType,
            );
        }

        if ($methodName === 'one') {
            return TypeCombinator::union(
                new NullType(),
                $calledOnType->isAsArray()
                    ? new ArrayType(new StringType(), new MixedType())
                    : new ActiveRecordObjectType($calledOnType->getModelClass()),
            );
        }

        if ($methodName === 'all') {
            return new ArrayType(
                $calledOnType->hasIndexBy() ? new StringType() : new IntegerType(),
                $calledOnType->isAsArray()
                    ? new ArrayType(new StringType(), new MixedType())
                    : new ActiveRecordObjectType($calledOnType->getModelClass()),
            );
        }

        return $calledOnType;
    }

}
