<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\CommentsQuery;
use yii\db\ActiveQuery;
use function PHPStan\Testing\assertType;

assertType(ActiveQuery::class . '<' . Article::class . '>', Comment::instance()->hasOne(Article::class, []));
assertType(ActiveQuery::class . '<' . Article::class . '>', Comment::instance()->hasMany(Article::class, []));
assertType(CommentsQuery::class . '<' . Comment::class . '>', Article::instance()->hasOne(Comment::class, []));
assertType(CommentsQuery::class . '<' . Comment::class . '>', Article::instance()->hasMany(Comment::class, []));
