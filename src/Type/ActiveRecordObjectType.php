<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use ArrayAccess;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class ActiveRecordObjectType extends ObjectType {

    public function hasOffsetValueType(Type $offsetType): TrinaryLogic {
        $constantStrings = $offsetType->getConstantStrings();
        if (empty($constantStrings)) {
            return TrinaryLogic::createNo();
        }

        if ($this->isInstanceOf(ArrayAccess::class)->yes()) {
            return TrinaryLogic::lazyExtremeIdentity($constantStrings, function(ConstantStringType $offset): TrinaryLogic {
                return $this->hasProperty($offset->getValue());
            });
        }

        return parent::hasOffsetValueType($offsetType);
    }

    public function getOffsetValueType(Type $offsetType): Type {
        $constantStrings = $offsetType->getConstantStrings();
        if (empty($constantStrings)) {
            throw new ShouldNotHappenException();
        }

        $types = [];
        foreach ($constantStrings as $offset) {
            $types[] = $this->getProperty($offset->getValue(), new OutOfClassScope())->getReadableType();
        }

        return TypeCombinator::union(...$types);
    }

    public function setOffsetValueType(?Type $offsetType, Type $valueType, bool $unionValues = true): Type {
        if ($offsetType === null) {
            return new ErrorType();
        }

        $constantStrings = $offsetType->getConstantStrings();
        if (empty($constantStrings)) {
            throw new ShouldNotHappenException();
        }

        $types = [];
        foreach ($constantStrings as $offset) {
            $types[] = $this->getProperty($offset->getValue(), new OutOfClassScope())->getWritableType();
        }

        return TypeCombinator::union(...$types);
    }

}
