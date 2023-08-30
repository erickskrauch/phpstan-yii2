<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment;

foreach ([Article::class, Comment::class] as $className) {
    Yii::createObject([
        'class' => $className,
    ]);
}
