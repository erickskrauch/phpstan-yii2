<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Reflection;

use ErickSkrauch\PHPStan\Yii2\Reflection\BaseObjectPropertiesClassReflectionExtension;
use ErickSkrauch\PHPStan\Yii2\Reflection\BaseObjectPropertyReflection;
use ErickSkrauch\PHPStan\Yii2\Tests\ConfigTrait;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment;
use ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;

/**
 * @covers \ErickSkrauch\PHPStan\Yii2\Reflection\BaseObjectPropertiesClassReflectionExtension
 * @covers \ErickSkrauch\PHPStan\Yii2\Reflection\BaseObjectPropertyReflection
 */
final class BaseObjectPropertiesClassReflectionExtensionTest extends PHPStanTestCase {
    use ConfigTrait;

    /**
     * @dataProvider provideHasPropertyCases
     */
    public function testHasProperty(string $className, string $propertyName, bool $expected): void {
        $reflectionProvider = self::createReflectionProvider();
        $extension = new BaseObjectPropertiesClassReflectionExtension();
        $this->assertSame($expected, $extension->hasProperty($reflectionProvider->getClass($className), $propertyName));
    }

    /**
     * @return iterable<array{class-string, string, bool}>
     */
    public static function provideHasPropertyCases(): iterable {
        yield [MyComponent::class, 'privateStringProp', true];
        yield [MyComponent::class, 'readonlyFunctionProp', true];
        yield 'implicit phpdoc' => [Article::class, 'comments', false];
        yield 'not exists property' => [MyComponent::class, 'unknownProp', false];
    }

    /**
     * @phpstan-param callable(BaseObjectPropertyReflection $propertyReflection): void $assert
     * @dataProvider provideGetPropertyCases
     */
    public function testGetProperty(string $className, string $propertyName, callable $assert): void {
        $reflectionProvider = self::createReflectionProvider();
        $extension = new BaseObjectPropertiesClassReflectionExtension();
        $assert($extension->getProperty($reflectionProvider->getClass($className), $propertyName));
    }

    /**
     * @return iterable<array{class-string, string, callable(BaseObjectPropertyReflection $propertyReflection): void}>
     */
    public function provideGetPropertyCases(): iterable {
        yield [MyComponent::class, 'privateStringProp', function(BaseObjectPropertyReflection $propertyReflection): void {
            $this->assertTrue($propertyReflection->isReadable());
            $this->assertTrue($propertyReflection->isWritable());
            $this->assertInstanceOf(StringType::class, $propertyReflection->getReadableType());
            $this->assertInstanceOf(StringType::class, $propertyReflection->getWritableType());
        }];
        yield [MyComponent::class, 'readonlyFunctionProp', function(BaseObjectPropertyReflection $propertyReflection): void {
            $this->assertTrue($propertyReflection->isReadable());
            $this->assertFalse($propertyReflection->isWritable());
            $this->assertInstanceOf(StringType::class, $propertyReflection->getReadableType());
            $this->assertInstanceOf(NeverType::class, $propertyReflection->getWritableType());
        }];
        yield [Article::class, 'topComment', function(BaseObjectPropertyReflection $propertyReflection): void {
            $this->assertTrue($propertyReflection->isReadable());
            $this->assertFalse($propertyReflection->isWritable());
            $readableType = $propertyReflection->getReadableType();
            $this->assertInstanceOf(UnionType::class, $readableType);
            /** @var UnionType $readableType */
            $this->assertInstanceOf(ObjectType::class, $readableType->getTypes()[0]);
            $this->assertSame(Comment::class, $readableType->getTypes()[0]->getClassName());
            $this->assertInstanceOf(NullType::class, $readableType->getTypes()[1]);
        }];
    }

}
