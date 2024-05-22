<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_customer_salename WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "AR_CODE" => $result['AR_CODE'],
            "AR_NAME" => $result['AR_NAME'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["AR_NAME"] !== '') {

        $AR_NAME = $_POST["AR_NAME"];
        $sql_find = "SELECT * FROM v_customer_salename WHERE AR_NAME = '" . $AR_NAME . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["AR_NAME"] !== '') {
        $AR_CODE = "B-" . sprintf('%04s', LAST_ID($conn, "v_customer_salename", 'id'));
        $AR_NAME = $_POST["AR_NAME"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM v_customer_salename WHERE AR_NAME = '" . $AR_NAME . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO v_customer_salename(AR_CODE,AR_NAME,status) VALUES (:AR_CODE,:AR_NAME,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':AR_CODE', $AR_CODE, PDO::PARAM_STR);
            $query->bindParam(':AR_NAME', $AR_NAME, PDO::PARAM_STR);
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

    if ($_POST["AR_NAME"] != '') {

        $id = $_POST["id"];
        $AR_CODE = $_POST["AR_CODE"];
        $AR_NAME = $_POST["AR_NAME"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM v_customer_salename WHERE AR_CODE = '" . $AR_CODE . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE v_customer_salename SET AR_CODE=:AR_CODE,AR_NAME=:AR_NAME,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':AR_CODE', $AR_CODE, PDO::PARAM_STR);
            $query->bindParam(':AR_NAME', $AR_NAME, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM v_customer_salename WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM v_customer_salename WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_CUSTOMER') {

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
        $searchQuery = " AND (AR_NAME LIKE :AR_NAME or
        SLMN_NAME LIKE :SLMN_NAME ) ";
        $searchArray = array(
            'AR_NAME' => "%$searchValue%",
            'SLMN_NAME' => "%$searchValue%",
        );
    }

    $company = ($_SESSION['company'] === '-') ? "%" : "%" . $_SESSION['company'] . "%" ;

    $manage_team_id = ($_SESSION['manage_team_id'] === '-') ? "%" : "%" . $_SESSION['manage_team_id'] . "%" ;

    $where_company = " AND AR_CODE LIKE '" . $company . "'";
    $where_manage_team = " AND SLMN_SLT LIKE '" . $manage_team_id . "'";

    $sql_count = "SELECT COUNT(*) AS allcount FROM v_customer_salename WHERE 1 " ;

## Total number of records without filtering
    $stmt = $conn->prepare($sql_count);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare($sql_count . $searchQuery . $where_company . $where_manage_team );

    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_load = "SELECT * FROM v_customer_salename "
    . " LEFT JOIN ims_customer_ar ar ON ar.customer_id = v_customer_salename.AR_CODE "
    . " WHERE 1 "
    . $searchQuery . $where_company . $where_manage_team
    . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    //$myfile = fopen("qry_file_mysql_server.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_load);
    //fclose($myfile);

    $stmt = $conn->prepare($sql_load);

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
                "AR_CODE" => $row['AR_CODE'],
                "AR_NAME" => $row['AR_NAME'],
                "province" => $row['province'],
                "SLMN_SLT" => $row['SLMN_SLT'],
                "SLMN_NAME" => $row['SLMN_NAME']

            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "AR_CODE" => $row['AR_CODE'],
                "AR_NAME" => $row['AR_NAME'],
                "select" => "<button type='button' name='select' id='" . $row['AR_CODE'] . "@" . $row['AR_NAME'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
