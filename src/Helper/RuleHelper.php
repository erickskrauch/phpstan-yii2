<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Helper;

use PHPStan\Rules\FileRuleError;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\MetadataRuleError;
use PHPStan\Rules\NonIgnorableRuleError;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\TipRuleError;

final class RuleHelper {

    /**
     * @template T of RuleError
     * @param T $error
     * @return T
     */
    public static function removeLine(RuleError $error): RuleError {
        $builder = RuleErrorBuilder::message($error->getMessage());
        // @phpstan-ignore-next-line
        if ($error instanceof IdentifierRuleError) {
            $builder = $builder->identifier($error->getIdentifier());
        }

        // @phpstan-ignore-next-line
        if ($error instanceof NonIgnorableRuleError) {
            $builder = $builder->nonIgnorable();
        }

        // @phpstan-ignore-next-line
        if ($error instanceof TipRuleError) {
            $builder = $builder->tip($error->getTip());
        }

        // @phpstan-ignore-next-line
        if ($error instanceof MetadataRuleError) {
            $builder = $builder->metadata($error->getMetadata());
        }

        // @phpstan-ignore-next-line
        if ($error instanceof FileRuleError) {
            $builder = $builder->file($error->getFile());
        }

        return $builder->build(); // @phpstan-ignore-line I don't understand why there is an error
    }

}
