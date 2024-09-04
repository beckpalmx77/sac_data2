<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT * FROM vims_customer_ar WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "l_name" => $result['l_name'],
            "address" => $result['address'],
            "phone" => $result['phone'],
            "email" => $result['email'],
            "province" => $result['province'],
            "amphure" => $result['amphure'],
            "tumbol" => $result['tumbol'],
            "province_name" => $result['province_name'],
            "amphure_name" => $result['amphure_name'],
            "tumbol_name" => $result['tumbol_name'],
            "zipcode" => $result['zipcode'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {
    if ($_POST["customer_name"] !== '') {
        $customer_name = $_POST["customer_name"];
        $sql_find = "SELECT * FROM ims_customer_ar WHERE customer_name = '" . $customer_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["customer_name"] !== '') {
        $customer_id = "C-" . sprintf('%04s', LAST_ID($conn, "ims_customer_ar", 'id'));
        $customer_name = $_POST["customer_name"];
        $l_name = $_POST["l_name"];
        $address = $_POST["address"];
        $phone = $_POST["phone"];
        $email = $_POST["email"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_customer_ar WHERE customer_name = '" . $customer_name . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_customer_ar(customer_id,customer_name,l_name,address,phone,email,status) 
            VALUES (:customer_id,:customer_name,:l_name,:address,:phone,:email,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
            $query->bindParam(':l_name', $l_name, PDO::PARAM_STR);
            $query->bindParam(':address', $address, PDO::PARAM_STR);
            $query->bindParam(':phone', $phone, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
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
    if ($_POST["customer_id"] != '') {
        $id = $_POST["id"];
        $customer_id = $_POST["customer_id"];
        $customer_name = $_POST["customer_name"];
        $l_name = $_POST["l_name"];
        $address = $_POST["address"];
        $phone = $_POST["phone"];
        $email = $_POST["email"];
        $province = $_POST["province"];
        $amphure = $_POST["amphure"];
        $tumbol = $_POST["tumbol"];
        $zipcode = $_POST["zipcode"];
        $status = $_POST["status"];

        //$txt = $status . " | " . $province . " | " . $amphure . " | " . $tumbol . " | " . $zipcode . " | " . $id . " | " . $customer_id;
        //$my_file = fopen("cust.txt", "w") or die("Unable to open file!");
        //fwrite($my_file, $txt);
        //fclose($my_file);

        $sql_find = "SELECT * FROM ims_customer_ar WHERE customer_id = '" . $customer_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_customer_ar SET customer_name=:customer_name,l_name=:l_name
            ,address=:address,phone=:phone,email=:email,province=:province,amphure=:amphure
            ,tumbol=:tumbol,zipcode=:zipcode,status=:status WHERE id = :id ";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
            $query->bindParam(':l_name', $l_name, PDO::PARAM_STR);
            $query->bindParam(':address', $address, PDO::PARAM_STR);
            $query->bindParam(':phone', $phone, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':province', $province, PDO::PARAM_STR);
            $query->bindParam(':amphure', $amphure, PDO::PARAM_STR);
            $query->bindParam(':tumbol', $tumbol, PDO::PARAM_STR);
            $query->bindParam(':zipcode', $zipcode, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM ims_customer_ar WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_customer_ar WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_CUSTOMER_CRM') {
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
        $searchQuery = " AND (customer_id LIKE :customer_id or
        customer_name LIKE :customer_name) ";
        $searchArray = array(
            'customer_id' => "%$searchValue%",
            'customer_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_sac ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_sac WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_get = "SELECT * FROM v_ims_customer_sac WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";
    $stmt = $conn->prepare($sql_get);

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
                "customer_id" => $row['customer_id'],
                "f_name" => $row['f_name'],
                "customer_name" => $row['customer_name'],
                "l_name" => $row['l_name'],
                "address" => $row['address'],
                "phone" => $row['phone'],
                "province_name" => $row['province_name'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "customer_id" => $row['customer_id'],
                "customer_name" => $row['customer_name'],
                "select" => "<button type='button' name='select' id='" . $row['customer_id'] . "@" . $row['customer_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

