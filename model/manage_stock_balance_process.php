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

    $doc_date_start = $_POST['doc_date_start'];
    $doc_date_to = $_POST['doc_date_to'];

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (product_id LIKE :product_id or product_name LIKE :product_name or wh LIKE :wh) ";
        $searchArray = array(
            'product_id' => "%$searchValue%",
            'product_name' => "%$searchValue%",
            'wh' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_wh_stock_balance WHERE v_wh_stock_balance.location <> 'OUT' " . $searchQuery
        . " ORDER BY product_id " . " LIMIT :limit,:offset");

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
                "wh" => $row['wh'],
                "wh_week_id" => $row['wh_week_id'],
                "location" => $row['location'],
                "qty" => $row['total_qty'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "wh" => $row['wh'],
                "select" => "<button type='button' name='select' id='" . $row['product_id'] . "@" . $row['wh'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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


if ($_POST["action"] === 'GET_STOCK_BALANCE_DISPLAY') {
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
        $searchQuery = " AND (product_id LIKE :product_id or product_name LIKE :product_name or wh LIKE :wh) ";
        $searchArray = array(
            'product_id' => "%$searchValue%",
            'product_name' => "%$searchValue%",
            'wh' => "%$searchValue%",
        );
    }

    //$doc_date_start = "01-01-2024";

    $doc_date_start = $_POST['doc_date_start'];
    $doc_date_to = $_POST['doc_date_to'];

    $product_id = $_POST['product_id'];
    $wh = $_POST['wh'];
    $wh_week_id = $_POST['wh_week_id'];

    $search_Query = "";

    if (!empty($product_id)) {
        $search_Query .= " AND t.product_id = '" . $product_id . "' ";
    }

    if (!empty($wh)) {
        $search_Query .= " AND t.wh = '" . $wh . "' ";
    }

    if (!empty($wh_week_id)) {
        $search_Query .= " AND t.wh_week_id = '" . $wh_week_id . "' ";
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_balance WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    $sql_get = "SELECT p.product_id,p.product_name,t.wh,t.wh_week_id,t.location,
                SUM(
                    CASE 
                        WHEN t.record_type = '+' THEN t.qty
                        WHEN t.record_type = '-' THEN -t.qty
                        ELSE 0
                    END) AS total_qty
                FROM wh_stock_transaction t
                JOIN wh_product_master p ON t.product_id = p.product_id
                WHERE t.location <> 'OUT' AND (t.doc_date BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "') " . $search_Query .
        " GROUP BY p.product_id,p.product_name,t.wh,t.wh_week_id,t.location" . " LIMIT :limit,:offset ";

## Fetch records
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
                "wh" => $row['wh'],
                "wh_week_id" => $row['wh_week_id'],
                "location" => $row['location'],
                "qty" => $row['total_qty'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "wh" => $row['wh'],
                "select" => "<button type='button' name='select' id='" . $row['product_id'] . "@" . $row['wh'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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