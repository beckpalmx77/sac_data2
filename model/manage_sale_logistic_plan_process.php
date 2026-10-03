<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('util/month_util.php');

if ($_POST["action"] === 'DELETE') {

    $id = filter_var($_POST["id"], FILTER_VALIDATE_INT);
    if ($id === false) {
        echo "Invalid ID provided.";
        exit();
    }

    try {
        // Begin a transaction to ensure atomicity
        $conn->beginTransaction();

        // Step 1: Find the DOC_ID from the main table using the provided id
        $sql_find_doc_id = "SELECT DOC_ID FROM ims_sale_logistic_plan WHERE id = :id";
        $stmt_find = $conn->prepare($sql_find_doc_id);
        $stmt_find->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt_find->execute();
        $doc_id_to_delete = $stmt_find->fetchColumn();

        if ($doc_id_to_delete) {
            // Step 2: Delete records from the detail table
            $sql_delete_detail = "DELETE FROM ims_sale_logistic_plan_detail WHERE DOC_ID = :doc_id";
            $stmt_detail = $conn->prepare($sql_delete_detail);
            $stmt_detail->bindValue(':doc_id', $doc_id_to_delete, PDO::PARAM_STR);
            $stmt_detail->execute();

            // Step 3: Delete the record from the main table
            $sql_delete_main = "DELETE FROM ims_sale_logistic_plan WHERE id = :id";
            $stmt_main = $conn->prepare($sql_delete_main);
            $stmt_main->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt_main->execute();

            // If all queries were successful, commit the transaction
            $conn->commit();
            echo $del_success;
        } else {
            // If DOC_ID was not found, rollback and inform the user.
            $conn->rollBack();
            echo "ไม่พบข้อมูล DOC_ID สำหรับการลบ";
        }

    } catch (Exception $e) {
        // If an error occurred, rollback the transaction
        $conn->rollBack();
        echo 'Message: ' . $e->getMessage();
    }
}

if ($_POST["action"] === 'GET_SALE_LOGISTIC') {

    ## Read value
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $row = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $columnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $columnName = isset($_POST['columns'][$columnIndex]['data']) ? $_POST['columns'][$columnIndex]['data'] : 'id';
    $columnSortOrder = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';
    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

    $searchArray = array();

    ## Search
    $searchQuery = " ";
    if (!empty($searchValue)) {
        $searchQuery = " AND (DOC_ID LIKE :DOC_ID OR DOC_DATE LIKE :DOC_DATE) ";
        $searchArray = array(
            'DOC_ID' => "%" . $searchValue . "%",
            'DOC_DATE' => "%" . $searchValue . "%",
        );
    }

    ## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_sale_logistic_plan");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_sale_logistic_plan WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## Fetch records
    // Use a whitelist for column names to prevent SQL Injection
    $columns = ['id', 'DOC_ID', 'DOC_DATE', 'MONTH', 'YEAR', 'create_date', 'seq_record', 'status'];
    $columnName = in_array($columnName, $columns) ? $columnName : 'id';
    $columnSortOrder = strtolower($columnSortOrder) === 'desc' ? 'DESC' : 'ASC';

    $stmt = $conn->prepare("SELECT * FROM ims_sale_logistic_plan WHERE 1 " . $searchQuery . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit OFFSET :offset");

    // Bind search values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    // Bind pagination values
    $stmt->bindValue(':limit', $rowperpage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $row, PDO::PARAM_INT);

    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    $sub_action = isset($_POST['sub_action']) ? $_POST['sub_action'] : '';

    foreach ($empRecords as $row) {
        if ($sub_action === "GET_MASTER") {
            $data[] = array(
                "id" => htmlspecialchars($row['id']),
                "DOC_ID" => htmlspecialchars($row['DOC_ID']),
                "DOC_DATE" => htmlspecialchars($row['DOC_DATE']),
                "MONTH" => htmlspecialchars($row['MONTH']),
                "YEAR" => htmlspecialchars($row['YEAR']),
                "create_date" => htmlspecialchars($row['create_date']),
                "seq_record" => htmlspecialchars($row['seq_record']),
                "update" => "<button type='button' name='update' class='btn btn-info btn-xs update' data-doc-id='" . htmlspecialchars($row['DOC_ID'])
                . "' data-month='" . htmlspecialchars($row['MONTH'])
                . "' data-year='" . htmlspecialchars($row['YEAR']) . "'>เป้าการขาย-ขนส่ง</button>",
                "delete" => "<button type='button' name='delete' class='btn btn-danger btn-xs delete' data-id='" . htmlspecialchars($row['id']) . "'>ลบข้อมูล</button>",
                "status" => htmlspecialchars($row['status']) === 'Active' ? "<div class='text-success'>" . htmlspecialchars($row['status']) . "</div>" : "<div class='text-muted'>" . htmlspecialchars($row['status']) . "</div>"
            );
        } else {
            $data[] = array(
                "id" => htmlspecialchars($row['id']),
                "DOC_ID" => htmlspecialchars($row['DOC_ID']),
                "DOC_DATE" => htmlspecialchars($row['DOC_DATE']),
                "select" => "<button type='button' name='select' class='btn btn-outline-success btn-xs select' data-doc-id='" . htmlspecialchars($row['DOC_ID']) . "' data-doc-date='" . htmlspecialchars($row['DOC_DATE']) . "'>select <i class='fa fa-check' aria-hidden='true'></i></button>",
            );
        }
    }

    ## Response
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
}