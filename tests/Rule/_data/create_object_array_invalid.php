<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject([
    'class' => MyComponent::class,
    '__construct()' => [
        'stringArg' => 123,
        'intArg' => 'string',
    ],
    'publicStringProp' => 123,
    'publicArrayProp' => ['key' => false],
    'privateStringProp' => 712,
]);

Yii::createObject([
    'class' => MyComponent::class,
    '__construct()' => [
        'notExists' => 123,
    ],
]);

Yii::createObject([
    'class' => MyComponent::class,
    '__construct()' => [
        'stringArg' => 'string',
        1 => 123,
    ],
]);
