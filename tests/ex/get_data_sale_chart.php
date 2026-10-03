<!-- PHP Script: get_data_sale_chart.php -->
<?php
// Database connection
include('../../config/connect_db.php');

// รับค่าเงื่อนไขจากหน้าเว็บ (ตามวันที่หรือประเภทสินค้า)
$SKU_CAT = isset($_GET['SKU_CAT']) ? $_GET['SKU_CAT'] : '';
$doc_date_start = isset($_GET['doc_date_start']) ? $_GET['doc_date_start'] : '';
$doc_date_to = isset($_GET['doc_date_to']) ? $_GET['doc_date_to'] : '';

// แปลงจากรูปแบบ YYYY-MM-DD
if (!empty($doc_date_start)) {
    $doc_date_start = DateTime::createFromFormat('Y-m-d', $doc_date_start) ? DateTime::createFromFormat('Y-m-d', $doc_date_start)->format('Y-m-d') : '';
}
if (!empty($doc_date_to)) {
    $doc_date_to = DateTime::createFromFormat('Y-m-d', $doc_date_to) ? DateTime::createFromFormat('Y-m-d', $doc_date_to)->format('Y-m-d') : '';
}

// SQL query
$query = "SELECT SKU_CAT, SUM(TRD_QTY) as total_qty FROM ims_data_sale_sac_all WHERE 1 ";

// เงื่อนไขตามประเภทสินค้า
if ($SKU_CAT != '') {
    $query .= " AND SKU_CAT = '" . $SKU_CAT . "'" ;
}

// เงื่อนไขตามวันที่
if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $query .= " AND STR_TO_DATE(DI_DATE, '%Y-%m-%d') BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "'";
}

$query .= " GROUP BY SKU_CAT";

// Debugging (log SQL query)
/*
$txt = "sql = " . $query;
$my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

$stmt = $conn->prepare($query);

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

//file_put_contents("results.json", json_encode($results));

echo json_encode($results);
?>