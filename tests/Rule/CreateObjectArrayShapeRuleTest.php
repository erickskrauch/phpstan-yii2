<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Rule;

use ErickSkrauch\PHPStan\Yii2\Rule\CreateObjectArrayShapeRule;
use ErickSkrauch\PHPStan\Yii2\Tests\ConfigTrait;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CreateObjectArrayShapeRule>
 */
final class CreateObjectArrayShapeRuleTest extends RuleTestCase {
    use ConfigTrait;

    public function testRule(): void {
        $this->analyse([__DIR__ . '/_data/create_object_array_shape_not_array_arg.php'], []);
        $this->analyse([__DIR__ . '/_data/create_object_array_shape_valid.php'], []);
        $this->analyse([__DIR__ . '/_data/create_object_array_shape_invalid.php'], [
            [
                'Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$publicStringProp (string) does not accept int.',
                8,
            ],
            [
                'Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$publicArrayProp (array{key: string}) does not accept array{key: false}.',
                9,
                "Offset 'key' (string) does not accept type false.",
            ],
            [
                'Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$privateStringProp (string) does not accept int.',
                10,
            ],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_shape_access_private_properties.php'], [
            [
                'Access to protected property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$protectedStringProp.',
                8,
            ],
            [
                'Access to private property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$_privateStringProp.',
                9,
            ],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_shape_set_to_readonly_properties.php'], [
            [
                'Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$readonlyPhpDocStringProp is not writable.',
                8,
            ],
            [
                'Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$readonlyFunctionProp is not writable.',
                9,
            ],
        ]);
    }

    protected function getRule(): Rule {
        return new CreateObjectArrayShapeRule(self::createReflectionProvider());
    }

}
