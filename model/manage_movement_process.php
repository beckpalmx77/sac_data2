<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_wh_stock_movement WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "product_id" => $result['product_id'],
            "product_name" => $result['product_name'],
            "qty" => $result['qty'],
            "wh_org" => $result['wh_org'],
            "wh_week_id" => $result['wh_week_id'],
            "location_org" => $result['location_org'],
            "location_to" => $result['location_to'],
            "create_by" => $result['create_by']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["product_id"] !== '') {

        $product_id = $_POST["product_id"];
        $sql_find = "SELECT * FROM v_wh_stock_movement WHERE product_id = '" . $product_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["product_id"] !== '' && $_SESSION['username'] !== '' && $_SESSION['doc_user_id'] !== '') {
        $create_by = $_SESSION['username'];
        $doc_user_id = $_SESSION['doc_user_id'];
        $doc_date = $_POST["doc_date"];

        $cond = "WHERE doc_date = '" . $doc_date . "' AND doc_user_id = '" . $doc_user_id . "'";

        //$doc_id = "MV-" . $create_by . "-" . $doc_date . "-" . sprintf('%06s', LAST_ID($conn, "wh_stock_movement", 'id'));

        $run_no = LAST_DOCUMENT_NUMBER($conn, "doc_id", "wh_stock_movement", $cond);
        $doc_id = "MV-" . $doc_user_id . "-" . $doc_date . "-" . sprintf('%06s', $run_no);

        $str = rand();
        $seq_record = md5($str);

        $product_id = $_POST["product_id"];
        $qty = $_POST["qty"];
        $wh_org = $_POST["wh_org"];
        $wh_week_id = $_POST["wh_week_id"];
        $location_org = $_POST["location_org"];
        $location_to = $_POST["location_to"];
        $sql_find = "SELECT * FROM wh_stock_movement WHERE doc_id = '" . $doc_id . "'";

        /*
                $txt = $sql_find . " | " . $run_no . " | " . $cond;
                $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
                fwrite($my_file, $txt);
                fclose($my_file);
        */

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO wh_stock_movement(doc_id,doc_date,product_id,qty,wh_org,wh_week_id,wh_to,location_org,location_to,create_by,doc_user_id,seq_record) 
            VALUES (:doc_id,:doc_date,:product_id,:qty,:wh_org,:wh_week_id,:wh_to,:location_org,:location_to,:create_by,:doc_user_id,:seq_record)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':wh_org', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
            $query->bindParam(':wh_to', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':location_org', $location_org, PDO::PARAM_STR);
            $query->bindParam(':location_to', $location_to, PDO::PARAM_STR);
            $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
            $query->bindParam(':doc_user_id', $doc_user_id, PDO::PARAM_STR);
            $query->bindParam(':seq_record', $seq_record, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                for ($line_no = 1; $line_no <= 2; $line_no++) {
                    $sql_find_trans = "SELECT * FROM wh_stock_transaction WHERE doc_id = '" . $doc_id . "' AND line_no = " . $line_no;
                    $nRows = $conn->query($sql_find_trans)->fetchColumn();
                    if ($nRows <= 0) {
                        if ($line_no === 1) {
                            $record_type = "+";
                            $location = $location_to;
                        } else {
                            $record_type = "-";
                            $location = $location_org;
                        }
                        $sql_ins = "INSERT INTO wh_stock_transaction(doc_id,record_type,line_no,doc_date,product_id,qty,wh,wh_week_id,location,create_by,doc_user_id,seq_record) 
                                    VALUES (:doc_id,:record_type,:line_no,:doc_date,:product_id,:qty,:wh,:wh_week_id,:location,:create_by,:doc_user_id,:seq_record)";
                        $query_trans = $conn->prepare($sql_ins);
                        $query_trans->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':record_type', $record_type, PDO::PARAM_STR);
                        $query_trans->bindParam(':line_no', $line_no, PDO::PARAM_STR);
                        $query_trans->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
                        $query_trans->bindParam(':product_id', $product_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':qty', $qty, PDO::PARAM_STR);
                        $query_trans->bindParam(':wh', $wh_org, PDO::PARAM_STR);
                        $query_trans->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':location', $location, PDO::PARAM_STR);
                        $query_trans->bindParam(':create_by', $create_by, PDO::PARAM_STR);
                        $query_trans->bindParam(':doc_user_id', $doc_user_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':seq_record', $seq_record, PDO::PARAM_STR);
                        $query_trans->execute();
                    }
                }

                echo $save_success;
            } else {
                echo $error;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE') {

    if ($_POST["product_id"] !== '' && $_SESSION['username'] !== '' && $_SESSION['doc_user_id'] !== '') {
        $update_by = $_SESSION['username'];
        $id = $_POST["id"];
        $doc_id = $_POST["doc_id"];
        $doc_date = $_POST["doc_date"];
        $product_id = $_POST["product_id"];
        $qty = $_POST["qty"];
        $wh_org = $_POST["wh_org"];
        $wh_week_id = $_POST["wh_week_id"];
        $wh_to = $_POST["wh_org"];
        $location_org = $_POST["location_org"];
        $location_to = $_POST["location_to"];
        $sql_find = "SELECT * FROM wh_stock_movement WHERE id = " . $id;
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE wh_stock_movement SET product_id=:product_id,qty=:qty            
            ,wh_org=:wh_org,wh_week_id=:wh_week_id,wh_to=:wh_to,location_org=:location_org,location_to=:location_to,update_by=:update_by
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':wh_org', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
            $query->bindParam(':wh_to', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':location_org', $location_org, PDO::PARAM_STR);
            $query->bindParam(':location_to', $location_to, PDO::PARAM_STR);
            $query->bindParam(':update_by', $update_by, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
/*
            $txt = "week = " . $wh_week_id . " | " . $qty . " | " . $location_org . " | " . $location_to . " | " . $wh_org . " | " . $product_id . " | " . $id . " | " . $doc_date;
            $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
            fwrite($my_file, $txt);
            fclose($my_file);
*/
            for ($line_no = 1; $line_no <= 2; $line_no++) {
                $sql_find_trans = "SELECT * FROM wh_stock_transaction WHERE doc_id = '" . $doc_id . "' AND line_no = " . $line_no;
                $nRows = $conn->query($sql_find_trans)->fetchColumn();
                if ($nRows > 0) {
                    if ($line_no === 1) {
                        $record_type = "+";
                        $location = $location_to;
                    } else {
                        $record_type = "-";
                        $location = $location_org;
                    }
                    $sql_updates = "UPDATE wh_stock_transaction SET record_type=:record_type,product_id=:product_id,qty=:qty,wh=:wh,wh_week_id=:wh_week_id,location=:location "
                        . " WHERE doc_id = '" . $doc_id . "' AND line_no = " . $line_no;
                    $query_trans = $conn->prepare($sql_updates);
                    $query_trans->bindParam(':record_type', $record_type, PDO::PARAM_STR);
                    $query_trans->bindParam(':product_id', $product_id, PDO::PARAM_STR);
                    $query_trans->bindParam(':qty', $qty, PDO::PARAM_STR);
                    $query_trans->bindParam(':wh', $wh_org, PDO::PARAM_STR);
                    $query_trans->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
                    $query_trans->bindParam(':location', $location, PDO::PARAM_STR);
                    $query_trans->execute();
                }
            }

            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM wh_stock_movement WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM wh_stock_movement WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_MOVEMENT') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    if ($_SESSION['account_type'] === 'stock') {
        $where_doc_user_id = " AND doc_user_id = '" . $_SESSION['doc_user_id'] . "' ";
    }

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_date LIKE :doc_date or wh_org LIKE :wh_org
        or product_id LIKE :product_id or product_name LIKE :product_name or create_by LIKE :create_by) ";
        $searchArray = array(
            'doc_date' => "%$searchValue%",
            'wh_org' => "%$searchValue%",
            'product_id' => "%$searchValue%",
            'product_name' => "%$searchValue%",
            'create_by' => "%$searchValue%",
        );
    }

/*
    $txt = "sql = " . $searchValue . " | " . $searchQuery ;
    $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_movement WHERE 1 " . $where_doc_user_id);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_movement WHERE 1 " . $where_doc_user_id . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_wh_stock_movement 
        WHERE 1 " . $where_doc_user_id . $searchQuery
        . " ORDER BY create_date DESC,doc_id DESC " . " LIMIT :limit,:offset");

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
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "doc_date" => $row['doc_date'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "qty" => $row['qty'],
                "wh_org" => $row['wh_org'],
                "location_org" => $row['location_org'],
                "wh_to" => $row['wh_to'],
                "wh_week_id" => $row['wh_week_id'],
                "location_to" => $row['location_to'],
                "create_by" => $row['create_by'],
                "user_name" => $row['user_name'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_date" => $row['doc_date'],
                "product_id" => $row['product_id'],
                "select" => "<button type='button' name='select' id='" . $row['doc_date'] . "@" . $row['product_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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


