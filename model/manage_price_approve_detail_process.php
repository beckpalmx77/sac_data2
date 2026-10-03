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
    $doc_no = $_POST["doc_no"];
    $table_name = $_POST["table_name"];

    $return_arr = array();

    $sql_get = "SELECT * FROM " . $table_name . " WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "line_no" => $result['line_no'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "product_id" => $result['product_id'],
            "product_name" => $result['product_name'],
            "price_normal" => $result['price_normal'],
            "price_special" => $result['price_special'],
            "remark" => $result['remark'],
            "picture" => $result['picture'],
            "request_edit_price_status" => $result['request_edit_price_status']);
    }

    echo json_encode($return_arr);

}


if ($_POST["action_detail"] === 'UPDATE') {

    if ($_POST["product_name"] !== '') {

        $table_name = "ims_price_approve_detail";
        $doc_date = $_POST["doc_date_detail"];
        $id = $_POST["detail_id"];
        $doc_no = $_POST["doc_no_detail_line"];
        $product_name = $_POST["product_name"];
        $price_normal = $_POST["price_normal"];
        $price_special = $_POST["price_special"];
        $request_edit_price_status = $_POST["request_edit_price_status"];

        $price_diff = $price_normal - $price_special ;

        if ($price_diff<>0) {
            $request_edit_price_status = "Y";
        } else {
            $request_edit_price_status = "N";
        }

        //$qry = $id . " | " .  " | " . $price_special . " | " . $product_name . " | " . $doc_no . " | " . $doc_date;
        $qry = $id . " | " . $price_normal . " | " . $price_special . " | " . $price_diff . " | " . $product_name . " | " . $doc_no . " | " . $doc_date;

        //$myfile = fopen("qry_file_update.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $qry);
        //fclose($myfile);

        $sql_find = "SELECT count(*) as row FROM " . $table_name . " WHERE id = '" . $id . "'";

        $row = $conn->query($sql_find)->fetch();
        if (empty($row["0"])) {
            echo $error;
        } else {
            $sql_update = "UPDATE " . $table_name
                . " SET price_special=:price_special , request_edit_price_status=:request_edit_price_status"
                . " WHERE id = :id ";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':price_special', $price_special, PDO::PARAM_STR);
            $query->bindParam(':request_edit_price_status', $request_edit_price_status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            if ($query->execute()) {
                save_approve_head($doc_no, $conn);
                echo $save_success;
            } else {
                echo $error;
            }
        }

    }
}

if ($_POST["action"] === 'GET_PRICE_DETAIL') {

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
        $searchQuery = " AND (doc_no LIKE :doc_no or
        doc_date LIKE :doc_date ) ";
        $searchArray = array(
            'doc_no' => "%$searchValue%",
            'doc_date' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM " . $table_name . " WHERE DOC_NO = '" . $_POST["doc_no"] . "'");

    $qry = "SELECT COUNT(*) AS allcount FROM " . $table_name . " WHERE DOC_NO = '" . $_POST["doc_no"] . "'";

    //$myfile = fopen("cnt_price_file.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $qry);
    //fclose($myfile);

    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM " . $table_name . " WHERE DOC_NO = '" . $_POST["doc_no"] . "'");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];


    $query_str = "SELECT * FROM " . $table_name . " WHERE doc_no = '" . $_POST["doc_no"] . "'"
        . " ORDER BY line_no ";

    $stmt = $conn->prepare($query_str);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "line_no" => $row['line_no'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "price_normal" => $row['request_edit_price_status'] === 'Y' ? "<div class='text-danger'>" . number_format($row['price_normal'], 2) . "</div>" : number_format($row['price_normal'], 2),
                "price_special" => $row['request_edit_price_status'] === 'Y' ? "<div class='text-danger'>" . number_format($row['price_special'], 2) . "</div>" : number_format($row['price_special'], 2),
                //"price_normal" => number_format($row['price_normal'],2),
                //"price_special" => number_format($row['price_special'],2),
                "request_edit_price_status" => $row['request_edit_price_status'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "select" => "<button type='button' name='select' id='" . $row['doc_no'] . "@" . $row['doc_date'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

function save_approve_head($doc_no, $conn)
{

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

    $sql_update = "UPDATE ims_price_approve_header SET request_status=:request_status WHERE doc_no = :doc_no";

    //$myfile = fopen("sql_update_data3.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_update . " | " . $request_status . " | " . $doc_no . " | " . $request_status);
    //fclose($myfile);

    $query = $conn->prepare($sql_update);
    $query->bindParam(':request_status', $request_status, PDO::PARAM_STR);
    $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
    if ($query->execute()) {
        echo "Success";
    } else {
        echo "Error";
    }


}

