<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\data;

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment;

final class ActiveDataProvider {

    /**
     * @return \yii\data\ActiveDataProvider<int, Comment>
     */
    public function activeProvider(): \yii\data\ActiveDataProvider {
        /** @phpstan-var \yii\data\ActiveDataProvider<int, Comment> $provider */
        $provider = new \yii\data\ActiveDataProvider();
        $provider->query = Comment::find();
        $provider->key = fn(Comment $row) => $row->id;

        return $provider;
    }

}
