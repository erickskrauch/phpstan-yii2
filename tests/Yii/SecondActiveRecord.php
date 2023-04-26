<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $field
 */
final class SecondActiveRecord extends ActiveRecord {

    public function getFirst(): ActiveQuery {
        return $this->hasMany(FirstActiveRecord::class, ['id' => 'id']);
    }

}
