<?php

/**
 * Allowed origins are a hardcoded baseline (production + common dev ports)
 * merged with ALLOWED_ORIGINS from the environment. This way production
 * deploys can't be broken by a typo/missing env var, and adding local
 * dev origins is still as easy as appending to the env var.
 */

$hardcoded = [
    // Production
    'https://almhaplasticsurgery.com',
    'https://www.almhaplasticsurgery.com',
    'https://admin.almhaplasticsurgery.com',

    // Local dev
    'http://localhost:3000',
    'http://localhost:4321', // Astro default
    'http://localhost:5173', // Vite default
    'http://localhost:8787', // Nuxt admin (custom port)
];

$fromEnv = array_filter(array_map('trim', explode(',', (string) env('ALLOWED_ORIGINS', ''))));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_merge($hardcoded, $fromEnv))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => true,
];
