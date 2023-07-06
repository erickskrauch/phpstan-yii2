<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\CommentsQuery;
use yii\db\ActiveQuery;
use function PHPStan\Testing\assertType;

assertType(ActiveQuery::class . '<' . Article::class . '>', Article::find());
assertType(CommentsQuery::class . '<' . Comment::class . '>', Comment::find());

$class = Article::class;
if (random_int(0, 10) === 0) {
    $class = Comment::class;
}

// TODO: future scope: "ActiveQuery<Article>|CommentsQuery<Comment>"
assertType(ActiveQuery::class, $class::find());
