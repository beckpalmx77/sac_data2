<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM wh_week WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "wh_week_id" => $result['wh_week_id'],
            "wh_desc" => $result['wh_desc'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["wh_desc"] !== '') {

        $wh_desc = $_POST["wh_desc"];
        $sql_find = "SELECT * FROM wh_week WHERE wh_desc = '" . $wh_desc . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["wh_desc"] !== '') {
        $wh_week_id = $_POST["wh_week_id"];
        $wh_desc = $_POST["wh_desc"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM wh_week WHERE wh_desc = '" . $wh_desc . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO wh_week(wh_week_id,wh_desc,status) VALUES (:wh_week_id,:wh_desc,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
            $query->bindParam(':wh_desc', $wh_desc, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
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

    if ($_POST["wh_desc"] != '') {

        $id = $_POST["id"];
        $wh_week_id = $_POST["wh_week_id"];
        $wh_desc = $_POST["wh_desc"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM wh_week WHERE wh_week_id = '" . $wh_week_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE wh_week SET wh_week_id=:wh_week_id,wh_desc=:wh_desc,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
            $query->bindParam(':wh_desc', $wh_desc, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM wh_week WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM wh_week WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_WEEK_ID') {

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
        $searchQuery = " AND (wh_week_id LIKE :wh_week_id or
        wh_desc LIKE :wh_desc ) ";
        $searchArray = array(
            'wh_week_id' => "%$searchValue%",
            'wh_desc' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM wh_week ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM wh_week WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM wh_week WHERE 1 " . $searchQuery
        . " ORDER BY id LIMIT :limit,:offset");

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
                "wh_week_id" => $row['wh_week_id'],
                "wh_desc" => $row['wh_desc'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "wh_week_id" => $row['wh_week_id'],
                "wh_desc" => $row['wh_desc'],
                "select" => "<button type='button' name='select' id='" . $row['wh_week_id'] . "@" . $row['wh_desc'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
