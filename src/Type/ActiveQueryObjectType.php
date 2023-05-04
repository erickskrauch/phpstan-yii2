<?php
declare(strict_types=1);

namespace Proget\PHPStan\Yii2\Type;

use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use yii\db\ActiveQueryInterface;

final class ActiveQueryObjectType extends ObjectType {

    private string $modelClass;

    private bool $asArray;

    public function __construct(
        string $modelClass,
        string $className = ActiveQueryInterface::class,
        bool $asArray = false
    ) {
        parent::__construct($className);

        $this->modelClass = $modelClass;
        $this->asArray = $asArray;
    }

    public function getModelClass(): string {
        return $this->modelClass;
    }

    public function isAsArray(): bool {
        return $this->asArray;
    }

    public function describe(VerbosityLevel $level): string {
        return sprintf('%s<%s>', parent::describe($level), $this->modelClass);
    }

}
