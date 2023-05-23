<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\base;

use Closure;
use yii\base\ActionEvent;
use yii\base\Controller;
use yii\base\Model;
use yii\base\ModelEvent;
use yii\db\Connection;

final class Behavior extends \yii\base\Behavior {

    public function events(): array {
        return [
            Connection::EVENT_AFTER_OPEN => 'handleOpen',
            Controller::EVENT_BEFORE_ACTION => [$this, 'handleBeforeAction'],
            Controller::EVENT_AFTER_ACTION => Closure::fromCallable([$this, 'handleAfterAction']),
            Model::EVENT_BEFORE_VALIDATE => function(ModelEvent $event) {},
        ];
    }

    public function handleOpen(): void {
    }

    public function handleBeforeAction(ActionEvent $event): void {
    }

    private function handleAfterAction(ActionEvent $event): void {
    }

}
