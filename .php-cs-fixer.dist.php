<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->append([__FILE__]);

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRules(
        [
            '@PSR12' => true,
            '@Symfony' => true,
            'array_indentation' => true,
            'class_definition' => ['multi_line_extends_each_single_line' => true],
            'compact_nullable_typehint' => true,
            'concat_space' => ['spacing' => 'one'],
            'declare_strict_types' => true,
            'heredoc_to_nowdoc' => true,
            'global_namespace_import' => [
                'import_classes' => false,
                'import_constants' => false,
                'import_functions' => false,
            ],
            'list_syntax' => ['syntax' => 'short'],
            'no_null_property_initialization' => true,
            'phpdoc_to_comment' => false,
            'phpdoc_align' => ['align' => 'left'],
            'phpdoc_summary' => false,
            'ternary_to_null_coalescing' => true,
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder);
