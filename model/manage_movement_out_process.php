<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_wh_stock_movement_out WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "product_id" => $result['product_id'],
            "product_name" => $result['product_name'],
            "qty" => $result['qty'],
            "car_no" => $result['car_no'],
            "line_no" => $result['line_no'],
            "wh_org" => $result['wh_org'],
            "wh_week_id" => $result['wh_week_id'],
            "remark" => $result['remark'],
            "location_org" => $result['location_org'],
            "location_to" => $result['location_to'],
            "create_by" => $result['create_by']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["product_id"] !== '') {

        $product_id = $_POST["product_id"];
        $sql_find = "SELECT * FROM v_wh_stock_movement_out WHERE product_id = '" . $product_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["product_id"] !== '' && $_POST["create_by"] !== '' && $_POST["doc_user_id"] !== '') {
        $create_by = $_POST["create_by"];
        $doc_user_id = $_POST["doc_user_id"];
        $doc_date = $_POST["doc_date"];

        $cond = "WHERE doc_date = '" . $doc_date . "' AND doc_user_id = '" . $doc_user_id . "'";

        //$doc_id = "MV-" . $create_by . "-" . $doc_date . "-" . sprintf('%06s', LAST_ID($conn, "wh_stock_movement_out", 'id'));

        $run_no = LAST_DOCUMENT_NUMBER($conn, "doc_id", "wh_stock_movement_out", $cond);
        $doc_id = "MO-" . $doc_user_id . "-" . $doc_date . "-" . sprintf('%06s', $run_no);

        $str = rand();
        $seq_record = md5($str);

        $product_id = $_POST["product_id"];
        $qty = $_POST["qty"];
        $wh_org = $_POST["wh_org"];
        $wh_week_id = $_POST["wh_week_id"];
        $location_org = $_POST["location_org"];
        $location_to = $_POST["location_to"];
        $car_no = $_POST["car_no"];
        $remark = $_POST["remark"];
        $sql_find = "SELECT * FROM wh_stock_movement_out WHERE doc_id = '" . $doc_id . "'";

        /*
                $txt = $sql_find . " | " . $run_no . " | " . $cond . " | " . $create_by . " | " . $doc_user_id;
                $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
                fwrite($my_file, $txt);
                fclose($my_file);
        */

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO wh_stock_movement_out(doc_id,doc_date,product_id,qty,wh_org,wh_week_id,wh_to,location_org,location_to,create_by,doc_user_id,seq_record,car_no,remark) 
            VALUES (:doc_id,:doc_date,:product_id,:qty,:wh_org,:wh_week_id,:wh_to,:location_org,:location_to,:create_by,:doc_user_id,:seq_record,:car_no,:remark)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':wh_org', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
            $query->bindParam(':wh_to', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':location_org', $location_org, PDO::PARAM_STR);
            $query->bindParam(':location_to', $location_to, PDO::PARAM_STR);
            $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
            $query->bindParam(':doc_user_id', $doc_user_id, PDO::PARAM_STR);
            $query->bindParam(':seq_record', $seq_record, PDO::PARAM_STR);
            $query->bindParam(':car_no', $car_no, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                for ($line_no = 1; $line_no <= 2; $line_no++) {
                    $sql_find_trans = "SELECT * FROM wh_stock_transaction WHERE doc_id = '" . $doc_id . "' AND line_no = " . $line_no;
                    $nRows = $conn->query($sql_find_trans)->fetchColumn();
                    if ($nRows <= 0) {
                        if ($line_no === 1) {
                            $record_type = "+";
                            $location = $location_to;
                        } else {
                            $record_type = "-";
                            $location = $location_org;
                        }
                        $sql_ins = "INSERT INTO wh_stock_transaction(doc_id,record_type,line_no,doc_date,product_id,qty,wh,wh_week_id,location,create_by,doc_user_id,seq_record) 
                                    VALUES (:doc_id,:record_type,:line_no,:doc_date,:product_id,:qty,:wh,:wh_week_id,:location,:create_by,:doc_user_id,:seq_record)";
                        $query_trans = $conn->prepare($sql_ins);
                        $query_trans->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':record_type', $record_type, PDO::PARAM_STR);
                        $query_trans->bindParam(':line_no', $line_no, PDO::PARAM_STR);
                        $query_trans->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
                        $query_trans->bindParam(':product_id', $product_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':qty', $qty, PDO::PARAM_STR);
                        $query_trans->bindParam(':wh', $wh_org, PDO::PARAM_STR);
                        $query_trans->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':location', $location, PDO::PARAM_STR);
                        $query_trans->bindParam(':create_by', $create_by, PDO::PARAM_STR);
                        $query_trans->bindParam(':doc_user_id', $doc_user_id, PDO::PARAM_STR);
                        $query_trans->bindParam(':seq_record', $seq_record, PDO::PARAM_STR);
                        $query_trans->execute();
                    }
                }

                echo $save_success;
            } else {
                echo $error;
            }
        }
    }
}

