<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_faq_anwser "
    . " WHERE v_ims_faq_anwser.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "faq_anwser_id" => $result['faq_anwser_id'],
            "faq_id" => $result['faq_id'],
            "faq_desc" => $result['faq_desc'],
            "faq_anwser" => $result['faq_anwser'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["faq_anwser"] !== '') {

        $faq_anwser = $_POST["faq_anwser"];
        $sql_find = "SELECT * FROM ims_faq_anwser WHERE faq_anwser = '" . $faq_anwser . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["faq_anwser"] !== '') {
        $faq_anwser_id = "A-" . sprintf('%04s', LAST_ID($conn, "ims_faq_anwser", 'id'));
        $faq_id = $_POST["faq_id"];
        $faq_anwser = $_POST["faq_anwser"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_faq_anwser WHERE faq_anwser = '" . $faq_anwser . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_faq_anwser(faq_anwser_id,faq_id,faq_anwser,status) 
                    VALUES (:faq_anwser_id,:faq_id,:faq_anwser,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':faq_anwser_id', $faq_anwser_id, PDO::PARAM_STR);
            $query->bindParam(':faq_id', $faq_id, PDO::PARAM_STR);
            $query->bindParam(':faq_anwser', $faq_anwser, PDO::PARAM_STR);
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

    if ($_POST["faq_anwser"] != '') {

        $id = $_POST["id"];
        $faq_anwser_id = $_POST["faq_anwser_id"];
        $faq_anwser = $_POST["faq_anwser"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_faq_anwser WHERE faq_anwser_id = '" . $faq_anwser_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_faq_anwser SET faq_anwser_id=:faq_anwser_id,faq_anwser=:faq_anwser,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':faq_anwser_id', $faq_anwser_id, PDO::PARAM_STR);
            $query->bindParam(':faq_anwser', $faq_anwser, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_faq_anwser WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_faq_anwser WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_FAQ') {

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
        $searchQuery = " AND (faq_anwser_id LIKE :faq_anwser_id or
        faq_anwser LIKE :faq_anwser ) ";
        $searchArray = array(
            'faq_anwser_id' => "%$searchValue%",
            'faq_anwser' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_faq_anwser ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_faq_anwser WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_ims_faq_anwser WHERE 1 " . $searchQuery
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
                "faq_anwser_id" => $row['faq_anwser_id'],
                "faq_id" => $row['faq_id'],
                "faq_desc" => $row['faq_desc'],
                "faq_anwser" => $row['faq_anwser'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "faq_anwser_id" => $row['faq_anwser_id'],
                "faq_anwser" => $row['faq_anwser'],
                "select" => "<button type='button' name='select' id='" . $row['faq_anwser_id'] . "@" . $row['faq_anwser'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
