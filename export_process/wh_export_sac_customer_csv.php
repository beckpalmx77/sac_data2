<?php
date_default_timezone_set('Asia/Bangkok');
include('../config/connect_db.php');

// Function สำหรับ Quote String
function quoteArray($arr, $conn) {
    return array_map(function ($v) use ($conn) {
        // ใช้ N นำหน้าสำหรับภาษาไทยใน SQL Server หากจำเป็น แต่ถ้า Column ปกติไม่ต้องก็ได้
        return "N" . $conn->quote(trim($v));
    }, $arr);
}

// 1. รับค่า Filter (Province)
$sql_condition = "";

if (!empty($_POST['provinces'])) {
    $in = implode(',', quoteArray($_POST['provinces'], $conn));
    $sql_condition .= " AND TRD_PROVINCE IN ($in) ";
} else {
    // ถ้าไม่ได้เลือกอะไรเลย ให้เงื่อนไขเป็นเท็จ หรือ ดึงทั้งหมด (ขึ้นอยู่กับนโยบาย)
    // กรณีนี้ขอใส่ดักไว้ ถ้าไม่เลือกจะไม่ดึงข้อมูลเพื่อ performance
    // $sql_condition .= " AND 1=0 ";
}

// 2. ตั้งค่า Header CSV
$filename = "SAC_Customer_Data_" . date('Ymd_His') . ".csv";

header('Content-type: text/csv; charset=UTF-8');
header('Content-Encoding: UTF-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

// 3. SQL Query
// ใช้ SQL ตามที่คุณระบุ และแทรกเงื่อนไข Filter
$String_Sql = "
SELECT AR_CODE, AR_NAME, TRD_AMPHUR, SALE_NAME, TRD_PROVINCE 
FROM ims_data_sale_sac_all
WHERE AR_CODE LIKE 'SAC%' 
$sql_condition
GROUP BY AR_CODE, AR_NAME, TRD_AMPHUR, SALE_NAME, TRD_PROVINCE 
ORDER BY AR_CODE, AR_NAME, TRD_AMPHUR, SALE_NAME, TRD_PROVINCE
";

$query = $conn->prepare($String_Sql);
$query->execute();

// 4. Output CSV Content
// Header Column
$csv_content = "รหัสลูกค้า,ชื่อลูกค้า,อำเภอ,จังหวัด,พนักงานขาย\n";

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    // Clean Data (ป้องกัน Comma)
    $ar_code  = str_replace(",", " ", $row['AR_CODE']);
    $ar_name  = str_replace(",", " ", $row['AR_NAME']);
    $amphur   = str_replace(",", " ", $row['TRD_AMPHUR']);
    $province = str_replace(",", " ", $row['TRD_PROVINCE']);
    $sale     = str_replace(",", " ", $row['SALE_NAME']);

    // เรียง column ตามความเหมาะสม หรือตาม Query
    $csv_content .= "$ar_code,$ar_name,$amphur,$province,$sale\n";
}

// แปลงเป็น Windows-874 สำหรับ Excel ภาษาไทย
echo iconv("utf-8", "windows-874//IGNORE", $csv_content);
exit();
?>