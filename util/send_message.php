<?php

//https://notify-bot.line.me/en/

function sendLineNotify($message,$token)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "message=" . $message);
    $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $token . '',);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);

    if (curl_error($ch)) {
        echo 'error:' . curl_error($ch);
    } else {
        $res = json_decode($result, true);
        echo "\n\r". "status : " . $res['status'];
        echo "\n\r". "message : " . $res['message'];
    }
    curl_close($ch);
}


function sendLineMessage($conn, $channelAccessToken, $userId, $messageText)
{
    if (empty($channelAccessToken) || empty($userId) || empty($messageText)) {
        return ['status' => 'error', 'message' => 'Missing required parameters'];
    }

    $url = 'https://api.line.me/v2/bot/message/push';
    $messageData = [
        'to' => $userId,
        'messages' => [
            ['type' => 'text', 'text' => $messageText]
        ]
    ];

    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $channelAccessToken
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $result = "Success " . $httpCode;
        logLineSend($conn,$channelAccessToken,$result,$userId,$messageText);
        return ['status' => 'success', 'response' => json_decode($response, true)];
    } else {
        $result = "Error " . $httpCode ;
        logLineSend($conn,$channelAccessToken,$result,$userId,$messageText);
        return ['status' => 'error', 'message' => 'Failed to send message'];
    }
}

function logLineSend($conn, $api, $result ,$userId, $msg)
{
    try {
        $stmt = $conn->prepare("INSERT INTO log_line_send (line_api, msg , result,line_user_id) VALUES (:api, :msg , :result,:line_user_id)");
        $stmt->bindParam(':api', $api);
        $stmt->bindParam(':msg', $msg);
        $stmt->bindParam(':result', $result);
        $stmt->bindParam(':line_user_id', $userId);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Log Line Send Error: " . $e->getMessage());
    }
}