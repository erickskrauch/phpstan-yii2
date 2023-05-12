<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2;

use PHPStan\Testing\TypeInferenceTestCase;

abstract class AbstractTypeInferenceTestCase extends TypeInferenceTestCase {

    public static function getAdditionalConfigFiles(): array {
        return [__DIR__ . '/phpstan.neon'];
    }

}
