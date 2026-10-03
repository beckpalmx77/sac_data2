<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
header('Content-Type: application/json; charset=utf-8');

include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['alogin'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$page_name = $_POST['page_name'] ?? $_GET['page_name'] ?? 'forecast_plan_display_realtime';

// Auto create table if not exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS `ims_user_column_config` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` VARCHAR(100) NOT NULL,
      `page_name` VARCHAR(100) NOT NULL,
      `column_settings` TEXT NOT NULL,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY `idx_user_page` (`user_id`, `page_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
} catch (PDOException $e) {
    // Ignore error if table exists or permissions issue
}

if ($action === 'save') {
    $column_settings = $_POST['column_settings'] ?? '';
    if (empty($column_settings)) {
        echo json_encode(['success' => false, 'message' => 'Missing column_settings']);
        exit;
    }

    // Verify valid JSON
    $decoded = json_decode($column_settings, true);
    if ($decoded === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON string']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO ims_user_column_config (user_id, page_name, column_settings) 
                                VALUES (:user_id, :page_name, :column_settings) 
                                ON DUPLICATE KEY UPDATE column_settings = VALUES(column_settings), updated_at = NOW()");
        $stmt->execute([
            ':user_id' => $user_id,
            ':page_name' => $page_name,
            ':column_settings' => json_encode($decoded)
        ]);

        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลลง DB เรียบร้อย']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'load') {
    try {
        $stmt = $conn->prepare("SELECT column_settings FROM ims_user_column_config WHERE user_id = :user_id AND page_name = :page_name LIMIT 1");
        $stmt->execute([
            ':user_id' => $user_id,
            ':page_name' => $page_name
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['column_settings'])) {
            echo json_encode([
                'success' => true, 
                'exists' => true, 
                'column_settings' => json_decode($row['column_settings'], true)
            ]);
        } else {
            echo json_encode(['success' => true, 'exists' => false, 'column_settings' => null]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'reset') {
    try {
        $stmt = $conn->prepare("DELETE FROM ims_user_column_config WHERE user_id = :user_id AND page_name = :page_name");
        $stmt->execute([
            ':user_id' => $user_id,
            ':page_name' => $page_name
        ]);
        echo json_encode(['success' => true, 'message' => 'รีเซ็ตข้อมูลใน DB เรียบร้อย']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
