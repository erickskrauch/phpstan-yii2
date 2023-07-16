<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use yii\BaseYii;

/**
 * @implements Rule<StaticCall>
 */
final class CreateObjectRule implements Rule {

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
        $calledOn = $node->class;
        if (!$calledOn instanceof Node\Name) {
            return [];
        }

        $methodName = $node->name;
        if (!$methodName instanceof Node\Identifier) {
            return [];
        }

        if ($methodName->toString() !== 'createObject') {
            return [];
        }

        if (!$this->reflectionProvider->getClass($calledOn->toString())->is(BaseYii::class)) {
            return [];
        }

        $errors = [];
        $args = $node->getArgs();
        // Probably invalid code so leave it to another rules
        if (count($args) < 1) {
            return [];
        }

        $firstArgType = $scope->getType($args[0]->value);
        if ($firstArgType->isConstantArray()->yes()) {
            /** @var \PHPStan\Type\Constant\ConstantArrayType $config */
            $config = $firstArgType->getConstantArrays()[0];
            $classNameOrError = YiiConfig::findClass($config);
            if ($classNameOrError instanceof RuleError) {
                return [$classNameOrError];
            }

            if (!$this->reflectionProvider->hasClass($classNameOrError)) {
                return [
                    RuleErrorBuilder::message(sprintf('Class %s not found.', $classNameOrError))
                        ->identifier('class.notFound')
                        ->discoveringSymbolsTip()
                        ->build(),
                ];
            }

            $classReflection = $this->reflectionProvider->getClass($classNameOrError);

            $errors = array_merge($errors, YiiConfig::validateArray($classReflection, $config, $scope));
        } elseif ($firstArgType->isClassStringType()->yes()) {
            $classNamesTypes = $firstArgType->getConstantStrings();
            // At this moment I'll skip supporting of multiple classes at once
            if (count($classNamesTypes) !== 1) {
                return [];
            }

            $className = $classNamesTypes[0]->getValue();
            if (!$this->reflectionProvider->hasClass($className)) {
                return [
                    RuleErrorBuilder::message(sprintf('Class %s not found.', $className))
                        ->identifier('class.notFound')
                        ->discoveringSymbolsTip()
                        ->build(),
                ];
            }

            $classReflection = $this->reflectionProvider->getClass($className);
        } else {
            // We can't process second argument without knowing the class
            return [];
        }

        if (isset($args[1])) {
            // TODO: it is possible to pass botch 2 argument and __construct() config param.
            //       at the moment I'll not cover that case.
            //       Note for future me 2nd argument value has priority when merging with __construct()
            $secondArgConstantArrays = $scope->getType($args[1]->value)->getConstantArrays();
            if (count($secondArgConstantArrays) === 1) {
                $argsConfig = $secondArgConstantArrays[0];
                $errors = array_merge($errors, YiiConfig::validateConstructorArgs($classReflection, $argsConfig, $scope));
            }
        }

        return $errors;
    }

}
