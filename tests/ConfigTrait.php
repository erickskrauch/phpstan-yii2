<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests;

trait ConfigTrait {

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array {
        return [__DIR__ . '/phpstan.neon'];
    }

}
