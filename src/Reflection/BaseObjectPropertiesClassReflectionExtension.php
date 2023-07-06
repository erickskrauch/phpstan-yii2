<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Reflection;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use yii\base\BaseObject;

final class BaseObjectPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension {

    private ClassMemberAccessAnswerer $scope;

    public function __construct() {
        $this->scope = new OutOfClassScope();
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool {
        // The extension shouldn't work when phpdoc for the property is presented
        if (self::hasPropertyFromPhpDoc($classReflection, $propertyName)) {
            return false;
        }

        if ($classReflection->getName() !== BaseObject::class && !$classReflection->isSubclassOf(BaseObject::class)) {
            return false;
        }

        foreach (self::getNames($propertyName) as $methodName) {
            if (!$classReflection->hasMethod($methodName)) {
                continue;
            }

            $method = $classReflection->getMethod($methodName, $this->scope);
            if (!$method->isPublic() || $method->isStatic()) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): BaseObjectPropertyReflection {
        [$getterName, $setterName] = self::getNames($propertyName);
        $getter = null;
        if ($classReflection->hasMethod($getterName)) {
            $method = $classReflection->getMethod($getterName, $this->scope);
            if ($method->isPublic() && !$method->isStatic()) {
                $getter = $method;
            }
        }

        $setter = null;
        if ($classReflection->hasMethod($setterName)) {
            $method = $classReflection->getMethod($setterName, $this->scope);
            if ($method->isPublic() && !$method->isStatic()) {
                $setter = $method;
            }
        }

        return new BaseObjectPropertyReflection($getter, $setter);
    }

    private static function hasPropertyFromPhpDoc(ClassReflection $classReflection, string $propertyName): bool {
        $phpDoc = $classReflection->getResolvedPhpDoc();
        if ($phpDoc === null) {
            return false;
        }

        return isset($phpDoc->getPropertyTags()[$propertyName]);
    }

    /**
     * @return array{string, string}
     */
    private static function getNames(string $propertyName): array {
        $cased = ucfirst($propertyName); // TODO: replace with mb analogue
        return ["get{$cased}", "set{$cased}"];
    }

}
