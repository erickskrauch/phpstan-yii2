<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject([
    'class' => MyComponent::class,
    'readonlyPhpDocStringProp' => '321',
    'readonlyFunctionProp' => '123',
]);
