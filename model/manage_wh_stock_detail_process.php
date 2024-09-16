<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_wh_stock_transaction WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "line_no" => $result['line_no'],
            "record_type" => $result['record_type'],
            "product_id" => $result['product_id'],
            "product_name" => $result['product_name'],
            "qty" => $result['qty'],
            "wh" => $result['wh'],
            "wh_week_id" => $result['wh_week_id'],
            "location" => $result['location'],
            "create_by" => $result['create_by']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action_detail"] === 'ADD') {
    if ($_POST["doc_date_detail"] !== '' && $_POST["doc_id_detail"]!=='') {
        $table_name = "wh_stock_transaction";
        $doc_id = $_POST["doc_id_detail"];
        $doc_date = $_POST["doc_date_detail"];
        $product_id = $_POST["product_id_detail"];
        $wh = $_POST["wh_to_detail"];
        $wh_week_id = $_POST["wh_week_id_detail"];
        $location = $_POST["location_detail"];
        $qty = $_POST["qty_detail"];
        $record_type = "+";
        $seq_record = $_POST["seq_record_detail"];
        $doc_user_id = $_POST["doc_user_id_detail"];
        $create_by = $_POST["create_by_detail"];
        $line_no = LAST_DOCUMENT_COND($conn,$table_name," WHERE doc_id = '" . $doc_id . "'");

        $txt = $doc_id . " | " . $doc_date . " | " . $product_id . " | " . $wh_week_id . " | "
                       . $wh . " | " . $location . " | " . $qty . " | " . $doc_user_id ;
/*
        $myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $txt . " > " . $line_no );
        fclose($myfile);
*/
        $sql = "INSERT INTO " . $table_name . " (doc_id,doc_date,product_id,record_type,qty,wh,wh_week_id,location,line_no,doc_user_id,seq_record,create_by) 
            VALUES (:doc_id,:doc_date,:product_id,:record_type,:qty,:wh,:wh_week_id,:location,:line_no,:doc_user_id,:seq_record,:create_by)";
        $query = $conn->prepare($sql);
        $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
        $query->bindParam(':record_type', $record_type, PDO::PARAM_STR);
        $query->bindParam(':qty', $qty, PDO::PARAM_STR);
        $query->bindParam(':wh', $wh, PDO::PARAM_STR);
        $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
        $query->bindParam(':location', $location, PDO::PARAM_STR);
        $query->bindParam(':line_no', $line_no, PDO::PARAM_STR);
        $query->bindParam(':doc_user_id', $doc_user_id, PDO::PARAM_STR);
        $query->bindParam(':seq_record', $seq_record, PDO::PARAM_STR);
        $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            $status = "Y";
            $sql_update_header = "UPDATE wh_stock_record SET status=:status WHERE doc_id = :doc_id";
            $query_header = $conn->prepare($sql_update_header);
            $query_header->bindParam(':status', $status, PDO::PARAM_STR);
            $query_header->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query_header->execute();
            echo $save_success;
        } else {
            echo $error . " | " . $doc_id . " | " . $line_no . " | " . $product_id . " | " . $location . " | " . $wh_week_id;
        }

    }
}

if ($_POST["action_detail"] === 'UPDATE') {
    if ($_POST["doc_date_detail"] !== '' && $_POST["doc_id_detail"] !== '') {
        $table_name = "wh_stock_transaction";
        $detail_id = $_POST["detail_id"];
        $doc_id = $_POST["doc_id_detail"];
        $doc_date = $_POST["doc_date_detail"];
        $product_id = $_POST["product_id_detail"];
        $wh = $_POST["wh_to_detail"];
        $wh_week_id = $_POST["wh_week_id_detail"];
        $location = $_POST["location_detail"];
        $qty = $_POST["qty_detail"];
        $record_type = "+";
        $seq_record = $_POST["seq_record_detail"];
        $doc_user_id = $_POST["doc_user_id_detail"];
        $create_by = $_POST["create_by_detail"];

        $sql_find = "SELECT * FROM wh_stock_transaction WHERE id = " . $detail_id;
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE wh_stock_transaction SET qty=:qty,wh_week_id=:wh_week_id,location=:location
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
            $query->bindParam(':location', $location, PDO::PARAM_STR);
            $query->bindParam(':id', $detail_id, PDO::PARAM_STR);
            $query->execute();

            $status = "Y";
            $sql_update_header = "UPDATE wh_stock_record SET status=:status WHERE doc_id = :doc_id";
            $query_header = $conn->prepare($sql_update_header);
            $query_header->bindParam(':status', $status, PDO::PARAM_STR);
            $query_header->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query_header->execute();

            echo $save_success;
        }
    }
}


if ($_POST["action_detail"] === 'DELETE') {
    $id = $_POST["detail_id"];
    $sql_find = "SELECT * FROM wh_stock_transaction WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM wh_stock_transaction WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'CAL_SUM_DETAIL') {

    $doc_id = $_POST['doc_id'];
    // Query เพื่อรวมค่าของ qty_detail ใน table Detail ตาม doc_id
    $stmt = $conn->prepare("SELECT SUM(qty) as total_qty FROM v_wh_stock_transaction WHERE doc_id = :doc_id");
    $stmt->bindParam(':doc_id', $doc_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // ส่งค่าผลรวมกลับไปให้ JavaScript
    echo $result['total_qty'];

}

if ($_POST["action"] === 'GET_STOCK_DETAIL') {

    ## Read value
    $table_name = $_POST['table_name'];
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
        $searchQuery = " AND (doc_id LIKE :doc_id or
        doc_date LIKE :doc_date ) ";
        $searchArray = array(
            'doc_id' => "%$searchValue%",
            'doc_date' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM " . $table_name . " WHERE doc_id = '" . $_POST["doc_id"] . "'");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM " . $table_name . " WHERE doc_id = '" . $_POST["doc_id"] . "'");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];


    $query_str = "SELECT * FROM " . $table_name . " WHERE doc_id = '" . $_POST["doc_id"] . "'"
        . " ORDER BY line_no ";

    $stmt = $conn->prepare($query_str);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "doc_date" => $row['doc_date'],
                "record_type" => $row['record_type'],
                "line_no" => $row['line_no'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "qty" => $row['qty'],
                "wh" => $row['wh'],
                "wh_week_id" => $row['wh_week_id'],
                "location" => $row['location'],
                "create_by" => $row['create_by'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "doc_date" => $row['doc_date'],
                "select" => "<button type='button' name='select' id='" . $row['doc_id'] . "@" . $row['doc_date'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

