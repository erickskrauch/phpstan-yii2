<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Yii;

use yii\base\Component;

/**
 * @property-read string $readonlyPhpDocStringProp
 */
final class MyComponent extends Component {

    public const TEST = 'stringArg';

    public string $publicStringProp = '';

    /**
     * @var array{key: string}
     */
    public array $publicArrayProp;

    protected string $protectedStringProp = '';

    private string  $_privateStringProp = '';

    // @phpstan-ignore-next-line ignore unused arguments errors and missing $config type
    public function __construct(string $stringArg, int $intArg = 0, array $config = []) {
        parent::__construct($config);
    }

    public function setPrivateStringProp(string $value): void {
        $this->_privateStringProp = $value;
    }

    public function getPrivateStringProp(): string {
        return $this->_privateStringProp;
    }

    public function getReadonlyFunctionProp(): string {
        return '';
    }

}
