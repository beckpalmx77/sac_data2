<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
include(__DIR__ . '/../config/connect_db.php');

header('Content-Type: application/json');

// Set resource limits for large imports
ini_set('memory_limit', '512M');
set_time_limit(300);

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $forcast_month = intval($_POST['forcast_month']);
    $forcast_year = intval($_POST['forcast_year']);
    $file = $_FILES['excel_file']['tmp_name'];

    if (empty($forcast_month) || empty($forcast_year)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาเลือกเดือนและปี']);
        exit;
    }

    try {
        // Load the Excel file.
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // toArray(nullValue, calculateFormulas, formatData, returnCellRef)
        $rows = $worksheet->toArray(null, true, false, false);

        if (count($rows) < 2) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลในไฟล์ Excel']);
            exit;
        }

        // 1. Map columns dynamically - detect header row index
        $headerRowIdx = 0;
        foreach ($rows as $rIdx => $rData) {
            if (!is_array($rData)) continue;
            foreach ($rData as $cellVal) {
                if ($cellVal !== null && strpos(trim((string)$cellVal), 'รหัสลูกค้า') !== false) {
                    $headerRowIdx = $rIdx;
                    break 2;
                }
            }
            if ($rIdx >= 5) break;
        }
        $headers = $rows[$headerRowIdx];
        
        // Define default column index mappings for big_tires.xlsx
        $idx_ar_code = 0;
        $idx_ar_name = 1;
        $idx_sale_name = 2;
        $idx_take_name = 3;
        $idx_amphur = 4;
        $idx_province = 5;
        $idx_brand_code = 6;
        $idx_product_type = 7;
        $idx_sku_code = 8;
        $idx_sku_name = 9;
        
        $idx_avg_per_month_year = 10;
        $idx_max_qty_year = 11;
        $idx_min_qty_year = 12;
        $idx_sales_month_3 = 13;
        $idx_sales_month_2 = 14;
        $idx_sales_month_1 = 15;
        $idx_avg_3_month = 16;
        $idx_target_qty_month = 17;
        $idx_forecast_qty_month_1 = 18;
        $idx_compare_forecast_month_1 = 19;
        $idx_compare_sales_percent = 20;
        $idx_responsible_person = 21;

        // Find matches in header row to override defaults dynamically
        foreach ($headers as $colIdx => $headerVal) {
            if ($headerVal === null || $headerVal === '') continue;
            $headerVal = trim($headerVal);
            
            if (strpos($headerVal, 'รหัสลูกค้า') !== false) {
                $idx_ar_code = $colIdx;
            } elseif (strpos($headerVal, 'รายชื่อลูกค้า') !== false || strpos($headerVal, 'ชื่อลูกค้า') !== false) {
                $idx_ar_name = $colIdx;
            } elseif (strpos($headerVal, 'เซลล์') !== false || strpos($headerVal, 'เซลส์') !== false || strpos($headerVal, 'ผู้แทนขาย') !== false) {
                $idx_sale_name = $colIdx;
            } elseif (strpos($headerVal, 'เทค') !== false || strpos($headerVal, 'Take') !== false || strpos($headerVal, 'take') !== false) {
                $idx_take_name = $colIdx;
            } elseif (strpos($headerVal, 'อำเภอ') !== false) {
                $idx_amphur = $colIdx;
            } elseif (strpos($headerVal, 'จังหวัด') !== false) {
                $idx_province = $colIdx;
            } elseif (strpos($headerVal, 'ยี่ห้อ') !== false) {
                $idx_brand_code = $colIdx;
            } elseif (strpos($headerVal, 'ประเภท') !== false) {
                $idx_product_type = $colIdx;
            } elseif (strpos($headerVal, 'รหัสสินค้า 1') !== false || strpos($headerVal, 'รหัสสินค้า') !== false) {
                $idx_sku_code = $colIdx;
            } elseif (strpos($headerVal, 'รายละเอียด') !== false || strpos($headerVal, 'ชื่อสินค้า') !== false) {
                $idx_sku_name = $colIdx;
            } elseif (strpos($headerVal, 'เฉลี่ยต่อเดือน ปี') !== false || strpos($headerVal, 'เฉลี่ยต่อเดือนปี') !== false || strpos($headerVal, 'เฉลี่ย 12 เดือน') !== false) {
                $idx_avg_per_month_year = $colIdx;
            } elseif (strpos($headerVal, 'ยอดขายสูงสุดต่อเดือน') !== false) {
                $idx_max_qty_year = $colIdx;
            } elseif (strpos($headerVal, 'ยอดขายต่ำสุดต่อเดือน') !== false) {
                $idx_min_qty_year = $colIdx;
            } elseif (strpos($headerVal, 'ค่าเฉลี่ย 3 เดือน') !== false || strpos($headerVal, 'เฉลี่ย 3 เดือน') !== false) {
                $idx_avg_3_month = $colIdx;
            } elseif (strpos($headerVal, 'เป้ารายเดือน') !== false) {
                $idx_target_qty_month = $colIdx;
            } elseif (strpos($headerVal, 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย %') !== false || strpos($headerVal, 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย%') !== false) {
                $idx_compare_sales_percent = $colIdx;
            } elseif (strpos($headerVal, 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย') !== false || strpos($headerVal, 'เทียบ แพลนยาง') !== false) {
                $idx_compare_forecast_month_1 = $colIdx;
            } elseif ((strpos($headerVal, 'แพลนยาง') !== false || strpos($headerVal, 'คาดการณ์') !== false) && strpos($headerVal, 'เทียบ') === false) {
                $idx_forecast_qty_month_1 = $colIdx;
            } elseif (strpos($headerVal, 'ผู้รับผิดชอบ') !== false || strpos($headerVal, 'ผู้ดูแล') !== false) {
                $idx_responsible_person = $colIdx;
            }
        }

        // Map June, May, April sales dynamically based on selected month
        $thai_months_full = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        $thai_months_short = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.',
            4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.',
            10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        // M-1, M-2, M-3
        $m1 = $forcast_month - 1; if ($m1 <= 0) $m1 += 12;
        $m2 = $forcast_month - 2; if ($m2 <= 0) $m2 += 12;
        $m3 = $forcast_month - 3; if ($m3 <= 0) $m3 += 12;

        $m1_name_full = $thai_months_full[$m1];
        $m1_name_short = $thai_months_short[$m1];
        $m2_name_full = $thai_months_full[$m2];
        $m2_name_short = $thai_months_short[$m2];
        $m3_name_full = $thai_months_full[$m3];
        $m3_name_short = $thai_months_short[$m3];

        foreach ($headers as $colIdx => $headerVal) {
            if ($headerVal === null || $headerVal === '') continue;
            $headerVal = trim($headerVal);
            
            // Avoid matching calculated summaries like average, plan, target
            if (strpos($headerVal, 'เฉลี่ย') !== false || strpos($headerVal, 'แพลน') !== false || strpos($headerVal, 'เป้า') !== false || strpos($headerVal, 'เทียบ') !== false) {
                continue;
            }

            if (strpos($headerVal, $m1_name_full) !== false || strpos($headerVal, $m1_name_short) !== false) {
                $idx_sales_month_1 = $colIdx;
            } elseif (strpos($headerVal, $m2_name_full) !== false || strpos($headerVal, $m2_name_short) !== false) {
                $idx_sales_month_2 = $colIdx;
            } elseif (strpos($headerVal, $m3_name_full) !== false || strpos($headerVal, $m3_name_short) !== false) {
                $idx_sales_month_3 = $colIdx;
            }
        }

        // 2. Load all salesperson teams into memory to optimize loop speed
        $stmt_teams = $conn->query("SELECT u.first_name, slt.SLT_NAME FROM ims_user u LEFT JOIN ims_slteam slt ON slt.SLT_KEY = u.manage_team_id WHERE u.first_name IS NOT NULL AND u.first_name != ''");
        $salesperson_teams = [];
        while ($team_row = $stmt_teams->fetch(PDO::FETCH_ASSOC)) {
            $salesperson_teams[trim($team_row['first_name'])] = $team_row['SLT_NAME'];
        }

        $conn->beginTransaction();

        // 3. Load all existing records for the month/year from ims_tires_plan_forecast_realtime_big_tirs
        $sql_load_all = "SELECT id, ar_code, sku_code, sale_name FROM ims_tires_plan_forecast_realtime_big_tirs WHERE forcast_month = ? AND forcast_year = ?";
        $stmt_load_all = $conn->prepare($sql_load_all);
        $stmt_load_all->execute([$forcast_month, $forcast_year]);
        $existing_records = [];
        while ($row_exist = $stmt_load_all->fetch(PDO::FETCH_ASSOC)) {
            $key = trim($row_exist['ar_code'] ?? '') . '||' . trim($row_exist['sku_code'] ?? '') . '||' . trim($row_exist['sale_name'] ?? '');
            $existing_records[$key] = $row_exist['id'];
        }

        // 4. Prepared Statements for database ops
        $sql_ins = "INSERT INTO ims_tires_plan_forecast_realtime_big_tirs (
            forcast_month, forcast_year, ar_code, ar_name, sale_name, take_name, team_name, brand_code, product_type, sku_code, sku_name, 
            amphur, province, zone, avg_per_month_year, max_qty_year, min_qty_year, 
            target_qty_month, sales_month_1, sales_month_2, sales_month_3, avg_3_month, 
            target_qty_month_2, forecast_qty_month_1, compare_forecast_month_1, 
            compare_sales_percent, remark, responsible_person, forecast_qty_month_2, compare_forecast_month_2, 
            compare_sales_percent_2, load_time
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 1
        )";
        $stmt_ins = $conn->prepare($sql_ins);

        $sql_upd = "UPDATE ims_tires_plan_forecast_realtime_big_tirs SET 
            ar_name = IF(? IS NOT NULL AND ? != '', ?, ar_name),
            sale_name = IF(? IS NOT NULL AND ? != '', ?, sale_name),
            take_name = IF(? IS NOT NULL AND ? != '', ?, take_name),
            team_name = IF(? IS NOT NULL AND ? != '', ?, team_name),
            brand_code = IF(? IS NOT NULL AND ? != '', ?, brand_code),
            product_type = IF(? IS NOT NULL AND ? != '', ?, product_type),
            sku_name = IF(? IS NOT NULL AND ? != '', ?, sku_name),
            amphur = IF(? IS NOT NULL AND ? != '', ?, amphur),
            province = IF(? IS NOT NULL AND ? != '', ?, province),
            avg_per_month_year = IF(? > 0, ?, avg_per_month_year),
            max_qty_year = IF(? > 0, ?, max_qty_year),
            min_qty_year = IF(? > 0, ?, min_qty_year),
            target_qty_month = ?,
            sales_month_1 = IF(? > 0, ?, sales_month_1),
            sales_month_2 = IF(? > 0, ?, sales_month_2),
            sales_month_3 = IF(? > 0, ?, sales_month_3),
            avg_3_month = IF(? > 0, ?, avg_3_month),
            forecast_qty_month_1 = ?,
            compare_forecast_month_1 = IF(? != 0, ?, compare_forecast_month_1),
            compare_sales_percent = IF(? != 0, ?, compare_sales_percent),
            responsible_person = IF(? IS NOT NULL AND ? != '', ?, responsible_person),
            import_date = CURRENT_TIMESTAMP, 
            load_time = 1
            WHERE id = ?";
        $stmt_upd = $conn->prepare($sql_upd);

        $count = 0;
        foreach ($rows as $index => $row) {
            if ($index <= $headerRowIdx) continue; // Skip header row and any title rows above it

            $ar_code = $row[$idx_ar_code] ?? null;
            $sale_name = $row[$idx_sale_name] ?? null;
            $sku_code = $row[$idx_sku_code] ?? null;

            // Skip rows that do not have client code, sale name, or product code, or header text
            if (empty($ar_code) || empty($sku_code) || empty($sale_name) || trim((string)$ar_code) === 'รหัสลูกค้า') {
                continue;
            }

            // Get salesperson team
            $team_name = null;
            if (!empty($sale_name)) {
                $team_name = $salesperson_teams[trim($sale_name)] ?? null;
            }

            // Helper to clean numeric values and return float
            $clean_num = function($val) {
                if ($val === null || $val === '') return 0.0;
                return (float)$val;
            };

            $raw_pct = $clean_num($row[$idx_compare_sales_percent] ?? 0);
            $compare_pct = $raw_pct * 100;
            $responsible_person = ($idx_responsible_person !== null && isset($row[$idx_responsible_person])) ? trim($row[$idx_responsible_person]) : null;

            $ar_name_val = $row[$idx_ar_name] ?? null;
            $take_name_val = $row[$idx_take_name] ?? null;
            $brand_code_val = $row[$idx_brand_code] ?? null;
            $product_type_val = ($idx_product_type !== null && isset($row[$idx_product_type])) ? trim($row[$idx_product_type]) : null;
            $sku_name_val = ($idx_sku_name !== null && isset($row[$idx_sku_name])) ? trim($row[$idx_sku_name]) : null;
            $amphur_val = $row[$idx_amphur] ?? null;
            $province_val = $row[$idx_province] ?? null;

            $avg_year_val = $clean_num($row[$idx_avg_per_month_year] ?? 0);
            $max_year_val = $clean_num($row[$idx_max_qty_year] ?? 0);
            $min_year_val = $clean_num($row[$idx_min_qty_year] ?? 0);
            $target_qty_val = $clean_num($row[$idx_target_qty_month] ?? 0);
            $sales_m1_val = $clean_num($row[$idx_sales_month_1] ?? 0);
            $sales_m2_val = $clean_num($row[$idx_sales_month_2] ?? 0);
            $sales_m3_val = $clean_num($row[$idx_sales_month_3] ?? 0);
            $avg_3m_val = $clean_num($row[$idx_avg_3_month] ?? 0);
            $forecast_qty_val = $clean_num($row[$idx_forecast_qty_month_1] ?? 0);
            $compare_forecast_val = $clean_num($row[$idx_compare_forecast_month_1] ?? 0);

            $lookup_key = trim($ar_code) . '||' . trim($sku_code) . '||' . trim($sale_name);
            $existing_id = $existing_records[$lookup_key] ?? null;

            if ($existing_id) {
                $params_upd = [
                    $ar_name_val, $ar_name_val, $ar_name_val,
                    $sale_name, $sale_name, $sale_name,
                    $take_name_val, $take_name_val, $take_name_val,
                    $team_name, $team_name, $team_name,
                    $brand_code_val, $brand_code_val, $brand_code_val,
                    $product_type_val, $product_type_val, $product_type_val,
                    $sku_name_val, $sku_name_val, $sku_name_val,
                    $amphur_val, $amphur_val, $amphur_val,
                    $province_val, $province_val, $province_val,
                    $avg_year_val, $avg_year_val,
                    $max_year_val, $max_year_val,
                    $min_year_val, $min_year_val,
                    $target_qty_val, // Column Q: เป้ารายเดือน
                    $sales_m1_val, $sales_m1_val,
                    $sales_m2_val, $sales_m2_val,
                    $sales_m3_val, $sales_m3_val,
                    $avg_3m_val, $avg_3m_val,
                    $forecast_qty_val, // Column R: แพลนยาง
                    $compare_forecast_val, $compare_forecast_val,
                    $compare_pct, $compare_pct,
                    $responsible_person, $responsible_person, $responsible_person,
                    $existing_id
                ];
                $stmt_upd->execute($params_upd);
            } else {
                $params_ins_final = [
                    $forcast_month, $forcast_year, $ar_code,
                    $ar_name_val, $sale_name, $take_name_val, $team_name, $brand_code_val, $product_type_val, $sku_code, $sku_name_val,
                    $amphur_val, $province_val, null,
                    $avg_year_val, $max_year_val, $min_year_val,
                    $target_qty_val, $sales_m1_val, $sales_m2_val, $sales_m3_val, $avg_3m_val,
                    0.0, $forecast_qty_val, $compare_forecast_val,
                    $compare_pct, null, $responsible_person, 0.0, 0.0, 0.0
                ];
                $stmt_ins->execute($params_ins_final);
                $existing_records[$lookup_key] = $conn->lastInsertId();
            }
            $count++;
        }

        // 5. Sync customer amphur and province from v_customer_data_sale_all_zone
        $sql_sync = "UPDATE ims_tires_plan_forecast_realtime_big_tirs p
                     INNER JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
                     SET 
                         p.amphur = IF(p.amphur = '' OR p.amphur IS NULL, v.TRD_AMPHUR, p.amphur),
                         p.province = IF(p.province = '' OR p.province IS NULL, v.TRD_PROVINCE, p.province),
                         p.zone = IF(p.zone = '' OR p.zone IS NULL, v.ZONE, p.zone)
                     WHERE p.forcast_month = ? AND p.forcast_year = ?";
        $stmt_sync = $conn->prepare($sql_sync);
        $stmt_sync->execute([$forcast_month, $forcast_year]);

        // 6. Sync salesperson and tech from v_sale_cust_sac
        $sql_sync_sale = "UPDATE ims_tires_plan_forecast_realtime_big_tirs p
                          INNER JOIN v_sale_cust_sac v ON p.ar_code = v.AR_CODE
                          SET 
                              p.sale_name = IF(p.sale_name = '' OR p.sale_name IS NULL, v.SALE_NAME, p.sale_name),
                              p.take_name = IF(p.take_name = '' OR p.take_name IS NULL, v.TAKE_NAME, p.take_name)
                          WHERE p.forcast_month = ? AND p.forcast_year = ?";
        $stmt_sync_sale = $conn->prepare($sql_sync_sale);
        $stmt_sync_sale->execute([$forcast_month, $forcast_year]);

        // 7. Auto-sync historical sales statistics from database if empty, and recalculate compare fields
        $m1_p = str_pad((string)$m1, 2, '0', STR_PAD_LEFT);
        $m2_p = str_pad((string)$m2, 2, '0', STR_PAD_LEFT);
        $m3_p = str_pad((string)$m3, 2, '0', STR_PAD_LEFT);

        $y1 = ($forcast_month - 1 <= 0) ? $forcast_year - 1 : $forcast_year;
        $y2 = ($forcast_month - 2 <= 0) ? $forcast_year - 1 : $forcast_year;
        $y3 = ($forcast_month - 3 <= 0) ? $forcast_year - 1 : $forcast_year;
        $ly = $forcast_year - 1;

        $sql_auto_sync = "UPDATE ims_tires_plan_forecast_realtime_big_tirs p
        LEFT JOIN (
            SELECT AR_CODE, SKU_CODE, 
                SUM(CASE WHEN (DI_MONTH = ? OR DI_MONTH = ?) AND DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s1,
                SUM(CASE WHEN (DI_MONTH = ? OR DI_MONTH = ?) AND DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s2,
                SUM(CASE WHEN (DI_MONTH = ? OR DI_MONTH = ?) AND DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s3,
                SUM(CASE WHEN DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 12 as avg_ly
            FROM ims_data_sale_sac_all
            WHERE ((DI_YEAR = ? AND (DI_MONTH = ? OR DI_MONTH = ?)) 
                OR (DI_YEAR = ? AND (DI_MONTH = ? OR DI_MONTH = ?)) 
                OR (DI_YEAR = ? AND (DI_MONTH = ? OR DI_MONTH = ?)) 
                OR (DI_YEAR = ?))
            GROUP BY AR_CODE, SKU_CODE
        ) s ON p.ar_code = s.AR_CODE AND p.sku_code = s.SKU_CODE
        SET 
            p.sales_month_1 = IF(p.sales_month_1 = 0, IFNULL(s.s1, 0), p.sales_month_1),
            p.sales_month_2 = IF(p.sales_month_2 = 0, IFNULL(s.s2, 0), p.sales_month_2),
            p.sales_month_3 = IF(p.sales_month_3 = 0, IFNULL(s.s3, 0), p.sales_month_3),
            p.avg_per_month_year = IF(p.avg_per_month_year = 0, IFNULL(s.avg_ly, 0), p.avg_per_month_year),
            p.avg_3_month = IF(p.avg_3_month = 0, (p.sales_month_1 + p.sales_month_2 + p.sales_month_3) / 3, p.avg_3_month),
            p.compare_forecast_month_1 = IF(p.avg_3_month > 0, p.forecast_qty_month_1 - p.avg_3_month, p.compare_forecast_month_1),
            p.compare_sales_percent = IF(p.avg_3_month > 0, (p.forecast_qty_month_1 / p.avg_3_month) * 100, p.compare_sales_percent)
        WHERE p.forcast_month = ? AND p.forcast_year = ?";
        
        $stmt_auto_sync = $conn->prepare($sql_auto_sync);
        $stmt_auto_sync->execute([
            $m1, $m1_p, $y1, $m2, $m2_p, $y2, $m3, $m3_p, $y3, $ly,
            $y1, $m1, $m1_p, $y2, $m2, $m2_p, $y3, $m3, $m3_p, $ly,
            $forcast_month, $forcast_year
        ]);

        $conn->commit();
        echo json_encode(['success' => true, 'count' => $count]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
