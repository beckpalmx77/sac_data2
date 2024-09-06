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
    $doc_id = $_POST["doc_id"];
    $table_name = $_POST["table_name"];

    $return_arr = array();

    $sql_get = "SELECT * FROM " . $table_name . " WHERE id = " . $id;

    //$myfile = fopen("crm-get-date-param.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $doc_id  . " | " . " | " . $id . " | " . $table_name . " | " .$_POST["action"] . " | " . $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "faq_id" => $result['faq_id'],
            "faq_desc" => $result['faq_desc'],
            "faq_anwser" => $result['faq_anwser'],
            "doc_date" => $result['doc_date']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action_detail"] === 'ADD') {
    if ($_POST["doc_date"] !== '') {

        if ($_POST["KeyAddDetail"] !== '') {
            $doc_id = $_POST["KeyAddDetail"];
            $table_name = "ims_customer_crm_quest_detail_temp";
        } else {
            $doc_id = $_POST["doc_id_detail"];
            $table_name = "ims_customer_crm_quest_detail";
        }

        $doc_date = $_POST["doc_date_detail"];
        $faq_id = $_POST["faq_id"];
        $unit_id = $_POST["unit_id"];
        $quantity = $_POST["quantity"];
        $price = $_POST["price"];

        $sql_find = "SELECT count(*) as row FROM " . $table_name . " WHERE doc_id = '" . $doc_id . "'";
        $row = $conn->query($sql_find)->fetch();
        if (empty($row["0"])) {
            $line_no = 1;
        } else {
            $line_no = $row["0"] + 1;
        }
        $sql = "INSERT INTO " . $table_name . " (doc_id,doc_date,faq_id,unit_id,quantity,price,line_no) 
            VALUES (:doc_id,:doc_date,:faq_id,:unit_id,:quantity,:price,:line_no)";
        $query = $conn->prepare($sql);
        $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query->bindParam(':faq_id', $faq_id, PDO::PARAM_STR);
        $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
        $query->bindParam(':quantity', $quantity, PDO::PARAM_STR);
        $query->bindParam(':price', $price, PDO::PARAM_STR);
        $query->bindParam(':line_no', $line_no, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo $save_success;
        } else {
            echo $error . " | " . $doc_id . " | " . $line_no . " | " . $faq_id . " | " . $quantity . " | " . $unit_id;
        }

    }
}


if ($_POST["action_detail"] === 'UPDATE') {

    if ($_POST["$faq_id"] !== '') {

        if ($_POST["KeyAddDetail"] !== '') {
            $doc_id = $_POST["KeyAddDetail"];
            $table_name = "ims_customer_crm_quest_detail_temp";
        } else {
            $doc_id = $_POST["doc_id_detail"];
            $table_name = "ims_customer_crm_quest_detail";
        }

        $id = $_POST["detail_id"];
        $faq_id = $_POST["faq_id"];
        $faq_anwser = $_POST["faq_anwser"];
/*
        $myfile = fopen("crm-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $_POST["$faq_id"]  . " | " . $_POST["action_detail"] . " | " . " | " . $id . " | " . $faq_anwser );
        fclose($myfile);
*/
        $sql_update = "UPDATE " . $table_name
            . " SET faq_anwser=:faq_anwser "
            . " WHERE id = :id ";
        $query = $conn->prepare($sql_update);
        $query->bindParam(':faq_anwser', $faq_anwser, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        if ($query->execute()) {
            echo $save_success;
        } else {
            echo $error;
        }


    }
}

if ($_POST["action"] === 'SAVE_DETAIL') {

    if ($_POST["KeyAddData"] != '') {

        $KeyAddData = $_POST["KeyAddData"];

        $sql_find = "SELECT * FROM ims_customer_crm_quest_header WHERE KeyAddData = '" . $KeyAddData . "'";
        $statement = $conn->query($sql_find);
        $results_head = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results_head as $result_head) {
            $doc_id = $result_head['doc_id'];
            $doc_date = $result_head['doc_date'];
            $customer_id = $result_head['customer_id'];
        }

        /*
                $txt .= $sql . " | " . $customer_id . " | " . $line_no . "\n\r";
                $myfile = fopen("imq_detail-param.txt", "w") or die("Unable to open file!");
                fwrite($myfile, $txt);
                fclose($myfile);
        */

        $line_no = 0;
        $sql_find_faq = "SELECT * FROM ims_faq_master WHERE 1=1 ";

        $statement = $conn->query($sql_find_faq);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result) {
            $line_no++;
            $sql = "INSERT INTO ims_customer_crm_quest_detail (doc_id,customer_id,faq_id,line_no) 
            VALUES (:doc_id,:customer_id,:faq_id,:line_no)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query->bindParam(':faq_id', $result['faq_id'], PDO::PARAM_STR);
            $query->bindParam(':line_no', $line_no, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();
        }

        if ($lastInsertId) {
            echo $save_success;
        } else {
            echo $error;
        }

    }

}


if ($_POST["action"] === 'GET_CRM_DETAIL') {

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
    $line_no = 0;
    foreach ($empRecords as $row) {

        $line_no++;

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "line_no" => $line_no,
                "doc_id" => $row['doc_id'],
                "doc_date" => $row['doc_date'],
                "faq_desc" => $row['faq_desc'],
                "faq_anwser" => $row['faq_anwser'],
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

