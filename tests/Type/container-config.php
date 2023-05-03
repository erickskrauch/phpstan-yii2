<?php
declare(strict_types=1);

return [
    'components' => [
        'simple' => new stdClass(),
        'request' => [
            'class' => yii\web\Request::class,
        ],
    ],
    'container' => [
        'singletons' => [
            DateTimeInterface::class => new DateTime(),
        ],
        'definitions' => [
            Iterator::class => fn() => new ArrayIterator(),
        ],
    ],
];
