<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\db\ActiveQueryInterface;
use yii\db\BatchQueryResult;

final class ActiveQueryBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension {

    public function getClass(): string {
        return ActiveQueryInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool {
        // This condition is necessary to process the query builder and not to lose the model during its work
        $type = ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants())->getReturnType();
        if ((new ObjectType(ActiveQueryInterface::class))->isSuperTypeOf($type)->yes()) {
            return true;
        }

        return in_array($methodReflection->getName(), ['one', 'all', 'batch', 'each'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type {
        $calledOnType = $scope->getType($methodCall->var);
        if (!$calledOnType instanceof ActiveQueryObjectType) {
            return ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants())->getReturnType();
        }

        $methodName = $methodReflection->getName();
        if ($methodName === 'asArray') {
            $argType = isset($methodCall->args[0]) && $methodCall->args[0] instanceof Arg
                ? $scope->getType($methodCall->args[0]->value)
                : new ConstantBooleanType(true);

            return new ActiveQueryObjectType(
                $calledOnType->getReturnType(),
                $calledOnType->getClassName(),
                $argType->isTrue()->yes(),
                $calledOnType->hasIndexBy(),
            );
        }

        if ($methodName === 'indexBy') {
            $argType = $scope->getType($methodCall->getArgs()[0]->value);

            return new ActiveQueryObjectType(
                $calledOnType->getReturnType(),
                $calledOnType->getClassName(),
                $calledOnType->isAsArray(),
                !$argType->isNull()->yes(),
            );
        }

        if ($methodName === 'one') {
            return TypeCombinator::union(
                new NullType(),
                $calledOnType->isAsArray()
                    ? new ArrayType(new StringType(), new MixedType())
                    : $calledOnType->getReturnType(),
            );
        }

        if ($methodName === 'all') {
            return new ArrayType(
                $calledOnType->hasIndexBy() ? new StringType() : new IntegerType(),
                $calledOnType->isAsArray()
                    ? new ArrayType(new StringType(), new MixedType())
                    : $calledOnType->getReturnType(),
            );
        }

        if ($methodName === 'batch') {
            return new GenericObjectType(
                BatchQueryResult::class,
                [
                    new IntegerType(),
                    new ArrayType(
                        $calledOnType->hasIndexBy() ? new StringType() : new IntegerType(),
                        $calledOnType->isAsArray()
                            ? new ArrayType(new StringType(), new MixedType())
                            : $calledOnType->getReturnType(),
                    ),
                ],
            );
        }

        if ($methodName === 'each') {
            return new GenericObjectType(
                BatchQueryResult::class,
                [
                    $calledOnType->hasIndexBy() ? new StringType() : new IntegerType(),
                    $calledOnType->isAsArray()
                        ? new ArrayType(new StringType(), new MixedType())
                        : $calledOnType->getReturnType(),
                ],
            );
        }

        return $calledOnType;
    }

}
