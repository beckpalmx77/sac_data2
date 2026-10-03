<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? 'unlock'; // 'lock' or 'unlock'
$account_type = $_SESSION['account_type'] ?? '';

if ($action === 'unlock') {
    if (!in_array($account_type, ['admin', 'sale_sup'])) {
        echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการปลดล็อคข้อมูล (เฉพาะ Admin หรือ หัวหน้างาน)']);
        exit;
    }
} else {
    // action == 'lock'
    if (!in_array($account_type, ['admin', 'sale_sup', 'sale_user', 'take'])) {
        echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการล็อคข้อมูล']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุเดือนและปี']);
    exit;
}

$month_padded = str_pad($month, 2, '0', STR_PAD_LEFT);
$month_int = strval(intval($month));

try {
    $where = "WHERE (forcast_month = :m1 OR forcast_month = :m2 OR forcast_month = :m3) AND forcast_year = :year";
    $params = [
        ':m1' => $month,
        ':m2' => $month_padded,
        ':m3' => $month_int,
        ':year' => $year
    ];

    if ($account_type === 'sale_user') {
        $where .= " AND sale_name = :session_first_name";
        $params[':session_first_name'] = $_SESSION['first_name'] ?? '';
    } elseif ($account_type === 'take') {
        $where .= " AND take_name = :session_first_name";
        $params[':session_first_name'] = $_SESSION['first_name'] ?? '';
    } else {
        if (!empty($_POST['sale_name'])) {
            $where .= " AND sale_name = :sale_name";
            $params[':sale_name'] = $_POST['sale_name'];
        }
    }
    if (!empty($_POST['ar_name'])) {
        $where .= " AND ar_name = :ar_name";
        $params[':ar_name'] = $_POST['ar_name'];
    }

    if ($action === 'lock') {
        $sql = "UPDATE ims_tires_plan_forecast_realtime_big_tirs 
                SET is_locked = 1, locked_at = CURRENT_TIMESTAMP, locked_by = :user 
                $where";
        $params[':user'] = $_SESSION['alogin'];
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->rowCount();
        echo json_encode([
            'success' => true, 
            'message' => "ล็อคข้อมูล Big Tires ประจำเดือน $month/$year เรียบร้อยแล้ว ($count รายการ)",
            'count' => $count
        ]);
    } else {
        $sql = "UPDATE ims_tires_plan_forecast_realtime_big_tirs 
                SET is_locked = 0, locked_at = NULL, locked_by = NULL 
                $where";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->rowCount();
        echo json_encode([
            'success' => true, 
            'message' => "ปลดล็อคข้อมูล Big Tires ประจำเดือน $month/$year เรียบร้อยแล้ว ($count รายการ) สามารถแก้ไขได้",
            'count' => $count
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
