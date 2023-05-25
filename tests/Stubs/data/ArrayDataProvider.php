<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\data;

final class ArrayDataProvider {

    /**
     * @return \yii\data\ArrayDataProvider<string, string>
     */
    public function arrayProvider(): \yii\data\ArrayDataProvider {
        /** @phpstan-var \yii\data\ArrayDataProvider<string, string> $provider */
        $provider = new \yii\data\ArrayDataProvider();
        $provider->allModels = ['hello', 'world'];
        $provider->key = fn(string $row) => $row;

        return $provider;
    }

}
