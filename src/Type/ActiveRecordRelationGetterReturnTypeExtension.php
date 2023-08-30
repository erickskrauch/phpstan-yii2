<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;

final class ActiveRecordRelationGetterReturnTypeExtension implements DynamicMethodReturnTypeExtension {

    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getClass(): string {
        return BaseActiveRecord::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool {
        if (!str_starts_with($methodReflection->getName(), 'get')) {
            return false;
        }

        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        if (!$returnType->isObject()->yes()) {
            return false;
        }

        foreach ($returnType->getObjectClassNames() as $className) {
            if (!$this->reflectionProvider->getClass($className)->is(ActiveQuery::class)) {
                return false;
            }
        }

        return true;
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        if ($returnType instanceof ActiveQueryObjectType) {
            return $returnType;
        }

        if (!$returnType->isObject()->yes()) {
            throw new ShouldNotHappenException(sprintf('Unexpected type %s during method call %s at line %d', get_class($returnType), $methodReflection->getName(), $methodCall->getLine()));
        }

        $arType = $returnType->getTemplateType(ActiveQuery::class, 'T');
        if (!$arType->isObject()->yes()) {
            throw new ShouldNotHappenException(sprintf('Unexpected type %s during method call %s at line %d', get_class($arType), $methodReflection->getName(), $methodCall->getLine()));
        }

        $types = [];
        foreach ($arType->getObjectClassNames() as $arClassName) {
            foreach ($returnType->getObjectClassNames() as $className) {
                $types[] = new ActiveQueryObjectType(new ActiveRecordObjectType($arClassName), $className);
            }
        }

        return TypeCombinator::union(...$types);
    }

}
