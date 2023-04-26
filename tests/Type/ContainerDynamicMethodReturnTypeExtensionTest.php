<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Type;

use PHPStan\Testing\TypeInferenceTestCase;

/**
 * @covers \Proget\PHPStan\Yii2\Type\ContainerDynamicMethodReturnTypeExtension
 */
final class ContainerDynamicMethodReturnTypeExtensionTest extends TypeInferenceTestCase {

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

    public static function getAdditionalConfigFiles(): array {
        return [/* __DIR__ . '/../../extension.neon', */__DIR__ . '/phpstan.neon'];
    }

}
