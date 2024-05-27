<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\db\ActiveRecordInterface;

abstract class AbstractActiveRecordFindReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension {

    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getClass(): string {
        return ActiveRecordInterface::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool {
        return $methodReflection->getName() === static::methodName();
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope
    ): ?Type {
        $calledOn = $methodCall->class;
        if ($calledOn instanceof Name) {
            return $this->createType($scope->resolveName($calledOn), $scope);
        }

        $types = [];
        if ($calledOn instanceof Variable) {
            foreach ($scope->getType($calledOn)->getConstantStrings() as $constantString) {
                if (!$constantString->isClassStringType()->yes()) {
                    return new NeverType();
                }

                $types[] = $this->createType($constantString->getValue(), $scope);
            }

            return TypeCombinator::union(...$types);
        }

        return null;
    }

    abstract protected function methodName(): string;

    private function createType(string $modelClass, Scope $scope): Type {
        $method = $this->reflectionProvider->getClass($modelClass)->getMethod('find', $scope);
        $returnType = ParametersAcceptorSelector::selectSingle($method->getVariants())->getReturnType();
        if (!$returnType->isObject()->yes()) {
            throw new ShouldNotHappenException();
        }

        $types = [];
        foreach ($returnType->getObjectClassNames() as $className) {
            $types[] = new ActiveQueryObjectType(new ActiveRecordObjectType($modelClass), $className);
        }

        return TypeCombinator::union(...$types);
    }

}
