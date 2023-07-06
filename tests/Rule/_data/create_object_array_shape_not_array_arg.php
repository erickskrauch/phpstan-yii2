<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject(MyComponent::class);
Yii::createObject(function() {
    return new MyComponent();
});
