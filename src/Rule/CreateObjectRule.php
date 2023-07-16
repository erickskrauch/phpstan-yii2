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

        $constantArrays = $scope->getType($node->getArgs()[0]->value)->getConstantArrays();
        if (count($constantArrays) !== 1) {
            return [];
        }

        /** @var \PHPStan\Type\Constant\ConstantArrayType $config */
        $config = $constantArrays[0];
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

        // TODO: second argument has priority over __construct()
        return YiiConfig::validateArray($classReflection, $config, $scope);
    }

}
