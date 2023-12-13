<?php

$config = new PhpCsFixer\Config();
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        '.webpack',
        'assets',
        'cache',
        'docs',
        'estimates',
        'languages',
        'lazy',
        'library_store',
        'public',
        'temp',
        'var',
        'vector',
        'vector_admin',
        'vendor',
    ])
;

return $config
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PSR12'                      => true,
        '@PhpCsFixer'                 => true,
        'concat_space'                => ['spacing' => 'one'],
        'echo_tag_syntax'             => ['format' => 'long'],
        'no_superfluous_phpdoc_tags'  => ['allow_mixed' => true],
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'declare',
                'default',
                'exit',
                'goto',
                'include',
                'include_once',
                'require',
                'require_once',
                'return',
                'throw',
            ],
        ],
        'binary_operator_spaces'      => [
            'default'   => 'single_space',
            'operators' => [
                '=>' => 'align',
            ],
        ],
        'ordered_imports'             => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
    ])
;
