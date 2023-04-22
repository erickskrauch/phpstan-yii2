<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2;

use InvalidArgumentException;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;
use Proget\PHPStan\Yii2\ServiceMap;
use Proget\Tests\PHPStan\Yii2\Yii\MyActiveRecord;
use RuntimeException;
use SplObjectStorage;
use SplStack;

final class ServiceMapTest extends TestCase {

    public function testThrowExceptionWhenConfigurationFileDoesNotExist(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided config path invalid-path must exist');

        new ServiceMap('invalid-path');
    }

    public function testThrowExceptionWhenClosureServiceHasMissingReturnType(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please provide return type for no-return-type service closure');

        new ServiceMap(__DIR__ . '/assets/yii-config-invalid.php');
    }

    public function testThrowExceptionWhenServiceHasUnsupportedType(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported definition for unsupported-type');

        new ServiceMap(__DIR__ . '/assets/yii-config-invalid-unsupported-type.php');
    }

    public function testThrowExceptionWhenServiceHasUnsupportedArray(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported definition for unsupported-array');

        new ServiceMap(__DIR__ . '/assets/yii-config-invalid-unsupported-array.php');
    }

    public function testThrowExceptionWhenComponentHasInvalidValue(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported definition for customComponent');

        new ServiceMap(__DIR__ . '/assets/yii-config-invalid-component.php');
    }

    public function testItLoadsServicesAndComponents(): void {
        $serviceMap = new ServiceMap(__DIR__ . '/assets/yii-config-valid.php');

        $this->assertSame(MyActiveRecord::class, $serviceMap->getServiceClassFromNode(new String_('singleton-string')));
        $this->assertSame(MyActiveRecord::class, $serviceMap->getServiceClassFromNode(new String_(MyActiveRecord::class)));
        $this->assertSame(SplStack::class, $serviceMap->getServiceClassFromNode(new String_('singleton-closure')));
        $this->assertSame(SplObjectStorage::class, $serviceMap->getServiceClassFromNode(new String_('singleton-service')));

        $this->assertSame(SplStack::class, $serviceMap->getServiceClassFromNode(new String_('closure')));
        $this->assertSame(SplObjectStorage::class, $serviceMap->getServiceClassFromNode(new String_('service')));

        $this->assertSame(MyActiveRecord::class, $serviceMap->getComponentClassById('customComponent'));
        $this->assertSame(MyActiveRecord::class, $serviceMap->getComponentClassById('customInitializedComponent'));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testItAllowsConfigWithoutSingletons(): void {
        new ServiceMap(__DIR__ . '/assets/yii-config-no-singletons.php');
    }

}
