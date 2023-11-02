<?php
include 'config_dbs.php';

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT * FROM menu_main WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "main_menu_id" => $result['main_menu_id'],
            "label" => $result['label'],
            "link" => $result['link'],
            "icon" => $result['icon'],
            "data_target" => $result['data_target'],
            "aria_controls" => $result['aria_controls'],
            "privilege" => $result['privilege']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'GET_BILL_DATA') {

## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

## Custom Field value
    $searchByName = $_POST['searchByName'];
    $searchBySale = $_POST['searchBySale'];
    $searchByDueDate = $_POST['searchByDueDate'] == '' ? "7" : $_POST['searchByDueDate'];

## Search 
    $searchQuery = " ";

/*
    $myfile = fopen("param_post_mssql_data.txt", "w") or die("Unable to open file!");
    fwrite($myfile, "searchByName | " . $searchByName . " | ". $searchBySale . " | searchByDueDate " . $searchByDueDate . " | searchQuery = " . $searchQuery);
    fclose($myfile);
*/



    if ($searchByName != '') {
        $searchQuery .= " and (ims_document_bill.AR_NAME like '%" . $searchByName . "%' ) ";
    }

    if ($searchBySale != '') {
        $searchQuery .= " and (ims_document_bill.SLMN_NAME like '%" . $searchBySale . "%' ) ";
    }

    $searchQuery .= " and DATEDIFF(ims_document_bill.ARD_DUE_DA, CURDATE()) = " . $searchByDueDate;

    /*
    if($searchByDueDate != ''){
        $searchQuery .= " and DATEDIFF(ims_document_bill.ARD_DUE_DA, CURDATE()) = " . $searchByDueDate;
    } else if($searchByDueDate > 31){
        $searchQuery .= " and DATEDIFF(ims_document_bill.ARD_DUE_DA, CURDATE()) > " . $searchByDueDate;
    }
    */


    /*


    if($searchValue != ''){
        $searchQuery .= " and (emp_name like '%".$searchValue."%' or
            email like '%".$searchValue."%' or
            city like'%".$searchValue."%' ) ";
    }

    */

## Total number of records without filtering
    $sel = mysqli_query($con, "select count(*) as allcount from ims_document_bill");
    $records = mysqli_fetch_assoc($sel);
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $sel = mysqli_query($con, "select count(*) as allcount from ims_document_bill WHERE 1 " . $searchQuery);
    $records = mysqli_fetch_assoc($sel);
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $billQuery = "select ims_document_bill.* , b.DI_REF AS BILL_DI_REF , b.DI_DATE AS BILL_DI_DATE
, b.TPA_REFER_REF , b.TPA_REFER_DATE , b.ARD_BIL_DA  AS BILL_ARD_BIL_DA , b.ARD_DUE_DA AS BILL_ARD_DUE_DA
, b.ARD_A_SV , b.ARD_A_VAT  , b.ARD_A_AMT 
from ims_document_bill
left join ims_document_bill_load b on b.TPA_REFER_REF = ims_document_bill.DI_REF   
WHERE 1 " . $searchQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

    /*
    $myfile = fopen("select_data.txt", "w") or die("Unable to open file!");
    $data_select = " | " . $billQuery;
    fwrite($myfile, $billQuery);
    fclose($myfile);
    */


    $empRecords = mysqli_query($con, $billQuery);
    $data = array();

    while ($row = mysqli_fetch_assoc($empRecords)) {

        $data[] = array(
            "DI_REF" => $row['DI_REF'],
            "DI_DATE" => $row['DI_DATE'] == "" ? "-" : substr($row['DI_DATE'], 8, 2) . "/" . substr($row['DI_DATE'], 5, 2) . "/" . substr($row['DI_DATE'], 0, 4),
            "AR_NAME" => $row['AR_NAME'],
            "DI_AMOUNT" => $row['DI_AMOUNT'],
            "AR_REMARK" => $row['AR_REMARK'],
            "AR_SLMNCODE" => $row['AR_SLMNCODE'],
            "SLMN_NAME" => $row['SLMN_NAME'],
            "ARD_DUE_DA" => $row['ARD_DUE_DA'] == "" ? "-" : substr($row['ARD_DUE_DA'], 8, 2) . "/" . substr($row['ARD_DUE_DA'], 5, 2) . "/" . substr($row['ARD_DUE_DA'], 0, 4),
            "detail" => "<button type='button' name='detail' id='" . $row['DI_REF'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Detail'>Detail</button>"
        );
    }

## Response
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);

}
