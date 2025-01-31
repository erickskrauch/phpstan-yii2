<?php
declare(strict_types=1);

use yii\base\Model;
use function PHPStan\Testing\assertType;

$model = new Model();

// getErrors()
assertType('array<string, array<string>>', $model->getErrors());
assertType('list<string>', $model->getErrors('attribute'));

// getFirstErrors()
assertType('array<string, string>', $model->getFirstErrors());
