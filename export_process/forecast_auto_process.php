<?php
include('../config/connect_db.php');

header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set('Asia/Bangkok');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'calculate';

if ($action === 'calculate' || $action === 'export_csv') {
    $target_month = isset($_POST['target_month']) ? intval($_POST['target_month']) : intval(date('n'));
    $target_year = isset($_POST['target_year']) ? intval($_POST['target_year']) : intval(date('Y'));
    $algorithm = isset($_POST['algorithm']) ? $_POST['algorithm'] : 'AUTO_BEST_FIT';
    $growth_factor = isset($_POST['growth_factor']) ? floatval($_POST['growth_factor']) : 0.0;
    $group_by = isset($_POST['group_by']) ? $_POST['group_by'] : 'SKU_ONLY';
    
    $filter_sale = isset($_POST['SALE_NAME']) ? $_POST['SALE_NAME'] : '';
    $filter_brand = isset($_POST['BRAND']) ? $_POST['BRAND'] : '';
    $filter_sku_cat = isset($_POST['SKU_CAT']) ? $_POST['SKU_CAT'] : '';
    $filter_ar = isset($_POST['AR_NAME']) ? $_POST['AR_NAME'] : '';

    // Calculate M-1, M-2, M-3 month & year
    $m1 = $target_month - 1; $y1 = $target_year;
    if ($m1 < 1) { $m1 += 12; $y1--; }

    $m2 = $target_month - 2; $y2 = $target_year;
    if ($m2 < 1) { $m2 += 12; $y2--; }

    $m3 = $target_month - 3; $y3 = $target_year;
    if ($m3 < 1) { $m3 += 12; $y3--; }

    $yoy_m = $target_month;
    $yoy_y = $target_year - 1;

    // Thai Short Month Labels
    $short_months_th = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];

    $label_m3 = $short_months_th[$m3] . " " . (($y3 + 543) % 100);
    $label_m2 = $short_months_th[$m2] . " " . (($y2 + 543) % 100);
    $label_m1 = $short_months_th[$m1] . " " . (($y1 + 543) % 100);
    $label_target = $short_months_th[$target_month] . " " . (($target_year + 543) % 100);

    // WHERE clause filters
    $where_clauses = ["sale_sac.SKU_CODE IS NOT NULL AND sale_sac.SKU_CODE != ''"];

    // Date condition for history AND target month actual sales
    $where_clauses[] = "(
        (CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$m1} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$y1}) OR
        (CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$m2} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$y2}) OR
        (CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$m3} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$y3}) OR
        (CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$target_month} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$target_year}) OR
        (CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$yoy_m} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$yoy_y})
    )";

    // Sale / Take filter with space normalization
    if (!empty($filter_sale) && $filter_sale !== '-') {
        if (is_array($filter_sale)) {
            $clean_sales = array_filter(array_map(function($s) {
                return preg_replace('/\s+/u', '', trim($s ?? ''));
            }, $filter_sale));
            if (!empty($clean_sales)) {
                $quoted_sales = array_map(function($s) use ($conn) { return $conn->quote($s); }, $clean_sales);
                $in_str = implode(',', $quoted_sales);
                $where_clauses[] = "(REPLACE(sale_sac.SALE_NAME, ' ', '') IN ({$in_str}) OR REPLACE(sale_sac.TAKE_NAME, ' ', '') IN ({$in_str}))";
            }
        } else {
            $clean_sale = preg_replace('/\s+/u', '', trim($filter_sale));
            if ($clean_sale !== '') {
                $q = $conn->quote('%' . $clean_sale . '%');
                $where_clauses[] = "(REPLACE(sale_sac.SALE_NAME, ' ', '') LIKE {$q} OR REPLACE(sale_sac.TAKE_NAME, ' ', '') LIKE {$q})";
            }
        }
    }

    if (!empty($filter_brand) && $filter_brand !== '-') {
        $where_clauses[] = "sale_sac.BRAND = " . $conn->quote($filter_brand);
    }
    if (!empty($filter_sku_cat) && $filter_sku_cat !== '-') {
        $where_clauses[] = "sale_sac.SKU_CAT = " . $conn->quote($filter_sku_cat);
    }
    if (!empty($filter_ar) && $filter_ar !== '-') {
        $where_clauses[] = "sale_sac.AR_NAME = " . $conn->quote($filter_ar);
    }

    $where_sql = "WHERE " . implode(" AND ", $where_clauses);

    if ($group_by === 'SKU_ONLY') {
        $select_fields = "
            'ALL' AS AR_CODE,
            'ทุกลูกค้า (All Customers)' AS AR_NAME,
            sale_sac.SKU_CODE,
            MAX(sale_sac.SKU_NAME) AS SKU_NAME,
            MAX(sale_sac.BRAND) AS BRAND,
            MAX(sale_sac.SKU_CAT) AS SKU_CAT,
            'SAC' AS SALE_NAME,
            'SAC' AS TAKE_NAME,
            '-' AS AMPHUR,
            '-' AS PROVINCE,
            AVG(CAST(sale_sac.TRD_PRC AS DECIMAL(10,2))) AS TRD_PRC,
        ";
        $group_by_sql = "GROUP BY sale_sac.SKU_CODE";
    } elseif ($group_by === 'AR_ONLY') {
        $select_fields = "
            sale_sac.AR_CODE,
            MAX(sale_sac.AR_NAME) AS AR_NAME,
            'ALL' AS SKU_CODE,
            'รวมทุกสินค้าของลูกค้า' AS SKU_NAME,
            'รวม' AS BRAND,
            'รวม' AS SKU_CAT,
            MAX(sale_sac.SALE_NAME) AS SALE_NAME,
            MAX(sale_sac.TAKE_NAME) AS TAKE_NAME,
            MAX(sale_sac.TRD_AMPHUR) AS AMPHUR,
            MAX(sale_sac.TRD_PROVINCE) AS PROVINCE,
            AVG(CAST(sale_sac.TRD_PRC AS DECIMAL(10,2))) AS TRD_PRC,
        ";
        $group_by_sql = "GROUP BY sale_sac.AR_CODE";
    } else {
        $select_fields = "
            sale_sac.AR_CODE,
            MAX(sale_sac.AR_NAME) AS AR_NAME,
            sale_sac.SKU_CODE,
            MAX(sale_sac.SKU_NAME) AS SKU_NAME,
            MAX(sale_sac.BRAND) AS BRAND,
            MAX(sale_sac.SKU_CAT) AS SKU_CAT,
            MAX(sale_sac.SALE_NAME) AS SALE_NAME,
            MAX(sale_sac.TAKE_NAME) AS TAKE_NAME,
            MAX(sale_sac.TRD_AMPHUR) AS AMPHUR,
            MAX(sale_sac.TRD_PROVINCE) AS PROVINCE,
            MAX(sale_sac.TRD_PRC) AS TRD_PRC,
        ";
        $group_by_sql = "GROUP BY sale_sac.AR_CODE, sale_sac.SKU_CODE";
    }

    $sql = "
    SELECT 
        {$select_fields}
        SUM(CASE WHEN CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$m1} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$y1} THEN CAST(sale_sac.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) AS qty_m1,
        SUM(CASE WHEN CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$m2} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$y2} THEN CAST(sale_sac.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) AS qty_m2,
        SUM(CASE WHEN CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$m3} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$y3} THEN CAST(sale_sac.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) AS qty_m3,
        SUM(CASE WHEN CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$target_month} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$target_year} THEN CAST(sale_sac.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) AS qty_actual_target,
        SUM(CASE WHEN CAST(sale_sac.DI_MONTH AS UNSIGNED) = {$yoy_m} AND CAST(sale_sac.DI_YEAR AS UNSIGNED) = {$yoy_y} THEN CAST(sale_sac.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) AS qty_yoy
    FROM ims_data_sale_sac_all sale_sac
    {$where_sql}
    {$group_by_sql}
    ORDER BY qty_actual_target DESC, qty_m1 DESC, qty_m2 DESC, qty_m3 DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    $total_forecast_qty = 0;
    $total_forecast_value = 0;
    $total_avg_3m = 0;
    $total_actual_target_qty = 0;

    $multiplier = 1 + ($growth_factor / 100.0);

    foreach ($rows as $r) {
        $m1_val = floatval($r['qty_m1']);
        $m2_val = floatval($r['qty_m2']);
        $m3_val = floatval($r['qty_m3']);
        $actual_target_val = floatval($r['qty_actual_target']);
        $yoy_val = floatval($r['qty_yoy']);
        $unit_price = floatval($r['TRD_PRC']);

        $avg_3m = round(($m1_val + $m2_val + $m3_val) / 3.0, 2);
        $wma_3m = round(($m1_val * 0.5) + ($m2_val * 0.3) + ($m3_val * 0.2), 2);
        $exp_smooth = round(($m1_val * 0.4) + ($m2_val * 0.36) + ($m3_val * 0.24), 2);

        $raw_forecast = 0;
        switch ($algorithm) {
            case 'AVG_3M':
                $raw_forecast = $avg_3m;
                break;
            case 'WMA_3M':
                $raw_forecast = $wma_3m;
                break;
            case 'YOY_GROWTH':
                $raw_forecast = ($yoy_val > 0) ? $yoy_val : $avg_3m;
                break;
            case 'EXP_SMOOTHING':
                $raw_forecast = $exp_smooth;
                break;
            case 'AUTO_BEST_FIT':
            default:
                if ($yoy_val > 0) {
                    $raw_forecast = ($yoy_val * 0.6) + ($wma_3m * 0.4);
                } else {
                    $raw_forecast = $wma_3m;
                }
                break;
        }

        $forecast_qty = round($raw_forecast * $multiplier);
        if ($forecast_qty < 0) $forecast_qty = 0;

        $diff_qty = round($forecast_qty - $avg_3m, 2);
        $diff_percent = ($avg_3m > 0) ? round(($diff_qty / $avg_3m) * 100, 2) : 0;
        $est_value = round($forecast_qty * $unit_price, 2);

        // Actual vs Forecast comparison
        $diff_actual_vs_forecast = round($actual_target_val - $forecast_qty, 2);
        $accuracy_percent = ($forecast_qty > 0) ? round(($actual_target_val / $forecast_qty) * 100, 2) : 0;

        $total_forecast_qty += $forecast_qty;
        $total_forecast_value += $est_value;
        $total_avg_3m += $avg_3m;
        $total_actual_target_qty += $actual_target_val;

        $items[] = [
            'ar_code' => $r['AR_CODE'],
            'ar_name' => $r['AR_NAME'],
            'sku_code' => $r['SKU_CODE'],
            'sku_name' => $r['SKU_NAME'],
            'brand' => $r['BRAND'],
            'sku_cat' => $r['SKU_CAT'],
            'sale_name' => $r['SALE_NAME'],
            'take_name' => $r['TAKE_NAME'],
            'amphur' => $r['AMPHUR'],
            'province' => $r['PROVINCE'],
            'unit_price' => $unit_price,
            'qty_m3' => $m3_val,
            'qty_m2' => $m2_val,
            'qty_m1' => $m1_val,
            'qty_actual_target' => $actual_target_val,
            'qty_yoy' => $yoy_val,
            'avg_3m' => $avg_3m,
            'forecast_qty' => $forecast_qty,
            'diff_qty' => $diff_qty,
            'diff_percent' => $diff_percent,
            'diff_actual_vs_forecast' => $diff_actual_vs_forecast,
            'accuracy_percent' => $accuracy_percent,
            'est_value' => $est_value
        ];
    }

    if ($action === 'export_csv') {
        $group_suffix = ($group_by === 'SKU_ONLY') ? "_sku_summary" : (($group_by === 'AR_ONLY') ? "_customer_summary" : "");
        $filename = "auto_forecast_" . $target_year . "_" . sprintf("%02d", $target_month) . "{$group_suffix}.csv";
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'ลำดับ', 'รหัสสินค้า', 'ชื่อสินค้า / รายละเอียด', 'ยี่ห้อ', 'ประเภท', 'รหัสลูกค้า', 'ชื่อลูกค้า',
            'พนักงานขาย', 'ผู้เทค', 'ราคาขายเฉลี่ย',
            $label_m3, $label_m2, $label_m1, 'เฉลี่ย 3ด.',
            'เดือนเดียวกันปีที่แล้ว', "ยอดพยากรณ์ ({$label_target})", "ยอดขายจริง ({$label_target})", 'ผลต่าง ยอดจริง-เป้า', '% ความแม่นยำ', 'มูลค่าประเมิน (บาท)'
        ]);

        $no = 0;
        foreach ($items as $item) {
            $no++;
            fputcsv($output, [
                $no, $item['sku_code'], $item['sku_name'], $item['brand'], $item['sku_cat'],
                $item['ar_code'], $item['ar_name'], $item['sale_name'], $item['take_name'],
                $item['unit_price'], $item['qty_m3'], $item['qty_m2'], $item['qty_m1'],
                $item['avg_3m'], $item['qty_yoy'], $item['forecast_qty'], $item['qty_actual_target'],
                $item['diff_actual_vs_forecast'], $item['accuracy_percent'] . '%', $item['est_value']
            ]);
        }
        fclose($output);
        exit();
    }

    // Return JSON response for AJAX calculate
    $growth_vs_avg3m = ($total_avg_3m > 0) ? round((($total_forecast_qty - $total_avg_3m) / $total_avg_3m) * 100, 2) : 0;
    $total_accuracy_percent = ($total_forecast_qty > 0) ? round(($total_actual_target_qty / $total_forecast_qty) * 100, 2) : 0;

    echo json_encode([
        'status' => 'success',
        'target_month' => $target_month,
        'target_year' => $target_year,
        'algorithm' => $algorithm,
        'group_by' => $group_by,
        'growth_factor' => $growth_factor,
        'month_labels' => [
            'm3' => $label_m3,
            'm2' => $label_m2,
            'm1' => $label_m1,
            'target' => $label_target
        ],
        'summary' => [
            'total_items' => count($items),
            'total_forecast_qty' => $total_forecast_qty,
            'total_actual_target_qty' => $total_actual_target_qty,
            'total_forecast_value' => $total_forecast_value,
            'total_avg_3m' => $total_avg_3m,
            'growth_vs_avg3m' => $growth_vs_avg3m,
            'total_accuracy_percent' => $total_accuracy_percent
        ],
        'items' => $items
    ]);
    exit();
}

