<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use yii\base\Configurable;

/**
 * @implements Rule<New_>
 */
final class CreateConfigurableObjectRule implements Rule {

    private ReflectionProvider $reflectionProvider;

    private YiiConfigHelper $configHelper;

    public function __construct(ReflectionProvider $reflectionProvider, YiiConfigHelper $configHelper) {
        $this->reflectionProvider = $reflectionProvider;
        $this->configHelper = $configHelper;
    }

    public function getNodeType(): string {
        return New_::class;
    }

    /**
     * @param New_ $node
     */
    public function processNode(Node $node, Scope $scope): array {
        $calledOn = $node->class;
        if (!$calledOn instanceof Node\Name) {
            return [];
        }

        $className = $calledOn->toString();

        // Invalid call, leave it for another rules
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $class = $this->reflectionProvider->getClass($className);
        // This rule intended for use only with Configurable interface
        if (!$class->is(Configurable::class)) {
            return [];
        }

        $constructorParams = ParametersAcceptorSelector::selectSingle($class->getConstructor()->getVariants())->getParameters();
        $lastArgName = $constructorParams[array_key_last($constructorParams)]->getName();

        $args = $node->args;
        foreach ($args as $arg) {
            // Try to find config by named argument
            if ($arg instanceof Node\Arg && $arg->name !== null && $arg->name->name === $lastArgName) {
                $configArg = $arg;
                break;
            }
        }

        // Attempt to find by named arg failed, try to find it by index
        if (!isset($configArg) && isset($args[count($constructorParams) - 1])) {
            $configArg = $args[count($constructorParams) - 1];
            // At this moment I don't know what to do with variadic arguments
            if (!$configArg instanceof Node\Arg) {
                return [];
            }
        }

        // Config arg wasn't specified, so nothing to validate
        if (!isset($configArg)) {
            return [];
        }

        $configArgType = $scope->getType($configArg->value);
        $errors = [];
        foreach ($configArgType->getConstantArrays() as $constantArray) {
            $errors = array_merge($errors, $this->configHelper->validateArray($class, $constantArray, $scope));
        }

        return $errors;
    }

}
