<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Customer_Addr-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_info_customer_ar.php');

$ar_code = $_POST['ar_code'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$province = $_POST['province'] ?? '';
$phone = $_POST['phone'] ?? '';
$slmn_code = $_POST['slmn_code'] ?? '';

$sql_cond_ext = '';
if (!empty($ar_code)) {
    $sql_cond_ext .= " AND ARFILE.AR_CODE LIKE '%" . $ar_code . "%' ";
}
if (!empty($ar_name)) {
    $sql_cond_ext .= " AND ARFILE.AR_NAME LIKE '%" . str_replace(" ", "%", $ar_name) . "%' ";
}
if (!empty($province)) {
    $sql_cond_ext .= " AND ADDRBOOK.ADDB_PROVINCE LIKE '%" . $province . "%' ";
}
if (!empty($phone)) {
    $sql_cond_ext .= " AND (ADDRBOOK.ADDB_PHONE LIKE '%" . $phone . "%' OR CONTACT.CT_MOBILE LIKE '%" . $phone . "%') ";
}
if (!empty($slmn_code)) {
    $sql_cond_ext .= " AND ARFILE.AR_SLMNCODE = '" . $slmn_code . "' ";
}

$String_Sql = $select_query . $sql_cond . $sql_cond_ext . $sql_order;

$data = "AR_CODE,AR_NAME,ADDB_COMPANY,ADDB_BRANCH,ADDB_TAX_ID,ADDB_ADDB_1,ADDB_ADDB_2,ADDB_ADDB_3,ADDB_PROVINCE,ADDB_POST,ADDB_PHONE,ADDB_FAX,ADDB_EMAIL,SLMN_NAME,CT_NAME,CT_MOBILE\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

$loop = 0;

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    $loop++;

    $data .= $loop . ",";
    $data .= str_replace(",", "^", $row['AR_CODE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['AR_NAME'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_COMPANY'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_BRANCH'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_TAX_ID'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_ADDB_1'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_ADDB_2'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_ADDB_3'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_PROVINCE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_POST'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_PHONE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_FAX'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ADDB_EMAIL'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['SLMN_NAME'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['CT_NAME'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['CT_MOBILE'] ?? '') . "\n";

}

$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();