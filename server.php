<?php

// Project router untuk `php artisan serve` (diutamakan atas vendor server.php).
// Meniru perilaku Apache/Nginx yang sudah diperbaiki: route /docs dan /docs/api
// harus ke Laravel meski public/docs adalah folder fisik.

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Paksa route Laravel — jangan biarkan PHP built-in server serve folder public/docs.
if ($uri === '/docs' || $uri === '/docs/' || $uri === '/docs/api' || $uri === '/docs/api/') {
    require_once $publicPath.'/index.php';

    return;
}

// Hanya file nyata yang diserve statis — folder (mis. public/docs) diteruskan ke Laravel.
// (Vendor memakai file_exists yang menganggap folder sebagai statis -> 404 bawaan PHP.)
if ($uri !== '/' && is_file($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
