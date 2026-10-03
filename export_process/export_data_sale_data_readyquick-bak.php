<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Sale_Free-Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../config/connect_db.php');
include('../cond_file/doc_info_sale_readyquick.php');

$DT_DOCCODE_MINUS = "IS";

$price_s1 = '0';

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);


$String_Sql = $select_query_daily . $select_query_daily_cond . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
    . $select_query_daily_order;

/*
$my_file = fopen("a-rq_str1.txt", "w") or die("Unable to open file!");
fwrite($my_file, $String_Sql);
fclose($my_file);
*/

$data = "ว/ด/ป,รหัสสินค้า,ชื่อลูกค้า,ประเภท,Platform,เลขที่บิล,จำนวน,ราคาขาย(เส้น),มูลค่ารวมเปิดบิล,ราคาค้าส่ง(เส้น),วิธีการจัดส่ง\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    //$data .= " " . $row['DI_DATE'] . ",";

    // 1. แยกส่วน วัน, เดือน, ปี ออกจากกันด้วยเครื่องหมาย "/"
    list($day, $month, $year) = explode('/', $row['DI_DATE']);

// 2. แปลงปี ค.ศ. เป็น พ.ศ. โดยการบวก 543
    $buddhist_year = (int)$year + 543;

// 3. ประกอบร่างกลับเข้าไปใหม่
    $buddhist_date = "$day/$month/$buddhist_year";

// 4. นำไปใช้งาน
    $data .= " " . $buddhist_date . ",";

    $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";

    $sql_str = "select price from ims_product where price_code = 'S1' AND product_id = '" .$row['SKU_CODE'] . "'";
    $query = $conn->prepare($sql_str);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    foreach ($results as $result) {
        $price_s1  = $result->price;
    }
    //$data .= " " . ",";

    // 1. ดึงค่าต้นฉบับมาเก็บในตัวแปรเพื่อง่ายต่อการอ่าน
    $original_remark = $row['DI_REMARK'];
    $DI_REMARK = ''; // กำหนดค่าเริ่มต้นเป็นค่าว่าง

// 2. ใช้ stripos() เพื่อค้นหาข้อความ (จะคืนค่าตำแหน่งถ้าเจอ, คืนค่า false ถ้าไม่เจอ)
//    เราต้องใช้ === false หรือ !== false ในการเปรียบเทียบเสมอ

    if (stripos($original_remark, 'LAZADA') !== false) {
        $DI_REMARK = 'LAZADA';

    } elseif (stripos($original_remark, 'Shoppe') !== false) {
        $DI_REMARK = 'SHOPEE'; // ใช้ค่าเดิมทั้งหมด

    } elseif (stripos($original_remark, 'ช้อปปี้') !== false) {
        $DI_REMARK = 'SHOPEE'; // ใช้ค่าเดิมทั้งหมด

    } elseif (stripos($original_remark, 'Shopee') !== false) {
        $DI_REMARK = 'SHOPEE'; // ใช้ค่าเดิมทั้งหมด

    } elseif (stripos($original_remark, 'TIKTOK') !== false) {
        $DI_REMARK = 'TIKTOK'; // ใช้ค่าเดิมทั้งหมด

    } elseif (stripos($original_remark, 'โคนัน') !== false) {
    $DI_REMARK = 'โคนัน'; // ใช้ค่าเดิมทั้งหมด

}

// 1. ตัด string ด้วย '**'
    $parts = explode('**', $original_remark);

// 2. ตรวจสอบว่ามีส่วนที่ 2 (index 1) หรือไม่
    if (isset($parts[1])) {
        // 3. ถ้ามี ก็ดึงข้อมูลส่วนนั้นมาใช้
        $CUST_NAME = $parts[1];
    }

    $data .= " " . $CUST_NAME . ",";
    $data .= " " . $row['ICCAT_NAME'] . ",";

    $data .= " " . $DI_REMARK . ",";

    //$DI_DATE = str_replace("\\r\ ", "", $row['DI_DATE']);
    //$data .= $DI_DATE . ",";
    //

    $data .= str_replace(",", "^", $row['DI_REF']) . ",";

    $TRD_QTY = $row['TRD_QTY'];
    $TRD_U_PRC = $row['TRD_U_PRC'];
    $TRD_B_AMT = $row['TRD_B_AMT'];

    //$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, "Data " . " = " . $TRD_QTY . " | " . $TRD_U_PRC . " | "
    //. $TRD_DSC_KEYINV . " | " . $TRD_B_SELL . " | " . $TRD_B_VAT . " | " . $TRD_G_KEYIN);
    //fclose($my_file);



    if(strpos($row['DT_DOCCODE'], $DT_DOCCODE_MINUS) !== false){
        $data .= "-" . $TRD_QTY . ",";
        $data .= "-" . $TRD_U_PRC . ",";
        $data .= "-" . $TRD_B_AMT . ",";
    } else {
        $data .= $TRD_QTY . ",";
        $data .= $TRD_U_PRC . ",";
        $data .= $TRD_B_AMT . ",";
    }
    $data .= "-" . $price_s1 . ",";
    $data .= "-" . "\n";


}

// $data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();