<?php
session_start();
error_reporting(0);

include("../config/connect_sqlserver.php");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../cond_file/query_reserve_sac.php');


if ($_POST["action"] === 'GET_RESERVE_PRODUCT') {
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
        $searchQuery = " AND (SKU_NAME LIKE :SKU_NAME or
        AR_NAME LIKE :AR_NAME) ";
        $searchArray = array(
            'SKU_NAME' => "%$searchValue%",
            'AR_NAME' => "%$searchValue%",
        );
    }

    if ($_POST["screen_action"] === 'DASHBOARD') {
        $searchQuery .= " AND DI_DATE = '" . date("Y-m-d") . "'";
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_reserve_product_sac WHERE 1 " . $searchQuery);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_reserve_product_sac WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_get_data = "SELECT * FROM ims_reserve_product_sac WHERE 1 " . $searchQuery
    . " ORDER BY id DESC, " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_get_data);

    /*
    $myfile = fopen("getdata-param.txt", "w") or die("Unable to open file!");
    fwrite($myfile,  $sql_get_data);
    fclose($myfile);
    */


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

            //SAC.0001405=Ready Quick
            if ($row['AR_CODE'] === "SAC.0001405") {
                $customer_name = preg_replace("/\s+/", " ", $row['DI_REMARK']);
            } else {
                $customer_name = $row['AR_NAME'];
            }

            $data[] = array(
                "DI_REF" => $row['DI_REF'],
                "DI_DATE" => $row['DI_DATE'],
                "SKU_CODE" => $row['SKU_CODE'],
                "SKU_NAME" => $row['SKU_NAME'],
                "TRD_QTY" => substr($row['TRD_QTY'], 0, strpos($row['TRD_QTY'], ".")),
                "WL_CODE" => $row['WL_CODE'],
                "SLMN_NAME" => $row['SLMN_NAME'],
                "AR_NAME" => $customer_name
            );
        } else {
            $data[] = array(
                "DI_REF" => $row['DI_REF'],
                "DI_DATE" => $doc_date,
                "SKU_CODE" => $row['SKU_CODE'],
                "select" => "<button type='button' name='select' id='" . $row['DI_DATE'] . "@" . $row['SKU_CODE'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

