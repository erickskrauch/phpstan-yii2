<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property boolean $flag
 */
final class Article extends ActiveRecord {

    public function getSelf(): ActiveQuery {
        return $this->hasOne(self::class, ['id' => 'id']);
    }

    public function getComments(): ActiveQuery {
        return $this->hasMany(Comment::class, ['id' => 'id']);
    }

}
