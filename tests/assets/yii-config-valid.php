<?php
declare(strict_types=1);

use Proget\Tests\PHPStan\Yii2\Yii\FirstActiveRecord;

return [
    'components' => [
        'customComponent' => [
            'class' => FirstActiveRecord::class,
        ],
        'customInitializedComponent' => new FirstActiveRecord(),
        'componentToContainer' => yii\caching\CacheInterface::class,
    ],
    'container' => [
        'singletons' => [
            'singleton-string' => FirstActiveRecord::class,
            Stringable::class => fn() => new class implements Stringable {
                public function __toString(): string {
                    return '';
                }
            },
        ],
        'definitions' => [
            'closure' => function(): SplStack {
                return new SplStack();
            },
            'service' => [
                'class' => SplObjectStorage::class,
            ],
            FirstActiveRecord::class => [
                'flag' => 'foo',
            ],
            Throwable::class => Exception::class,
        ],
    ],
];
