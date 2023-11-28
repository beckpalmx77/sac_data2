<?php
include 'config_dbs.php';
include '../config/connect_db.php';
include '../config/lang.php';

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();

    $sql_get = "select ims_document_bill.* , b.DI_REF AS BILL_DI_REF , b.DI_DATE AS BILL_DI_DATE
    , b.TPA_REFER_REF , b.TPA_REFER_DATE , b.ARD_BIL_DA  AS BILL_ARD_BIL_DA , b.ARD_DUE_DA AS BILL_ARD_DUE_DA
    , b.ARD_A_SV , b.ARD_A_VAT  , b.ARD_A_AMT 
    from ims_document_bill
    left join ims_document_bill_load b on b.TPA_REFER_REF = ims_document_bill.DI_REF   
    WHERE ims_document_bill.id = " . $id;

/*
    $myfile = fopen("param_post_get_data.txt", "w") or die("Unable to open file!");
    fwrite($myfile, "sql_get = " . $sql_get . " | Action = ". $_POST["action"]);
    fclose($myfile);
*/


    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "DI_REF" => $result['DI_REF'],
            "DI_DATE" => $result['DI_DATE'] == "" ? "-" : substr($result['DI_DATE'], 8, 2) . "/" . substr($result['DI_DATE'], 5, 2) . "/" . substr($result['DI_DATE'], 0, 4),
            "BILL_DI_REF" => $result['BILL_DI_REF'] == "" ? "-" : $result['BILL_DI_REF'],
            "BILL_DI_DATE" => $result['BILL_DI_DATE'] == "" ? "-" : substr($result['BILL_DI_DATE'], 8, 2) . "/" . substr($result['BILL_DI_DATE'], 5, 2) . "/" . substr($result['BILL_DI_DATE'], 0, 4),
            "AR_NAME" => $result['AR_NAME'],
            "BILL_NOTE_DATE" => $result['BILL_NOTE_DATE'],
            "ARD_DUE_DA" => $result['ARD_DUE_DA'] == "" ? "-" : substr($result['ARD_DUE_DA'], 8, 2) . "/" . substr($result['ARD_DUE_DA'], 5, 2) . "/" . substr($result['ARD_DUE_DA'], 0, 4),
            "DI_AMOUNT" => $result['DI_AMOUNT']);
    }

    echo json_encode($return_arr);

}


