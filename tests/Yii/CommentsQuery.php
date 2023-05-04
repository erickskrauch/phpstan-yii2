<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use yii\db\ActiveQuery;

final class CommentsQuery extends ActiveQuery {

    public function notDeletedSelf(): self {
        return $this->andWhere(['is_deleted' => false]);
    }

    public function notDeletedStatic(): static {
        return $this->andWhere(['is_deleted' => false]);
    }

    /**
     * @return $this
     */
    public function notDeletedThis() {
        return $this->andWhere(['is_deleted' => false]);
    }

}
