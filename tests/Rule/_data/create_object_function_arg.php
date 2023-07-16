<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

Yii::createObject(function(): MyComponent {
    return new MyComponent('string', 123);
});
