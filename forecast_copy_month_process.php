<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$source_month = $_POST['source_month'] ?? '';
$source_year = $_POST['source_year'] ?? '';
$target_month = $_POST['target_month'] ?? '';
$target_year = $_POST['target_year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';

if (empty($source_month) || empty($source_year) || empty($target_month) || empty($target_year)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเลือกเดือนและปีให้ครบถ้วน']);
    exit;
}

try {
    // 1. Get source data
    $sql_src = "SELECT * FROM ims_tires_plan_forecast WHERE forcast_month = :m AND forcast_year = :y";
    $params_src = [':m' => $source_month, ':y' => $source_year];
    
    if (!empty($sale_name)) {
        $sql_src .= " AND sale_name = :s";
        $params_src[':s'] = $sale_name;
    }
    
    $stmt_src = $conn->prepare($sql_src);
    $stmt_src->execute($params_src);
    $sources = $stmt_src->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sources)) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลต้นฉบับในเดือนที่เลือก']);
        exit;
    }

    $conn->beginTransaction();

    // 2. Prepare Check/Insert/Update statements
    $sql_check = "SELECT id FROM ims_tires_plan_forecast 
                  WHERE forcast_month = ? AND forcast_year = ? AND ar_code = ? AND sku_code = ? AND sale_name = ?";
    $stmt_check = $conn->prepare($sql_check);

    $sql_ins = "INSERT INTO ims_tires_plan_forecast (
        forcast_month, forcast_year, ar_code, ar_name, sale_name, take_name, team_name, brand_code, sku_code, 
        amphur, province, zone, avg_per_month_year, max_qty_year, min_qty_year, 
        target_qty_month, sales_month_1, sales_month_2, sales_month_3, avg_3_month, 
        target_qty_month_2, forecast_qty_month_1, compare_forecast_month_1, 
        compare_sales_percent, remark, forecast_qty_month_2, compare_forecast_month_2, 
        compare_sales_percent_2, load_time
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, 
        ?, ?, ?, 
        ?, ?, ?, ?, ?, 0
    )";
    $stmt_ins = $conn->prepare($sql_ins);

    $sql_upd = "UPDATE ims_tires_plan_forecast SET 
        ar_name = ?, take_name = ?, team_name = ?, brand_code = ?, 
        amphur = ?, province = ?, zone = ?, avg_per_month_year = ?, max_qty_year = ?, min_qty_year = ?, 
        target_qty_month = ?, sales_month_1 = ?, sales_month_2 = ?, sales_month_3 = ?, avg_3_month = ?, 
        target_qty_month_2 = ?, forecast_qty_month_1 = ?, compare_forecast_month_1 = ?, 
        compare_sales_percent = ?, remark = ?, forecast_qty_month_2 = ?, compare_forecast_month_2 = ?, 
        compare_sales_percent_2 = ?, import_date = CURRENT_TIMESTAMP, load_time = 0
        WHERE id = ?";
    $stmt_upd = $conn->prepare($sql_upd);

    $count = 0;
    foreach ($sources as $row) {
        $ar_code = $row['ar_code'];
        $sku_code = $row['sku_code'];
        $sale_name = $row['sale_name'];

        $stmt_check->execute([$target_month, $target_year, $ar_code, $sku_code, $sale_name]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            $stmt_ins->execute([
                $target_month, $target_year, $ar_code, $row['ar_name'], $sale_name, $row['take_name'], $row['team_name'], $row['brand_code'], $sku_code,
                $row['amphur'], $row['province'], $row['zone'], $row['avg_per_month_year'], $row['max_qty_year'], $row['min_qty_year'],
                $row['target_qty_month'], $row['sales_month_1'], $row['sales_month_2'], $row['sales_month_3'], $row['avg_3_month'],
                $row['target_qty_month_2'], $row['forecast_qty_month_1'], $row['compare_forecast_month_1'],
                $row['compare_sales_percent'], $row['remark'], $row['forecast_qty_month_2'], $row['compare_forecast_month_2'],
                $row['compare_sales_percent_2']
            ]);
            $count++;
        }
    }

    // ซิงก์ข้อมูลที่อยู่ลูกค้าล่าสุดสำหรับเดือน/ปีเป้าหมายที่เพิ่งคัดลอกมา
    $sql_sync = "UPDATE ims_tires_plan_forecast p
                 INNER JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
                 SET 
                     p.amphur = v.TRD_AMPHUR,
                     p.province = v.TRD_PROVINCE
                 WHERE p.forcast_month = ? AND p.forcast_year = ?
                   AND (v.TRD_AMPHUR IS NOT NULL AND v.TRD_AMPHUR != '' 
                        OR v.TRD_PROVINCE IS NOT NULL AND v.TRD_PROVINCE != '')";
    $stmt_sync = $conn->prepare($sql_sync);
    $stmt_sync->execute([$target_month, $target_year]);

    // ซิงก์พนักงานขาย และ คนเทคสินค้า (SALE_NAME, TAKE_NAME) จาก v_sale_cust_sac สำหรับเดือน/ปีเป้าหมายที่คัดลอกมา
    $sql_sync_sale = "UPDATE ims_tires_plan_forecast p
                      INNER JOIN v_sale_cust_sac v ON p.ar_code = v.AR_CODE
                      SET 
                          p.sale_name = v.SALE_NAME,
                          p.take_name = v.TAKE_NAME
                      WHERE p.forcast_month = ? AND p.forcast_year = ?
                        AND (v.SALE_NAME IS NOT NULL AND v.SALE_NAME != '' 
                             OR v.TAKE_NAME IS NOT NULL AND v.TAKE_NAME != '')";
    $stmt_sync_sale = $conn->prepare($sql_sync_sale);
    $stmt_sync_sale->execute([$target_month, $target_year]);

    $conn->commit();
    $msg = $count > 0 ? "คัดลอกข้อมูลใหม่ไปยังเดือน " . $target_month . "/" . $target_year . " เรียบร้อยแล้วจำนวน " . $count . " รายการ (ข้ามรายการที่มีอยู่แล้ว)" : "ไม่มีรายการใหม่ถูกคัดลอก (ข้อมูลมีอยู่แล้วทั้งหมด)";
    echo json_encode([
        'success' => true, 
        'message' => $msg,
        'target_month' => $target_month,
        'target_year' => $target_year
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
