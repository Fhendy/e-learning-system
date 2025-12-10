<?php

return [
    'format' => 'png', // Ubah dari 'svg' ke 'png'
    'size' => 300,
    'margin' => 1,
    'errorCorrection' => 'H',
    'encoding' => 'UTF-8',
    'style' => [
        'eye' => 'square',
        'color' => [0, 0, 0], // Black
        'gradientType' => 'linear',
        'gradientColor' => [0, 0, 0],
        'backgroundColor' => [255, 255, 255], // White
    ],
    'image' => [
        'png' => [
            'enable' => true,
            'width' => 300,
            'height' => 300,
            'scale' => 1,
            'quality' => 90,
            'compress_level' => 6,
            'filter' => PNG_ALL_FILTERS,
        ],
        'svg' => [
            'enable' => false, // Nonaktifkan SVG
            'width' => 300,
            'height' => 300,
            'scale' => 1,
            'color' => '#000000',
            'background' => '#ffffff',
        ],
        'eps' => [
            'enable' => false,
        ],
    ],
    'renderer' => 'gd', // INI YANG PENTING: ubah dari 'imagick' ke 'gd'
    'rendererOptions' => [
        'imagick' => [
            'format' => 'png',
            'quality' => 90,
        ],
        'gd' => [
            'format' => 'png',
            'quality' => 90,
            'scale' => 1,
        ],
    ],
];