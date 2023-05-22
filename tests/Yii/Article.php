<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Yii;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $text
 */
final class Article extends ActiveRecord {

    public function getComments(): CommentsQuery {
        return $this->hasMany(Comment::class, ['article_id' => 'id']);
    }

    public function getTopComment(): ?Comment {
        return $this->getComments()
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

}
