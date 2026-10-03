<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$requested_brands = $_POST['brands'] ?? []; // Array of selected checkbox values

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$group_at_leao_llit = $_POST['group_at_leao_llit'] ?? 'N';

// Define mapping for each checkbox value to actual brand_codes
$brand_map = [
    'AT' => ($group_at_leao_llit === 'Y') ? ['AT', 'LEAO', 'LLIT'] : ['AT'],
    'LEAO' => ($group_at_leao_llit === 'Y') ? ['AT', 'LEAO', 'LLIT'] : ['LEAO'],
    'LLIT' => ($group_at_leao_llit === 'Y') ? ['AT', 'LEAO', 'LLIT'] : ['LLIT'],
    'BS' => ['BS'],
    'FS' => ['FS'],
    'DT' => ['DT'],
    'ML' => ['ML'],
    'DS' => ['DS'],
    'DL' => ['DL'],
    'PR' => ['PR'],
    'VB' => ['VB'],
    'WESTLAKE' => ['WESTLAKE'],
    'BS/FS' => ['BS', 'FS'],
    'BS/FS/DT' => ['BS', 'FS', 'DT'],
    'DT/FS/DT' => ['DT', 'FS'],
    'FS/DT' => ['FS', 'DT'],
    'ยางเล็ก' => ['ยางเล็ก'],
    'TBR/LTR (ยางใหญ่)' => ['TBR/LTR (ยางใหญ่)', 'TBR/LTR', 'LTR'],
    'Off The Road' => ['Off The Road', 'OTR'],
    'TBB/LTB (ยางผ้าใบ)' => ['TBB/LTB (ยางผ้าใบ)', 'TBB/LTB', 'TBB', 'LTB'],
    'ยางใน' => ['ยางใน'],
    'ยางรอง' => ['ยางรอง'],
    'TBR' => ['TBR'],
    'กระทะล้อ' => ['กระทะล้อ', 'WHEEL'],
    'LSR' => ['LSR'],
    'TBR/LTB' => ['TBR/LTB'],
    'ยางเคลม' => ['ยางเคลม', 'เคลม'],
    'แบตเตอรี่' => ['แบตเตอรี่', 'BATTERY', 'BATT']
];

// Defined single brands to exclude for "OTHER"
$defined_brands = [
    'AT', 'LEAO', 'LLIT', 'BS', 'FS', 'DT', 'ML', 'DS', 'DL', 'PR', 'VB', 'WESTLAKE',
    'ยางเล็ก', 'TBR/LTR (ยางใหญ่)', 'TBR/LTR', 'LTR', 'Off The Road', 'OTR',
    'TBB/LTB (ยางผ้าใบ)', 'TBB/LTB', 'TBB', 'LTB', 'ยางใน', 'ยางรอง',
    'TBR', 'กระทะล้อ', 'WHEEL', 'LSR', 'TBR/LTB', 'ยางเคลม', 'เคลม', 'แบตเตอรี่', 'BATTERY', 'BATT'
];

// Normalize requested brands
$groups_to_fetch = [];
foreach ($requested_brands as $rb) {
    if (in_array($rb, ['AT', 'LEAO', 'LLIT']) && $group_at_leao_llit === 'Y') {
        $groups_to_fetch['AT / LEAO / LLIT'] = ['AT', 'LEAO', 'LLIT'];
    } elseif (isset($brand_map[$rb])) {
        $groups_to_fetch[$rb] = $brand_map[$rb];
    } elseif ($rb === 'OTHER') {
        $groups_to_fetch['ยี่ห้ออื่นๆ'] = 'OTHER';
    }
}

