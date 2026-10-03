<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_sale_team WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "sale_take_id" => $result['sale_take_id'],
            "name" => $result['name'],
            "position_type" => $result['position_type'],
            "product_type" => $result['product_type'],
            "remark" => $result['remark'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'ADD') {
    if ($_POST["name"] !== '') {
        $name = $_POST["name"];
        $position_type = $_POST["position_type"];
        $product_type = $_POST["product_type"];
        $remark = $_POST["remark"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_sale_team WHERE name = '" . $name . "'";

        $field = "sale_take_id";
        $table = "ims_sale_team";
        $where = " WHERE position_type = '" . $position_type . "' ORDER BY id DESC limit 1 ";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {

            $sale_take_id = LAST_DOCUMENT_NUMBER($conn,$field,$table,$where);
            $sql = "INSERT INTO ims_sale_team(sale_take_id,name,position_type,product_type,remark,status) VALUES (:sale_take_id,:name,:position_type,:product_type,:remark,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':sale_take_id', $sale_take_id, PDO::PARAM_STR);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':position_type', $position_type, PDO::PARAM_STR);
            $query->bindParam(':product_type', $product_type, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
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

    if ($_POST["remark"] != '') {

        $id = $_POST["id"];
        $name = $_POST["name"];
        $remark = $_POST["remark"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_sale_team WHERE name = '" . $name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_sale_team SET name=:name,remark=:remark,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_sale_team WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_sale_team WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_SALE_TAKE') {

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
        $searchQuery = " AND (name LIKE :name or
        remark LIKE :remark ) ";
        $searchArray = array(
            'name' => "%$searchValue%",
            'remark' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_sale_team ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_sale_team WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_sale_team WHERE 1 " . $searchQuery
        . " ORDER BY id DESC LIMIT :limit,:offset");

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
                "sale_take_id" => $row['sale_take_id'],
                "name" => $row['name'],
                "position_type" => $row['position_type'],
                "product_type" => $row['product_type'],
                "remark" => $row['remark'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "name" => $row['name'],
                "remark" => $row['remark'],
                "select" => "<button type='button' name='select' id='" . $row['name'] . "@" . $row['remark'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
