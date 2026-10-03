<?php
date_default_timezone_set('Asia/Bangkok');

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED);

$filename = "Data_Sale_ReadyQuick-Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../config/connect_db.php');
include('../cond_file/doc_info_sale_return_readyquick.php');

$DT_DOCCODE_MINUS = "IS";

$CUST_NAME = "";

$price_s1 = '0';

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);


$String_Sql = $select_query_daily . $select_query_daily_cond . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to
    . $select_query_daily_order;

/*
$my_file = fopen("a-rq_str1.txt", "w") or die("Unable to open file!");
fwrite($my_file, $String_Sql);
fclose($my_file);
*/

//$data = "ว/ด/ป,รหัสสินค้า,ชื่อลูกค้า,ประเภท,Platform,เลขที่บิล,จำนวน,ของแถม,ราคาขาย(เส้น),มูลค่ารวมเปิดบิล,ราคาค้าส่ง(เส้น),วิธีการจัดส่ง,หมายเหตุ\n";
$data = "ว/ด/ป,รหัสสินค้า,ชื่อลูกค้า,ประเภท,Platform,เลขที่บิล,จำนวน,ราคาขาย(เส้น),มูลค่ารวมเปิดบิล,ราคาค้าส่ง(เส้น),วิธีการจัดส่ง,หมายเหตุ\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    // 1. แยกส่วน วัน, เดือน, ปี ออกจากกันด้วยเครื่องหมาย "/"
    $di_date = $row['DI_DATE'] ?? '';
    $date_parts = explode('/', $di_date);
    if (count($date_parts) === 3) {
        list($day, $month, $year) = $date_parts;
        $buddhist_year = (int)$year + 543;
        $buddhist_date = "$day/$month/$buddhist_year";
    } else {
        $buddhist_date = $di_date;
    }

// 4. นำไปใช้งาน
    $data .= $buddhist_date . ",";

    $data .= str_replace(",", "^", $row['SKU_CODE'] ?? '') . ",";

    // 1. ดึงค่าต้นฉบับมาเก็บในตัวแปรเพื่อง่ายต่อการอ่าน
    $original_remark = $row['DI_REMARK'] ?? '';
    $original_sb_name = $row['SB_NAME'] ?? '';
    $DI_REMARK = ''; // กำหนดค่าเริ่มต้นเป็นค่าว่าง

    // Define the mapping of keywords to remarks
// Grouping similar keywords (like Shopee variants) in an array
    $remark_map = [
        'LAZADA' => ['LAZADA'],
        'SHOPEE' => ['Shoppe', 'ช้อปปี้', 'Shopee'],
        'TIKTOK' => ['TIKTOK'],
        'โคนัน'  => ['โคนัน'],
        'Wyz'    => ['Wyz'],
    ];

/* Code for DI_REMARK Get DI_REF
    $text = "อ้างถึงใบส่งสินค้า#DS03256811/260:04/11/2568";
    $pattern = '/#(.+?):/'; // รูปแบบ Regex

    $match = [];
    if (preg_match($pattern, $text, $match)) {
        // $match[0] คือ ข้อความทั้งหมดที่จับคู่ได้ (#DS03256811/260:)
        // $match[1] คือ ข้อความที่อยู่ในวงเล็บ (DS03256811/260)
        $result = $match[1];
        echo "Substring ที่ต้องการ: " . $result;
    } else {
        echo "ไม่พบข้อความที่ตรงตามรูปแบบ";
    }
*/

// Set a default value for DI_REMARK if needed, e.g., null or an empty string
    $DI_REMARK = '-';



// Combine the strings to be checked, giving priority to $original_sb_name
    $strings_to_check = [$original_sb_name, $original_remark];

// Loop through the map and then through the strings to find a match
    foreach ($remark_map as $remark => $keywords) {
        foreach ($keywords as $keyword) {
            foreach ($strings_to_check as $string) {
                // If the keyword is found in the current string
                if (!empty($string) && stripos($string, $keyword) !== false) {
                    // Assign the remark and break out of all loops
                    $DI_REMARK = $remark;
                    break 3; // Breaks out of the 3 nested foreach loops
                }
            }
        }
    }

    // **********************************************
    // แก้ไข: ลบเครื่องหมายลูกน้ำต่อท้ายออก
    $ship_remark = $row['TRH_SHIP_REMARK'] ?? '';
    $CUST_NAME = (strlen($ship_remark) > 10 ? "'" . $ship_remark : $ship_remark);
    // **********************************************

    // ตอนนี้บรรทัดนี้จะเพิ่มเพียง 1 คอลัมน์ (ชื่อลูกค้า) + 1 ตัวคั่น (,)
    $data .= trim($CUST_NAME) . ",";

    $data .= ($row['ICCAT_NAME'] ?? '') . ",";

    $data .= $DI_REMARK . ",";

    //$DI_DATE = str_replace("\\r\ ", "", $row['DI_DATE']);
    //$data .= $DI_DATE . ",";
    //

    $data .= str_replace(",", "^", $row['DI_REF'] ?? '') . ",";

    $TRD_QTY = $row['TRD_QTY'] ?? 0;
    $TRD_Q_FREE = $row['TRD_Q_FREE'] ?? 0;
    $TRD_U_PRC = $row['TRD_U_PRC'] ?? 0;
    $TRD_B_AMT = $row['TRD_B_AMT'] ?? 0;
    $ARPLU_U_PRC = $row['ARPLU_U_PRC'] ?? 0;

    //$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, "Data " . " = " . $TRD_QTY . " | " . $TRD_U_PRC . " | "
    //. $TRD_DSC_KEYINV . " | " . $TRD_B_SELL . " | " . $TRD_B_VAT . " | " . $TRD_G_KEYIN);
    //fclose($my_file);


    if (strpos($row['DT_DOCCODE'] ?? '', $DT_DOCCODE_MINUS) !== false) {
        $data .= "-" . $TRD_QTY . ",";
        //$data .= "-" . $TRD_Q_FREE . ",";
        $data .= "-" . $TRD_U_PRC . ",";
        $data .= "-" . $TRD_B_AMT . ",";
    } else {
        $data .= $TRD_QTY . ",";
        //$data .= $TRD_Q_FREE . ",";
        $data .= $TRD_U_PRC . ",";
        $data .= $TRD_B_AMT . ",";
    }

    $cleaned_remark = preg_replace('/[\r\n]+/', ' ', $original_remark ?? '');

    $data .= $ARPLU_U_PRC . ",";
    $data .= ($row['SB_NAME'] ?? '') . ",";
    $data .= trim($cleaned_remark) . "\n";


}

// $data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();