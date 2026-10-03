<?php
// ... (โค้ดตรวจสอบ request เดิม)

// ต้องแน่ใจว่าได้ include connect_db.php แล้ว
include("../config/connect_db.php");

$response = [
    'results' => [],
    'error' => '',
    'search_value' => '', // ค่านี้จะเปลี่ยนไปเก็บ Serial Number ที่ใช้ค้นหาหลัก
    'di_ref' => '',
    'ar_name' => '',
    'sku_code' => '',
    'month' => '',
    'year' => ''
];

// รับค่าทั้งหมด
$search_value = trim($_POST['search_serial'] ?? '');
$di_ref = trim($_POST['di_ref'] ?? '');
$ar_name = trim($_POST['ar_name'] ?? '');
$sku_code = trim($_POST['sku_code'] ?? '');
$month = trim($_POST['month'] ?? '');
$year = trim($_POST['year'] ?? '');

$response['search_value'] = $search_value;
$response['di_ref'] = $di_ref;
$response['ar_name'] = $ar_name;
$response['sku_code'] = $sku_code;
$response['month'] = $month;
$response['year'] = $year;

$table_name = "ims_product_sale_sac";
$field_name = "serial_tires_number";

// 1. สร้างเงื่อนไขพื้นฐาน (ALWAYS TRUE)
$conditions = [];

// 2. เงื่อนไขสำหรับ Serial Number (หลัก)
if (!empty($search_value)) {
    // ใช้ FIND_IN_SET สำหรับ Serial Number
    $conditions[] = "FIND_IN_SET(:search_value, {$field_name}) > 0";
}

// 3. เงื่อนไขสำหรับ DI_REF
if (!empty($di_ref)) {
    // ใช้ LIKE % % สำหรับการค้นหาบางส่วน
    $conditions[] = "DI_REF LIKE :di_ref";
}

// 4. เงื่อนไขสำหรับ AR_NAME
if (!empty($ar_name)) {
    // ใช้ LIKE % % สำหรับการค้นหาบางส่วน
    $conditions[] = "AR_NAME LIKE :ar_name";
}

// 5. เงื่อนไขสำหรับ SKU_CODE
if (!empty($sku_code)) {
    // ใช้ LIKE % % สำหรับการค้นหาบางส่วน
    $conditions[] = "SKU_CODE LIKE :sku_code";
}


// 6. เงื่อนไขสำหรับ เดือน (DI_DATE)
if (!empty($month) && $month !== 'all') {
    // MySQL MONTH() function
    $conditions[] = "MONTH(STR_TO_DATE(DI_DATE, '%d/%m/%Y')) = :month";
}

// 7. เงื่อนไขสำหรับ ปี (DI_DATE)
if (!empty($year) && $year !== 'all') {
    // MySQL YEAR() function
    $conditions[] = "YEAR(STR_TO_DATE(DI_DATE, '%d/%m/%Y')) = :year";
}


// ถ้าไม่มีเงื่อนไขใด ๆ เลย ไม่ต้อง Query
if (empty($conditions)) {
    $response['error'] = "กรุณาป้อน Serial Number หรือเงื่อนไขอื่น ๆ อย่างน้อยหนึ่งอย่าง";
    echo json_encode($response);
    exit();
}

try {
    // สร้าง WHERE clause
    $where_clause = " WHERE " . implode(" AND ", $conditions);

    $sql = "
        SELECT 
            id,
            DI_REF, 
            DI_DATE, 
            AR_NAME,
            SKU_CODE,
            TRD_QTY,
            serial_tires_number 
        FROM 
            {$table_name} 
        {$where_clause}
    ";

    $stmt = $conn->prepare($sql);

    // ผูกค่า (Bind Parameters)
    if (!empty($search_value)) {
        $stmt->bindParam(':search_value', $search_value, PDO::PARAM_STR);
    }
    if (!empty($di_ref)) {
        $stmt->bindValue(':di_ref', '%' . $di_ref . '%', PDO::PARAM_STR);
    }
    if (!empty($ar_name)) {
        $stmt->bindValue(':ar_name', '%' . $ar_name . '%', PDO::PARAM_STR);
    }
    if (!empty($sku_code)) {
        $stmt->bindValue(':sku_code', '%' . $sku_code . '%', PDO::PARAM_STR);
    }
    if (!empty($month) && $month !== 'all') {
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    }
    if (!empty($year) && $year !== 'all') {
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    }

    $stmt->execute();
    $response['results'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $response['error'] = "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage() . " SQL: " . $sql;
}

// *** ส่วนเพิ่มเติมสำหรับบันทึก JSON Response ลงในไฟล์ ***
$json_output = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// กำหนดชื่อไฟล์ (search_results.json)
// ไฟล์จะถูกสร้างในไดเรกทอรีเดียวกับสคริปต์นี้
$filename = 'search_results.json';

// บันทึกเนื้อหา JSON ลงในไฟล์
//file_put_contents($filename, $json_output);

// ... (โค้ดปิดการเชื่อมต่อและส่ง JSON เดิม)
echo json_encode($response);