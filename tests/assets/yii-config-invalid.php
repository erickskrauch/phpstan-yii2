<?php

return [
    'container' => [
        'singletons' => [
            'no-return-type' => fn() => new \ArrayObject(),
        ],
    ],
];
