<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_STOCK_BALANCE') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

    ## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (wh LIKE :wh or product_id LIKE :product_id or product_name LIKE :product_name) ";
        $searchArray = array(
            'wh' => "%$searchValue%",
            'product_id' => "%$searchValue%",
            'product_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance WHERE 1 ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_get = "SELECT v_wh_stock_balance.*,wh_product_master.product_name AS product_name FROM v_wh_stock_balance 
        LEFT JOIN wh_product_master ON wh_product_master.product_id = v_wh_stock_balance.productv_wh_stock_balance_id 
        WHERE 1 " . $searchQuery . " ORDER BY v_wh_stock_balance.product_id ,v_wh_stock_balance.wh " . " LIMIT :limit,:offset";

    $txt = "sql = " . $sql_get . " | " . (int)$row . " | " . (int)$rowperpage ;
    $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);

    $stmt = $conn->prepare($sql_get);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "qty" => $row['qty'],
                "wh" => $row['wh'],
                "wh_week_id" => $row['wh_week_id'],
                "location" => $row['location']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "select" => "<button type='button' name='select' id='" . $row['product_id'] . "@" . $row['product_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
        }

    }

## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);

}

