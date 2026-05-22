<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('GEMINI_API_KEY');
$url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
$response = Http::get($url);

if ($response->successful()) {
    $models = $response->json('models');
    foreach ($models as $m) {
        echo $m['name'] . " - " . implode(', ', $m['supportedGenerationMethods']) . "\n";
    }
} else {
    echo "FAILED: Status " . $response->status() . "\n" . $response->body() . "\n";
}
