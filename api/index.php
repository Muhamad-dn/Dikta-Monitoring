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

// Cek koneksi database dari environment (misal pgsql, mysql, atau sqlite)
$dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? ($_SERVER['DB_CONNECTION'] ?? null));
$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? null));
$databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? ($_SERVER['DATABASE_URL'] ?? null));

// Jika ada DB_HOST atau DATABASE_URL cloud (misal Supabase / Postgres), otomatis arahkan ke pgsql
if (!$dbConnection) {
    if ($databaseUrl || ($dbHost && (str_contains($dbHost, 'supabase') || str_contains($dbHost, 'neon') || str_contains($dbHost, 'pooler')))) {
        $dbConnection = 'pgsql';
    } elseif ($dbHost) {
        $dbConnection = 'mysql';
    } else {
        $dbConnection = 'sqlite';
    }
}

if ($dbConnection === 'sqlite') {
    // Salin database SQLite ke /tmp jika menggunakan SQLite lokal di serverless
    $dbSource = __DIR__ . '/../database/database.sqlite';
    $dbTarget = '/tmp/database.sqlite';
    if (!file_exists($dbTarget) && file_exists($dbSource)) {
        copy($dbSource, $dbTarget);
    }

    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_SERVER['DB_CONNECTION'] = 'sqlite';
    putenv('DB_CONNECTION=sqlite');

    $_ENV['DB_DATABASE'] = $dbTarget;
    $_SERVER['DB_DATABASE'] = $dbTarget;
    putenv("DB_DATABASE={$dbTarget}");
} else {
    // Pastikan koneksi cloud tersinkron penuh ke putenv, $_ENV, dan $_SERVER
    $_ENV['DB_CONNECTION'] = $dbConnection;
    $_SERVER['DB_CONNECTION'] = $dbConnection;
    putenv("DB_CONNECTION={$dbConnection}");
}

// Jalankan Laravel public/index.php
require __DIR__ . '/../public/index.php';
