<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject([
    'class' => MyComponent::class,
    'publicStringProp' => 'string',
    'publicArrayProp' => ['key' => 'hello world'],
    'privateStringProp' => 'private value',
]);
