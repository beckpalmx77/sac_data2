<?php
date_default_timezone_set("Asia/Bangkok");
include('config/connect_db.php');

function tableExists($conn, $table) {
    try {
        $result = $conn->query("SELECT 1 FROM $table LIMIT 1");
    } catch (Exception $e) {
        return false;
    }
    return $result !== false;
}

$header_exists = tableExists($conn, 'sales_report_header');
$detail_exists = tableExists($conn, 'sales_report_detail');

if ($header_exists && $detail_exists) {
    echo "<h2>ระบบตรวจพบตารางในฐานข้อมูลแล้ว</h2>";
    echo "<p>ตาราง sales_report_header และ sales_report_detail มีอยู่แล้วในระบบ ไม่จำเป็นต้องสร้างใหม่</p>";
    echo "<a href='forecast_report_list.php' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>ไปหน้ารายการ</a>";
} else {
    $sql = "
    CREATE TABLE IF NOT EXISTS sales_report_header (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ar_code VARCHAR(50) NULL COMMENT 'รหัสร้านค้า',
        ar_name VARCHAR(255) NULL COMMENT 'ชื่อร้านค้า',
        report_month INT NOT NULL COMMENT 'เดือนที่เลือก',
        report_year INT NOT NULL COMMENT 'ปีที่เลือก',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้าง'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS sales_report_detail (
        id INT AUTO_INCREMENT PRIMARY KEY,
        header_id INT NOT NULL COMMENT 'อ้างอิง header',
        brand_code VARCHAR(50) NULL COMMENT 'ยี่ห้อ (BRN_CODE)',
        sku_code VARCHAR(50) NOT NULL COMMENT 'รหัสสินค้า',
        sku_name VARCHAR(255) NULL COMMENT 'ชื่อสินค้า',
        qty_month1 DECIMAL(10,2) DEFAULT 0 COMMENT 'ยอดขายเดือนที่ 1',
        qty_month2 DECIMAL(10,2) DEFAULT 0 COMMENT 'ยอดขายเดือนที่ 2',
        qty_month3 DECIMAL(10,2) DEFAULT 0 COMMENT 'ยอดขายเดือนที่ 3',
        avg_3month DECIMAL(10,2) DEFAULT 0 COMMENT 'ค่าเฉลี่ย 3 เดือน',
        avg_monthly_last_year DECIMAL(10,2) DEFAULT 0 COMMENT 'ค่าเฉลี่ยต่อเดือน (12 เดือนปีที่แล้ว)',
        min_qty_last_year DECIMAL(10,2) DEFAULT 0 COMMENT 'ค่า MIN (12 เดือนปีที่แล้ว)',
        max_qty_last_year DECIMAL(10,2) DEFAULT 0 COMMENT 'ค่า MAX (12 เดือนปีที่แล้ว)',
        target_qty DECIMAL(10,2) DEFAULT 0,
        forecast_qty DECIMAL(10,2) DEFAULT 0,
        compare_sales_1 DECIMAL(10,2) DEFAULT 0,
        compare_sales_2 DECIMAL(10,2) DEFAULT 0,
        remark TEXT,
        sales_person VARCHAR(100),
        take_person VARCHAR(100),
        FOREIGN KEY (header_id) REFERENCES sales_report_header(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";

    try {
        $conn->exec($sql);
        echo "<h2>สร้างตารางเรียบร้อยแล้ว</h2>";
        echo "<p>ตาราง sales_report_header และ sales_report_detail ถูกสร้างขึ้นใหม่ในฐานข้อมูลของคุณ</p>";
        echo "<a href='forecast_report_list.php' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>ไปหน้ารายการ</a>";
    } catch (Exception $e) {
        echo "<h2>เกิดข้อผิดพลาดในการสร้างตาราง</h2>";
        echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
    }
}
$conn = null;
?>
