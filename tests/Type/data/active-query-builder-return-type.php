<?php
declare(strict_types=1);

use Proget\Tests\PHPStan\Yii2\Yii\Article;
use Proget\Tests\PHPStan\Yii2\Yii\Comment;
use function PHPStan\Testing\assertType;

assertType(Article::class . '|null', Article::find()->one());
assertType('array<int, ' . Article::class . '>', Article::find()->all());
assertType('array<int, ' . Comment::class . '>', Comment::find()->andWhere(['user_id' => 123])->all());
assertType('array<int, ' . Comment::class . '>', Comment::find()->notDeletedSelf()->all());
assertType('array<int, ' . Comment::class . '>', Comment::find()->notDeletedStatic()->all());
assertType('array<int, ' . Comment::class . '>', Comment::find()->notDeletedThis()->all());
assertType('array<string, mixed>|null', Comment::find()->asArray()->one());
assertType('array<int, array<string, mixed>>', Comment::find()->asArray()->all());
