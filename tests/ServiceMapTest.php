<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests;

use ErickSkrauch\PHPStan\Yii2\ServiceMap;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article;
use Exception;
use InvalidArgumentException;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplObjectStorage;
use SplStack;
use Stringable;
use Throwable;
use yii\caching\CacheInterface;
use yii\web\Request;
use yii\web\Response;

final class ServiceMapTest extends TestCase {

    public function testItLoadsServicesAndComponents(): void {
        $serviceMap = new ServiceMap(__DIR__ . '/assets/yii-config-valid.php');

        // Singletons
        $this->assertSame(Article::class, $serviceMap->getServiceClassFromNode(new String_('singleton-string')));
        $this->assertSame(Article::class, $serviceMap->getServiceClassFromNode(new String_(Article::class)));
        $this->assertSame(Exception::class, $serviceMap->getServiceClassFromNode(new String_(Throwable::class)));
        $this->assertSame(Stringable::class, $serviceMap->getServiceClassFromNode(new String_(Stringable::class)));

        // Definitions
        $this->assertSame(SplStack::class, $serviceMap->getServiceClassFromNode(new String_('closure')));
        $this->assertSame(SplObjectStorage::class, $serviceMap->getServiceClassFromNode(new String_('service')));

        // Components
        $this->assertSame(Article::class, $serviceMap->getComponentClassById('customComponent'));
        $this->assertSame(Article::class, $serviceMap->getComponentClassById('customInitializedComponent'));
        $this->assertSame(CacheInterface::class, $serviceMap->getComponentClassById('componentToContainer'));
        $this->assertSame(Request::class, $serviceMap->getComponentClassById('request'));
        $this->assertSame(Response::class, $serviceMap->getComponentClassById('response'));
        $this->assertSame(\yii\caching\ArrayCache::class, $serviceMap->getComponentClassById('cache'));
    }

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

    /**
     * @doesNotPerformAssertions
     */
    public function testItAllowsConfigWithoutSingletons(): void {
        new ServiceMap(__DIR__ . '/assets/yii-config-no-singletons.php');
    }

}
