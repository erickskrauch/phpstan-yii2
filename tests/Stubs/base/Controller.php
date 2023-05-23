<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\base;

use yii\base\Controller as Yii2Controller;
use yii\web\ViewAction;

final class Controller extends Yii2Controller {

    public function actions(): array {
        return [
            'action1' => ViewAction::class,
            'action2' => [
                'class' => ViewAction::class,
                'viewParam' => 'myParam',
            ],
            'action3' => [
                '__class' => ViewAction::class,
                'defaultView' => 'default',
            ],
            'action4' => fn() => new ViewAction('action4', $this),
        ];
    }

}
