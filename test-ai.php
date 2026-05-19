<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('GEMINI_API_KEY');
$response = Illuminate\Support\Facades\Http::get("https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey");
$models = $response->json('models');
foreach ($models as $m) {
    echo $m['name'] . "\n";
}