if ($_POST["action"] === 'UPDATE') {

    if ($_POST["product_id"] !== '' && $_POST["create_by"] !== '' && $_POST["doc_user_id"] !== '') {

        $update_by = $_POST["create_by"];
        $id = $_POST["id"];
        $doc_id = $_POST["doc_id"];
        $doc_date = $_POST["doc_date"];
        $product_id = $_POST["product_id"];
        $qty = $_POST["qty"];
        $wh_org = $_POST["wh_org"];
        $wh_week_id = $_POST["wh_week_id"];
        $wh_to = $_POST["wh_org"];
        $location_org = $_POST["location_org"];
        $location_to = $_POST["location_to"];
        $car_no = $_POST["car_no"];
        $remark = $_POST["remark"];

        if ($wh_week_id !== '' && $location_org !== '' && $wh_org !== '') {
            $status = 'Y';
        } else {
            $status = 'N';
        }


        // ตรวจสอบและทำการ UPDATE ข้อมูลใน wh_stock_movement_out
        $sql_find = "SELECT * FROM wh_stock_movement_out WHERE id = :id";
        $query_find = $conn->prepare($sql_find);
        $query_find->bindParam(':id', $id, PDO::PARAM_INT);
        $query_find->execute();
        $nRows = $query_find->fetchColumn();

        if ($nRows > 0) {
            $sql_update = "UPDATE wh_stock_movement_out SET product_id=:product_id,qty=:qty            
            ,wh_org=:wh_org,wh_week_id=:wh_week_id,wh_to=:wh_to,location_org=:location_org,location_to=:location_to,update_by=:update_by,car_no=:car_no,remark=:remark,status=:status
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':wh_org', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
            $query->bindParam(':wh_to', $wh_org, PDO::PARAM_STR);
            $query->bindParam(':location_org', $location_org, PDO::PARAM_STR);
            $query->bindParam(':location_to', $location_to, PDO::PARAM_STR);
            $query->bindParam(':update_by', $update_by, PDO::PARAM_STR);
            $query->bindParam(':car_no', $car_no, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();

            // วนลูปเพื่อทำงานกับ wh_stock_transaction
            for ($line_no = 1; $line_no <= 2; $line_no++) {
                if ($line_no === 1) {
                    $record_type = "+";
                    $location = "OUT";
                } else {
                    $record_type = "-";
                    $location = $location_org;
                }

                // ตรวจสอบข้อมูลใน wh_stock_transaction ก่อนทำการ UPDATE หรือ INSERT
                $sql_find_trans = "SELECT * FROM wh_stock_transaction WHERE doc_id = :doc_id AND line_no = :line_no";
                $query_find_trans = $conn->prepare($sql_find_trans);
                $query_find_trans->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
                $query_find_trans->bindParam(':line_no', $line_no, PDO::PARAM_INT);
                $query_find_trans->execute();
                $nRows_trans = $query_find_trans->fetchColumn();

                if ($nRows_trans > 0) {
                    // ถ้ามีข้อมูล ให้ทำการ UPDATE
                    $sql_updates = "UPDATE wh_stock_transaction SET record_type=:record_type,product_id=:product_id,qty=:qty,wh=:wh,wh_week_id=:wh_week_id,location=:location "
                        . " WHERE doc_id = :doc_id AND line_no = :line_no";
                    $query_trans = $conn->prepare($sql_updates);
                    $query_trans->bindParam(':record_type', $record_type, PDO::PARAM_STR);
                    $query_trans->bindParam(':product_id', $product_id, PDO::PARAM_STR);
                    $query_trans->bindParam(':qty', $qty, PDO::PARAM_STR);
                    $query_trans->bindParam(':wh', $wh_org, PDO::PARAM_STR);
                    $query_trans->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
                    $query_trans->bindParam(':location', $location, PDO::PARAM_STR);
                    $query_trans->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
                    $query_trans->bindParam(':line_no', $line_no, PDO::PARAM_INT);
                    $query_trans->execute();
                } else {
                    $sql_inserts = "INSERT INTO wh_stock_transaction (doc_id,doc_date,line_no,record_type,product_id,qty,wh,wh_week_id,location,create_by) "
                        . "VALUES (:doc_id,:doc_date,:line_no,:record_type,:product_id,:qty,:wh,:wh_week_id,:location,:create_by)";
                    $query_insert = $conn->prepare($sql_inserts);
                    $query_insert->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
                    $query_insert->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
                    $query_insert->bindParam(':line_no', $line_no, PDO::PARAM_INT);
                    $query_insert->bindParam(':record_type', $record_type, PDO::PARAM_STR);
                    $query_insert->bindParam(':product_id', $product_id, PDO::PARAM_STR);
                    $query_insert->bindParam(':qty', $qty, PDO::PARAM_STR);
                    $query_insert->bindParam(':wh', $wh_org, PDO::PARAM_STR);
                    $query_insert->bindParam(':wh_week_id', $wh_week_id, PDO::PARAM_STR);
                    $query_insert->bindParam(':location', $location, PDO::PARAM_STR);
                    $query_insert->bindParam(':create_by', $update_by, PDO::PARAM_STR);
                    $query_insert->execute();
                }
            }

            echo $save_success;
        }
    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM wh_stock_movement_out WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM wh_stock_movement_out WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_MOVEMENT_OUT') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value
    $where_filter = "";

    $status = $_POST['status'];
    $doc_date_start = $_POST['doc_date_start'];
    $doc_date_to = $_POST['doc_date_to'];
    $car_no = $_POST['car_no'];
    $brand_main = isset($_POST['brand_main']) ? $_POST['brand_main'] : '-';

    $searchArray = array();

    ## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery .= " AND vo.doc_id LIKE :doc_id ";
        $searchArray['doc_id'] = "%$searchValue%";
    }

    $doc_date_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
    $doc_date_to = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

    if (!empty($doc_date_start) && !empty($doc_date_to)) {
        $where_filter .= " AND STR_TO_DATE(vo.doc_date, '%d-%m-%Y') BETWEEN '" . $doc_date_start . "' AND '". $doc_date_to ."' " ;
    }


    if ($car_no !== '-' && !empty($car_no)) {
        $where_filter .= " AND vo.car_no = :car_no ";
        $searchArray['car_no'] = $car_no;
    }

    if ($brand_main !== '-' && !empty($brand_main)) {
        $where_filter .= " AND vo.brand LIKE :brand_main ";
        $searchArray['brand_main'] = "$brand_main%";
    }

    ## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_wh_stock_movement_out vo WHERE 1");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## Total number of records with filtering
    $sql_count = "SELECT COUNT(*) AS allcount FROM v_wh_stock_movement_out vo WHERE 1 " . $where_filter . $searchQuery;
    $stmt = $conn->prepare($sql_count);

    // Bind search array
    foreach ($searchArray as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }

    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## Fetch filtered records
    $sql_get = "SELECT 
        vo.id, vo.doc_date, vo.doc_id, vo.line_no, vo.brand, vo.product_id, vo.product_name, vo.wh_org, vo.wh_week_id, vo.location_org,
        vo.sale_take, vo.customer_name, vo.car_no, vo.doc_user_id, vo.qty, vb.total_qty, vo.create_by, vo.create_date, vo.status 
        FROM v_wh_stock_movement_out vo 
        LEFT JOIN v_wh_stock_balance vb ON vb.product_id = vo.product_id AND vb.wh = vo.wh_org AND vb.wh_week_id = vo.wh_week_id AND vb.location = vo.location_org 
        WHERE 1 " . $where_filter . $searchQuery . " 
        ORDER BY $columnName $columnSortOrder 
        LIMIT :limit OFFSET :offset";
/*
    $txt = $sql_get;
    $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/
    $stmt = $conn->prepare($sql_get);

    // Bind search values and pagination
    foreach ($searchArray as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);

    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $doc_id = $row['doc_id'];
            $status = $row['status'];
            $doc_id_html = ($status == 'Y') ? '<span style="color:green;">' . $doc_id . '</span>' : '<span style="color:red;">' . $doc_id . '</span>';
            $status_html = ($status == 'Y') ? '<span style="color:green;">' . $status . '</span>' : '<span style="color:red;">' . $status . '</span>';

            $data[] = array(
                "id" => $row['id'],
                "doc_id" => $doc_id_html,
                "doc_date" => $row['doc_date'],
                "product_id" => $row['product_id'],
                "brand" => $row['brand'],
                "product_name" => $row['product_name'],
                "customer_name" => $row['customer_name'],
                "sale_take" => $row['sale_take'],
                "qty" => $row['qty'],
                "wh_org" => $row['wh_org'],
                "location_org" => $row['location_org'],
                "wh_to" => $row['wh_to'],
                "wh_week_id" => $row['wh_week_id'],
                "car_no" => $row['car_no'],
                "line_no" => $row['line_no'],
                "location_to" => $row['location_to'],
                "create_by" => $row['create_by'],
                "create_date" => $row['create_date'],
                "total_qty" => $row['total_qty'],
                "user_name" => $row['user_name'],
                "status" => $status_html,
                "remark" => $row['remark'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_date" => $row['doc_date'],
                "product_id" => $row['product_id'],
                "select" => "<button type='button' name='select' id='" . $row['doc_date'] . "@" . $row['product_id'] . "' class='btn btn-outline-success btn-xs select'>select</button>",
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
