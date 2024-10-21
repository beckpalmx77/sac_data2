<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM log_user_login WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "user_id" => $result['user_id'],
            "login_timestamp" => $result['login_timestamp'],
            "ip_address" => $result['ip_address']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["login_timestamp"] !== '') {

        $login_timestamp = $_POST["login_timestamp"];
        $sql_find = "SELECT * FROM log_user_login WHERE login_timestamp = '" . $login_timestamp . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["login_timestamp"] !== '') {
        $user_id = "B-" . sprintf('%04s', LAST_ID($conn, "log_user_login", 'id'));
        $login_timestamp = $_POST["login_timestamp"];
        $ip_address = $_POST["ip_address"];
        $sql_find = "SELECT * FROM log_user_login WHERE login_timestamp = '" . $login_timestamp . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO log_user_login(user_id,login_timestamp,ip_address) VALUES (:user_id,:login_timestamp,:ip_address)";
            $query = $conn->prepare($sql);
            $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $query->bindParam(':login_timestamp', $login_timestamp, PDO::PARAM_STR);
            $query->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
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

    if ($_POST["login_timestamp"] != '') {

        $id = $_POST["id"];
        $user_id = $_POST["user_id"];
        $login_timestamp = $_POST["login_timestamp"];
        $ip_address = $_POST["ip_address"];
        $sql_find = "SELECT * FROM log_user_login WHERE user_id = '" . $user_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE log_user_login SET user_id=:user_id,login_timestamp=:login_timestamp,ip_address=:ip_address            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $query->bindParam(':login_timestamp', $login_timestamp, PDO::PARAM_STR);
            $query->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM log_user_login WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM log_user_login WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_LOGIN_LOG') {

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
        $searchQuery = " AND (user_id LIKE :user_id or
        login_timestamp LIKE :login_timestamp ) ";
        $searchArray = array(
            'user_id' => "%$searchValue%",
            'login_timestamp' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM log_user_login ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM log_user_login WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM log_user_login WHERE 1 " . $searchQuery
        . " ORDER BY id DESC " . " LIMIT :limit,:offset");

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
                "user_id" => $row['user_id'],
                "login_timestamp" => $row['login_timestamp'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "ip_address" => $row['ip_address'] === 'Active' ? "<div class='text-success'>" . $row['ip_address'] . "</div>" : "<div class='text-muted'> " . $row['ip_address'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "user_id" => $row['user_id'],
                "login_timestamp" => $row['login_timestamp'],
                "select" => "<button type='button' name='select' id='" . $row['user_id'] . "@" . $row['login_timestamp'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
