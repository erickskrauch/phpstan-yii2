<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Stubs\test;

use ErickSkrauch\PHPStan\Yii2\Tests\Yii\Comment;

final class BaseActiveFixture extends \yii\test\BaseActiveFixture {

    public $modelClass = Comment::class;

}
