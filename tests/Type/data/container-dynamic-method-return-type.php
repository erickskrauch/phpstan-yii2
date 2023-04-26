<?php
declare(strict_types=1);

use yii\web\Request;
use function PHPStan\Testing\assertType;

assertType(stdClass::class, Yii::$container->get('simple'));
assertType(Request::class, Yii::$app->request);