if ($_POST["action"] === 'UPDATE') {

/*
    $myfile = fopen("param_post_update_data.txt", "w") or die("Unable to open file!");
    fwrite($myfile, "Action = ". $_POST["action"] . " id = " . $_POST["id"] . " bill = " . $_POST["bill_note_date"]);
    fclose($myfile);
*/

    $id = $_POST["id"];
    $BILL_NOTE_DATE = $_POST["bill_note_date"];

    if ($BILL_NOTE_DATE != '') {

        $sql_update = "UPDATE ims_document_bill SET BILL_NOTE_DATE=:BILL_NOTE_DATE
            WHERE id = :id";
        $query = $conn->prepare($sql_update);
        $query->bindParam(':BILL_NOTE_DATE', $BILL_NOTE_DATE, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        //echo $save_success;
        echo 1;
    } else {
        //echo $empty;
        echo 4;
    }

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
    $searchByBillDoc = $_POST['searchByBillDoc'];
    $searchByName = $_POST['searchByName'];
    $searchBySale = $_POST['searchBySale'];
    $searchByDueDate = $_POST['searchByDueDate'] == '' ? "7" : $_POST['searchByDueDate'];
    $searchByBillNoteDate = $_POST['searchByBillNoteDate'];


    //$filename = "D:\\temp_app\\post_data_from_front.txt";
    //$filename = "post_data_from_front.txt";
    //$myfile = fopen($filename, "w") or die("Unable to open file!");
    //fwrite($myfile, $_POST["action"]);
    //fclose($myfile);



## Search
    $searchQuery = " ";


    if ($searchByBillDoc != '') {
        $searchQuery .= " and (ims_document_bill.DI_REF like '%" . $searchByBillDoc . "%' ) ";
    }

    if ($searchByName != '') {
        $searchQuery .= " and (ims_document_bill.AR_NAME like '%" . $searchByName . "%' ) ";
    }

    if ($searchBySale != '') {
        $searchQuery .= " and (ims_document_bill.SLMN_NAME like '%" . $searchBySale . "%' ) ";
    }

    // $searchQuery .= " and DATEDIFF(ims_document_bill.ARD_DUE_DA, CURDATE()) = " . $searchByDueDate;


    if($searchByDueDate != '-'){
        $searchQuery .= " and DATEDIFF(ims_document_bill.ARD_DUE_DA, CURDATE()) = " . $searchByDueDate;
    }

    if ($searchByBillNoteDate != '') {
        $searchQuery .= " and (ims_document_bill.BILL_NOTE_DATE like '%" . $searchByBillNoteDate . "%' ) ";
    }

/*
    $filename = "d:\\temp_app\\param_post_mssql_data.txt";
    $myfile = fopen($filename, "w") or die("Unable to open file!");
    fwrite($myfile, "searchByName | " . $searchByName . " | ". $searchBySale . " | searchByDueDate " . $searchByDueDate . " | searchQuery = " . $searchQuery);
    fclose($myfile);
*/





    /*


    if($searchValue != ''){
        $searchQuery .= " and (emp_name like '%".$searchValue."%' or
            email like '%".$searchValue."%' or
            city like'%".$searchValue."%' ) ";
    }

    */

## Total number of records without filtering
    $sel = mysqli_query($con, "select count(*) as allcount from ims_document_bill WHERE PAYMENT_STATUS = 'N' ");
    $records = mysqli_fetch_assoc($sel);
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $sel = mysqli_query($con, "select count(*) as allcount from ims_document_bill WHERE PAYMENT_STATUS = 'N'  " . $searchQuery);
    $records = mysqli_fetch_assoc($sel);
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $billQuery = "select ims_document_bill.* , b.DI_REF AS BILL_DI_REF , b.DI_DATE AS BILL_DI_DATE
, b.TPA_REFER_REF , b.TPA_REFER_DATE , b.ARD_BIL_DA  AS BILL_ARD_BIL_DA , b.ARD_DUE_DA AS BILL_ARD_DUE_DA
, b.ARD_A_SV , b.ARD_A_VAT  , b.ARD_A_AMT 
from ims_document_bill
left join ims_document_bill_load b on b.TPA_REFER_REF = ims_document_bill.DI_REF   
WHERE PAYMENT_STATUS = 'N' " . $searchQuery . " order by ims_document_bill.id DESC " . " limit " . $row . "," . $rowperpage;

/*
    $filename = "d:\\temp_app\\sel_data.txt";
    $myfile = fopen($filename, "w") or die("Unable to open file!");
    fwrite($myfile, $billQuery);
    fclose($myfile);
*/

    $billRecords = mysqli_query($con, $billQuery);
    $data = array();

    while ($row = mysqli_fetch_assoc($billRecords)) {

        $data[] = array(
            "id" => $row['id'],
            "DI_REF" => $row['DI_REF'],
            "DI_DATE" => $row['DI_DATE'] == "" ? "-" : substr($row['DI_DATE'], 8, 2) . "/" . substr($row['DI_DATE'], 5, 2) . "/" . substr($row['DI_DATE'], 0, 4),
            "AR_NAME" => $row['AR_NAME'],
            "DI_AMOUNT" => $row['DI_AMOUNT'],
            "AR_REMARK" => $row['AR_REMARK'],
            "AR_SLMNCODE" => $row['AR_SLMNCODE'],
            "SLMN_NAME" => $row['SLMN_NAME'],
            "BILL_NOTE_DATE" => $row['BILL_NOTE_DATE'],
            "ARD_DUE_DA" => $row['ARD_DUE_DA'] == "" ? "-" : substr($row['ARD_DUE_DA'], 8, 2) . "/" . substr($row['ARD_DUE_DA'], 5, 2) . "/" . substr($row['ARD_DUE_DA'], 0, 4),
            "detail" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>รายละเอียด</button>"
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
