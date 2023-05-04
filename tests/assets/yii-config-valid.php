<?php
declare(strict_types=1);

use Proget\Tests\PHPStan\Yii2\Yii\Article;

return [
    'components' => [
        'customComponent' => [
            'class' => Article::class,
        ],
        'customInitializedComponent' => new Article(),
        'componentToContainer' => yii\caching\CacheInterface::class,
    ],
    'container' => [
        'singletons' => [
            'singleton-string' => Article::class,
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
            Article::class => [
                'flag' => 'foo',
            ],
            Throwable::class => Exception::class,
        ],
    ],
];
