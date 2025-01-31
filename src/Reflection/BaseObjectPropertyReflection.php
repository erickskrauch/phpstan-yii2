<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;

final class BaseObjectPropertyReflection implements PropertyReflection {

    private ?MethodReflection $getter;

    private ?MethodReflection $setter;

    public function __construct(?MethodReflection $getter, ?MethodReflection $setter) {
        $this->getter = $getter;
        $this->setter = $setter;
    }

    public function getDeclaringClass(): ClassReflection {
        if ($this->getter) {
            return $this->getter->getDeclaringClass();
        }

        if ($this->setter) {
            return $this->setter->getDeclaringClass();
        }

        throw new ShouldNotHappenException('At least one getter or setter must be presented');
    }

    public function isStatic(): bool {
        return false;
    }

    public function isPrivate(): bool {
        return false;
    }

    public function isPublic(): bool {
        return true;
    }

    public function getDocComment(): ?string {
        return null;
    }

    public function getReadableType(): Type {
        if ($this->getter === null) {
            return new NeverType();
        }

        return ParametersAcceptorSelector::combineAcceptors($this->getter->getVariants())->getReturnType();
    }

    public function getWritableType(): Type {
        if ($this->setter === null) {
            return new NeverType();
        }

        /** @var \PHPStan\Reflection\ParameterReflection[] $params */
        $params = ParametersAcceptorSelector::combineAcceptors($this->setter->getVariants())->getParameters();
        if (!isset($params[0])) {
            throw new ShouldNotHappenException("Getter doesn't accept any arguments");
        }

        return $params[0]->getType();
    }

    public function canChangeTypeAfterAssignment(): bool {
        return false;
    }

    public function isReadable(): bool {
        return $this->getter !== null;
    }

    public function isWritable(): bool {
        return $this->setter !== null;
    }

    public function isDeprecated(): TrinaryLogic {
        $result = TrinaryLogic::createNo();
        if ($this->getter !== null) {
            $result = $result->or($this->getter->isDeprecated());
        }

        if ($this->setter !== null) {
            $result = $result->or($this->setter->isDeprecated());
        }

        return $result;
    }

    public function getDeprecatedDescription(): ?string {
        if ($this->getter !== null && $this->getter->getDeprecatedDescription()) {
            return $this->getter->getDeprecatedDescription();
        }

        if ($this->setter !== null && $this->setter->getDeprecatedDescription()) {
            return $this->setter->getDeprecatedDescription();
        }

        return null;
    }

    public function isInternal(): TrinaryLogic {
        $result = TrinaryLogic::createNo();
        if ($this->getter !== null) {
            $result = $result->or($this->getter->isInternal());
        }

        if ($this->setter !== null) {
            $result = $result->or($this->setter->isInternal());
        }

        return $result;
    }

}
