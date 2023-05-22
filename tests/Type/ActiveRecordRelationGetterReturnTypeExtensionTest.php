<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Type;

use ErickSkrauch\PHPStan\Yii2\Tests\AbstractTypeInferenceTestCase;

/**
 * @covers \ErickSkrauch\PHPStan\Yii2\Type\ActiveRecordRelationGetterReturnTypeExtension
 * @covers \ErickSkrauch\PHPStan\Yii2\Type\ActiveQueryObjectType
 */
final class ActiveRecordRelationGetterReturnTypeExtensionTest extends AbstractTypeInferenceTestCase {

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable {
        yield from self::gatherAssertTypes(__DIR__ . '/data/active-record-relation-getter-return-type.php');
    }

    /**
     * @param mixed $args
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, ...$args): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

}
