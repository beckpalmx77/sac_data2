<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();
    $sql_get = "SELECT * FROM v_ims_price_approve_header WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "customer_name" => $result['customer_name'],
            "remark" => $result['remark'],
            "request_status" => $result['request_status'],
            "approve_status" => $result['approve_status'],
            "edit_price_status" => $result['edit_price_status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_no"] !== '') {

        $doc_no = $_POST["doc_no"];
        $sql_find = "SELECT * FROM ims_price_approve_header WHERE doc_no = '" . $doc_no . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}


if ($_POST["action"] === 'UPDATE') {

    if ($_POST["doc_no_detail"] != '') {

        $doc_no = $_POST["doc_no_detail"];
        $request_status = $_POST["request_status"];
        $approve_status = $_POST["approve_status"];
        $edit_price_status = $_POST["edit_price_status"];
        $update_date = date('Y-m-d H:i:s');
        $sql_find = "SELECT * FROM ims_price_approve_header WHERE doc_no = '" . $doc_no . "'";

        $qry = $doc_no . "|" . $request_status . "|" . $approve_status . "|" . $edit_price_status . "|" . $update_date . " | " . $_SESSION['permission_price'];

        //$myfile = fopen("qry_file.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $qry);
        //fclose($myfile);

        $query_str = "SELECT count(*) as record_counts FROM ims_price_approve_detail WHERE doc_no = '" . $doc_no . "' and request_edit_price_status = 'Y'";

        //$myfile = fopen("sql_update_data2.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $query_str);
        //fclose($myfile);

        $statement = $conn->query($query_str);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        $record = 0;

        foreach ($results as $result) {
            $record = $result['record_counts'];
        }

        if ($record > 0) {
            $request_status = "Y ขออนุมัติราคาขาย";
        } else {
            $request_status = "N ขายราคาปกติ";
        }

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_price_approve_header SET request_status=:request_status,approve_status=:approve_status            
            ,edit_price_status=:edit_price_status WHERE doc_no = :doc_no";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':request_status', $request_status, PDO::PARAM_STR);
            $query->bindParam(':approve_status', $approve_status, PDO::PARAM_STR);
            $query->bindParam(':edit_price_status', $edit_price_status, PDO::PARAM_STR);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            if ($query->execute()) {
                echo $save_success;
            } else {
                echo $error;
            }
        }

    }
}

if ($_POST["action"] === 'GET_PRICE') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    if ($columnName === 'doc_no') {
        $columnSortOrder = "desc";
    }

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_no LIKE :doc_no or
        customer_name LIKE :customer_name ) ";
        $searchArray = array(
            'doc_no' => "%$searchValue%",
            'customer_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_price_approve_header ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_price_approve_header WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $query_str = "SELECT * FROM v_ims_price_approve_header WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $columnName = " id " ;
    $columnSortOrder = " desc " ;

    $stmt = $conn->prepare("SELECT * FROM v_ims_price_approve_header WHERE 1 " . $searchQuery
        //. " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

    //$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $query_str);
    //fclose($myfile);

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
                "doc_no" => $row['doc_no'],
                "customer_name" => $row['customer_name'],
                "doc_date" => $row['doc_date'],
                "request_status" => strpos($row['request_status'], 'Y') !== false ? "<div class='text-danger'>" . $row['request_status'] . "</div>" : "<div class='text-muted'> " . $row['request_status'] . "</div>",
                "approve_status" => strpos($row['approve_status'], 'Y') !== false ? "<div class='text-success'>" . $row['approve_status'] . "</div>" : "<div class='text-muted'> " . $row['approve_status'] . "</div>",
                "edit_price_status" => strpos($row['edit_price_status'], 'Y') !== false ? "<div class='text-success'>" . $row['edit_price_status'] . "</div>" : "<div class='text-muted'> " . $row['edit_price_status'] . "</div>",
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "supplier_id" => $row['supplier_id'],
                "select" => "<button type='button' name='select' id='" . $row['doc_no'] . "@" . $row['supplier_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
