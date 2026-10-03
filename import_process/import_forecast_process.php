<?php
session_start();
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
include('../config/connect_db.php');

header('Content-Type: application/json');

// Set resource limits for large imports
ini_set('memory_limit', '512M');
set_time_limit(300);

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $forcast_month = $_POST['forcast_month'];
    $forcast_year = $_POST['forcast_year'];
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        // Optimize PhpSpreadsheet load by reading only raw data (ignoring styles/formatting)
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Guarantee Database Indexes for High Performance
        try { $conn->exec("CREATE INDEX idx_forecast_m_y ON ims_tires_plan_forecast (forcast_year, forcast_month, ar_code)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_ar_code ON ims_tires_plan_forecast (ar_code)"); } catch (Exception $e) {}

        $conn->beginTransaction();

        // Optimize database speed: Load all existing records for the given month/year at once
        $sql_load_all = "SELECT id, ar_code, sku_code, sale_name FROM ims_tires_plan_forecast WHERE forcast_month = ? AND forcast_year = ?";
        $stmt_load_all = $conn->prepare($sql_load_all);
        $stmt_load_all->execute([$forcast_month, $forcast_year]);
        $existing_records = [];
        while ($row_exist = $stmt_load_all->fetch(PDO::FETCH_ASSOC)) {
            $key = trim($row_exist['ar_code'] ?? '') . '||' . trim($row_exist['sku_code'] ?? '') . '||' . trim($row_exist['sale_name'] ?? '');
            $existing_records[$key] = $row_exist['id'];
        }

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
            ar_name = IF(? IS NOT NULL AND ? != '', ?, ar_name),
            sale_name = IF(? IS NOT NULL AND ? != '', ?, sale_name),
            take_name = IF(? IS NOT NULL AND ? != '', ?, take_name),
            team_name = IF(? IS NOT NULL AND ? != '', ?, team_name),
            brand_code = IF(? IS NOT NULL AND ? != '', ?, brand_code),
            amphur = IF(? IS NOT NULL AND ? != '', ?, amphur),
            province = IF(? IS NOT NULL AND ? != '', ?, province),
            zone = IF(? IS NOT NULL AND ? != '', ?, zone),
            avg_per_month_year = IF(? > 0, ?, avg_per_month_year),
            max_qty_year = IF(? > 0, ?, max_qty_year),
            min_qty_year = IF(? > 0, ?, min_qty_year),
            target_qty_month = ?,
            sales_month_1 = IF(? > 0, ?, sales_month_1),
            sales_month_2 = IF(? > 0, ?, sales_month_2),
            sales_month_3 = IF(? > 0, ?, sales_month_3),
            avg_3_month = IF(? > 0, ?, avg_3_month),
            target_qty_month_2 = IF(? > 0, ?, target_qty_month_2),
            forecast_qty_month_1 = ?,
            compare_forecast_month_1 = IF(? != 0, ?, compare_forecast_month_1),
            compare_sales_percent = IF(? != 0, ?, compare_sales_percent),
            remark = IF(? IS NOT NULL AND ? != '', ?, remark),
            forecast_qty_month_2 = IF(? > 0, ?, forecast_qty_month_2),
            compare_forecast_month_2 = IF(? != 0, ?, compare_forecast_month_2),
            compare_sales_percent_2 = IF(? != 0, ?, compare_sales_percent_2),
            import_date = CURRENT_TIMESTAMP, 
            load_time = 0
            WHERE id = ?";
        $stmt_upd = $conn->prepare($sql_upd);

        $count = 0;
        foreach ($rows as $index => $row) {
            if ($index < 2) continue; // Skip headers (Rows 1-2)

            $ar_code = $row[0] ?? null;
            $sale_name = $row[2] ?? null;
            $sku_code = $row[6] ?? null;

            // Skip rows that do not have a customer code, salesperson name, or product code
            if (empty($ar_code) || empty($sku_code) || empty($sale_name)) continue;

            $ar_name_val = $row[1] ?? null;
            $take_name_val = $row[3] ?? null;
            $team_name_val = $row[4] ?? null;
            $brand_code_val = $row[5] ?? null;
            $amphur_val = $row[7] ?? null;
            $province_val = $row[8] ?? null;
            $zone_val = $row[9] ?? null;

            $avg_year_val = (float)($row[10] ?? 0);
            $max_year_val = (float)($row[11] ?? 0);
            $min_year_val = (float)($row[12] ?? 0);
            $target_qty_val = (float)($row[13] ?? 0);
            $sales_m1_val = (float)($row[14] ?? 0);
            $sales_m2_val = (float)($row[15] ?? 0);
            $sales_m3_val = (float)($row[16] ?? 0);
            $avg_3m_val = (float)($row[17] ?? 0);
            $target_qty_m2_val = (float)($row[18] ?? 0);
            $forecast_qty_val = (float)($row[19] ?? 0);
            $compare_forecast_val = (float)($row[20] ?? 0);
            $compare_pct_val = (float)($row[21] ?? 0);
            $remark_val = $row[22] ?? null;
            $forecast_qty_m2_val = (float)($row[23] ?? 0);
            $compare_forecast_m2_val = (float)($row[24] ?? 0);
            $compare_pct_m2_val = (float)($row[25] ?? 0);

            // Use the in-memory lookup instead of querying DB for every single row
            $lookup_key = trim($ar_code) . '||' . trim($sku_code) . '||' . trim($sale_name);
            $existing_id = $existing_records[$lookup_key] ?? null;

            if ($existing_id) {
                $params_upd = [
                    $ar_name_val, $ar_name_val, $ar_name_val,
                    $sale_name, $sale_name, $sale_name,
                    $take_name_val, $take_name_val, $take_name_val,
                    $team_name_val, $team_name_val, $team_name_val,
                    $brand_code_val, $brand_code_val, $brand_code_val,
                    $amphur_val, $amphur_val, $amphur_val,
                    $province_val, $province_val, $province_val,
                    $zone_val, $zone_val, $zone_val,
                    $avg_year_val, $avg_year_val,
                    $max_year_val, $max_year_val,
                    $min_year_val, $min_year_val,
                    $target_qty_val, // Column Q: เป้ารายเดือน
                    $sales_m1_val, $sales_m1_val,
                    $sales_m2_val, $sales_m2_val,
                    $sales_m3_val, $sales_m3_val,
                    $avg_3m_val, $avg_3m_val,
                    $target_qty_m2_val, $target_qty_m2_val,
                    $forecast_qty_val, // Column R: แพลนยาง
                    $compare_forecast_val, $compare_forecast_val,
                    $compare_pct_val, $compare_pct_val,
                    $remark_val, $remark_val, $remark_val,
                    $forecast_qty_m2_val, $forecast_qty_m2_val,
                    $compare_forecast_m2_val, $compare_forecast_m2_val,
                    $compare_pct_m2_val, $compare_pct_m2_val,
                    $existing_id
                ];
                $stmt_upd->execute($params_upd);
            } else {
                $params_ins_final = [
                    $forcast_month, $forcast_year, $ar_code,
                    $ar_name_val, $sale_name, $take_name_val, $team_name_val, $brand_code_val, $sku_code,
                    $amphur_val, $province_val, $zone_val,
                    $avg_year_val, $max_year_val, $min_year_val,
                    $target_qty_val, $sales_m1_val, $sales_m2_val, $sales_m3_val, $avg_3m_val,
                    $target_qty_m2_val, $forecast_qty_val, $compare_forecast_val,
                    $compare_pct_val, $remark_val, $forecast_qty_m2_val, $compare_forecast_m2_val,
                    $compare_pct_m2_val
                ];
                $stmt_ins->execute($params_ins_final);
                // Cache the newly inserted ID in case the same key is duplicated later in the Excel file
                $existing_records[$lookup_key] = $conn->lastInsertId();
            }
            $count++;
        }

        // ซิงก์ที่อยู่ลูกค้า (อำเภอ, จังหวัด) จาก v_customer_data_sale_all_zone สำหรับข้อมูลที่นำเข้าของเดือน/ปีนี้
        $sql_sync = "UPDATE ims_tires_plan_forecast p
                     INNER JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
                     SET 
                         p.amphur = v.TRD_AMPHUR,
                         p.province = v.TRD_PROVINCE
                     WHERE p.forcast_month = ? AND p.forcast_year = ?
                       AND (v.TRD_AMPHUR IS NOT NULL AND v.TRD_AMPHUR != '' 
                            OR v.TRD_PROVINCE IS NOT NULL AND v.TRD_PROVINCE != '')";
        $stmt_sync = $conn->prepare($sql_sync);
        $stmt_sync->execute([$forcast_month, $forcast_year]);

        // ซิงก์พนักงานขาย และ คนเทคสินค้า (SALE_NAME, TAKE_NAME) จาก v_sale_cust_sac สำหรับข้อมูลที่นำเข้าของเดือน/ปีนี้
        $sql_sync_sale = "UPDATE ims_tires_plan_forecast p
                          INNER JOIN v_sale_cust_sac v ON p.ar_code = v.AR_CODE
                          SET 
                              p.sale_name = v.SALE_NAME,
                              p.take_name = v.TAKE_NAME
                          WHERE p.forcast_month = ? AND p.forcast_year = ?
                            AND (v.SALE_NAME IS NOT NULL AND v.SALE_NAME != '' 
                                 OR v.TAKE_NAME IS NOT NULL AND v.TAKE_NAME != '')";
        $stmt_sync_sale = $conn->prepare($sql_sync_sale);
        $stmt_sync_sale->execute([$forcast_month, $forcast_year]);

        $conn->commit();
        echo json_encode(['success' => true, 'count' => $count]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
