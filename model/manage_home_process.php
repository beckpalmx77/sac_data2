<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT ims_home_model.*,proj.project_name as project_name FROM ims_home_model
    left join ims_project proj 
    on proj.project_id = ims_home_model.project_id
    WHERE ims_home_model.id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "home_id" => $result['home_id'],
            "home_model_name" => $result['home_model_name'],
            "project_id" => $result['project_id'],
            "project_name" => $result['project_name'],
            "area" => $result['area'],
            "floor" => $result['floor'],
            "bedroom" => $result['bedroom'],
            "bathroom" => $result['bathroom'],
            "img" => $result['img'],
            "comment" => $result['comment'],
            "status" => $result['status']);
    }

    $home_get = json_encode($return_arr);
    //file_put_contents("home_get.json", $home_get);
    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["home_id"] !== '') {

        $home_id = $_POST["home_id"];
        $sql_find = "SELECT * FROM ims_home_model WHERE home_id = '" . $home_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if ($_POST["home_id"] != '') {

        $home_id = $_POST["home_id"];
        $home_model_name = $_POST["home_model_name"];
        $project_id = $_POST["project_id"];
        $area = $_POST["area"];
        $floor = $_POST["floor"];
        $bedroom = $_POST["bedroom"];
        $bathroom = $_POST["bathroom"];
        $img = "";
        $comment = $_POST["comment"];
        $status = $_POST["status"];

        $sql_find = "SELECT * FROM ims_home_model WHERE home_id = '" . $home_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_home_model(home_id,home_model_name,project_id,area,floor,bedroom,bathroom,img,comment,status)
            VALUES (:home_id,:home_model_name,:project_id,:area,:floor,:bedroom,:bathroom,:img,:comment,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':home_id', $home_id, PDO::PARAM_STR);
            $query->bindParam(':home_model_name', $home_model_name, PDO::PARAM_STR);
            $query->bindParam(':project_id', $project_id, PDO::PARAM_STR);
            $query->bindParam(':area', $area, PDO::PARAM_STR);
            $query->bindParam(':floor', $floor, PDO::PARAM_STR);
            $query->bindParam(':bedroom', $bedroom, PDO::PARAM_STR);
            $query->bindParam(':bathroom', $bathroom, PDO::PARAM_STR);
            $query->bindParam(':img', $img, PDO::PARAM_STR);
            $query->bindParam(':comment', $comment, PDO::PARAM_STR);
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

    if ($_POST["home_id"] != '') {

        $id = $_POST["id"];
        $home_id = $_POST["home_id"];
        $home_model_name = $_POST["home_model_name"];
        $project_id = $_POST["project_id"];
        $area = $_POST["area"];
        $floor = $_POST["floor"];
        $bedroom = $_POST["bedroom"];
        $bathroom = $_POST["bathroom"];
        $comment = $_POST["comment"];
        $status = $_POST["status"];

        $txt = $id . "|" . $home_id . "|" . $home_model_name . "|" . $home_model_name
            . "|" . $project_id . "|" . $area . "|" . $floor . "|" . $bedroom
            . "|" . $bathroom . "|" . $status;

        //$my_file = fopen("HomeUpdate.txt", "w") or die("Unable to open file!");
        //fwrite($my_file, $txt);
        //fclose($my_file);

        $sql_find = "SELECT * FROM ims_home_model WHERE id = " . $id;
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_home_model SET home_id=:home_id,home_model_name=:home_model_name
            ,project_id=:project_id,area=:area,floor=:floor,bedroom=:bedroom,bathroom=:bathroom,comment=:comment
            ,status=:status WHERE id = :id";

            $my_file = fopen("sql_update.txt", "w") or die("Unable to open file!");
            fwrite($my_file, $sql_update);
            fclose($my_file);

            $query = $conn->prepare($sql_update);
            $query->bindParam(':home_id', $home_id, PDO::PARAM_STR);
            $query->bindParam(':home_model_name', $home_model_name, PDO::PARAM_STR);
            $query->bindParam(':project_id', $project_id, PDO::PARAM_STR);
            $query->bindParam(':area', $area, PDO::PARAM_STR);
            $query->bindParam(':floor', $floor, PDO::PARAM_STR);
            $query->bindParam(':bedroom', $bedroom, PDO::PARAM_STR);
            $query->bindParam(':bathroom', $bathroom, PDO::PARAM_STR);
            $query->bindParam(':comment', $comment, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_home_model WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_home_model WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            Reorder_Record($conn, "ims_home_model");
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_HOME') {

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
        $searchQuery = " AND (home_id LIKE :home_id or 
        home_model_name LIKE :home_model_name OR
        project_id LIKE :project_id OR         
        status LIKE :status ) ";
        $searchArray = array(
            'home_id' => "%$searchValue%",
            'home_model_name' => "%$searchValue%",
            'project_id' => "%$searchValue%",
            'status' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_home_model ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_home_model WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_fetch = "SELECT ims_home_model.*,proj.project_name as project_name FROM ims_home_model
    left join ims_project proj 
    on proj.project_id = ims_home_model.project_id";

    $stmt = $conn->prepare($sql_fetch . " WHERE 1 " . $searchQuery
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
                "home_id" => $row['home_id'],
                "home_model_name" => $row['home_model_name'],
                "project_id" => $row['project_id'],
                "project_name" => $row['project_name'],
                "area" => $row['area'],
                "floor" => $row['floor'],
                "bedroom" => $row['bedroom'],
                "bathroom" => $row['bathroom'],
                "image" => "<button type='button' name='image' id='" . $row['id'] . "' class='btn btn-success btn-xs image' data-toggle='tooltip' title='AddImage'>Image</button>",
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "home_id" => $row['home_id'],
                "home_model_name" => $row['home_model_name'],
                "floor" => $row['floor'],
                "bedroom" => $row['bedroom'],
                "select" => "<button type='button' name='select' id='" . $row['home_id'] . "@" . $row['home_model_name'] . "@" . $row['floor'] . "@" . $row['bedroom'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

    $home = json_encode($response);
    //file_put_contents("home.json", $home);

    echo json_encode($response);

}