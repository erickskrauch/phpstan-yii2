<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Type;

/**
 * @covers \Proget\PHPStan\Yii2\Type\ContainerDynamicMethodReturnTypeExtension
 */
final class ContainerDynamicMethodReturnTypeExtensionTest extends AbstractTypeInferenceTestCase {

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable {
        yield from self::gatherAssertTypes(__DIR__ . '/data/container-dynamic-method-return-type.php');
    }

    /**
     * @param mixed $args
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, ...$args): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

}
