<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $events = App\Models\Event::with(['sport'])->get();
    echo 'Success! Events loaded with relationships.' . PHP_EOL;
    echo 'Event: ' . $events . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

try {
    $events = App\Models\Event::with(['sport'])->get();
    echo 'Success! Events loaded with relationships.' . PHP_EOL;
    foreach ($events as $event) {
        echo 'Event: ' . $event->title . ', Date: ' . $event->event_date . ', Time: ' . $event->event_time . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
