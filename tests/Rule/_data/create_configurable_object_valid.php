<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

// Config param wasn't used
new MyComponent('string', 123);

// Param passed as a config arg
new MyComponent('string', 123, [
    'privateStringProp' => 'string',
]);

// Param passed as a named arg
new MyComponent('string', config: [
    'privateStringProp' => 'string',
]);

// Some real world usage
new \yii\widgets\DetailView([
    'model' => new \ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article(),
    'attributes' => [
        'id',
        'text',
    ],
]);
