<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Rule;

use ErickSkrauch\PHPStan\Yii2\Rule\CreateObjectRule;
use ErickSkrauch\PHPStan\Yii2\Rule\YiiConfigHelper;
use ErickSkrauch\PHPStan\Yii2\Tests\ConfigTrait;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CreateObjectRule>
 */
final class CreateObjectRuleTest extends RuleTestCase {
    use ConfigTrait;

    public function testRule(): void {
        $this->analyse([__DIR__ . '/_data/create_object_function_arg.php'], []);
        $this->analyse([__DIR__ . '/_data/create_object_classname_valid.php'], []);
        $this->analyse([__DIR__ . '/_data/create_object_array_valid.php'], []);
        $this->analyse([__DIR__ . '/_data/create_object_array_union_type_valid.php'], []);
        $this->analyse([__DIR__ . '/_data/create_object_classname_invalid.php'], [
            ['Parameter #1 $stringArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects string, int given.', 6],
            ['Parameter #2 $intArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects int, string given.', 6],
            ['Parameter $stringArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects string, int given.', 7],
            ['Parameter $intArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects int, string given.', 7],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_invalid.php'], [
            ['Parameter $stringArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects string, int given.', 6],
            ['Parameter $intArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects int, string given.', 6],
            ['Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$publicStringProp (string) does not accept int.', 6],
            [
                'Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$publicArrayProp (array{key: string}) does not accept array{key: false}.',
                6,
                "Offset 'key' (string) does not accept type false.",
            ],
            ['Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$privateStringProp (string) does not accept int.', 6],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_access_private_properties.php'], [
            ['Access to protected property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$protectedStringProp.', 6],
            ['Access to private property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$_privateStringProp.', 6],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_set_to_readonly_properties.php'], [
            ['Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$readonlyPhpDocStringProp is not writable.', 6],
            ['Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$readonlyFunctionProp is not writable.', 6],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_with_indexed_args.php'], [
            ['Parameter #1 $stringArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects string, int given.', 6],
            ['Parameter #2 $intArg of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent constructor expects int, string given.', 6],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_with_missing_class.php'], [
            ['Configuration params array must have "class" or "__class" key', 4],
        ]);
        $this->analyse([__DIR__ . '/_data/create_object_array_union_type_invalid.php'], [
            ["The config for ErickSkrauch\PHPStan\Yii2\Tests\Yii\Article|ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment is wrong: the property field doesn't exists", 10],
            ['Parameter #1 $dateTime of class ErickSkrauch\PHPStan\Yii2\Tests\Yii\BarComponent constructor expects DateTimeInterface, string given.', 17],
        ]);
    }

    protected function getRule(): Rule {
        return new CreateObjectRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(YiiConfigHelper::class),
        );
    }

}
