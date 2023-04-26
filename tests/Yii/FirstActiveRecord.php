<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property boolean $flag
 */
final class FirstActiveRecord extends ActiveRecord {

    public function getSelf(): ActiveQuery {
        return $this->hasOne(self::class, ['id' => 'id']);
    }

    public function getSecond(): ActiveQuery {
        return $this->hasOne(SecondActiveRecord::class, ['id' => 'id']);
    }

}
