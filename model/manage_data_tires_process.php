<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

$user_id = $_SESSION['user_id'];

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT * FROM v_ims_tires_request WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "date_request" => $result['date_request'],
            "ar_code" => $result['ar_code'],
            "customer_name" => $result['customer_name'],
            "tires_id" => $result['tires_id'],
            "brand" => $result['brand'],
            "class" => $result['class'],
            "tires_code" => $result['tires_code'],
            "detail" => $result['detail'] . $result['other_tires_request'],
            "sale_name" => $result['sale_name'],
            "qty_need" => $result['qty_need'],
            "date_in" => $result['date_in'],
            "remark" => $result['remark']);
    }

    echo json_encode($return_arr);

}


if ($_POST["action"] === 'SAVE') {
    if ($_POST["AR_CODE"] !== '') {
        $date_request = $_POST["date_request"];
        $ar_code = $_POST["AR_CODE"];
        //$tires_id = $_POST["tires_id"];
        $tires_id = ($_POST['myCheckValue'] === 'Y') ? "0" : $_POST['tires_id'];
        $sale_name = $_POST["sale_name"];
        $qty_need = $_POST["qty_need"];
        $remark = $_POST["remark"];
        $date_in = $_POST["date_in"];
        $other_tires_request = $_POST["other_tires_request"];

        $sql_find = "SELECT * FROM ims_tires_request 
        WHERE date_request = '" . $date_request . "'"
        . " AND ar_code = '" . $ar_code . "'"
        . " AND tires_id = '" . $tires_id . "'"
        . " AND sale_name = '" . $sale_name . "'" ;

        //$my_file = fopen("SEARCH_DATA-1.txt", "w") or die("Unable to open file!");
        //fwrite($my_file, $sql_find . " | " . $other_tires_request . " | " . $_POST['myCheckValue']);
        //fclose($my_file);

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {

            $sql_update = "UPDATE ims_tires_request SET qty_need=:qty_need
            ,date_in=:date_in,remark=:remark,update_by=:update_by
            WHERE date_request =:date_request AND tires_id =:tires_id AND ar_code=:ar_code AND sale_name=:sale_name";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':qty_need', $qty_need, PDO::PARAM_STR);
            $query->bindParam(':date_in', $date_in, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':other_tires_request', $other_tires_request, PDO::PARAM_STR);
            $query->bindParam(':update_by', $user_id, PDO::PARAM_STR);
            $query->bindParam(':date_request', $date_request, PDO::PARAM_STR);
            $query->bindParam(':tires_id', $tires_id, PDO::PARAM_STR);
            $query->bindParam(':ar_code', $ar_code, PDO::PARAM_STR);
            $query->bindParam(':sale_name', $sale_name, PDO::PARAM_STR);
            $query->execute();
            echo 2;

        } else {
            $sql = "INSERT INTO ims_tires_request(date_request,tires_id,ar_code,sale_name,qty_need,date_in,remark,other_tires_request,create_by) 
            VALUES (:date_request,:tires_id,:ar_code,:sale_name,:qty_need,:date_in,:remark,:other_tires_request,:create_by)";
            $query = $conn->prepare($sql);
            $query->bindParam(':date_request', $date_request, PDO::PARAM_STR);
            $query->bindParam(':tires_id', $tires_id, PDO::PARAM_STR);
            $query->bindParam(':ar_code', $ar_code, PDO::PARAM_STR);
            $query->bindParam(':sale_name', $sale_name, PDO::PARAM_STR);
            $query->bindParam(':qty_need', $qty_need, PDO::PARAM_STR);
            $query->bindParam(':date_in', $date_in, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':other_tires_request', $other_tires_request, PDO::PARAM_STR);
            $query->bindParam(':create_by', $user_id, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                echo 1;
            } else {
                echo 3;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE') {
    if ($_POST["id"] != '') {
        $id = $_POST["id"];
        $date_in = $_POST["date_in"];
        $sql_find = "SELECT * FROM ims_tires_request WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_tires_request SET date_in=:date_in            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':date_in', $date_in, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM menu_main WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM menu_main WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_TIRES_REQUEST') {
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
        $searchQuery = " AND (date_request LIKE :date_request or detail LIKE :detail or customer_name LIKE :customer_name or sale_name LIKE sale_name) ";
        $searchArray = array(
            'date_request' => "%$searchValue%",
            'detail' => "%$searchValue%",
            'customer_name' => "%$searchValue%",
            'sale_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_tires_request ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_tires_request WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    $sql_get_data = "SELECT * FROM v_ims_tires_request WHERE 1 " . $searchQuery
    . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

## Fetch records
    $stmt = $conn->prepare($sql_get_data);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $dataRecords = $stmt->fetchAll();
    $data = array();

    foreach ($dataRecords as $row) {

        //$myfile = fopen("param_post_mysql.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $row['other_tires_request'] . " | "  . $row['detail']);
        //fclose($myfile);

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "tires_id" => $row['tires_id'],
                "tires_code" => $row['tires_code'],
                "brand" => $row['brand'],
                "class" => $row['class'],
                "detail" => $row['detail'] . $row['other_tires_request'],
                "other_tires_request" => $row['other_tires_request'],
                "ar_code" => $row['ar_code'],
                "date_request" => ($row['date_request'] === '' || $row['date_request'] === null) ? "-" : $row['date_request'],
                "customer_name" => ($row['customer_name'] === '' || $row['customer_name'] === null) ? "-" : $row['customer_name'],
                "sale_name" => $row['sale_name'],
                "qty_need" => $row['qty_need'],
                "remark" => $row['remark'],
                "date_in" => ($row['date_in'] === '' || $row['date_in'] === null) ? "-" : $row['date_in'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "main_menu_id" => $row['main_menu_id'],
                "label" => $row['label'],
                "select" => "<button type='button' name='select' id='" . $row['main_menu_id'] . "@" . $row['label'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

