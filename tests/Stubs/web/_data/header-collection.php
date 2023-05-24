<?php
declare(strict_types=1);

use yii\web\HeaderCollection;
use function PHPStan\Testing\assertType;

$collection = new HeaderCollection();

assertType('string|null', $collection->get('Content-Length'));
assertType('int|string', $collection->get('Content-Length', 0, true));
assertType('non-empty-array<string>|string', $collection->get('X-Key', '', false));
