<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;

final class ActiveRecordRelationGetterReturnTypeExtension implements DynamicMethodReturnTypeExtension {

    public function getClass(): string {
        return BaseActiveRecord::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool {
        if (!str_starts_with($methodReflection->getName(), 'get')) {
            return false;
        }

        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        if (!$returnType instanceof ObjectType) {
            return false;
        }

        return is_a($returnType->getClassName(), ActiveQuery::class, true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        if ($returnType instanceof ActiveQueryObjectType) {
            return $returnType;
        }

        if (!$returnType instanceof ObjectType) {
            throw new ShouldNotHappenException(sprintf('Unexpected type %s during method call %s at line %d', get_class($returnType), $methodReflection->getName(), $methodCall->getLine()));
        }

        /** @var ObjectType $arType */
        $arType = $returnType->getTemplateType(ActiveQuery::class, 'T');

        return new ActiveQueryObjectType($arType, $returnType->getClassName());
    }

}
