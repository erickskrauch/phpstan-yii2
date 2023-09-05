<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2\Tests\Yii;

use DateTimeInterface;
use yii\base\Component;

final class BarComponent extends Component {

    // @phpstan-ignore-next-line ignore unused arguments errors and missing $config type
    public function __construct(DateTimeInterface $dateTime, array $config = []) {
        parent::__construct($config);
    }

}
