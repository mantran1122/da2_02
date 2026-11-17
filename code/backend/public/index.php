<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Kiểm tra xem ứng dụng có đang bảo trì không
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Nạp Composer Autoloader
require __DIR__.'/../vendor/autoload.php';

// Khởi tạo và xử lý Request
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