if ($action === 'save') {
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);

    if (!$data) {
        $data = $_POST;
    }

    $target_month = isset($data['target_month']) ? intval($data['target_month']) : 0;
    $target_year = isset($data['target_year']) ? intval($data['target_year']) : 0;
    $group_by = isset($data['group_by']) ? $data['group_by'] : 'SKU_ONLY';
    $algorithm = isset($data['algorithm']) ? $data['algorithm'] : 'AUTO_BEST_FIT';
    $growth_factor = isset($data['growth_factor']) ? floatval($data['growth_factor']) : 0.0;
    $items = isset($data['items']) ? $data['items'] : [];

    if ($target_month < 1 || $target_year < 2000 || empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ถูกต้อง หรือไม่มีรายการข้อมูลเพื่อบันทึก']);
        exit();
    }

    $conn->beginTransaction();
    try {
        $sql_upsert = "
        INSERT INTO ims_forecast_auto (
            target_month, target_year, group_by, algorithm, growth_factor,
            ar_code, ar_name, sku_code, sku_name, brand, sku_cat,
            sale_name, take_name, amphur, province, unit_price,
            qty_m3, qty_m2, qty_m1, avg_3m, qty_yoy,
            forecast_qty, qty_actual_target, diff_qty, diff_percent,
            diff_actual_vs_forecast, accuracy_percent, est_value,
            created_at, updated_at
        ) VALUES (
            :target_month, :target_year, :group_by, :algorithm, :growth_factor,
            :ar_code, :ar_name, :sku_code, :sku_name, :brand, :sku_cat,
            :sale_name, :take_name, :amphur, :province, :unit_price,
            :qty_m3, :qty_m2, :qty_m1, :avg_3m, :qty_yoy,
            :forecast_qty, :qty_actual_target, :diff_qty, :diff_percent,
            :diff_actual_vs_forecast, :accuracy_percent, :est_value,
            NOW(), NOW()
        )
        ON DUPLICATE KEY UPDATE
            ar_name = VALUES(ar_name),
            sku_name = VALUES(sku_name),
            brand = VALUES(brand),
            sku_cat = VALUES(sku_cat),
            sale_name = VALUES(sale_name),
            take_name = VALUES(take_name),
            amphur = VALUES(amphur),
            province = VALUES(province),
            algorithm = VALUES(algorithm),
            growth_factor = VALUES(growth_factor),
            unit_price = VALUES(unit_price),
            qty_m3 = VALUES(qty_m3),
            qty_m2 = VALUES(qty_m2),
            qty_m1 = VALUES(qty_m1),
            avg_3m = VALUES(avg_3m),
            qty_yoy = VALUES(qty_yoy),
            forecast_qty = VALUES(forecast_qty),
            qty_actual_target = VALUES(qty_actual_target),
            diff_qty = VALUES(diff_qty),
            diff_percent = VALUES(diff_percent),
            diff_actual_vs_forecast = VALUES(diff_actual_vs_forecast),
            accuracy_percent = VALUES(accuracy_percent),
            est_value = VALUES(est_value),
            updated_at = NOW()
        ";

        $stmt = $conn->prepare($sql_upsert);

        $saved_count = 0;
        foreach ($items as $item) {
            $stmt->execute([
                ':target_month' => $target_month,
                ':target_year' => $target_year,
                ':group_by' => $group_by,
                ':algorithm' => $algorithm,
                ':growth_factor' => $growth_factor,
                ':ar_code' => !empty($item['ar_code']) ? $item['ar_code'] : 'ALL',
                ':ar_name' => isset($item['ar_name']) ? $item['ar_name'] : '',
                ':sku_code' => !empty($item['sku_code']) ? $item['sku_code'] : 'ALL',
                ':sku_name' => isset($item['sku_name']) ? $item['sku_name'] : '',
                ':brand' => isset($item['brand']) ? $item['brand'] : '',
                ':sku_cat' => isset($item['sku_cat']) ? $item['sku_cat'] : '',
                ':sale_name' => isset($item['sale_name']) ? $item['sale_name'] : '',
                ':take_name' => isset($item['take_name']) ? $item['take_name'] : '',
                ':amphur' => isset($item['amphur']) ? $item['amphur'] : '',
                ':province' => isset($item['province']) ? $item['province'] : '',
                ':unit_price' => floatval($item['unit_price'] ?? 0),
                ':qty_m3' => floatval($item['qty_m3'] ?? 0),
                ':qty_m2' => floatval($item['qty_m2'] ?? 0),
                ':qty_m1' => floatval($item['qty_m1'] ?? 0),
                ':avg_3m' => floatval($item['avg_3m'] ?? 0),
                ':qty_yoy' => floatval($item['qty_yoy'] ?? 0),
                ':forecast_qty' => floatval($item['forecast_qty'] ?? 0),
                ':qty_actual_target' => floatval($item['qty_actual_target'] ?? 0),
                ':diff_qty' => floatval($item['diff_qty'] ?? 0),
                ':diff_percent' => floatval($item['diff_percent'] ?? 0),
                ':diff_actual_vs_forecast' => floatval($item['diff_actual_vs_forecast'] ?? 0),
                ':accuracy_percent' => floatval($item['accuracy_percent'] ?? 0),
                ':est_value' => floatval($item['est_value'] ?? 0)
            ]);
            $saved_count++;
        }

        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'message' => "บันทึกข้อมูลพยากรณ์ยอดขายลงในตาราง ims_forecast_auto สำเร็จเรียบร้อย ({$saved_count} รายการ)",
            'saved_count' => $saved_count
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()]);
    }
    exit();
}
