<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Yii;

use yii\db\ActiveQuery;

/**
 * @extends ActiveQuery<Comment>
 */
final class CommentsQuery extends ActiveQuery {

    public function notDeletedSelf(): self {
        return $this->andWhere(['is_deleted' => false]);
    }

    /**
     * @return static
     */
    public function notDeletedStatic() {
        return $this->andWhere(['is_deleted' => false]);
    }

    /**
     * @return $this
     */
    public function notDeletedThis(): self {
        return $this->andWhere(['is_deleted' => false]);
    }

}
