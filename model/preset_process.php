<?php
session_start();
include('../config/connect_db.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$user_id = $_SESSION['alogin'];
$screen_id = isset($_POST['screen_id']) ? $_POST['screen_id'] : 'default';

// 1. SAVE / UPDATE
if ($action == 'save') {
    $preset_name = $_POST['preset_name'];
    // Receive the ID passed from the form (if updating)
    $preset_id   = isset($_POST['preset_id']) ? $_POST['preset_id'] : '';

    $data = [
        'icc_codes' => isset($_POST['icc_codes']) ? $_POST['icc_codes'] : [],
        'brn_codes' => isset($_POST['brn_codes']) ? $_POST['brn_codes'] : [],
        'wh_codes'  => isset($_POST['wh_codes'])  ? $_POST['wh_codes'] : [],
        'wl_codes'  => isset($_POST['wl_codes'])  ? $_POST['wl_codes'] : []
    ];

/*
    $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $action . " | " . $preset_id . " | " . $user_id . " | " . $screen_id);
    fclose($myfile);
*/

    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);

    if (!empty($preset_id)) {
        // --- UPDATE Existing Preset ---
        // Ensure user_id matches for security
        $sql = "UPDATE USER_EXPORT_PRESETS 
                SET preset_name = ?, preset_data = ?, updated_at = NOW() 
                WHERE preset_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$preset_name, $json_data, $preset_id, $user_id])) {
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'แก้ไขข้อมูลไม่สำเร็จ']);
        }
    } else {
        // --- INSERT New Preset ---
        $sql = "INSERT INTO USER_EXPORT_PRESETS (user_id, screen_id, preset_name, preset_data) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$user_id, $screen_id, $preset_name, $json_data])) {
            echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลใหม่สำเร็จ']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
        }
    }
}

// 2. LOAD
if ($action == 'load') {
    $preset_id = $_POST['preset_id'];
    $sql = "SELECT preset_data FROM USER_EXPORT_PRESETS WHERE preset_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$preset_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo $result['preset_data'];
    } else {
        echo json_encode([]);
    }
}

// 3. DELETE
if ($action == 'delete') {
    $preset_id = $_POST['preset_id'];
    $sql = "DELETE FROM USER_EXPORT_PRESETS WHERE preset_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if($stmt->execute([$preset_id, $user_id])) {
        echo json_encode(['status' => 'success']);
    }
}