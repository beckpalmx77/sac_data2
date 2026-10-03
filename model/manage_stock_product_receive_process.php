<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_stock_product_receive WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id"            => $result['id'],
            "user_id"       => $result['user_id'],
            "display_name"  => $result['emp_name'],
            "customer_name" => $result['customer_name'],
            "product_id"    => $result['product_id'],
            "car_no"        => $result['car_no'],
            "checkin_time"  => $result['checkin_time'],
            "photo_path"    => $result['photo_path'],
            "check_type"    => $result['check_type'],
            "token_checkin" => $result['token_checkin'],
            "remark"        => $result['remark'],
        );
    }

    echo json_encode($return_arr);
}


if ($_POST["action"] === 'SEARCH') {

    if ($_POST["car_no"] !== '') {
        $car_no = $_POST["car_no"];
        $sql_find = "SELECT * FROM v_ims_stock_product_receive WHERE car_no = '" . $car_no . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}


if ($_POST["action"] === 'ADD') {

    $user_id       = $_POST["user_id"];
    $display_name  = $_POST["display_name"];
    $customer_name = $_POST["customer_name"];
    $product_id = $_POST["product_id"];
    $car_no        = $_POST["car_no"];
    $checkin_time  = $_POST["checkin_time"];
    $photo_path    = $_POST["photo_path"] ?? '';
    $check_type    = $_POST["check_type"];
    $token_checkin = $_POST["token_checkin"];
    $remark        = $_POST["remark"];

    $sql = "INSERT INTO ims_stock_product_receive
                (user_id, display_name, customer_name, car_no, checkin_time, photo_path, check_type, token_checkin,product_id, remark)
            VALUES
                (:user_id, :display_name, :customer_name, :car_no, :checkin_time, :photo_path, :check_type, :token_checkin,:product_id, :remark)";

    $query = $conn->prepare($sql);
    $query->bindParam(':user_id',       $user_id,       PDO::PARAM_STR);
    $query->bindParam(':display_name',  $display_name,  PDO::PARAM_STR);
    $query->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
    $query->bindParam(':car_no',        $car_no,        PDO::PARAM_STR);
    $query->bindParam(':checkin_time',  $checkin_time,  PDO::PARAM_STR);
    $query->bindParam(':photo_path',    $photo_path,    PDO::PARAM_STR);
    $query->bindParam(':check_type',    $check_type,    PDO::PARAM_STR);
    $query->bindParam(':token_checkin', $token_checkin, PDO::PARAM_STR);
    $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
    $query->bindParam(':remark',        $remark,        PDO::PARAM_STR);
    $query->execute();

    $lastInsertId = $conn->lastInsertId();

    if ($lastInsertId) {
        echo $save_success;
    } else {
        echo $error;
    }
}


if ($_POST["action"] === 'UPDATE') {

    $id            = $_POST["id"];
    $user_id       = $_POST["user_id"];
    $display_name  = $_POST["display_name"];
    $customer_name = $_POST["customer_name"];
    $product_id = $_POST["product_id"];
    $car_no        = $_POST["car_no"];
    $checkin_time  = $_POST["checkin_time"];
    $photo_path    = $_POST["photo_path"] ?? '';
    $check_type    = $_POST["check_type"];
    $token_checkin = $_POST["token_checkin"];
    $remark        = $_POST["remark"];

    $sql_find = "SELECT * FROM ims_stock_product_receive WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();

    if ($nRows > 0) {
        $sql_update = "UPDATE ims_stock_product_receive SET
                            user_id       = :user_id,
                            display_name  = :display_name,
                            customer_name = :customer_name,
                            car_no        = :car_no,                            
                            photo_path    = :photo_path,
                            check_type    = :check_type,
                            token_checkin = :token_checkin,
                            product_id = :product_id,                            
                            remark        = :remark
                       WHERE id = :id";

        $query = $conn->prepare($sql_update);
        $query->bindParam(':user_id',       $user_id,       PDO::PARAM_STR);
        $query->bindParam(':display_name',  $display_name,  PDO::PARAM_STR);
        $query->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
        $query->bindParam(':car_no',        $car_no,        PDO::PARAM_STR);
        $query->bindParam(':photo_path',    $photo_path,    PDO::PARAM_STR);
        $query->bindParam(':check_type',    $check_type,    PDO::PARAM_STR);
        $query->bindParam(':token_checkin', $token_checkin, PDO::PARAM_STR);
        $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
        $query->bindParam(':remark',        $remark,        PDO::PARAM_STR);
        $query->bindParam(':id',            $id,            PDO::PARAM_INT);
        $query->execute();
        echo $save_success;
    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_stock_product_receive WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();

    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_stock_product_receive WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}


if ($_POST["action"] === 'GET_RECEIVE') {

    ## Read value
    $draw          = $_POST['draw'];
    $row           = $_POST['start'];
    $rowperpage    = $_POST['length'];
    $columnIndex   = $_POST['order'][0]['column'];
    $columnName    = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $searchValue   = $_POST['search']['value'];

    $searchArray = array();

    ## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (user_id       LIKE :user_id
                           OR display_name  LIKE :display_name
                           OR customer_name LIKE :customer_name
                           OR product_id LIKE :product_id
                           OR car_no        LIKE :car_no
                           OR check_type    LIKE :check_type
                           OR remark        LIKE :remark) ";
        $searchArray = array(
            'user_id'       => "%$searchValue%",
            'display_name'  => "%$searchValue%",
            'customer_name' => "%$searchValue%",
            'product_id' => "%$searchValue%",
            'car_no'        => "%$searchValue%",
            'check_type'    => "%$searchValue%",
            'remark'        => "%$searchValue%",
        );
    }

    ## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_stock_product_receive");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_stock_product_receive WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_ims_stock_product_receive WHERE 1 " . $searchQuery
        . " ORDER BY id DESC LIMIT :limit,:offset");

    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit',  (int)$row,        PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $empRow) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id"            => $empRow['id'],
                "user_id"       => $empRow['user_id'],
                "display_name"  => $empRow['emp_name'],
                "customer_name" => $empRow['customer_name'],
                "product_id"    => $empRow['product_id'],
                "car_no"        => $empRow['car_no'],
                "checkin_time"  => $empRow['checkin_time'],
                "check_type"    => $empRow['check_type'],
                "remark"        => $empRow['remark'],
                "update"        => "<button type='button' name='update' id='" . $empRow['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "info"          => "<button type='button' name='info' id='" . $empRow['id'] . "' class='btn btn-info btn-xs info' data-toggle='tooltip' title='Info'>Info</button>",
                "delete"        => "<button type='button' name='delete' id='" . $empRow['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
            );
        } else {
            $data[] = array(
                "id"            => $empRow['id'],
                "user_id"       => $empRow['user_id'],
                "display_name"  => $empRow['display_name'],
                "customer_name" => $empRow['customer_name'],
                "product_id"    => $empRow['product_id'],
                "car_no"        => $empRow['car_no'],
                "checkin_time"  => $empRow['checkin_time'],
                "check_type"    => $empRow['check_type'],
                "remark"        => $empRow['remark'],
                "select"        => "<button type='button' name='select' id='" . $empRow['id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i></button>",
            );
        }
    }

    ## Response
    $response = array(
        "draw"                => intval($draw),
        "iTotalRecords"       => $totalRecords,
        "iTotalDisplayRecords"=> $totalRecordwithFilter,
        "aaData"              => $data
    );

    echo json_encode($response);
}



