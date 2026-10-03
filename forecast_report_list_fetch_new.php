<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$draw = intval($_POST['draw'] ?? 0);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = $_POST['search']['value'] ?? '';

$where = "WHERE 1=1";
$params = [];

// Security Filter
if ($account_type === 'sale_user') {
    // Regular Sale User sees only their own data
    $where .= " AND sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    // Take User sees only their own data
    $where .= " AND take_name = :first_name";
    $params[':first_name'] = $first_name;
} 
// Note: 'admin' and 'sale_sup' can see all data (no filter added)

// Search Filter
if (!empty($search)) {
    $where .= " AND (ar_code LIKE :search OR ar_name LIKE :search OR sale_name LIKE :search)";
    $params[':search'] = "%$search%";
}

// Total records (Grouped)
$sql_count = "SELECT COUNT(*) FROM (
                SELECT ar_code, ar_name, forcast_month, forcast_year, sale_name, take_name, import_date 
                FROM ims_tires_plan_forecast 
                $where 
                GROUP BY ar_code, ar_name, forcast_month, forcast_year, sale_name, take_name, import_date
              ) as t";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$recordsTotal = $stmt_count->fetchColumn();

// Fetch Data
$sql = "SELECT ar_code, ar_name, forcast_month, forcast_year, sale_name, take_name, import_date 
        FROM ims_tires_plan_forecast 
        $where 
        GROUP BY ar_code, ar_name, forcast_month, forcast_year, sale_name, take_name, import_date
        ORDER BY import_date DESC, forcast_year DESC, forcast_month DESC 
        LIMIT :start, :length";

$stmt = $conn->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$thai_months = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
    4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
    7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$result = [];
foreach ($data as $row) {
    $month_name = $thai_months[intval($row['forcast_month'])] ?? $row['forcast_month'];
    $result[] = [
        'ar_code' => $row['ar_code'],
        'ar_name' => $row['ar_name'],
        'forecast_period' => $month_name . ' ' . $row['forcast_year'],
        'sale_name' => $row['sale_name'],
        'take_name' => $row['take_name'],
        'import_date' => date('d/m/Y H:i', strtotime($row['import_date'])),
        'actions' => '<button class="btn btn-primary btn-sm btn-view" 
                        data-month="'.$row['forcast_month'].'" 
                        data-year="'.$row['forcast_year'].'" 
                        data-sale="'.htmlspecialchars($row['sale_name']).'" 
                        data-customer="'.htmlspecialchars($row['ar_name']).'">
                        <i class="fas fa-search me-1"></i> ดูรายละเอียด
                      </button>'
    ];
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsTotal,
    'data' => $result
]);
?>
