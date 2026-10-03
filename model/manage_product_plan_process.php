<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM wh_product_master WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "product_id" => $result['product_id'],
            "product_name" => $result['product_name'],
            "iccat_code" => $result['iccat_code'],
            "brand_code" => $result['brand_code'],
            "unit_id" => $result['unit_id'],
            "unit_name" => $result['unit_name']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'GET_PRODUCT') {

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
        $searchQuery = " AND (product_id LIKE :product_id or 
        product_name LIKE :product_name OR
        brand_code LIKE :brand_code OR
        brand_name LIKE :brand_name) ";
        $searchArray = array(
            'product_id' => "%$searchValue%",
            'product_name' => "%$searchValue%",
            'brand_code' => "%$searchValue%",
            'brand_name' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM wh_product_master ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM wh_product_master WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM wh_product_master WHERE 1 AND unit_id = '70' AND iccat_code <> '6SAC001' " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

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
                "brand_code" => $row['brand_code'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "brand_code" => $row['brand_code'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "select" => "<button type='button' name='select' id='" . $row['product_id'] . "@" . $row['product_name'] . "@" . $row['unit_id'] . "@" . $row['unit_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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