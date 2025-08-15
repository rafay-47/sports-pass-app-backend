<?php

// Test script to verify our new endpoint
require_once 'vendor/autoload.php';

use App\Models\Sport;
use App\Models\SportService;

// Get first sport with services
$sport = Sport::with('services')->first();

if (!$sport) {
    echo "No sports found in database.\n";
    exit;
}

echo "Testing endpoint for sport: {$sport->name} (ID: {$sport->id})\n";
echo "Services count: " . $sport->services->count() . "\n";

if ($sport->services->count() > 0) {
    echo "\nServices:\n";
    foreach ($sport->services as $service) {
        echo "- {$service->service_name}: \${$service->base_price}";
        if ($service->discount_percentage > 0) {
            echo " (Discounted: \${$service->discounted_price})";
        }
        echo "\n";
    }
}

echo "\nAPI endpoint URL: http://127.0.0.1:8000/api/sports/{$sport->id}/services/prices\n";
