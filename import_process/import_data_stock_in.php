<?php
include '../config/connect_db.php';
require '../vendor/autoload.php'; // Load PhpSpreadsheet
include '../util/record_util.php';

use PhpOffice\PhpSpreadsheet\IOFactory;


$doc_date = date("d-m-Y");
$doc_user_id = "IM01";
$cond = " WHERE doc_date = '" . $doc_date . "' AND doc_user_id = '" . $doc_user_id . "'";
$run_no = LAST_DOCUMENT_NUMBER($conn, "doc_id", "wh_stock_movement", $cond);
$doc_id = "BF-" . $doc_user_id . "-" . $doc_date . "-" . sprintf('%06s', $run_no);

$record_type = "+";
$line_no = 0;

$str=rand();
$seq_record = md5($str);

/*
$txt = $doc_id . " | " . $run_no . " | " . $cond;
$my_file = fopen("import_wh_param.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];

    $spreadsheet = IOFactory::load($fileTmp);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    $importedRows = 0;
    $duplicateRows = 0;

    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // Skip header row

        $doc_date = ($row[0] === "" || $row[0] === null) ? "-" : trim($row[0]);
        $product_id = ($row[1] === "" || $row[1] === null) ? "-" : trim($row[1]);
        $product_name = ($row[2] === "" || $row[2] === null) ? "0" : $row[2];
        $qty = ($row[3] === "" || $row[3] === null) ? "-" : $row[3];
        $wh = ($row[4] === "" || $row[4] === null) ? "-" : trim($row[4]);
        $wh_week_id = ($row[5] === "" || $row[5] === null) ? "-" : trim($row[5]);
        $location = ($row[6] === "" || $row[6] === null) ? "-" : trim($row[6]);

        // Check for duplicates in wh_stock_transaction
        $statement = $conn->prepare("SELECT COUNT(*) FROM wh_stock_transaction WHERE doc_id = ? AND doc_date = ? AND product_id = ? AND wh = ? AND wh_week_id = ? AND location = ? ");
        $statement->execute([$doc_id,$doc_date,$product_id,$wh,$wh_week_id,$location]);
        if ($statement->fetchColumn() == 0) {
            $line_no++;
            // Insert new customer
            $stmt_insert_stock_wh = $conn->prepare("INSERT INTO wh_stock_transaction (doc_id,doc_date,line_no,product_id,qty,wh,wh_week_id,location,doc_user_id,record_type,create_by,seq_record) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt_insert_stock_wh->execute([$doc_id,$doc_date,$line_no,$product_id,$qty,$wh,$wh_week_id,$location,$doc_user_id,$record_type,$doc_user_id,$seq_record]);
        } else {
            $duplicateRows++;
        }
    }

    echo "Imported: $importedRows, Duplicates: $duplicateRows";
} else {
    echo "No file uploaded or error in file upload.";
}

