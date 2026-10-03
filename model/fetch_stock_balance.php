<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

// Getting parameters
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$product_name = $_POST['product_name'];

// SQL query
$sql = "SELECT 
            p.product_id,
            p.product_name,
            t.wh,
            t.wh_week_id,
            t.location,
            SUM(
                CASE 
                    WHEN t.record_type = '+' THEN t.qty
                    WHEN t.record_type = '-' THEN -t.qty
                    ELSE 0
                END
            ) AS total_qty
        FROM 
            wh_stock_transaction t
        JOIN 
            wh_product_master p ON t.product_id = p.product_id
        WHERE 
            (STR_TO_DATE(t.doc_date, '%d/%m/%Y') BETWEEN :start_date AND :end_date 
            OR p.product_name LIKE :product_name)
        GROUP BY 
            p.product_id,
            p.product_name,
            t.wh,
            t.wh_week_id,
            t.location";

// Prepare statement
$stmt = $conn->prepare($sql);
$stmt->bindValue(':start_date', $start_date);
$stmt->bindValue(':end_date', $end_date);
$stmt->bindValue(':product_name', '%' . $product_name . '%');
$stmt->execute();

// Fetch data
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return as JSON
echo json_encode([
    "data" => $data
]);


