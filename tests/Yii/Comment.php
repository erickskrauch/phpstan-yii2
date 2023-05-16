<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $article_id
 * @property string $field
 */
final class Comment extends ActiveRecord {

    public static function find(): CommentsQuery {
        return new CommentsQuery(self::class);
    }

    public static function findById(string $id): ?self {
        return self::find()->notDeletedSelf()->notDeletedStatic()->notDeletedStatic()->limit(1)->one();
    }

    /**
     * @return ActiveQuery<Article>
     */
    public function getArticle(): ActiveQuery {
        return $this->hasOne(Article::class, ['id' => 'article_id']);
    }

}
