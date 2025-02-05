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
        $provider->key = function(array $row): string {
            if (!array_key_exists('id', $row) || !is_string($row['id'])) {
                throw new \InvalidArgumentException('Invalid row data');
            }

            return $row['id'];
        };

        return $provider;
    }

}
