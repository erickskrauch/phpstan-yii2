<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

// Config param wasn't used
new MyComponent('string', 123);

// Param passed as a config arg
new MyComponent('string', 123, [
    'privateStringProp' => 123,
]);

// Param passed as a named arg
new MyComponent('string', config: [
    'privateStringProp' => 123,
]);
