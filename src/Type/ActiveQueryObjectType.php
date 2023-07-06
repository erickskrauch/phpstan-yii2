<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Type;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use yii\db\ActiveQueryInterface;

final class ActiveQueryObjectType extends GenericObjectType {

    private ObjectType $model;

    private bool $asArray;

    private bool $hasIndexBy;

    public function __construct(
        ObjectType $model,
        string $className = ActiveQueryInterface::class,
        bool $asArray = false,
        bool $hasIndexBy = false
    ) {
        parent::__construct($className, [$model]);

        $this->model = $model;
        $this->asArray = $asArray;
        $this->hasIndexBy = $hasIndexBy;
    }

    public function getModel(): ObjectType {
        return $this->model;
    }

    public function isAsArray(): bool {
        return $this->asArray;
    }

    public function hasIndexBy(): bool {
        return $this->hasIndexBy;
    }

}
