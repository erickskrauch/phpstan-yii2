<?php
declare(strict_types=1);

use Proget\Tests\PHPStan\Yii2\Yii\Article;
use Proget\Tests\PHPStan\Yii2\Yii\Comment;
use Proget\Tests\PHPStan\Yii2\Yii\CommentsQuery;
use yii\db\ActiveQuery;
use function PHPStan\Testing\assertType;

assertType(ActiveQuery::class . '<' . Article::class . '>', Comment::instance()->hasOne(Article::class, []));
assertType(ActiveQuery::class . '<' . Article::class . '>', Comment::instance()->getArticle());
assertType(CommentsQuery::class, Article::instance()->getComments());
