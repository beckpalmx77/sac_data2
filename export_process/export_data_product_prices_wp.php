<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Product_Price-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/query-product-price-main.php');

//$IMG_DIR = "http://192.168.88.40:8888/sac_wp/wp-content/uploads/products/";
$IMG_DIR = "http://171.100.56.194:8999/sac_tires/wp-content/uploads/products/";

$price_code = $_POST['price_code'];


$sql_where_ext = " AND ICCAT_CODE  in ('1SAC14','4SAC01','3SAC01','1SAC06','1SAC05','1SAC01','1SAC02','1SAC03','1SAC04','1SAC08','1SAC07',
'1SAC09','1SAC10','1SAC11','1SAC12','1SAC13','2SAC09','2SAC04','2SAC13','2SAC14','2SAC02','2SAC03',
'2SAC10','2SAC06','2SAC05','2SAC07','2SAC08','3SAC02','3SAC06','3SAC03','3SAC04','4SAC02','4SAC03',
'4SAC04','4SAC06','3SAC05','4SAC05') AND ARPRB_CODE like '" . $price_code . "'";

$sql_order = " ORDER BY SKU_KEY DESC ";

$String_Sql = $select_query . $sql_cond . $sql_where_ext . $sql_order;

//$my_file = fopen("D-sac_str1.txt", "w") or die("Unable to open file!");
//fwrite($my_file, $String_Sql);
//fclose($my_file);

$data = "ID,Type,SKU,Name,Published,Is featured?,Visibility in catalogue,Short description,Description,Date sale price starts,Date sale price ends,Tax status,Tax class,In stock?,Stock,Low stock amount,Backorders allowed?,Sold individually?,Weight (kg),Length (cm),Width (cm),Height (cm),Allow customer reviews?,Purchase note,Sale price,Regular price,Categories,Tags,Shipping class,Images,Download limit,Download expiry days,Parent,Grouped products,Upsells,Crosssells,External URL,Button text,Position,Attribute 1 name,Attribute 1 value(s),Attribute 1 visible,Attribute 1 global\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

$loop = 1000;

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    include 'cond_brand.php';

    $loop++;

    $data .= $loop . ",";
    $data .= "simple" . ",";
    $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";
    $data .= "1" . ",";
    $data .= "0" . ",";
    $data .= "visible" . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= "taxable" . ",";
    $data .= " " . ",";
    $data .= "1" . ",";
    $data .= "0" . ",";
    $data .= " " . ",";
    $data .= "1" . ",";
    $data .= "0" . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= "1" . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= $row['ARPLU_U_PRC'] . ",";
    $data .= "Tires" . ",";
    $data .= $BRN_NAME . ",";
    $data .= " " . ",";
    $data .= $IMG_DIR . $IMG . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= "0" . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . ",";
    $data .= " " . "\n";

}

//$data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874", $data);
echo $data;

exit();