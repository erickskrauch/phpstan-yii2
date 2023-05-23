<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\base;

use yii\base\Component as Yii2Component;
use yii\behaviors\TimestampBehavior;

final class Component extends Yii2Component {

    public function behaviors(): array {
        return [
            new TimestampBehavior(),
            TimestampBehavior::class,
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'createdAt',
            ],
            [
                '__class' => TimestampBehavior::class,
                'updatedAtAttribute' => 'updatedAt',
            ],
            fn() => new TimestampBehavior(),
        ];
    }

}
