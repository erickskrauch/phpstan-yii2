<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject([
    'class' => MyComponent::class,
    'publicStringProp' => 123,
    'publicArrayProp' => ['key' => false],
    'privateStringProp' => 712,
]);
