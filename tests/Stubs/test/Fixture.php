<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\test;

use yii\test\ActiveFixture;

final class Fixture extends \yii\test\Fixture {

    public $depends = [
        ActiveFixture::class,
    ];

}
