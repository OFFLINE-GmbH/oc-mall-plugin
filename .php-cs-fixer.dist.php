<?php

$finder = PhpCsFixer\Finder::create();

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'list_syntax' => ['syntax' => 'short'],
        'no_leading_import_slash' => true,
        'single_blank_line_before_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'operator_linebreak' => true,
        'concat_space' => ['spacing' => 'one'],
        'new_with_braces' => true,
        'no_closing_tag' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'no_trailing_whitespace' => true,
        'echo_tag_syntax' => ['format' => 'short'],
        'return_assignment' => true,
        'semicolon_after_instruction' => true,
        'explicit_string_variable' => true,
        'single_quote' => ['strings_containing_single_quote_chars' => false],
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'no_trailing_comma_in_list_call' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'fully_qualified_strict_types' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'no_blank_lines_after_class_opening' => true,
        'visibility_required' => ['elements' => ['property', 'method', 'const']],
        'control_structure_continuation_position' => ['position' => 'same_line'],
        'simplified_if_return' => true,
        'array_indentation' => true,
        'method_chaining_indentation' => true,
        'no_empty_phpdoc' => true,
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_indent' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_order' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_trim' => true,
        'global_namespace_import' => ['import_classes' => true],
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'square_brace_block',
                'use',
                'return',
                'throw',
                'parenthesis_brace_block',
                'continue',
                'curly_brace_block',
                'switch',
                'case',
                'default',
            ],
        ],
        'no_spaces_around_offset' => true,
        'no_spaces_inside_parenthesis' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'use_arrow_functions' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'continue',
                'return',
                'throw',
                'try',
                'if',
                'switch',
                'foreach',
            ],
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
    ])
    ->setIndent('    ')
    ->setFinder($finder);

