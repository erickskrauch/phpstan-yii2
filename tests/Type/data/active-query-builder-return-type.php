<?php
declare(strict_types=1);

use Proget\Tests\PHPStan\Yii2\Yii\Article;
use Proget\Tests\PHPStan\Yii2\Yii\Comment;
use yii\db\BatchQueryResult;
use function PHPStan\Testing\assertType;

// Simple cases
assertType(Article::class . '|null', Article::find()->one());
assertType('array<int, ' . Article::class . '>', Article::find()->all());

// Preserve when built-in filtering
assertType('array<int, ' . Comment::class . '>', Comment::find()->andWhere(['user_id' => 123])->all());

// Preserve when custom filter
assertType('array<int, ' . Comment::class . '>', Comment::find()->notDeletedSelf()->all());
assertType('array<int, ' . Comment::class . '>', Comment::find()->notDeletedStatic()->all());
assertType('array<int, ' . Comment::class . '>', Comment::find()->notDeletedThis()->all());

// As array
assertType('array<string, mixed>|null', Comment::find()->asArray()->one());
assertType('array<int, array<string, mixed>>', Comment::find()->asArray()->all());

// Index by
assertType('array<string, ' . Comment::class . '>', Comment::find()->indexBy('user_id')->all());
assertType('array<string, ' . Comment::class . '>', Comment::find()->indexBy(fn() => 'key')->all());
assertType('array<string, array<string, mixed>>', Comment::find()->asArray()->indexBy('user_id')->all());
assertType('array<string, array<string, mixed>>', Comment::find()->asArray()->indexBy(fn() => 'key')->all());
assertType('array<int, ' . Comment::class . '>', Comment::find()->indexBy(null)->all());
assertType('array<int, array<string, mixed>>', Comment::find()->asArray()->indexBy(null)->all());

// Batch
assertType(BatchQueryResult::class . '<int, array<int, ' . Comment::class . '>>', Comment::find()->batch(250));
assertType(BatchQueryResult::class . '<int, array<int, array<string, mixed>>>', Comment::find()->asArray()->batch(250));
assertType(BatchQueryResult::class . '<int, array<string, ' . Comment::class . '>>', Comment::find()->indexBy('user_id')->batch(250));
assertType(BatchQueryResult::class . '<int, array<string, array<string, mixed>>>', Comment::find()->asArray()->indexBy('user_id')->batch(250));

// Each
assertType(BatchQueryResult::class . '<int, ' . Comment::class . '>', Comment::find()->each(250));
assertType(BatchQueryResult::class . '<int, array<string, mixed>>', Comment::find()->asArray()->each(250));
assertType(BatchQueryResult::class . '<string, ' . Comment::class . '>', Comment::find()->indexBy('user_id')->each(250));
assertType(BatchQueryResult::class . '<string, array<string, mixed>>', Comment::find()->asArray()->indexBy('user_id')->each(250));
