<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/getdata_field.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_customer_crm_header_quest WHERE id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'GET_DATA_KEY') {

    $KeyAddData = $_POST["KeyAddData"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_customer_crm_header_quest WHERE KeyAddData = '" . $KeyAddData . "'";
    /*
        $myfile = fopen("crm-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile,  $KeyAddData . " | " . $sql_get);
        fclose($myfile);
    */
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_id"] !== '') {

        $doc_id = $_POST["doc_id"];
        $sql_find = "SELECT * FROM ims_customer_crm_quest_header WHERE doc_id = '" . $doc_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["customer_id"] !== '') {
        $table = "ims_customer_crm_quest_header";
        $KeyAddData = $_POST["KeyAddData"];
        $doc_year = substr($_POST["doc_date"], 0, 4);
        $customer_id = $_POST["customer_id"];
        $cond = "WHERE customer_id LIKE '%" . $customer_id . "%'";
        $doc_runno = LAST_DOCUMENT_COND($conn, $table, $cond);
        $doc_id = "Q-" . $customer_id . "-" . sprintf('%04s', $doc_runno);
        $customer_id = $_POST["customer_id"];
        $doc_date = $_POST["doc_date"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM " . $table . " WHERE doc_id = '" . $doc_id . "'";
        $stmt = $conn->query($sql_find);
        $nRows = $stmt->rowCount();

        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO " . $table . " (doc_id,customer_id,doc_date,KeyAddData)
                    VALUES (:doc_id,:customer_id,:doc_date,:KeyAddData)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':KeyAddData', $KeyAddData, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();
            if ($lastInsertId) {
                echo $save_success;
            } else {
                echo $error;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE') {

    if ($_POST["doc_id"] != '') {

        $id = $_POST["id"];
        $doc_id = $_POST["doc_id"];
        $customer_id = $_POST["customer_id"];
        $status = $_POST["status"];
        $update_date = date('Y-m-d H:i:s');
        $sql_find = "SELECT * FROM ims_customer_crm_quest_header WHERE doc_id = '" . $doc_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_customer_crm_quest_header SET customer_id=:customer_id,status=:status            
            ,update_date=:update_date WHERE doc_id = :doc_id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':update_date', $update_date, PDO::PARAM_STR);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            if ($query->execute()) {
                echo $save_success;
            } else {
                echo $error;
            }
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_get_doc_id = "SELECT ims_customer_crm_quest_header.doc_id AS doc_id FROM ims_customer_crm_quest_header WHERE id = " . $id;

    $doc_id = GetDataValue($conn, $sql_get_doc_id);

    $sql_find = "SELECT * FROM ims_customer_crm_quest_header WHERE id = " . $id;

    /*
        $myfile = fopen("crm-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile,  $doc_id . " | " . $sql_get_doc_id);
        fclose($myfile);
    */
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {

            $sql_header = "DELETE FROM ims_customer_crm_quest_header WHERE id = " . $id;
            $query_header = $conn->prepare($sql_header);
            $query_header->execute();

            $sql_detail = "DELETE FROM ims_customer_crm_quest_detail WHERE doc_id = '" . $doc_id . "'";
            $query_detail = $conn->prepare($sql_detail);
            $query_detail->execute();

            echo json_encode(['status' => 'success']);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete data.']);
        }
    }
}

if ($_POST["action"] === 'GET_WH_STOCK') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    if ($columnName === 'doc_id') {
        $columnSortOrder = "desc";
    }

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_id LIKE :doc_id or
        doc_date LIKE :doc_date ) ";
        $searchArray = array(
            'doc_id' => "%$searchValue%",
            'doc_date' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_document_wh_stock_record ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_document_wh_stock_record WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $query_str = "SELECT * FROM v_document_wh_stock_record WHERE 1 " . $searchQuery
        . " ORDER BY v_document_wh_stock_record.doc_id DESC , v_document_wh_stock_record.create_date DESC , v_document_wh_stock_record.line_no " . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($query_str);

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
                "doc_id" => $row['doc_id'],
                "doc_date" => $row['doc_date'],
                "line_no" => $row['line_no'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "wh_org" => $row['wh_org'],
                "wh_to" => $row['wh_to'],
                "qty" => $row['qty'],
                "create_by" => $row['create_by'],
                "create_date" => $row['create_date'],
                "remark" => $row['remark'],
                "status" => $row['status'],
                "seq_record" => $row['seq_record'],
                "update" => "<button type='button' name='update' id='" . $row['seq_record'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['seq_record'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "customer_id" => $row['customer_id'],
                "select" => "<button type='button' name='select' id='" . $row['doc_id'] . "@" . $row['customer_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

