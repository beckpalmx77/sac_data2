<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_POST["action"] === 'GET_STOCK_BALANCE') {
    ## อ่านค่าที่รับเข้ามา
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $searchValue = $_POST['search']['value'];
    $searchArray = array();

    ## ค้นหา
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (product_id LIKE :product_id OR wh LIKE :wh) ";
        $searchArray = array(
            ':product_id' => "%$searchValue%",
            ':wh' => "%$searchValue%",
        );
    }

    ## นับจำนวนระเบียนทั้งหมดโดยไม่มีการกรอง
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## นับจำนวนระเบียนทั้งหมดหลังจากการกรอง
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## ดึงข้อมูลระเบียน
    $sql_get = "SELECT v_wh_stock_balance.*, wh_product_master.product_name AS product_name 
                FROM v_wh_stock_balance 
                LEFT JOIN wh_product_master ON wh_product_master.product_id = v_wh_stock_balance.product_id 
                WHERE 1 " . $searchQuery . " 
                ORDER BY " . $columnName . " " . $columnSortOrder . " 
                LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql_get);

    // ผูกค่าต่าง ๆ
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue($key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "wh" => $row['wh'],
                "wh_week_id" => $row['wh_week_id'],
                "location" => $row['location'],
                "qty" => $row['qty']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "select" => "<button type='button' name='select' id='" . $row['product_id'] . "@" . $row['product_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i></button>",
            );
        }
    }

    ## ส่งค่ากลับ JSON
    header('Content-Type: application/json');
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'JSON Error: ' . json_last_error_msg();
    }
}
