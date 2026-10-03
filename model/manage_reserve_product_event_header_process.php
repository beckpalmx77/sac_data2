<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_product_reserve_event_header WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "DOC_ID" => $result['DOC_ID'],
            "DOC_DATE" => $result['DOC_DATE'],
            "SALE_NAME" => $result['SALE_NAME']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'DELETE') {
    $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;

    try {
        $stmt = $conn->prepare("SELECT doc_id FROM ims_product_reserve_event_header WHERE id = ?");
        $stmt->execute([$id]);
        $doc_id = $stmt->fetchColumn();

        if (!$doc_id) {
            http_response_code(400);
            echo "ไม่พบข้อมูลเอกสารใน header";
            exit;
        }

        $conn->beginTransaction();

        $sql_delete_detail = "DELETE FROM ims_product_reserve_event WHERE doc_id = ?";
        $sql_delete_header = "DELETE FROM ims_product_reserve_event_header WHERE id = ?";

        $stmt_delete_detail = $conn->prepare($sql_delete_detail);
        $stmt_delete_detail->execute([$doc_id]);

        $stmt_delete_header = $conn->prepare($sql_delete_header);
        $stmt_delete_header->execute([$id]);

        $conn->commit();

        echo "ลบข้อมูลเรียบร้อยแล้ว";
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}


if ($_POST["action"] === 'GET_DOC_ID') {

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
        $searchQuery = " AND (DOC_ID LIKE :DOC_ID or
        DOC_DATE LIKE :DOC_DATE or PHONE LIKE :PHONE or CUSTOMER_NAME LIKE :CUSTOMER_NAME  or SALE_NAME LIKE :SALE_NAME) ";
        $searchArray = array(
            'DOC_ID' => "%$searchValue%",
            'DOC_DATE' => "%$searchValue%",
            'PHONE' => "%$searchValue%",
            'CUSTOMER_NAME' => "%$searchValue%",
            'SALE_NAME' => "%$searchValue%",
        );
    }

    $and_sale = "";
    if ($_SESSION['account_type'] === "sale_user") {
        $and_sale = " AND SALE_NAME = '" . $_SESSION['first_name'] . "'";
    }

    $and_sale = "";
    if ($_SESSION['account_type'] === "take") {
        $and_sale = " AND TAKE_NAME = '" . $_SESSION['first_name'] . "'";
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product_reserve_event_header WHERE 1=1 " . $and_sale);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product_reserve_event_header WHERE 1=1 " . $and_sale . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_getdata = "SELECT * FROM ims_product_reserve_event_header WHERE 1=1 " . $and_sale . $searchQuery
        . " ORDER BY id DESC LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_getdata);
    /*
        $txt = $sql_getdata . " | " . $_SESSION['account_type'] . " | "  . $_SESSION['first_name'];
        $my_file = fopen("a_param.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
    */

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
                "DOC_ID" => $row['DOC_ID'],
                "DOC_DATE" => $row['DOC_DATE'],
                "CUSTOMER_CODE" => $row['CUSTOMER_CODE'],
                "CUSTOMER_NAME" => $row['CUSTOMER_NAME'],
                "PHONE" => $row['PHONE'],
                "PROVINCE" => $row['PROVINCE'],
                "SALE_NAME" => $row['SALE_NAME'],
                "update" => "<button type='button' name='detail' id='" . htmlspecialchars($row['DOC_ID'], ENT_QUOTES)
                . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'
                data-doc_date='" . htmlspecialchars($row['DOC_DATE'], ENT_QUOTES) . "'
                data-customer_code='" . htmlspecialchars($row['CUSTOMER_CODE'], ENT_QUOTES) . "'
                data-customer_name='" . htmlspecialchars($row['CUSTOMER_NAME'], ENT_QUOTES) . "'
                data-phone='" . htmlspecialchars($row['PHONE'], ENT_QUOTES) . "'
                data-sale_name='" . htmlspecialchars($row['SALE_NAME'], ENT_QUOTES) . "'
                data-province='" . htmlspecialchars($row['PROVINCE'], ENT_QUOTES) . "'>
                เซลล์ Update
            </button>",
                "detail" => "<button type='button' name='detail' id='" . htmlspecialchars($row['DOC_ID'], ENT_QUOTES) . "' class='btn btn-secondary btn-xs detail' data-toggle='tooltip' title='Detail'
                data-doc_date='" . htmlspecialchars($row['DOC_DATE'], ENT_QUOTES) . "'
                data-customer_code='" . htmlspecialchars($row['CUSTOMER_CODE'], ENT_QUOTES) . "'
                data-customer_name='" . htmlspecialchars($row['CUSTOMER_NAME'], ENT_QUOTES) . "'
                data-phone='" . htmlspecialchars($row['PHONE'], ENT_QUOTES) . "'
                data-sale_name='" . htmlspecialchars($row['SALE_NAME'], ENT_QUOTES) . "'
                data-province='" . htmlspecialchars($row['PROVINCE'], ENT_QUOTES) . "'>
                สรุปการจองยาง-ร้านค้า
            </button>",
                "summary" => "<button type='button' name='summary' id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "' class='btn btn-success btn-xs summary' data-toggle='tooltip' title='Summary'>สรุป</button>",
                "delete" => "<button type='button' name='delete' id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "DOC_ID" => $row['DOC_ID'],
                "DOC_DATE" => $row['DOC_DATE'],
                "select" => "<button type='button' name='select' id='" . htmlspecialchars($row['DOC_ID'], ENT_QUOTES) . "@" . htmlspecialchars($row['DOC_DATE'], ENT_QUOTES) . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i></button>"
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
