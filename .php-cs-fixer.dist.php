<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

return Ely\CS\Config::create([
    // Disable "parameters" and "match" to keep compatibility with PHP 7.4
    'trailing_comma_in_multiline' => [
        'elements' => ['arrays', 'arguments'],
    ],
    // Add some really strict rules
    'declare_strict_types' => true,
    'final_class' => true,
    // Disable controversial rules
    'comment_to_phpdoc' => false,
])->setFinder($finder);
