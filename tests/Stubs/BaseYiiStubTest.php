<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs;

use ErickSkrauch\PHPStan\Yii2\Tests\ConfigTrait;
use PHPStan\Testing\TypeInferenceTestCase;

final class BaseYiiStubTest extends TypeInferenceTestCase {
    use ConfigTrait;

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable {
        yield from self::gatherAssertTypes(__DIR__ . '/_data/base-yii.php');
    }

    /**
     * @param mixed $args
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, ...$args): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

}
