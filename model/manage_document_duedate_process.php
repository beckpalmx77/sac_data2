<?php
session_start();
error_reporting(0);

include('../config/connect_sqlserver.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA_DUE_DATE') {

    $sql_query_count = " SELECT COUNT(*) AS allcount 
FROM DOCINFO 
LEFT JOIN ARDETAIL ON DOCINFO.DI_KEY = ARDETAIL.ARD_DI
LEFT JOIN ARFILE ON ARDETAIL.ARD_AR = ARFILE.AR_KEY ";

    $sql_query_data = " SELECT DOCTYPE.DT_DOCCODE,DOCTYPE.DT_THAIDESC,DOCINFO.DI_REF,DOCINFO.DI_DATE,DOCINFO.DI_AMOUNT,ARFILE.AR_CODE,ARFILE.AR_NAME
,ARDETAIL.ARD_BIL_DA,ARDETAIL.ARD_DUE_DA,ARDETAIL.ARD_CHQ_DA,ARFILE.AR_SLMNCODE,SALESMAN.SLMN_NAME,ARFILE.AR_REMARK ,DOCINFO.DI_REMARK 
FROM DOCINFO 
LEFT JOIN ARDETAIL ON DOCINFO.DI_KEY = ARDETAIL.ARD_DI
LEFT JOIN ARFILE ON ARDETAIL.ARD_AR = ARFILE.AR_KEY 
LEFT JOIN SALESMAN ON SALESMAN.SLMN_CODE = ARFILE.AR_SLMNCODE
LEFT JOIN DOCTYPE ON DOCTYPE.DT_KEY = DOCINFO.DI_DT ";

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value
    $searchArray = array();

    $searchQuery = " ";

    if ($searchValue != '') {
        $searchQuery = " ";
        $searchArray = array(
        );
    }

    $myfile = fopen("param_post_mssql_data.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $sql_query_data . " | " . $searchQuery);
    fclose($myfile);

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
    // . " ORDER BY DI_REF  LIMIT :limit,:offset";
       . " ORDER BY DI_REF  ";

    $myfile = fopen("param_post_mssql.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $sql_get_data);
    fclose($myfile);

## Fetch records
    $stmt = $conn->prepare($sql_get_data);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    //$stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    //$stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $dataRecords = $stmt->fetchAll();
    $data = array();

    foreach ($dataRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "DI_REF" => $row['DI_REF'],
                "DI_DATE" => $row['DI_DATE'],
                "AR_NAME" => $row['AR_NAME'],
                "DI_AMOUNT" => $row['DI_AMOUNT'],
                "AR_REMARK" => $row['AR_REMARK'],
                "AR_SLMNCODE" => $row['AR_SLMNCODE'],
                "SLMN_NAME" => $row['SLMN_NAME'],
                "ARD_DUE_DA" => $row['ARD_DUE_DA'],
                "update" => "<button type='button' name='update' id='" . $row['DI_REF'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>"
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

