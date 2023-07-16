<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject(MyComponent::class, ['string', 123]);
Yii::createObject(MyComponent::class, [
    'stringArg' => 'string',
    'intArg' => 123,
]);
