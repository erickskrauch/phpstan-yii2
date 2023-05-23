<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs;

use Closure;
use yii\base\Model as Yii2Model;
use yii\validators\NumberValidator;

final class Model extends Yii2Model {

    public function rules(): array {
        return [
            [['field1', 'field2'], 'required'],
            ['field3', 'string'],
            [['field4'], 'boolean', 'message' => 'Custom validation message'],
            [['field5'], NumberValidator::class, 'allowArray' => false],
            [['field6'], 'local', 'allowArray' => false],
            [['field7'], Closure::fromCallable([$this, 'privateValidator'])],
        ];
    }

    public function localValidator(string $attribute): void {
        $this->addError($attribute, 'This is an error');
    }

    private function privateValidator(string $attribute): void {
        $this->addError($attribute, 'This is an error too');
    }

}
