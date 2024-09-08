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
    if ($_POST["product_id"] !== '') {
        $create_by = $_SESSION['username'];
        $doc_date = $_POST["doc_date"];
        $doc_id = "WH-" . $doc_date . "-" . sprintf('%06s', LAST_ID($conn, "wh_stock_movement", 'id'));
        $product_id = $_POST["product_id"];
        $qty = $_POST["qty"];
        $wh_org = $_POST["wh_org"];
        $location_org = $_POST["location_org"];
        $location_to = $_POST["location_to"];
        $sql_find = "SELECT * FROM wh_stock_movement WHERE doc_id = '" . $doc_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO wh_stock_movement(doc_id,doc_date,product_id,qty,wh_org,wh_to,location_org,location_to,create_by) 
            VALUES (:doc_id,:doc_date,:product_id,:qty,:wh_org,:wh_to,:location_org,:location_to,:create_by)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':wh_org', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':wh_to', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':location_org', $location_org, PDO::PARAM_STR);
            $query->bindParam(':location_to', $location_to, PDO::PARAM_STR);
            $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
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

    if ($_POST["product_id"] != '') {
        $update_by = $_SESSION['username'];
        $id = $_POST["id"];
        $doc_date = $_POST["doc_date"];
        $product_id = $_POST["product_id"];
        $qty = $_POST["qty"];
        $wh_org = $_POST["wh_org"];
        $wh_to = $_POST["wh_org"];
        $location_org = $_POST["location_org"];
        $location_to = $_POST["location_to"];

        $sql_find = "SELECT * FROM wh_stock_movement WHERE id = " . $id;
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE wh_stock_movement SET product_id=:product_id,qty=:qty            
            ,wh_org=:wh_org,wh_to=:wh_to,location_org=:location_org,location_to=:location_to,update_by=:update_by
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':wh_org', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':wh_to', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':location_org', $location_org, PDO::PARAM_STR);
            $query->bindParam(':location_to', $location_to, PDO::PARAM_STR);
            $query->bindParam(':update_by', $update_by, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
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

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_date LIKE :doc_date or
        product_id LIKE :product_id ) ";
        $searchArray = array(
            'doc_date' => "%$searchValue%",
            'product_id' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_movement ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_movement WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_wh_stock_movement WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

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
                "location_to" => $row['location_to'],
                "create_by" => $row['create_by'],
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
