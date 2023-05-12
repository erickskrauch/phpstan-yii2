<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property boolean $flag
 */
final class Article extends ActiveRecord {

    /**
     * @return CommentsQuery<Comment>
     */
    public function getComments(): CommentsQuery {
        return $this->hasMany(Comment::class, ['id' => 'id']);
    }

}
