<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\BarComponent;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;

foreach ([Article::class, Comment::class] as $className) {
    Yii::createObject([
        'class' => $className,
        'field' => 'available only on Comment',
    ]);
}

$className = random_int(0, 1) ? MyComponent::class : BarComponent::class;
Yii::createObject($className, ['string']);
Yii::createObject([
    'class' => $className,
    '__construct()' => [
        'stringArg' => 'string',
    ],
]);
