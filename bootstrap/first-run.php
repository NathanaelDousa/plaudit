<?php

// Runs once, before Laravel boots, when there is no .env yet: the "upload and open in a browser"
// install on shared hosting where people have no SSH to run artisan commands.
$base = dirname(__DIR__);

$notWritable = array_filter(
    [$base, "$base/storage", "$base/bootstrap/cache", "$base/database", "$base/public"],
    fn ($dir) => ! is_writable($dir)
);
if ($notWritable) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Plaudit can't finish installing because these folders aren't writable by the web server:\n\n"
        .implode("\n", $notWritable)
        ."\n\nIn your hosting file manager, set their permissions to 755 (or 775), then reload this page.\n";
    exit;
}

$env = file_get_contents("$base/.env.example");
$env = preg_replace(
    ['/^APP_KEY=.*$/m', '/^APP_ENV=.*$/m', '/^APP_DEBUG=.*$/m', '/^LOG_LEVEL=.*$/m'],
    ['APP_KEY=base64:'.base64_encode(random_bytes(32)), 'APP_ENV=production', 'APP_DEBUG=false', 'LOG_LEVEL=warning'],
    $env
);
file_put_contents("$base/.env", $env, LOCK_EX);
chmod("$base/.env", 0600);

if (! file_exists("$base/database/database.sqlite")) {
    touch("$base/database/database.sqlite");
}
