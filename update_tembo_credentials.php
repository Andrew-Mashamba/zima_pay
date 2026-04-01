<?php

/**
 * One-time script to set TEMBO aggregator credentials from environment.
 * Usage: TEMBO_ACCOUNT_ID=xxx TEMBO_SECRET_KEY=xxx php update_tembo_credentials.php
 * Or set in .env and run: php update_tembo_credentials.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Aggregator;

$apiKey = env('TEMBO_ACCOUNT_ID') ?: getenv('TEMBO_ACCOUNT_ID');
$apiSecret = env('TEMBO_SECRET_KEY') ?: getenv('TEMBO_SECRET_KEY');
$baseUrl = env('TEMBO_BASE_URL', 'https://api.temboplus.com/tembo/v1');

if (!$apiKey || !$apiSecret) {
    echo "Set TEMBO_ACCOUNT_ID and TEMBO_SECRET_KEY (env or .env).\n";
    exit(1);
}

$aggregator = Aggregator::where('code', 'TEMBO')->first();
if (!$aggregator) {
    echo "TEMBO aggregator not found.\n";
    exit(1);
}

$aggregator->api_key = $apiKey;
$aggregator->api_secret = $apiSecret;
$aggregator->api_endpoint = rtrim($baseUrl, '/');
$aggregator->save();

echo "Updated TEMBO aggregator (id={$aggregator->id}):\n";
echo "  api_endpoint: {$aggregator->api_endpoint}\n";
echo "  api_key: " . substr($aggregator->api_key, 0, 12) . "...\n";
echo "  api_secret: " . substr($aggregator->api_secret, 0, 8) . "...\n";
echo "Done.\n";
