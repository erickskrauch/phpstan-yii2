<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests;

use PHPStan\Testing\TypeInferenceTestCase;

abstract class AbstractTypeInferenceTestCase extends TypeInferenceTestCase {

    public static function getAdditionalConfigFiles(): array {
        return [__DIR__ . '/phpstan.neon'];
    }

}
