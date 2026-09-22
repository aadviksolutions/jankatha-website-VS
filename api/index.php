<?php

/**
 * Vercel Serverless Function Bridge for Laravel
 *
 * Directs Laravel's ephemeral cache, views, sessions, and logs to /tmp/storage
 * which is writable in serverless environments.
 */
$storageDir = '/tmp/storage';

if (! is_dir($storageDir.'/framework/views')) {
    $dirs = [
        $storageDir.'/framework/views',
        $storageDir.'/framework/cache',
        $storageDir.'/framework/sessions',
        $storageDir.'/framework/testing',
        $storageDir.'/logs',
        $storageDir.'/app/public',
    ];

    foreach ($dirs as $dir) {
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}

$_ENV['LARAVEL_STORAGE_PATH'] = $storageDir;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storageDir;

require __DIR__.'/../public/index.php';
