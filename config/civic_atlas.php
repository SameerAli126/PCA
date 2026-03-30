<?php

return [
    'name' => env('CIVIC_ATLAS_NAME', 'Peshawar Civic GIS Atlas'),
    'default_area_slug' => env('CIVIC_ATLAS_DEFAULT_AREA', 'peshawar-district'),
    'public_url' => env('CIVIC_ATLAS_PUBLIC_URL', env('APP_URL', 'http://localhost')),
    'map' => [
        'center' => [
            'lat' => (float) env('CIVIC_ATLAS_CENTER_LAT', 34.0151),
            'lng' => (float) env('CIVIC_ATLAS_CENTER_LNG', 71.5249),
        ],
        'zoom' => (int) env('CIVIC_ATLAS_DEFAULT_ZOOM', 11),
        'tile_url' => env('CIVIC_ATLAS_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'tile_attribution' => env(
            'CIVIC_ATLAS_TILE_ATTRIBUTION',
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        ),
    ],
    'dataset' => [
        'allowed_extensions' => ['csv', 'xls', 'xlsx'],
        'preview_rows' => 8,
        'required_columns' => ['name', 'category', 'latitude', 'longitude'],
    ],
    'palette' => [
        '#0f766e',
        '#0ea5e9',
        '#f97316',
        '#f59e0b',
        '#8b5cf6',
        '#e11d48',
    ],
    'seo' => [
        'default_title' => env('CIVIC_ATLAS_SEO_TITLE', 'Peshawar Civic GIS Atlas'),
        'default_description' => env(
            'CIVIC_ATLAS_SEO_DESCRIPTION',
            'Public-services GIS for Peshawar with facility discovery, dataset provenance, and admin import workflows built on Laravel and MariaDB.'
        ),
        'locale' => env('CIVIC_ATLAS_SEO_LOCALE', 'en_PK'),
        'default_image_path' => env('CIVIC_ATLAS_OG_IMAGE_PATH', '/og/peshawar-civic-gis-atlas.svg'),
        'twitter_card' => env('CIVIC_ATLAS_TWITTER_CARD', 'summary_large_image'),
        'public_owner' => env('CIVIC_ATLAS_PUBLIC_OWNER', 'Peshawar Civic GIS Atlas'),
    ],
];
