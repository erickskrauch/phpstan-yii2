<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $field
 */
final class Comment extends ActiveRecord {

    public static function find(): CommentsQuery {
        return new CommentsQuery(self::class);
    }

    /**
     * @return ActiveQuery<Article>
     */
    public function getArticle(): ActiveQuery {
        return $this->hasOne(Article::class, ['id' => 'id']);
    }

}
