<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use yii\db\ActiveQueryInterface;

final class ActiveQueryObjectType extends GenericObjectType {

    private Type $returnType;

    private bool $asArray;

    private bool $hasIndexBy;

    public function __construct(
        Type $returnType,
        string $className = ActiveQueryInterface::class,
        bool $asArray = false,
        bool $hasIndexBy = false
    ) {
        parent::__construct($className, [$returnType]);
        $this->returnType = $returnType;
        $this->asArray = $asArray;
        $this->hasIndexBy = $hasIndexBy;
    }

    public function getReturnType(): Type {
        return $this->returnType;
    }

    public function isAsArray(): bool {
        return $this->asArray;
    }

    public function hasIndexBy(): bool {
        return $this->hasIndexBy;
    }

}
