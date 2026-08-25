<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);
if ($response->exception) {
    echo "EXCEPTION: " . $response->exception->getMessage() . "\n";
    echo $response->exception->getTraceAsString() . "\n";
} else {
    echo "STATUS: " . $response->getStatusCode() . "\n";
}