// If "All" was checked, groups_to_fetch might be empty if we didn't handle it in JS.
// In JS, if brandAll is checked, selectedBrands is empty.
if (empty($requested_brands)) {
    // Default show all in groups
    if ($group_at_leao_llit === 'Y') {
        $groups_to_fetch = [
            'AT / LEAO / LLIT' => ['AT', 'LEAO', 'LLIT'],
            'BS' => ['BS'],
            'FS' => ['FS'],
            'DT' => ['DT'],
            'ML' => ['ML'],
            'DS' => ['DS'],
            'DL' => ['DL'],
            'PR' => ['PR'],
            'VB' => ['VB'],
            'WESTLAKE' => ['WESTLAKE'],
            'ยางเล็ก' => ['ยางเล็ก'],
            'TBR/LTR (ยางใหญ่)' => ['TBR/LTR (ยางใหญ่)', 'TBR/LTR', 'LTR'],
            'Off The Road' => ['Off The Road', 'OTR'],
            'TBB/LTB (ยางผ้าใบ)' => ['TBB/LTB (ยางผ้าใบ)', 'TBB/LTB', 'TBB', 'LTB'],
            'ยางใน' => ['ยางใน'],
            'ยางรอง' => ['ยางรอง'],
            'TBR' => ['TBR'],
            'กระทะล้อ' => ['กระทะล้อ', 'WHEEL'],
            'LSR' => ['LSR'],
            'TBR/LTB' => ['TBR/LTB'],
            'ยางเคลม' => ['ยางเคลม', 'เคลม'],
            'แบตเตอรี่' => ['แบตเตอรี่', 'BATTERY', 'BATT'],
            'ยี่ห้ออื่นๆ' => 'OTHER'
        ];
    } else {
        $groups_to_fetch = [
            'AT' => ['AT'],
            'LEAO' => ['LEAO'],
            'LLIT' => ['LLIT'],
            'BS' => ['BS'],
            'FS' => ['FS'],
            'DT' => ['DT'],
            'ML' => ['ML'],
            'DS' => ['DS'],
            'DL' => ['DL'],
            'PR' => ['PR'],
            'VB' => ['VB'],
            'WESTLAKE' => ['WESTLAKE'],
            'ยางเล็ก' => ['ยางเล็ก'],
            'TBR/LTR (ยางใหญ่)' => ['TBR/LTR (ยางใหญ่)', 'TBR/LTR', 'LTR'],
            'Off The Road' => ['Off The Road', 'OTR'],
            'TBB/LTB (ยางผ้าใบ)' => ['TBB/LTB (ยางผ้าใบ)', 'TBB/LTB', 'TBB', 'LTB'],
            'ยางใน' => ['ยางใน'],
            'ยางรอง' => ['ยางรอง'],
            'TBR' => ['TBR'],
            'กระทะล้อ' => ['กระทะล้อ', 'WHEEL'],
            'LSR' => ['LSR'],
            'TBR/LTB' => ['TBR/LTB'],
            'ยางเคลม' => ['ยางเคลม', 'เคลม'],
            'แบตเตอรี่' => ['แบตเตอรี่', 'BATTERY', 'BATT'],
            'ยี่ห้ออื่นๆ' => 'OTHER'
        ];
    }
}

$results = [];

foreach ($groups_to_fetch as $label => $codes) {
    $where = "WHERE forcast_month = :month AND forcast_year = :year";
    $params = [':month' => $month, ':year' => $year];

    // Security Filter
    if ($account_type === 'sale_user') {
        $where .= " AND sale_name = :first_name";
        $params[':first_name'] = $first_name;
    } elseif ($account_type === 'take') {
        $where .= " AND take_name = :first_name";
        $params[':first_name'] = $first_name;
    }

    // User Filter from UI
    if (!empty($sale_name)) {
        $where .= " AND sale_name = :sale_name";
        $params[':sale_name'] = $sale_name;
    }
    if (!empty($ar_name)) {
        $where .= " AND ar_name = :ar_name";
        $params[':ar_name'] = $ar_name;
    }

    // Brand Filter for this group
    if ($codes === 'OTHER') {
        $where .= " AND brand_code NOT IN ('" . implode("','", $defined_brands) . "')";
    } else {
        $where .= " AND brand_code IN ('" . implode("','", $codes) . "')";
    }

    $sql = "SELECT 
                SUM(avg_per_month_year) as total_avg_12m,
                SUM(sales_month_1) as total_m1,
                SUM(sales_month_2) as total_m2,
                SUM(sales_month_3) as total_m3,
                SUM(avg_3_month) as total_avg_3m,
                SUM(forecast_qty_month_1) as total_forecast,
                SUM(compare_forecast_month_1) as total_diff,
                (SELECT SUM(max_target) FROM (
                    SELECT MAX(target_qty_month) as max_target 
                    FROM ims_tires_plan_forecast 
                    $where 
                    GROUP BY ar_code
                ) as t1) as total_target,
                (SELECT SUM(max_target2) FROM (
                    SELECT MAX(target_qty_month_2) as max_target2 
                    FROM ims_tires_plan_forecast 
                    $where 
                    GROUP BY ar_code
                ) as t2) as total_target2
            FROM ims_tires_plan_forecast 
            $where";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && ($row['total_forecast'] !== null || $row['total_target'] !== null)) {
            $results[] = [
                'brand_code' => $label,
                'total_avg_12m' => $row['total_avg_12m'] ?: 0,
                'total_m1' => $row['total_m1'] ?: 0,
                'total_m2' => $row['total_m2'] ?: 0,
                'total_m3' => $row['total_m3'] ?: 0,
                'total_avg_3m' => $row['total_avg_3m'] ?: 0,
                'total_target' => $row['total_target'] ?: 0,
                'total_target2' => $row['total_target2'] ?: 0,
                'total_forecast' => $row['total_forecast'] ?: 0,
                'total_diff' => $row['total_diff'] ?: 0
            ];
        }
    } catch (Exception $e) {
        // Skip or handle error
    }
}

echo json_encode(['success' => true, 'data' => $results]);
?>
