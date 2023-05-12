<?php
declare(strict_types=1);

use function PHPStan\Testing\assertType;

assertType(stdClass::class, Yii::createObject(stdClass::class));
assertType(stdClass::class, Yii::createObject(['class' => stdClass::class]));
assertType(stdClass::class, Yii::createObject(['__class' => stdClass::class]));
assertType(stdClass::class, Yii::createObject(function() {
    return new stdClass();
}));
assertType(stdClass::class, Yii::createObject(fn() => new stdClass()));
