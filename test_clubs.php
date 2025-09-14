<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $clubs = App\Models\Club::with(['sport', 'amenities', 'facilities'])->take(1)->get();
    echo 'Success! Clubs loaded with relationships.' . PHP_EOL;
    echo 'Club: ' . $clubs->first()->name . PHP_EOL;
    echo 'Sport: ' . ($clubs->first()->sport ? $clubs->first()->sport->name : 'None') . PHP_EOL;
    echo 'Amenities count: ' . $clubs->first()->amenities->count() . PHP_EOL;
    echo 'Facilities count: ' . $clubs->first()->facilities->count() . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
