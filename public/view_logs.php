<?php

header('Content-Type: text/plain; charset=utf-8');

$logFile = __DIR__ . '/../storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "Log file does not exist: {$logFile}\n";
    exit;
}

echo "=== LAST 150 LINES OF LARAVEL LOG ===\n\n";

$lines = file($logFile);
$lastLines = array_slice($lines, -150);

foreach ($lastLines as $line) {
    echo $line;
}
