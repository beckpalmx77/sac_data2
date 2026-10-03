<?php
include '../config/connect_db.php';

$table_name = "ims_data_sale_sac_all";

// คำสั่ง SQL ลบข้อมูลที่ DI_YEAR ไม่ใช่ตัวเลข
$sql_delete = "DELETE FROM " . $table_name . " WHERE DI_YEAR NOT REGEXP '^[0-9]+$' ";


// บันทึกคำสั่ง SQL ลงไฟล์เพื่อตรวจสอบ
/*
$myfile = fopen("sql-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_delete);
fclose($myfile);
*/

$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->execute();

// ตรวจสอบจำนวนแถวที่ถูกลบ
$deleted_rows = $stmt_delete->rowCount();
echo "ลบข้อมูลทั้งหมด: " . $deleted_rows . " รายการ";

$conn = null; // ปิดการเชื่อมต่อ



