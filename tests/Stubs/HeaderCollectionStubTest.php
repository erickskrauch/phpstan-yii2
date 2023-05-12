<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Stubs;

use Proget\Tests\PHPStan\Yii2\AbstractTypeInferenceTestCase;

final class HeaderCollectionStubTest extends AbstractTypeInferenceTestCase {

    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable {
        yield from self::gatherAssertTypes(__DIR__ . '/data/header-collection.php');
    }

    /**
     * @param mixed $args
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, ...$args): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

}
