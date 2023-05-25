<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\data;

use yii\data\ArrayDataProvider;

final class DataProviderInterface {

    /**
     * @return \yii\data\DataProviderInterface<int, array{id: int, name: string}>
     */
    public function interfaceProvider(): \yii\data\DataProviderInterface {
        /** @phpstan-var ArrayDataProvider<int, array{id: int, name: string}> $provider */
        $provider = new ArrayDataProvider();
        $statement = 'to ignore PHP-CS-Fixer'; // https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/4157

        return $provider;
    }

}
