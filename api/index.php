<?php

/**
 * Vercel Serverless Function Handler for Laravel
 */

// Siapkan folder yang memerlukan izin tulis ke direktori sementara (/tmp)
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Arahkan cache path ke direktori /tmp
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');

// Salin database SQLite ke /tmp jika digunakan
$dbSource = __DIR__ . '/../database/database.sqlite';
$dbTarget = '/tmp/database.sqlite';
if (!file_exists($dbTarget) && file_exists($dbSource)) {
    copy($dbSource, $dbTarget);
}
if (file_exists($dbTarget)) {
    putenv("DB_DATABASE={$dbTarget}");
}

// Jalankan Laravel public/index.php
require __DIR__ . '/../public/index.php';
