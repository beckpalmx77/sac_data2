<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA_DUE_DATE') {


    //$searchByName = $_POST['searchByName'];
    //$searchByDueDate = $_POST['searchByDueDate'] =='' ? "7" : $_POST['searchByDueDate'];



    //select DI_REF , ARD_DUE_DA , CURDATE() AS CurrentDate , DATEDIFF(ARD_DUE_DA, CURDATE()) AS DateDueDiff  from ims_document_bill order by id desc

    $sql_query_count = " SELECT COUNT(*) AS allcount FROM ims_document_bill where DI_ACTIVE = 0 ";

    $sql_query_data = " SELECT ims_document_bill.* , b.DI_REF AS BILL_DI_REF , b.DI_DATE AS BILL_DI_DATE
                        , b.TPA_REFER_REF , b.TPA_REFER_DATE , b.ARD_BIL_DA  AS BILL_ARD_BIL_DA , b.ARD_DUE_DA AS BILL_ARD_DUE_DA
                        , b.ARD_A_SV , b.ARD_A_VAT  , b.ARD_A_AMT 
                        FROM ims_document_bill
                        LEFT JOIN ims_document_bill_load b ON b.TPA_REFER_REF = ims_document_bill.DI_REF 
                        WHERE ims_document_bill.DI_ACTIVE = 0 ";

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
        $searchQuery = " AND (ims_document_bill.AR_NAME LIKE :AR_NAME or
        ims_document_bill.DI_REF LIKE :DI_REF) ";
        $searchArray = array(
            'AR_NAME' => "%$searchValue%",
            'DI_REF' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare($sql_query_count);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare($sql_query_count . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    $sql_get_data = $sql_query_data . $searchQuery
       . " ORDER BY id DESC  LIMIT :limit,:offset";


/*
    $myfile = fopen("param_qry_data.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $sql_get_data . " | " . $searchValue);
    fclose($myfile);
*/


## Fetch records
    $stmt = $conn->prepare($sql_get_data);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->execute();
    $dataRecords = $stmt->fetchAll();
    $data = array();

    foreach ($dataRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "DI_REF" => $row['DI_REF'],
                "DI_DATE" => $row['DI_DATE']=="" ? "-" : substr($row['DI_DATE'],8,2) . "/" . substr($row['DI_DATE'],5,2) . "/" . substr($row['DI_DATE'],0,4),
                "AR_NAME" => $row['AR_NAME'],
                "DI_AMOUNT" => $row['DI_AMOUNT'],
                "AR_REMARK" => $row['AR_REMARK'],
                "AR_SLMNCODE" => $row['AR_SLMNCODE'],
                "SLMN_NAME" => $row['SLMN_NAME'],
                "ARD_DUE_DA" => $row['ARD_DUE_DA']=="" ? "-" : substr($row['ARD_DUE_DA'],8,2) . "/" . substr($row['ARD_DUE_DA'],5,2) . "/" . substr($row['ARD_DUE_DA'],0,4)
            );
        } else {
            $data[] = array(
                "DI_REF" => $row['DI_REF'],
                "DI_DATE" => $row['DI_DATE'],
                "AR_NAME" => $row['AR_NAME'],
                "select" => "<button type='button' name='select' id='" . $row['AR_CODE'] . "@" . $row['AR_NAME'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

    //$data = json_encode($response);
    //file_put_contents("data.json", $data);

    echo json_encode($response);
}

