<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT * FROM v_ims_customer_crm_detail WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "faq_id" => $result['faq_id'],
            "faq_desc" => $result['faq_desc'],
            "faq_anwser" => $result['faq_anwser']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH_DATA') {
    if ($_POST["customer_id"] !== '') {
        $customer_id = $_POST["customer_id"];
        $sql_find = "SELECT * FROM ims_faq_master ";
        $statement = $conn->query($sql_find);

        //$my_file = fopen("SEARCH_DATA-1.txt", "w") or die("Unable to open file!");
        //fwrite($my_file, $sql_find);
        //fclose($my_file);


        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result) {
            $sql_find_detail = "SELECT * FROM ims_customer_crm WHERE customer_id = '" . $customer_id . "' AND faq_id = '" . $result['faq_id'] . "'";
            $nRows = $conn->query($sql_find_detail)->fetchColumn();
            if ($nRows <= 0) {
                $sql_ins = "INSERT INTO ims_customer_crm(customer_id,faq_id) 
                            VALUES (:customer_id,:faq_id)";

                $sql_ins1 .= $sql_ins;

                //$my_file = fopen("SEARCH_DATA-3.txt", "w") or die("Unable to open file!");
                //fwrite($my_file, $sql_ins1);
                //fclose($my_file);

                $query = $conn->prepare($sql_ins);
                $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
                $query->bindParam(':faq_id', $result['faq_id'], PDO::PARAM_STR);
                $query->execute();
            }
        }
    }
    echo "1";
}

if ($_POST["action_detail"] === 'UPDATE') {
    if ($_POST["customer_detail_id"] != '') {
        $customer_id = $_POST["customer_detail_id"];
        $faq_id = $_POST["faq_id"];
        $faq_anwser = $_POST["faq_anwser"];
        $sql_find = "SELECT * FROM ims_customer_crm WHERE customer_id = '" . $customer_id . "' AND faq_id = '" . $faq_id . "'" ;

        //$myfile = fopen("sql_faq.txt", "w") or die("Unable to open file!");
        //fwrite($myfile,  $id  . " | " . $sql_find);
        //fclose($myfile);

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_customer_crm SET faq_anwser=:faq_anwser
            WHERE customer_id = :customer_id AND faq_id = :faq_id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':faq_anwser', $faq_anwser, PDO::PARAM_STR);
            $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query->bindParam(':faq_id', $faq_id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'GET_CUSTOMER_DATA_CRM') {
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
        customer_name LIKE :customer_name or address LIKE :address) ";
        $searchArray = array(
            'customer_id' => "%$searchValue%",
            'customer_name' => "%$searchValue%",
            'address' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_crm_header ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_crm_header WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_ims_customer_crm_header WHERE 1 " . $searchQuery
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
                "customer_id" => $row['customer_id'],
                "customer_name" => $row['customer_name'],
                "address" => $row['address'],
                "phone" => $row['phone'],
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


if ($_POST["action"] === 'GET_DATA_CRM') {
    ## Read value
    $customer_id = $_POST['customer_id'];

//$myfile = fopen("sql_get-f.txt", "w") or die("Unable to open file!");
//fwrite($myfile,  $customer_id);
//fclose($myfile);

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
        faq_id LIKE :faq_id or faq_anwser LIKE :faq_anwser) ";
        $searchArray = array(
            'customer_id' => "%$searchValue%",
            'faq_id' => "%$searchValue%",
            'faq_anwser' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_crm_detail WHERE customer_id = '" . $customer_id . "'");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_crm_detail WHERE customer_id = '" . $customer_id . "' " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_ims_customer_crm_detail WHERE customer_id = '" . $customer_id . "' " . $searchQuery
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
                "faq_id" => $row['faq_id'],
                "faq_desc" => $row['faq_desc'],
                "faq_anwser" => $row['faq_anwser'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "faq_id" => $row['faq_id'],
                "faq_desc" => $row['faq_desc'],
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

