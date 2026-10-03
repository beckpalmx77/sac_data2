<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
include(__DIR__ . '/../config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

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
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, false, false);

        if (count($rows) < 2) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลในไฟล์ Excel']);
            exit;
        }

        // Detect header row index
        $headerRowIdx = 0;
        foreach ($rows as $rIdx => $rData) {
            if (!is_array($rData)) continue;
            foreach ($rData as $cellVal) {
                $cStr = trim((string)$cellVal);
                if (strpos($cStr, 'เซลล์') !== false || strpos($cStr, 'รายชื่อลูกค้า') !== false || strpos($cStr, 'รหัสสินค้า') !== false) {
                    $headerRowIdx = $rIdx;
                    break 2;
                }
            }
            if ($rIdx >= 5) break;
        }
        $headers = $rows[$headerRowIdx];

        // Default column mapping for template_v_tires.xlsx (Col A to R)
        $idx_sale_name = 0;             // A: เซลล์
        $idx_take_name = 1;             // B: เทค
        $idx_ar_name = 2;               // C: รายชื่อลูกค้า
        $idx_province = 3;              // D: จังหวัด
        $idx_product_type = 4;          // E: ประเภท
        $idx_sku_code = 5;              // F: รหัสสินค้า
        $idx_sku_name = 6;              // G: รายละเอียด
        $idx_brand_code = 7;            // H: ยี่ห้อ
        $idx_max_qty_year = 8;          // I: ยอดขายสูงสุดต่อเดือน 2026
        $idx_min_qty_year = 9;          // J: ยอดขายต่ำสุดต่อเดือน 2026
        $idx_sales_month_1 = 10;        // K: M-3 (e.g. กรกฎาคม)
        $idx_sales_month_2 = 11;        // L: M-2 (e.g. สิงหาคม)
        $idx_sales_month_3 = 12;        // M: M-1 (e.g. กันยายน)
        $idx_avg_3_month = 13;          // N: ค่าเฉลี่ย 3 เดือน
        $idx_forecast_qty_month_1 = 14; // O: แพลนยางเดือนที่ต้องการ
        $idx_compare_forecast_month_1 = 15; // P: เทียบ แพลนยาง ลบ ค่าเฉลี่ย
        $idx_compare_sales_percent = 16;// Q: เทียบ แพลนยาง ลบ ค่าเฉลี่ย %
        $idx_responsible_person = 17;   // R: ผู้รับผิดชอบ
        $idx_ar_code = null;            // Not present in standard template, but could be present
        $idx_amphur = null;

        // Dynamic header matching
        foreach ($headers as $colIdx => $headerVal) {
            if ($headerVal === null || $headerVal === '') continue;
            $headerVal = trim((string)$headerVal);

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
            } elseif (strpos($headerVal, 'รหัสสินค้า') !== false) {
                $idx_sku_code = $colIdx;
            } elseif (strpos($headerVal, 'รายละเอียด') !== false || strpos($headerVal, 'ชื่อสินค้า') !== false) {
                $idx_sku_name = $colIdx;
            } elseif (strpos($headerVal, 'ยอดขายสูงสุดต่อเดือน') !== false) {
                $idx_max_qty_year = $colIdx;
            } elseif (strpos($headerVal, 'ยอดขายต่ำสุดต่อเดือน') !== false) {
                $idx_min_qty_year = $colIdx;
            } elseif (strpos($headerVal, 'ค่าเฉลี่ย 3 เดือน') !== false || strpos($headerVal, 'เฉลี่ย 3 เดือน') !== false) {
                $idx_avg_3_month = $colIdx;
            } elseif (strpos($headerVal, 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย %') !== false || strpos($headerVal, 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย%') !== false) {
                $idx_compare_sales_percent = $colIdx;
            } elseif (strpos($headerVal, 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย') !== false || strpos($headerVal, 'เทียบ แพลนยาง') !== false) {
                $idx_compare_forecast_month_1 = $colIdx;
            } elseif (strpos($headerVal, 'แพลนยางเดือนที่ต้องการ') !== false || (strpos($headerVal, 'แพลนยาง') !== false && strpos($headerVal, 'เทียบ') === false)) {
                $idx_forecast_qty_month_1 = $colIdx;
            } elseif (strpos($headerVal, 'ผู้รับผิดชอบ') !== false || strpos($headerVal, 'ผู้ดูแล') !== false) {
                $idx_responsible_person = $colIdx;
            }
        }

        // Map sales month dynamically based on month names if present
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

        foreach ($headers as $colIdx => $headerVal) {
            if ($headerVal === null || $headerVal === '') continue;
            $headerVal = trim((string)$headerVal);
            if (strpos($headerVal, 'เฉลี่ย') !== false || strpos($headerVal, 'แพลน') !== false || strpos($headerVal, 'เทียบ') !== false) {
                continue;
            }
            if (strpos($headerVal, $thai_months_full[$m1]) !== false || strpos($headerVal, $thai_months_short[$m1]) !== false) {
                $idx_sales_month_3 = $colIdx; // M-1 is latest back month
            } elseif (strpos($headerVal, $thai_months_full[$m2]) !== false || strpos($headerVal, $thai_months_short[$m2]) !== false) {
                $idx_sales_month_2 = $colIdx; // M-2
            } elseif (strpos($headerVal, $thai_months_full[$m3]) !== false || strpos($headerVal, $thai_months_short[$m3]) !== false) {
                $idx_sales_month_1 = $colIdx; // M-3
            }
        }

        // Load salesperson teams into memory
        $stmt_teams = $conn->query("SELECT u.first_name, slt.SLT_NAME FROM ims_user u LEFT JOIN ims_slteam slt ON slt.SLT_KEY = u.manage_team_id WHERE u.first_name IS NOT NULL AND u.first_name != ''");
        $salesperson_teams = [];
        while ($team_row = $stmt_teams->fetch(PDO::FETCH_ASSOC)) {
            $salesperson_teams[trim($team_row['first_name'])] = $team_row['SLT_NAME'];
        }

        // Cache customer lookup to optimize speed
        $customer_cache = [];
        $findCustomer = function($conn, $input) use (&$customer_cache) {
            $input = trim((string)$input);
            if (empty($input)) return null;
            if (isset($customer_cache[$input])) return $customer_cache[$input];

            // 1. Exact match in v_customer_data_sale_all_zone
            $stmt = $conn->prepare("SELECT AR_CODE, AR_NAME, TRD_AMPHUR, TRD_PROVINCE, ZONE FROM v_customer_data_sale_all_zone WHERE AR_NAME = ? LIMIT 1");
            $stmt->execute([$input]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $customer_cache[$input] = $row;
                return $row;
            }

            // 2. If contains parentheses, extract inner text and outer text
            if (preg_match("/(.*?)\s*\((.*?)\)/u", $input, $m)) {
                $outer = trim($m[1]);
                $inner = trim($m[2]);

                $stmt->execute([$inner]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $customer_cache[$input] = $row;
                    return $row;
                }

                $stmt->execute([$outer]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $customer_cache[$input] = $row;
                    return $row;
                }

                $stmt_like = $conn->prepare("SELECT AR_CODE, AR_NAME, TRD_AMPHUR, TRD_PROVINCE, ZONE FROM v_customer_data_sale_all_zone WHERE AR_NAME LIKE ? LIMIT 1");
                $stmt_like->execute(["%$inner%"]);
                if ($row = $stmt_like->fetch(PDO::FETCH_ASSOC)) {
                    $customer_cache[$input] = $row;
                    return $row;
                }

                $stmt_like->execute(["%$outer%"]);
                if ($row = $stmt_like->fetch(PDO::FETCH_ASSOC)) {
                    $customer_cache[$input] = $row;
                    return $row;
                }
            }

            // 3. Fallback LIKE input
            $stmt_like = $conn->prepare("SELECT AR_CODE, AR_NAME, TRD_AMPHUR, TRD_PROVINCE, ZONE FROM v_customer_data_sale_all_zone WHERE AR_NAME LIKE ? LIMIT 1");
            $stmt_like->execute(["%$input%"]);
            $row = $stmt_like->fetch(PDO::FETCH_ASSOC);
            $customer_cache[$input] = $row ?: null;
            return $row ?: null;
        };

        // Cache product lookup to optimize speed
        $product_cache = [];
        $findProduct = function($conn, $sku_desc, $sku_code_in = '') use (&$product_cache) {
            $desc = trim((string)$sku_desc);
            $sku_code_in = trim((string)$sku_code_in);
            $cache_key = $sku_code_in . '||' . $desc;
            if (isset($product_cache[$cache_key])) return $product_cache[$cache_key];

            if (!empty($sku_code_in)) {
                $s = $conn->prepare("SELECT SKU_CODE, SKU_NAME, BRAND, SKU_CAT, VOLUME FROM m_volume_tires WHERE SKU_CODE = ? LIMIT 1");
                $s->execute([$sku_code_in]);
                if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                    $product_cache[$cache_key] = $r;
                    return $r;
                }
            }

            if (empty($desc)) return null;

            $normalize = function($str) {
                $str = str_replace(["Ⅰ", "Ⅱ", "Ⅲ"], ["I", "II", "III"], $str);
                $str = preg_replace("/\s+/", " ", $str);
                return trim($str);
            };
            $norm_desc = $normalize($desc);

            // 1. Exact match in m_volume_tires
            $s = $conn->prepare("SELECT SKU_CODE, SKU_NAME, BRAND, SKU_CAT, VOLUME FROM m_volume_tires WHERE TRIM(SKU_NAME) = ? OR TRIM(SKU_NAME) = ? LIMIT 1");
            $s->execute([$desc, $norm_desc]);
            if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                $product_cache[$cache_key] = $r;
                return $r;
            }

            // 2. Exact match in ims_product
            $s = $conn->prepare("SELECT product_id as SKU_CODE, name_t as SKU_NAME, REPLACE(brand_id, '.', '') as BRAND, '' as SKU_CAT, 0.0 as VOLUME FROM ims_product WHERE TRIM(name_t) = ? OR TRIM(name_t) = ? LIMIT 1");
            $s->execute([$desc, $norm_desc]);
            if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                $product_cache[$cache_key] = $r;
                return $r;
            }

            // 3. LIKE in m_volume_tires
            $s = $conn->prepare("SELECT SKU_CODE, SKU_NAME, BRAND, SKU_CAT, VOLUME FROM m_volume_tires WHERE SKU_NAME LIKE ? LIMIT 1");
            $s->execute(["%$desc%"]);
            if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                $product_cache[$cache_key] = $r;
                return $r;
            }

            // 4. LIKE in ims_product
            $s = $conn->prepare("SELECT product_id as SKU_CODE, name_t as SKU_NAME, REPLACE(brand_id, '.', '') as BRAND, '' as SKU_CAT, 0.0 as VOLUME FROM ims_product WHERE name_t LIKE ? LIMIT 1");
            $s->execute(["%$desc%"]);
            if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                $product_cache[$cache_key] = $r;
                return $r;
            }

            // 5. Token match
            if (preg_match("/(KTT\d+|AP-?\d+[A-Z]*|R\d+[A-Z]*|VT-?\d+)/i", $desc, $m)) {
                $token = $m[1];
                $token_clean = str_replace("-", "", $token);
                $s = $conn->prepare("SELECT SKU_CODE, SKU_NAME, BRAND, SKU_CAT, VOLUME FROM m_volume_tires WHERE SKU_NAME LIKE ? OR SKU_CODE LIKE ? LIMIT 1");
                $s->execute(["%$token%", "%$token_clean%"]);
                if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                    $product_cache[$cache_key] = $r;
                    return $r;
                }

                $s = $conn->prepare("SELECT product_id as SKU_CODE, name_t as SKU_NAME, REPLACE(brand_id, '.', '') as BRAND, '' as SKU_CAT, 0.0 as VOLUME FROM ims_product WHERE name_t LIKE ? OR product_id LIKE ? LIMIT 1");
                $s->execute(["%$token%", "%$token_clean%"]);
                if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                    $product_cache[$cache_key] = $r;
                    return $r;
                }
            }

            $product_cache[$cache_key] = null;
            return null;
        };

        $conn->beginTransaction();

        // Load existing records for month/year from ims_tires_plan_forecast_volume_tires
        $sql_load_all = "SELECT id, ar_code, sku_code, sku_name, sale_name FROM ims_tires_plan_forecast_volume_tires WHERE forcast_month = ? AND forcast_year = ?";
        $stmt_load_all = $conn->prepare($sql_load_all);
        $stmt_load_all->execute([$forcast_month, $forcast_year]);
        $existing_records = [];
        while ($row_exist = $stmt_load_all->fetch(PDO::FETCH_ASSOC)) {
            $sku_key = !empty($row_exist['sku_code']) ? trim($row_exist['sku_code']) : trim($row_exist['sku_name'] ?? '');
            $key = trim($row_exist['ar_code'] ?? '') . '||' . $sku_key . '||' . trim($row_exist['sale_name'] ?? '');
            $existing_records[$key] = $row_exist['id'];
        }

        // Insert Statement
        $sql_ins = "INSERT INTO ims_tires_plan_forecast_volume_tires (
            forcast_month, forcast_year, ar_code, ar_name, sale_name, take_name, team_name, brand_code, product_type, sku_code, sku_name, volume,
            amphur, province, zone, avg_per_month_year, max_qty_year, min_qty_year, 
            target_qty_month, sales_month_1, sales_month_2, sales_month_3, avg_3_month, 
            target_qty_month_2, forecast_qty_month_1, compare_forecast_month_1, 
            compare_sales_percent, remark, responsible_person, forecast_qty_month_2, compare_forecast_month_2, 
            compare_sales_percent_2, load_time
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, 
            ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 1
        )";
        $stmt_ins = $conn->prepare($sql_ins);

        // Update Statement
        $sql_upd = "UPDATE ims_tires_plan_forecast_volume_tires SET 
            ar_name = IF(? IS NOT NULL AND ? != '', ?, ar_name),
            sale_name = IF(? IS NOT NULL AND ? != '', ?, sale_name),
            take_name = IF(? IS NOT NULL AND ? != '', ?, take_name),
            team_name = IF(? IS NOT NULL AND ? != '', ?, team_name),
            brand_code = IF(? IS NOT NULL AND ? != '', ?, brand_code),
            product_type = IF(? IS NOT NULL AND ? != '', ?, product_type),
            sku_name = IF(? IS NOT NULL AND ? != '', ?, sku_name),
            volume = IF(? > 0, ?, volume),
            amphur = IF(? IS NOT NULL AND ? != '', ?, amphur),
            province = IF(? IS NOT NULL AND ? != '', ?, province),
            max_qty_year = IF(? > 0, ?, max_qty_year),
            min_qty_year = IF(? > 0, ?, min_qty_year),
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

        $clean_num = function($val) {
            if ($val === null || $val === '') return 0.0;
            if (is_string($val) && (strpos($val, '#') !== false || strpos($val, 'สูตร') !== false)) return 0.0;
            return (float)$val;
        };

        $count = 0;
        foreach ($rows as $index => $row) {
            if ($index <= $headerRowIdx) continue;

            $sale_name_raw = $row[$idx_sale_name] ?? null;
            $take_name_raw = $row[$idx_take_name] ?? null;
            $ar_name_raw = $row[$idx_ar_name] ?? null;
            $sku_desc_raw = $row[$idx_sku_name] ?? null;
            $sku_code_raw = ($idx_sku_code !== null) ? ($row[$idx_sku_code] ?? null) : null;

            // Skip invalid / formula summary rows
            if (empty($ar_name_raw) && empty($sku_desc_raw)) continue;
            if (is_string($ar_name_raw) && strpos($ar_name_raw, 'สูตร') !== false) continue;
            if (is_string($sku_desc_raw) && strpos($sku_desc_raw, 'สูตร') !== false) continue;
            if (trim((string)$sale_name_raw) === 'เซลล์') continue;

            // 1. Resolve Customer
            $cust_info = $findCustomer($conn, $ar_name_raw);
            $ar_code = $cust_info ? $cust_info['AR_CODE'] : (($idx_ar_code !== null && !empty($row[$idx_ar_code])) ? trim($row[$idx_ar_code]) : '');
            $ar_name = $cust_info ? $cust_info['AR_NAME'] : trim((string)$ar_name_raw);
            $amphur = $cust_info ? ($cust_info['TRD_AMPHUR'] ?? '') : (($idx_amphur !== null && !empty($row[$idx_amphur])) ? trim($row[$idx_amphur]) : '');
            $province = !empty($row[$idx_province]) ? trim($row[$idx_province]) : ($cust_info['TRD_PROVINCE'] ?? '');
            $zone = $cust_info['ZONE'] ?? '';

            // 2. Resolve Product
            $prod_info = $findProduct($conn, $sku_desc_raw, $sku_code_raw);
            $sku_code = $prod_info ? $prod_info['SKU_CODE'] : trim((string)$sku_code_raw);
            $sku_name = !empty($sku_desc_raw) ? trim((string)$sku_desc_raw) : ($prod_info['SKU_NAME'] ?? '');
            $brand_code = !empty($row[$idx_brand_code]) ? trim($row[$idx_brand_code]) : ($prod_info['BRAND'] ?? '');
            $product_type = !empty($row[$idx_product_type]) ? trim($row[$idx_product_type]) : ($prod_info['SKU_CAT'] ?? '');
            $volume = $prod_info ? floatval($prod_info['VOLUME']) : 0.0;

            $sale_name = trim((string)$sale_name_raw);
            $take_name = trim((string)$take_name_raw);
            $team_name = !empty($sale_name) ? ($salesperson_teams[$sale_name] ?? null) : null;

            // Numeric values
            $max_year_val = $clean_num($row[$idx_max_qty_year] ?? 0);
            $min_year_val = $clean_num($row[$idx_min_qty_year] ?? 0);
            $sales_m1_val = $clean_num($row[$idx_sales_month_1] ?? 0);
            $sales_m2_val = $clean_num($row[$idx_sales_month_2] ?? 0);
            $sales_m3_val = $clean_num($row[$idx_sales_month_3] ?? 0);
            $avg_3m_val = $clean_num($row[$idx_avg_3_month] ?? 0);
            $forecast_qty_val = $clean_num($row[$idx_forecast_qty_month_1] ?? 0);
            $compare_forecast_val = $clean_num($row[$idx_compare_forecast_month_1] ?? 0);
            
            $raw_pct = $clean_num($row[$idx_compare_sales_percent] ?? 0);
            // If percent in decimal like -1 -> -100%
            $compare_pct = (abs($raw_pct) <= 1.0 && $raw_pct != 0.0) ? $raw_pct * 100 : $raw_pct;
            $responsible_person = ($idx_responsible_person !== null && isset($row[$idx_responsible_person])) ? trim($row[$idx_responsible_person]) : null;

            $sku_key = !empty($sku_code) ? $sku_code : $sku_name;
            $lookup_key = trim($ar_code) . '||' . trim($sku_key) . '||' . trim($sale_name);
            $existing_id = $existing_records[$lookup_key] ?? null;

            if ($existing_id) {
                $params_upd = [
                    $ar_name, $ar_name, $ar_name,
                    $sale_name, $sale_name, $sale_name,
                    $take_name, $take_name, $take_name,
                    $team_name, $team_name, $team_name,
                    $brand_code, $brand_code, $brand_code,
                    $product_type, $product_type, $product_type,
                    $sku_name, $sku_name, $sku_name,
                    $volume, $volume,
                    $amphur, $amphur, $amphur,
                    $province, $province, $province,
                    $max_year_val, $max_year_val,
                    $min_year_val, $min_year_val,
                    $sales_m1_val, $sales_m1_val,
                    $sales_m2_val, $sales_m2_val,
                    $sales_m3_val, $sales_m3_val,
                    $avg_3m_val, $avg_3m_val,
                    $forecast_qty_val,
                    $compare_forecast_val, $compare_forecast_val,
                    $compare_pct, $compare_pct,
                    $responsible_person, $responsible_person, $responsible_person,
                    $existing_id
                ];
                $stmt_upd->execute($params_upd);
            } else {
                $params_ins = [
                    $forcast_month, $forcast_year, $ar_code, $ar_name,
                    $sale_name, $take_name, $team_name, $brand_code, $product_type, $sku_code, $sku_name, $volume,
                    $amphur, $province, $zone, 0.0, $max_year_val, $min_year_val,
                    0.0, $sales_m1_val, $sales_m2_val, $sales_m3_val, $avg_3m_val,
                    0.0, $forecast_qty_val, $compare_forecast_val,
                    $compare_pct, null, $responsible_person, 0.0, 0.0, 0.0
                ];
                $stmt_ins->execute($params_ins);
                $existing_records[$lookup_key] = $conn->lastInsertId();
            }
            $count++;
        }

        // Sync customer amphur and province from v_customer_data_sale_all_zone
        $sql_sync = "UPDATE ims_tires_plan_forecast_volume_tires p
                     INNER JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
                     SET 
                         p.amphur = IF(p.amphur = '' OR p.amphur IS NULL, v.TRD_AMPHUR, p.amphur),
                         p.province = IF(p.province = '' OR p.province IS NULL, v.TRD_PROVINCE, p.province),
                         p.zone = IF(p.zone = '' OR p.zone IS NULL, v.ZONE, p.zone)
                     WHERE p.forcast_month = ? AND p.forcast_year = ? AND (p.ar_code IS NOT NULL AND p.ar_code != '')";
        $stmt_sync = $conn->prepare($sql_sync);
        $stmt_sync->execute([$forcast_month, $forcast_year]);

        // Sync salesperson and tech from v_sale_cust_sac
        $sql_sync_sale = "UPDATE ims_tires_plan_forecast_volume_tires p
                          INNER JOIN v_sale_cust_sac v ON p.ar_code = v.AR_CODE
                          SET 
                              p.sale_name = IF(p.sale_name = '' OR p.sale_name IS NULL, v.SALE_NAME, p.sale_name),
                              p.take_name = IF(p.take_name = '' OR p.take_name IS NULL, v.TAKE_NAME, p.take_name)
                          WHERE p.forcast_month = ? AND p.forcast_year = ? AND (p.ar_code IS NOT NULL AND p.ar_code != '')";
        $stmt_sync_sale = $conn->prepare($sql_sync_sale);
        $stmt_sync_sale->execute([$forcast_month, $forcast_year]);

        // Auto-sync historical sales if empty
        $m1_p = str_pad((string)$m1, 2, '0', STR_PAD_LEFT);
        $m2_p = str_pad((string)$m2, 2, '0', STR_PAD_LEFT);
        $m3_p = str_pad((string)$m3, 2, '0', STR_PAD_LEFT);

        $y1 = ($forcast_month - 1 <= 0) ? $forcast_year - 1 : $forcast_year;
        $y2 = ($forcast_month - 2 <= 0) ? $forcast_year - 1 : $forcast_year;
        $y3 = ($forcast_month - 3 <= 0) ? $forcast_year - 1 : $forcast_year;
        $ly = $forcast_year - 1;

        $sql_auto_sync = "UPDATE ims_tires_plan_forecast_volume_tires p
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
