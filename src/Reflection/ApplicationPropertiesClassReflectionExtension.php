<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Reflection;

use ErickSkrauch\PHPStan\Yii2\ServiceMap;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Dummy\DummyPropertyReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use yii\base\Application as BaseApplication;
use yii\web\Application as WebApplication;

final class ApplicationPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension {

    private AnnotationsPropertiesClassReflectionExtension $annotationsProperties;

    private ServiceMap $serviceMap;

    private ReflectionProvider $reflectionProvider;

    public function __construct(
        AnnotationsPropertiesClassReflectionExtension $annotationsProperties,
        ReflectionProvider $reflectionProvider,
        ServiceMap $serviceMap
    ) {
        $this->annotationsProperties = $annotationsProperties;
        $this->serviceMap = $serviceMap;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool {
        if ($classReflection->getName() !== BaseApplication::class && !$classReflection->isSubclassOf(BaseApplication::class)) {
            return false;
        }

        if ($classReflection->getName() !== WebApplication::class) {
            $classReflection = $this->reflectionProvider->getClass(WebApplication::class);
        }

        return $classReflection->hasNativeProperty($propertyName)
            || $this->annotationsProperties->hasProperty($classReflection, $propertyName)
            || $this->serviceMap->getComponentClassById($propertyName);
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection {
        if ($classReflection->getName() !== WebApplication::class) {
            $classReflection = $this->reflectionProvider->getClass(WebApplication::class);
        }

        $componentClass = $this->serviceMap->getComponentClassById($propertyName);
        if ($componentClass !== null) {
            return new ComponentPropertyReflection(new DummyPropertyReflection($propertyName), new ObjectType($componentClass));
        }

        if ($classReflection->hasNativeProperty($propertyName)) {
            return $classReflection->getNativeProperty($propertyName);
        }

        return $this->annotationsProperties->getProperty($classReflection, $propertyName);
    }

}
