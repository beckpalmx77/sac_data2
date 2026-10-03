<?php
require "config/connect_db.php";

$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (!is_array($events['events'])) {
    http_response_code(400);
    exit();
}

foreach ($events['events'] as $event) {
    if ($event['type'] === 'message' && $event['message']['type'] === 'location') {
        $userId = $event['source']['userId'];
        $lat = $event['message']['latitude'];
        $lon = $event['message']['longitude'];
        $timestamp = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO checkins (user_id, latitude, longitude, checkin_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $lat, $lon, $timestamp]);
    }
}

echo "OK";