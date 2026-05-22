<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('GEMINI_API_KEY');
$models = ['gemini-3.5-flash', 'gemini-2.5-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash', 'gemini-3.1-flash-lite', 'gemini-3-flash-preview', 'gemini-flash-lite-latest', 'gemini-pro-latest'];

foreach ($models as $model) {
    echo "Testing $model...\n";
    $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [['text' => 'Halo']]
            ]
        ]
    ]);
    
    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        echo "Response: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Error: " . $response->body() . "\n";
    }
    echo "----------------------------------------\n";
}
