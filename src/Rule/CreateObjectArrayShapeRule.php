<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Type\Constant\ConstantStringType;
use yii\BaseYii;

/**
 * @implements Rule<StaticCall>
 */
final class CreateObjectArrayShapeRule implements Rule {

    private ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getNodeType(): string {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     */
    public function processNode(Node $node, Scope $scope): array {
        $class = $node->class;
        if (!$class instanceof Node\Name) {
            return [];
        }

        $methodName = $node->name;
        if (!$methodName instanceof Node\Identifier) {
            return [];
        }

        if (!is_a($class->toString(), BaseYii::class, true) || $methodName->toString() !== 'createObject') {
            return [];
        }

        $constantArrays = $scope->getType($node->getArgs()[0]->value)->getConstantArrays();
        if (count($constantArrays) !== 1) {
            return [];
        }

        /** @var \PHPStan\Type\Constant\ConstantArrayType $config */
        $config = $constantArrays[0];

        /** @var ConstantStringType $class */
        $class = $config->getOffsetValueType(new ConstantStringType('class'));
        if (count($class->getConstantStrings()) !== 1) {
            /** @var ConstantStringType $class */
            $class = $config->getOffsetValueType(new ConstantStringType('__class'));
            if (count($class->getConstantStrings()) !== 1) {
                // TODO: report as an error
                return [];
            }
        }

        $className = $class->getConstantStrings()[0]->getValue();
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        return YiiConfig::validateArray($classReflection, $config, $scope);
    }

}
