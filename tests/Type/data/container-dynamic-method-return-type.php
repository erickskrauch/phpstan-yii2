<?php
declare(strict_types=1);

use function PHPStan\Testing\assertType;

assertType(DateTime::class, Yii::$container->get(DateTimeInterface::class));
assertType(Iterator::class, Yii::$container->get(Iterator::class));
