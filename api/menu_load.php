<?php

header('Content-Type: application/json');
require '../config/connect_db.php'; // ไฟล์เชื่อมต่อฐานข้อมูล

$userId = $_GET['userId'];

$stmt = $conn->prepare("
    SELECT id, name, parent_id, route, icon
    FROM menus
    JOIN user_permissions ON menus.id = user_permissions.menu_id
    WHERE user_permissions.user_id = :userId
    ORDER BY parent_id, id
");
$stmt->execute(['userId' => $userId]);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($menus);
