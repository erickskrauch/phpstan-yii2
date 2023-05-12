<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Type;

use Proget\Tests\PHPStan\Yii2\AbstractTypeInferenceTestCase;

/**
 * @covers \Proget\PHPStan\Yii2\Type\ActiveQueryObjectType
 * @covers \Proget\PHPStan\Yii2\Type\ActiveRecordFindReturnTypeExtension
 * @covers \Proget\PHPStan\Yii2\Type\ActiveQueryBuilderReturnTypeExtension
 */
final class ActiveQueryBuilderReturnTypeExtensionTest extends AbstractTypeInferenceTestCase {

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable {
        yield from self::gatherAssertTypes(__DIR__ . '/data/active-query-builder-return-type.php');
    }

    /**
     * @param mixed $args
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, ...$args): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

}
