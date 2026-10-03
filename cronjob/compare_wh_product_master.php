<?php
/**
 * Data Comparison Tool: MSSQL vs MySQL Product Master
 * This script compares the product master records between SQL Server and MySQL
 * based on the query criteria in import_wh_product_master.php.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Bangkok');

// Load database connections
require_once __DIR__ . "/../config/connect_sqlserver.php"; // $conn_sqlsvr
require_once __DIR__ . "/../config/connect_db.php";         // $conn

echo "=== เริ่มต้นกระบวนการตรวจสอบและเปรียบเทียบข้อมูลฐานข้อมูล ===\n";
echo "เวลาเริ่มงาน: " . date("Y-m-d H:i:s") . "\n\n";

try {
    // 1. ดึงข้อมูลจาก MSSQL (Source)
    echo "1. กำลังดึงข้อมูลจาก MSSQL Server...\n";
    $sql_mssql = "SELECT 
        ICCAT.ICCAT_CODE,
        SKUMASTER.SKU_CODE,
        SKUMASTER.SKU_NAME,
        GOODSMASTER.GOODS_SKU,    
        UOFQTY.UTQ_KEY,
        UOFQTY.UTQ_NAME,
        BRAND.BRN_CODE,
        BRAND.BRN_NAME
    FROM ICCAT
    LEFT JOIN SKUMASTER ON ICCAT.ICCAT_KEY = SKUMASTER.SKU_ICCAT
    LEFT JOIN GOODSMASTER ON GOODSMASTER.GOODS_SKU = SKUMASTER.SKU_KEY 
    LEFT JOIN UOFQTY ON UOFQTY.UTQ_KEY = GOODSMASTER.GOODS_UTQ    
    LEFT JOIN BRAND ON BRAND.BRN_KEY = SKUMASTER.SKU_BRN
    WHERE 
        ICCAT.ICCAT_CODE IN (
            '1SAC01', '2SAC01', '4SAC01', '3SAC01', '5SAC02', '1SAC07', '5SAC01', '1SAC06', '6SAC08', '1SAC02',
            '1SAC03', '1SAC04', '1SAC05', '1SAC09', '1SAC08', '1SAC10', '1SAC11', '1SAC12', '1SAC13', '1SAC14',
            '2SAC09', '2SAC04', '2SAC13', '2SAC14', '2SAC12', '2SAC02', '2SAC03', '2SAC10', '2SAC15', '2SAC06',
            '2SAC05', '2SAC07', '2SAC08', '2SAC11', '3SAC02', '3SAC06', '3SAC03', '3SAC04', '4SAC02', '4SAC03',
            '4SAC04', '4SAC06', '3SAC05', '4SAC05', '6SAC001', '2SAC16', '2SAC17', '2SAC18', '6SAC002', '1SAC15',
            '2SAC19', '2SAC20', '2SAC21' 
        ) ORDER BY SKUMASTER.SKU_CODE;";

    $stmt_mssql = $conn_sqlsvr->prepare($sql_mssql);
    $stmt_mssql->execute();
    $mssql_rows = $stmt_mssql->fetchAll(PDO::FETCH_ASSOC);
    $mssql_count = count($mssql_rows);
    echo "พบข้อมูลใน MSSQL: $mssql_count รายการ\n\n";

    // 2. ดึงข้อมูลจาก MySQL (Target)
    echo "2. กำลังดึงข้อมูลจาก MySQL...\n";
    $sql_mysql = "SELECT product_id, product_name, unit_id, unit_name, brand_code, brand_name, iccat_code 
                  FROM wh_product_master";
    $stmt_mysql = $conn->prepare($sql_mysql);
    $stmt_mysql->execute();
    $mysql_rows = $stmt_mysql->fetchAll(PDO::FETCH_ASSOC);
    $mysql_count = count($mysql_rows);
    echo "พบข้อมูลใน MySQL: $mysql_count รายการ\n\n";

    // จัดโครงสร้างเพื่อความรวดเร็วในการค้นหาแบบ O(1)
    $mysql_map = [];
    foreach ($mysql_rows as $row) {
        $mysql_map[$row['product_id']] = $row;
    }

    $mssql_map = [];
    foreach ($mssql_rows as $row) {
        if ($row['SKU_CODE'] !== null) {
            $mssql_map[$row['SKU_CODE']] = $row;
        }
    }

    // 3. เริ่มเปรียบเทียบข้อมูล
    echo "3. ดำเนินการเปรียบเทียบความแตกต่าง...\n";
    
    $missing_in_mysql = [];
    $mismatching_fields = [];
    $matched_count = 0;

    foreach ($mssql_rows as $ms_row) {
        $sku = $ms_row['SKU_CODE'];
        if ($sku === null) continue;

        // เช็คว่ามีใน MySQL ไหม
        if (!isset($mysql_map[$sku])) {
            $missing_in_mysql[] = [
                'product_id' => $sku,
                'product_name' => $ms_row['SKU_NAME'],
                'brand_name' => $ms_row['BRN_NAME'],
                'iccat_code' => $ms_row['ICCAT_CODE']
            ];
            continue;
        }

        // ถ้ามี ดึงมาเทียบแต่ละฟิลด์
        $my_row = $mysql_map[$sku];
        $mismatches = [];

        // เช็คข้อมูลไม่ตรงกัน
        if (trim($ms_row['SKU_NAME']) !== trim($my_row['product_name'])) {
            $mismatches['product_name'] = ['ms' => $ms_row['SKU_NAME'], 'my' => $my_row['product_name']];
        }
        if (trim($ms_row['UTQ_KEY']) !== trim($my_row['unit_id'])) {
            $mismatches['unit_id'] = ['ms' => $ms_row['UTQ_KEY'], 'my' => $my_row['unit_id']];
        }
        if (trim($ms_row['UTQ_NAME']) !== trim($my_row['unit_name'])) {
            $mismatches['unit_name'] = ['ms' => $ms_row['UTQ_NAME'], 'my' => $my_row['unit_name']];
        }
        if (trim($ms_row['BRN_CODE']) !== trim($my_row['brand_code'])) {
            $mismatches['brand_code'] = ['ms' => $ms_row['BRN_CODE'], 'my' => $my_row['brand_code']];
        }
        if (trim($ms_row['BRN_NAME']) !== trim($my_row['brand_name'])) {
            $mismatches['brand_name'] = ['ms' => $ms_row['BRN_NAME'], 'my' => $my_row['brand_name']];
        }
        if (trim($ms_row['ICCAT_CODE']) !== trim($my_row['iccat_code'])) {
            $mismatches['iccat_code'] = ['ms' => $ms_row['ICCAT_CODE'], 'my' => $my_row['iccat_code']];
        }

        if (count($mismatches) > 0) {
            $mismatching_fields[] = [
                'product_id' => $sku,
                'mismatches' => $mismatches
            ];
        } else {
            $matched_count++;
        }
    }

    // หาตัวที่เกินมาใน MySQL (มีใน MySQL แต่ไม่มีในเงื่อนไขการดึงข้อมูลจาก MSSQL)
    $extraneous_in_mysql = [];
    foreach ($mysql_rows as $my_row) {
        $id = $my_row['product_id'];
        if (!isset($mssql_map[$id])) {
            $extraneous_in_mysql[] = [
                'product_id' => $id,
                'product_name' => $my_row['product_name'],
                'brand_name' => $my_row['brand_name']
            ];
        }
    }

    // 4. เขียนสรุปรายงานออกทาง Console เพื่อให้ Agent ทราบ
    echo "\n=== รายงานผลการเปรียบเทียบข้อมูล ===\n";
    echo "จำนวนสินค้าต้นทาง (MSSQL): " . $mssql_count . " รายการ\n";
    echo "จำนวนสินค้าปลายทาง (MySQL): " . $mysql_count . " รายการ\n";
    echo "จำนวนสินค้าที่ข้อมูลตรงกันอย่างสมบูรณ์: " . $matched_count . " รายการ\n";
    echo "จำนวนสินค้าที่ไม่มีใน MySQL (ตกหล่น): " . count($missing_in_mysql) . " รายการ\n";
    echo "จำนวนสินค้าที่มีข้อมูลฟิลด์ต่างกัน (ค่าไม่ตรง): " . count($mismatching_fields) . " รายการ\n";
    echo "จำนวนสินค้าที่มีใน MySQL แต่ไม่พบจากเงื่อนไข MSSQL (ส่วนเกิน): " . count($extraneous_in_mysql) . " รายการ\n";
    echo "====================================\n\n";

    if (count($missing_in_mysql) > 0) {
        echo "[รายการสินค้าที่ไม่มีใน MySQL (ตัวอย่าง 10 รายการแรก)]\n";
        $i = 0;
        foreach ($missing_in_mysql as $item) {
            if ($i++ >= 10) break;
            echo " - SKU: " . $item['product_id'] . " | " . $item['product_name'] . " (Brand: " . $item['brand_name'] . ")\n";
        }
        echo "\n";
    }

    if (count($mismatching_fields) > 0) {
        echo "[รายการสินค้าที่ค่าข้อมูลบางคอลัมน์ไม่ตรงกัน (ตัวอย่าง 10 รายการแรก)]\n";
        $i = 0;
        foreach ($mismatching_fields as $item) {
            if ($i++ >= 10) break;
            echo " - SKU: " . $item['product_id'] . "\n";
            foreach ($item['mismatches'] as $field => $diff) {
                echo "   * ฟิลด์ " . $field . " -> MSSQL: [" . $diff['ms'] . "] | MySQL: [" . $diff['my'] . "]\n";
            }
        }
    }

} catch (Exception $ex) {
    echo "เกิดข้อผิดพลาดในการตรวจสอบ: " . $ex->getMessage() . "\n";
}

$conn_sqlsvr = null;
$conn = null;
echo "สิ้นสุดกระบวนการตรวจสอบเวลา: " . date("Y-m-d H:i:s") . "\n";
