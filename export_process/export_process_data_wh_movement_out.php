<?php
include('../config/connect_db.php');
$filename = "movement-stock" . "-" . date('m/d/Y H:i:s', time()) . ".csv";
date_default_timezone_set('Asia/Bangkok');
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

// ดึงข้อมูลตามวันที่เริ่มต้นและสิ้นสุดที่ส่งมาจาก form
$search_value = $_POST['search_value'];
$doc_date_start = $_POST['doc_date_start'];
$doc_date_to = $_POST['doc_date_to'];
$car_no = $_POST['car_no_main'];
$BRAND = $_POST['BRAND'];

$where_cond = "";
$where_BRAND = "";
$search_Query = "";

if ($car_no !== '-') {
    $where_cond = " AND vo.car_no = " . $car_no;
}

if ($BRAND!=='-') {
    $where_BRAND = " AND vo.product_id LIKE '" . $BRAND . "%' ";
}

// แปลงจากรูปแบบ DD-MM-YYYY เป็น YYYY-MM-DD
$doc_date_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$doc_date_to = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $search_Query .= " AND STR_TO_DATE(vo.doc_date, '%d-%m-%Y') BETWEEN '" . $doc_date_start . "' AND '". $doc_date_to ."' " ;
}

$sql_get = "SELECT 
    vo.id,vo.doc_date,vo.doc_id, vo.line_no,vo.product_id,vo.product_name,vo.wh_org,vo.wh_week_id,vo.location_org
    ,vo.sale_take,vo.customer_name,vo.car_no,vo.doc_user_id,vo.location_to
    ,vo.qty,vb.total_qty,vo.create_by,vo.create_date 
FROM 
    v_wh_stock_movement_out vo
LEFT JOIN 
    v_wh_stock_balance vb 
ON 
    vb.product_id = vo.product_id 
    AND vb.wh = vo.wh_org 
    AND vb.wh_week_id = vo.wh_week_id 
    AND vb.location = vo.location_org 
WHERE 1 ";

$select_query_wh_movement = $sql_get . $search_Query . $where_cond . $where_BRAND . " ORDER BY vo.doc_id ";

/*
$txt = "sql = " . $select_query_wh_movement ;
$my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/


$String_Sql = $select_query_wh_movement;

$data = "วันที่,รหัสสินค้า,รายละเอียด,จำนวน,คลังปี,สัปดาห์,ตำแหน่ง,เลขที่เอกสาร,รถคันที่,เทค,Supplier/ลูกค้า,ยอดคงเหลือ\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $data .= $result->doc_date . ",";
        $data .= $result->product_id . ",";
        $data .= $result->product_name . ",";
        $data .= $result->qty . ",";
        $data .= $result->wh_org . ",";
        $data .= $result->wh_week_id . ",";
        $data .= $result->location_org . ",";
        $data .= $result->doc_id . ",";
        $data .= $result->car_no . ",";
        $data .= $result->sale_take . ",";
        $data .= $result->customer_name . ",";
        $data .= $result->total_qty . "\n";
    }
}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();