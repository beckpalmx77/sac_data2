<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT * FROM ims_sac_tires_point WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "SKU_CODE" => $result['SKU_CODE'],
            "SKU_NAME" => $result['SKU_NAME'],
            "BRAND" => $result['BRAND'],
            "SKU_CAT" => $result['SKU_CAT'],
            "TIRES_EDGE" => $result['TIRES_EDGE'],
            "TRD_U_POINT" => $result['TRD_U_POINT'],
            "TRD_S_POINT" => $result['TRD_S_POINT']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {
    if ($_POST["SKU_NAME"] !== '') {
        $SKU_NAME = $_POST["SKU_NAME"];
        $sql_find = "SELECT * FROM ims_sac_tires_point WHERE SKU_NAME = '" . $SKU_NAME . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if ($_POST["SKU_NAME"] !== '') {
        $SKU_CODE = $_POST["SKU_CODE"];
        $SKU_NAME = $_POST["SKU_NAME"];
        $BRAND = $_POST["BRAND"];
        $SKU_CAT = $_POST["SKU_CAT"];
        $TIRES_EDGE = $_POST["TIRES_EDGE"];
        $TRD_U_POINT = $_POST["TRD_U_POINT"];
        $TRD_S_POINT = $_POST["TRD_S_POINT"];

        $sql_find = "SELECT * FROM ims_sac_tires_point WHERE SKU_CODE = '" . $SKU_CODE . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_sac_tires_point(SKU_CODE,SKU_NAME,BRAND,SKU_CAT,TIRES_EDGE,TRD_U_POINT,TRD_S_POINT) 
            VALUES (:SKU_CODE,:SKU_NAME,:BRAND,:SKU_CAT,:TIRES_EDGE,:TRD_U_POINT,:TRD_S_POINT)";
            $query = $conn->prepare($sql);
            $query->bindParam(':SKU_CODE', $SKU_CODE, PDO::PARAM_STR);
            $query->bindParam(':SKU_NAME', $SKU_NAME, PDO::PARAM_STR);
            $query->bindParam(':BRAND', $BRAND, PDO::PARAM_STR);
            $query->bindParam(':SKU_CAT', $SKU_CAT, PDO::PARAM_STR);
            $query->bindParam(':TIRES_EDGE', $TIRES_EDGE, PDO::PARAM_STR);
            $query->bindParam(':TRD_U_POINT', $TRD_U_POINT, PDO::PARAM_STR);
            $query->bindParam(':TRD_S_POINT', $TRD_S_POINT, PDO::PARAM_STR);
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

    $txt = $_POST["action"];

    if ($_POST["SKU_CODE"] != '') {
        $id = $_POST["id"];
        $SKU_CODE = $_POST["SKU_CODE"];
        $SKU_NAME = $_POST["SKU_NAME"];
        $BRAND = $_POST["BRAND"];
        $SKU_CAT = $_POST["SKU_CAT"];
        $TIRES_EDGE = $_POST["TIRES_EDGE"];
        $TRD_U_POINT = $_POST["TRD_U_POINT"];
        $TRD_S_POINT = $_POST["TRD_S_POINT"];

        $sql_find = "SELECT * FROM ims_sac_tires_point WHERE id = '" . $id . "'";
/*
        $txt = $sql_find . " | " . $SKU_CODE;
        $myfile = fopen("asku-param1.txt", "w") or die("Unable to open file!");
        fwrite($myfile,  $txt);
        fclose($myfile);
*/

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_sac_tires_point SET SKU_CODE=:SKU_CODE,SKU_NAME=:SKU_NAME,BRAND=:BRAND,SKU_CAT=:SKU_CAT
            ,TIRES_EDGE=:TIRES_EDGE,TRD_U_POINT=:TRD_U_POINT,TRD_S_POINT=:TRD_S_POINT
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':SKU_CODE', $SKU_CODE, PDO::PARAM_STR);
            $query->bindParam(':SKU_NAME', $SKU_NAME, PDO::PARAM_STR);
            $query->bindParam(':BRAND', $BRAND, PDO::PARAM_STR);
            $query->bindParam(':SKU_CAT', $SKU_CAT, PDO::PARAM_STR);
            $query->bindParam(':TIRES_EDGE', $TIRES_EDGE, PDO::PARAM_STR);
            $query->bindParam(':TRD_U_POINT', $TRD_U_POINT, PDO::PARAM_STR);
            $query->bindParam(':TRD_S_POINT', $TRD_S_POINT, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM ims_sac_tires_point WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_sac_tires_point WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_TIRES_POINT') {
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
        $searchQuery = " AND (SKU_CODE LIKE :SKU_CODE OR
        SKU_NAME LIKE :SKU_NAME OR SKU_CAT LIKE :SKU_CAT ) ";
        $searchArray = array(
            'SKU_CODE' => "%$searchValue%",
            'SKU_NAME' => "%$searchValue%",
            'SKU_CAT' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_sac_tires_point ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_sac_tires_point WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_sac_tires_point WHERE 1 " . $searchQuery
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
                "SKU_CODE" => $row['SKU_CODE'],
                "SKU_NAME" => $row['SKU_NAME'],
                "SKU_CAT" => $row['SKU_CAT'],
                "BRAND" => $row['BRAND'],
                "TIRES_EDGE" => $row['TIRES_EDGE'],
                "TRD_U_POINT" => $row['TRD_U_POINT'],
                "TRD_S_POINT" => $row['TRD_S_POINT'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "SKU_CODE" => $row['SKU_CODE'],
                "SKU_NAME" => $row['SKU_NAME'],
                "select" => "<button type='button' name='select' id='" . $row['SKU_CODE'] . "@" . $row['SKU_NAME'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

