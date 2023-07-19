<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Rule;

use ErickSkrauch\PHPStan\Yii2\Rule\CreateConfigurableObjectRule;
use ErickSkrauch\PHPStan\Yii2\Rule\YiiConfigHelper;
use ErickSkrauch\PHPStan\Yii2\Tests\ConfigTrait;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CreateConfigurableObjectRule>
 */
final class CreateConfigurableObjectRuleTest extends RuleTestCase {
    use ConfigTrait;

    public function testRule(): void {
        $this->analyse([__DIR__ . '/_data/create_configurable_object_valid.php'], []);
        $this->analyse([__DIR__ . '/_data/create_configurable_object_invalid.php'], [
            ['Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$privateStringProp (string) does not accept int.', 10],
            ['Property ErickSkrauch\PHPStan\Yii2\Tests\Yii\MyComponent::$privateStringProp (string) does not accept int.', 15],
        ]);
    }

    protected function getRule(): Rule {
        return new CreateConfigurableObjectRule(
            self::createReflectionProvider(),
            self::getContainer()->getByType(YiiConfigHelper::class),
        );
    }

}
