<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\data;

final class SqlDataProvider {

    /**
     * @return \yii\data\SqlDataProvider<string, array{id: string}>
     */
    public function sqlProvider(): \yii\data\SqlDataProvider {
        /** @phpstan-var \yii\data\SqlDataProvider<string, array{id: string}> $provider */
        $provider = new \yii\data\SqlDataProvider();
        $provider->sql = 'SELECT * FROM comments';
        $provider->key = fn(array $row) => $row['id'];

        return $provider;
    }

}
