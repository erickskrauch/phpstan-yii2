<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject(MyComponent::class, [123, 'string']);
Yii::createObject(MyComponent::class, [
    'stringArg' => 123,
    'intArg' => 'string',
]);
