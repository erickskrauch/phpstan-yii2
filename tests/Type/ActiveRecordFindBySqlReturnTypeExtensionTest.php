<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Type;

use ErickSkrauch\PHPStan\Yii2\Tests\ConfigTrait;
use PHPStan\Testing\TypeInferenceTestCase;

/**
 * @covers \ErickSkrauch\PHPStan\Yii2\Type\ActiveRecordFindBySqlReturnTypeExtension
 * @covers \ErickSkrauch\PHPStan\Yii2\Type\ActiveQueryObjectType
 */
final class ActiveRecordFindBySqlReturnTypeExtensionTest extends TypeInferenceTestCase {
    use ConfigTrait;

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable {
        yield from self::gatherAssertTypes(__DIR__ . '/_data/active-record-find-by-sql-return-type.php');
    }

    /**
     * @param mixed $args
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, ...$args): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

}
