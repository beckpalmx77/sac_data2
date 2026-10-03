<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM wh_warehouse WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "warehouse_id" => $result['warehouse_id'],
            "warehouse_year" => $result['warehouse_year'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["warehouse_id"] !== '') {

        $warehouse_id = $_POST["warehouse_year"];
        $sql_find = "SELECT * FROM wh_warehouse WHERE warehouse_id = '" . $warehouse_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["warehouse_year"] !== '') {
        $warehouse_id = $_POST["warehouse_id"];
        $warehouse_year = $_POST["warehouse_year"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM wh_warehouse WHERE warehouse_id = '" . $warehouse_id . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {

            $sql = "INSERT INTO wh_warehouse(warehouse_id,warehouse_year,status) VALUES (:warehouse_id,:warehouse_year,:status)";
            /*
                        $myfile = fopen("wh1_param.txt", "w") or die("Unable to open file!");
                        fwrite($myfile, $sql);
                        fclose($myfile);
            */
            $query = $conn->prepare($sql);
            $query->bindParam(':warehouse_id', $warehouse_id, PDO::PARAM_STR);
            $query->bindParam(':warehouse_year', $warehouse_year, PDO::PARAM_STR);
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

    if ($_POST["warehouse_year"] != '') {

        $id = $_POST["id"];
        $warehouse_id = $_POST["warehouse_id"];
        $warehouse_year = $_POST["warehouse_year"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM wh_warehouse WHERE id = " . $id;
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE wh_warehouse SET warehouse_id=:warehouse_id,warehouse_year=:warehouse_year,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':warehouse_id', $warehouse_id, PDO::PARAM_STR);
            $query->bindParam(':warehouse_year', $warehouse_year, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM wh_warehouse WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM wh_warehouse WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_WAREHOUSE') {

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
        $searchQuery = " AND (warehouse_id LIKE :warehouse_id or
        warehouse_year LIKE :warehouse_year ) ";
        $searchArray = array(
            'warehouse_id' => "%$searchValue%",
            'warehouse_year' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM wh_warehouse ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM wh_warehouse WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM wh_warehouse WHERE 1 " . $searchQuery
        . " ORDER BY warehouse_id DESC LIMIT :limit,:offset");

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
                "warehouse_id" => $row['warehouse_id'],
                "warehouse_year" => $row['warehouse_year'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "warehouse_id" => $row['warehouse_id'],
                "warehouse_year" => $row['warehouse_year'],
                "select" => "<button type='button' name='select' id='" . $row['warehouse_id'] . "@" . $row['warehouse_year'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
