<?php
// ไฟล์: model/fetch_data_by_date.php

// *** 1. การเชื่อมต่อฐานข้อมูล (DB Connection) ***
// *** โปรดตรวจสอบและแก้ไข path ให้ถูกต้องตามโครงสร้างไฟล์ของคุณ ***
// ตัวอย่าง:
include('../config/connect_db.php');

// สมมติว่าไฟล์ที่ include สร้างตัวแปรเชื่อมต่อฐานข้อมูลชื่อ $conn (PDO object)
if (!$conn) {
    // กรณีที่ include สำเร็จ แต่การเชื่อมต่อล้มเหลว
    // ถ้าไฟล์ include ของคุณไม่ได้จัดการ error ไว้ ควรเพิ่มการตรวจสอบ
    die(json_encode(["error" => "Database connection (PDO) object is missing or failed."]));
}

// *** 2. การกำหนดค่า (Configuration) ***
$table_name = "ims_tires_number"; // <<< เปลี่ยนเป็นชื่อตารางข้อมูลจริงของคุณ !!! >>>
// คอลัมน์ที่สามารถค้นหาและเรียงลำดับได้ (ต้องตรงกับชื่อคอลัมน์ใน DB)
$columns = array(
    array('db' => 'id', 'dt' => 0),
    array('db' => 'send_date', 'dt' => 1),
    array('db' => 'customer_name', 'dt' => 2),
    array('db' => 'tires_brand', 'dt' => 3),
    array('db' => 'tires_size', 'dt' => 4),
    array('db' => 'tires_number', 'dt' => 5)
    // ถ้ามีคอลัมน์อื่น ให้เพิ่มที่นี่
);

// *** 3. รับค่าจาก DataTables (POST Parameters) ***
$draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
$start = isset($_POST['start']) ? $_POST['start'] : 0;
$length = isset($_POST['length']) ? $_POST['length'] : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// พารามิเตอร์การกรองจากฟอร์ม
$doc_date_start = isset($_POST['doc_date_start']) ? $_POST['doc_date_start'] : ''; // YYYY-MM-DD
$doc_date_to = isset($_POST['doc_date_to']) ? $_POST['doc_date_to'] : '';       // YYYY-MM-DD
$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : '';


// *** 4. คำนวณจำนวนเรคคอร์ดทั้งหมด (ไม่กรอง) ***
try {
    $stmt_total = $conn->query("SELECT COUNT(id) FROM `$table_name`");
    $totalRecords = $stmt_total->fetchColumn();
} catch (PDOException $e) {
    // จัดการข้อผิดพลาด
    $totalRecords = 0;
}


// *** 5. สร้างเงื่อนไข WHERE และพารามิเตอร์สำหรับ Prepared Statement (PDO) ***
$where = " WHERE 1=1 ";
$params = []; // สำหรับ PDO เราใช้ array ธรรมดา

// กรองตามช่วงวันที่
if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $where .= " AND send_date_format BETWEEN :date_start AND :date_to ";
    $params[':date_start'] = $doc_date_start;
    $params[':date_to'] = $doc_date_to;
}

// กรองตามชื่อลูกค้า
if (!empty($customer_name)) {
    if ($customer_name !== '-- ไม่ระบุ (ทั้งหมด) --' && $customer_name !== '') {
        $where .= " AND customer_name = :customer_name ";
        $params[':customer_name'] = $customer_name;
    }
}

// กรองตามค่าค้นหาทั่วไปของ DataTables (General Search)
if (!empty($searchValue)) {
    $where .= " AND (doc_id LIKE :search_val OR customer_name LIKE :search_val_2 OR detail LIKE :search_val_3) ";
    $searchWildcard = "%" . $searchValue . "%";
    $params[':search_val'] = $searchWildcard;
    $params[':search_val_2'] = $searchWildcard;
    $params[':search_val_3'] = $searchWildcard;
}


// *** 6. คำนวณจำนวนเรคคอร์ดทั้งหมดหลังจากการกรอง ***
$sql_filter = "SELECT COUNT(id) FROM `$table_name` " . $where;

$myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_filter );
fclose($myfile);

try {
    $stmt_filtered = $conn->prepare($sql_filter);

    // ผูกพารามิเตอร์เฉพาะส่วน WHERE
    $where_params = $params;
    $stmt_filtered->execute($where_params);
    $filteredRecords = $stmt_filtered->fetchColumn();
} catch (PDOException $e) {
    $filteredRecords = 0;
}


// *** 7. สร้างคำสั่ง SELECT สำหรับดึงข้อมูล ***
$sql = "SELECT " . implode(', ', array_map(function($col){ return '`' . $col['db'] . '`'; }, $columns)) . " FROM `$table_name` " . $where;

// การเรียงลำดับ (Ordering)
$columnIndex = $_POST['order'][0]['column'];
$columnName = $columns[$columnIndex]['db'];
$columnSortOrder = $_POST['order'][0]['dir'];
$sql .= " ORDER BY `" . $columnName . "` " . $columnSortOrder;

// การจำกัดจำนวนแถว (Paging)
$sql .= " LIMIT :start, :length ";
$params[':start'] = (int)$start;
$params[':length'] = (int)$length;

// ดำเนินการคำสั่ง SQL ดึงข้อมูล
try {
    $stmt_data = $conn->prepare($sql);

    // PDO ต้องผูกพารามิเตอร์ที่เป็น integer แยกต่างหากสำหรับ LIMIT
    $stmt_data->bindParam(':start', $params[':start'], PDO::PARAM_INT);
    $stmt_data->bindParam(':length', $params[':length'], PDO::PARAM_INT);

    // ผูกพารามิเตอร์อื่น ๆ ที่ไม่ใช่ LIMIT
    foreach ($params as $key => $value) {
        if ($key !== ':start' && $key !== ':length') {
            $stmt_data->bindValue($key, $value);
        }
    }

    $stmt_data->execute();
    $data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // จัดการข้อผิดพลาด
    $data = [];
    error_log("PDO Data Fetch Error: " . $e->getMessage());
}

// *** 8. ส่งผลลัพธ์กลับในรูปแบบ JSON ***
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $filteredRecords,
    "aaData" => $data
);

header('Content-Type: application/json');
echo json_encode($response);
?>