<?php
declare(strict_types=1);

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article;
use function PHPStan\Testing\assertType;

// Isset
assertType('bool', isset(Article::find()->one()['id']));
assertType('bool', isset(Article::find()->one()['text']));

assertType('bool', isset(Article::findBySql('')->one()['id']));
assertType('bool', isset(Article::findBySql('')->one()['text']));

// Read
assertType('int', Article::find()->one()['id']);
assertType('string', Article::find()->one()['text']);

assertType('int', Article::findBySql('')->one()['id']);
assertType('string', Article::findBySql('')->one()['text']);

// Write
$article = Article::find()->one();
$article['id'] = 123;
$article['text'] = 'mock text';
