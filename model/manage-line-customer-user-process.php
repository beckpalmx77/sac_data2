<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/send_message.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT * FROM ims_customer_line_user WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "line_user_id" => $result['line_user_id'],
            "line_user_name" => $result['line_user_name'],
            "line_email" => $result['line_email'],
            "line_phone" => $result['line_phone'],
            "line_picture_profile" => $result['line_picture_profile'],
            "line_status_profile" => $result['line_status_profile'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "user_type" => $result['user_type'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {
    if ($_POST["line_user_name"] !== '') {
        $line_user_name = $_POST["line_user_name"];
        $sql_find = "SELECT * FROM ims_customer_line_user WHERE line_user_name = '" . $line_user_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {
    if ($_POST["line_user_id"] != '') {
        $id = $_POST["id"];
        $line_user_id = $_POST["line_user_id"];
        $line_user_name = $_POST["line_user_name"];
        $customer_id = $_POST["customer_id"];
        $customer_name = $_POST["customer_name"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_customer_line_user WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_customer_line_user SET line_user_id=:line_user_id,line_user_name=:line_user_name
            ,customer_id=:customer_id,customer_name=:customer_name,status=:status
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':line_user_id', $line_user_id, PDO::PARAM_STR);
            $query->bindParam(':line_user_name', $line_user_name, PDO::PARAM_STR);
            $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
            if ($status === 'Y') {
                $stmt = $conn->prepare("SELECT line_api_token FROM aline_api WHERE doc_type = 'APPROVE_REG'");
                $stmt->execute();
                $line_api_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($line_api_tokens as $line_api_token) {
                    $channelAccessToken = $line_api_token['line_api_token'];
                    $sMessage = "✅🌟✨🌟 ลงทะเบียนเรียบร้อยแล้ว สามารถเข้าระบบเพื่อใช้งานได้ทันที 🌟✨🌟✅";
                    $userId = $line_user_id;
                    sendLineMessage($conn, $channelAccessToken, $userId, $sMessage);
                }
            }
        }
    }
}

if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM ims_customer_line_user WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_customer_line_user WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_LINE_CUSTOMER_USER') {
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
        $searchQuery = " AND (line_user_id LIKE :line_user_id or
        line_user_name LIKE :line_user_name ) ";
        $searchArray = array(
            'line_user_id' => "%$searchValue%",
            'line_user_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_customer_line_user ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_customer_line_user WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_customer_line_user WHERE 1 " . $searchQuery
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
                "line_user_id" => $row['line_user_id'],
                "line_user_name" => $row['line_user_name'],
                "line_email" => $row['line_email'],
                "line_phone" => $row['line_phone'],
                "line_picture_profile" => $row['line_picture_profile'],
                "line_status_profile" => $row['line_status_profile'],
                "customer_id" => $row['customer_id'],
                "customer_name" => $row['customer_name'],
                "user_type" => $row['user_type'],
                "status" => $row['status'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "line_user_id" => $row['line_user_id'],
                "line_user_name" => $row['line_user_name'],
                "select" => "<button type='button' name='select' id='" . $row['line_user_id'] . "@" . $row['line_user_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

