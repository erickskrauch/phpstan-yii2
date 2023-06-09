<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Yii;

use yii\base\Component;

/**
 * @property-read string $readonlyPhpDocStringProp
 */
final class MyComponent extends Component {

    public string $publicStringProp = '';

    /**
     * @var array{key: string}
     */
    public array $publicArrayProp;

    protected string $protectedStringProp = '';

    private string  $_privateStringProp = '';

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
