<?php
// barryvdh/laravel-dompdf config
// For details and documentation @see https://github.com/barryvdh/laravel-dompdf
return [
    'show_warnings' => false,
    'orientation'   => 'portrait',
    'defines'       => [
        'font_dir'               => storage_path('fonts/'),
        'font_cache'             => storage_path('fonts/'),
        'temp_dir'               => sys_get_temp_dir(),
        'chroot'                 => realpath(base_path()),
        'enable_font_subsetting' => false,
        'pdf_backend'            => 'CPDF',
        'default_media_type'     => 'screen',
        'default_paper_size'     => 'a4',
        'default_font'           => 'serif',
        'dpi'                    => 300,
        'enable_php'             => false,
        'enable_javascript'      => true,
        'enable_remote'          => true,
        'font_height_ratio'      => 1.1,
        'enable_html5_parser'    => false,
    ],
];
