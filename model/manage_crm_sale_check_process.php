<?php

session_start();

if (!isset($_SESSION['last_regenerate'])) {
    $_SESSION['last_regenerate'] = time();
} elseif (time() - $_SESSION['last_regenerate'] > 900) { // 900 วินาที (15 นาที)
    session_regenerate_id(true);
    $_SESSION['last_regenerate'] = time();
}

error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/getdata_field.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_customer_crm_sale_check_header WHERE id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "purpose" => $result['purpose'],
            "purpose_text" => $result['purpose_text'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "province" => $result['province'],
            "phone" => $result['phone'],
            "contact_name" => $result['contact_name'],
            "remark" => $result['remark'],
            "picture" => $result['picture'],
            "latitude_longitude" => $result['latitude_longitude'],
            "agent_status" => $result['agent_status'],
            "tbr_quantity_name1" => $result['tbr_quantity_name1'],
            "tbr_quantity1" => $result['tbr_quantity1'],
            "tbr_quantity_name2" => $result['tbr_quantity_name2'],
            "tbr_quantity2" => $result['tbr_quantity2'],
            "tbr_quantity_name3" => $result['tbr_quantity_name3'],
            "tbr_quantity3" => $result['tbr_quantity3'],
            "tbr_quantity_name4" => $result['tbr_quantity_name4'],
            "tbr_quantity4" => $result['tbr_quantity4'],
            "tbr_quantity_total" => $result['tbr_quantity_total'],
            "tbb_quantity_name1" => $result['tbb_quantity_name1'],
            "tbb_quantity1" => $result['tbb_quantity1'],
            "tbb_quantity_name2" => $result['tbb_quantity_name2'],
            "tbb_quantity2" => $result['tbb_quantity2'],
            "tbb_quantity_name3" => $result['tbb_quantity_name3'],
            "tbb_quantity3" => $result['tbb_quantity3'],
            "tbb_quantity_name4" => $result['tbb_quantity_name4'],
            "tbb_quantity4" => $result['tbb_quantity4'],
            "tbb_quantity_total" => $result['tbb_quantity_total'],
            "ltr_quantity_name1" => $result['ltr_quantity_name1'],
            "ltr_quantity1" => $result['ltr_quantity1'],
            "ltr_quantity_name2" => $result['ltr_quantity_name2'],
            "ltr_quantity2" => $result['ltr_quantity2'],
            "ltr_quantity_name3" => $result['ltr_quantity_name3'],
            "ltr_quantity3" => $result['ltr_quantity3'],
            "ltr_quantity_name4" => $result['ltr_quantity_name4'],
            "ltr_quantity4" => $result['ltr_quantity4'],
            "ltr_quantity_total" => $result['ltr_quantity_total'],
            "ltb_quantity_name1" => $result['ltb_quantity_name1'],
            "ltb_quantity1" => $result['ltb_quantity1'],
            "ltb_quantity_name2" => $result['ltb_quantity_name2'],
            "ltb_quantity2" => $result['ltb_quantity2'],
            "ltb_quantity_name3" => $result['ltb_quantity_name3'],
            "ltb_quantity3" => $result['ltb_quantity3'],
            "ltb_quantity_name4" => $result['ltb_quantity_name4'],
            "ltb_quantity4" => $result['ltb_quantity4'],
            "ltb_quantity_total" => $result['ltb_quantity_total'],
            "forecast_month" => $result['forecast_month'],
            "tbr_forecast_name1" => $result['tbr_forecast_name1'],
            "tbr_forecast_quantity1" => $result['tbr_forecast_quantity1'],
            "tbr_forecast_name2" => $result['tbr_forecast_name2'],
            "tbr_forecast_quantity2" => $result['tbr_forecast_quantity2'],
            "tbb_forecast_name1" => $result['tbb_forecast_name1'],
            "tbb_forecast_quantity1" => $result['tbb_forecast_quantity1'],
            "tbb_forecast_name2" => $result['tbb_forecast_name2'],
            "tbb_forecast_quantity2" => $result['tbb_forecast_quantity2'],
            "ltr_forecast_name1" => $result['ltr_forecast_name1'],
            "ltr_forecast_quantity1" => $result['ltr_forecast_quantity1'],
            "ltr_forecast_name2" => $result['ltr_forecast_name2'],
            "ltr_forecast_quantity2" => $result['ltr_forecast_quantity2'],
            "ltb_forecast_name1" => $result['ltb_forecast_name1'],
            "ltb_forecast_quantity1" => $result['ltb_forecast_quantity1'],
            "ltb_forecast_name2" => $result['ltb_forecast_name2'],
            "ltb_forecast_quantity2" => $result['ltb_forecast_quantity2'],
            "financial_status" => $result['financial_status'],
            "financial_reason" => $result['financial_reason'],
            "candidate" => $result['candidate'],
            "candidate_reason" => $result['candidate_reason'],
            "sale_out" => $result['sale_out'],
            "sale_out_reason" => $result['sale_out_reason'],
            "stock_shop" => $result['stock_shop'],
            "stock_shop_reason" => $result['stock_shop_reason'],
            "buy_from1" => $result['buy_from1'],
            "buy_from2" => $result['buy_from2'],
            "buy_from3" => $result['buy_from3'],
            "buy_from_sac" => $result['buy_from_sac'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

    /*
        $json_data = json_encode($return_arr, JSON_PRETTY_PRINT);
        $file_name = 'data.json';
        if (file_put_contents($file_name, $json_data)) {
            echo "Data has been saved to " . $file_name;
        } else {
            echo "Error writing data to file.";
        }
    */

}

if ($_POST["action"] === 'GET_ADD_KEYDATA') {
    $KeyAddData = $_POST["KeyAddData"];
    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_customer_crm_sale_check_header WHERE KeyAddData = :KeyAddData";
    $statement = $conn->prepare($sql_get);
    $statement->bindParam(':KeyAddData', $KeyAddData, PDO::PARAM_STR);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "purpose" => $result['purpose'],
            "purpose_text" => $result['purpose_text'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "province" => $result['province'],
            "phone" => $result['phone'],
            "contact_name" => $result['contact_name'],
            "remark" => $result['remark'],
            "picture" => $result['picture'],
            "latitude_longitude" => $result['latitude_longitude'],
            "agent_status" => $result['agent_status'],
            "tbr_quantity1" => $result['tbr_quantity1'],
            "tbr_quantity2" => $result['tbr_quantity2'],
            "tbr_quantity3" => $result['tbr_quantity3'],
            "tbr_quantity4" => $result['tbr_quantity4'],
            "tbr_quantity_total" => $result['tbr_quantity_total'],
            "tbb_quantity1" => $result['tbb_quantity1'],
            "tbb_quantity2" => $result['tbb_quantity2'],
            "tbb_quantity_total" => $result['tbb_quantity_total'],
            "ltr_quantity1" => $result['ltr_quantity1'],
            "ltr_quantity2" => $result['ltr_quantity2'],
            "ltr_quantity_total" => $result['ltr_quantity_total'],
            "ltb_quantity1" => $result['ltb_quantity1'],
            "ltb_quantity2" => $result['ltb_quantity2'],
            "ltb_quantity_total" => $result['ltb_quantity_total'],
            "forecast_month" => $result['forecast_month'],
            "forecast_tbr_quantity1" => $result['forecast_tbr_quantity1'],
            "forecast_tbr_quantity2" => $result['forecast_tbr_quantity2'],
            "forecast_tbb_quantity1" => $result['forecast_tbb_quantity1'],
            "forecast_ltr_quantity1" => $result['forecast_ltr_quantity1'],
            "forecast_ltr_quantity2" => $result['forecast_ltr_quantity2'],
            "forecast_ltb_quantity1" => $result['forecast_ltb_quantity1'],
            "financial_status" => $result['financial_status'],
            "financial_reason" => $result['financial_reason'],
            "candidate" => $result['candidate'],
            "candidate_reason" => $result['candidate_reason'],
            "sale_out" => $result['sale_out'],
            "sale_out_reason" => $result['sale_out_reason'],
            "stock_shop" => $result['stock_shop'],
            "stock_shop_reason" => $result['stock_shop_reason'],
            "buy_from1" => $result['buy_from1'],
            "buy_from2" => $result['buy_from2'],
            "buy_from3" => $result['buy_from3'],
            "buy_from_sac" => $result['buy_from_sac'],
            "status" => $result['status']
        );
    }

    header('Content-Type: application/json');
    echo json_encode($return_arr);
}

if ($_POST["action"] === 'GET_DATA_KEY') {

    $KeyAddData = $_POST["KeyAddData"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_customer_crm_sale_check_header WHERE KeyAddData = '" . $KeyAddData . "'";
    /*
        $myfile = fopen("crm-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile,  $KeyAddData . " | " . $sql_get);
        fclose($myfile);
    */
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "customer_id" => $result['customer_id'],
            "customer_name" => $result['customer_name'],
            "province" => $result['province'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_id"] !== '') {

        $doc_id = $_POST["doc_id"];
        $sql_find = "SELECT * FROM ims_customer_crm_sale_check_header WHERE doc_id = '" . $doc_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if (!empty($_POST["customer_name"])) {

        // รับค่าจากฟอร์ม
        $customer_name = $_POST["customer_name"];
        $province = $_POST["province"] ?? '';
        $phone = $_POST["phone"] ?? '';
        $contact_name = $_POST["contact_name"] ?? '';
        $doc_date = $_POST["doc_date"];
        $KeyAddData = $_POST["KeyAddData"] ?? '';

        // ตรวจสอบว่าถูกเลือก checkbox ไว้หรือไม่
        if (isset($_POST["myCheckValue"]) && $_POST["myCheckValue"] === 'Y') {
            $customer_id = "CUST-" . sprintf('%04s', LAST_ID($conn, "ims_customer_ar", 'id'));

            // ตรวจสอบว่ามีลูกค้าชื่อนี้อยู่หรือไม่
            $sql_find_customer = "SELECT COUNT(*) FROM ims_customer_ar WHERE f_name = :customer_name";
            $query_find_customer = $conn->prepare($sql_find_customer);
            $query_find_customer->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
            $query_find_customer->execute();
            $nRows = $query_find_customer->fetchColumn();

            if ($nRows == 0) {
                // Insert ข้อมูลใหม่ลงตาราง ims_customer_ar
                $sql_ins = "INSERT INTO ims_customer_ar (customer_id, f_name, province, phone, contact_name) 
                            VALUES (:customer_id, :customer_name, :province, :phone, :contact_name)";
                $query_ins = $conn->prepare($sql_ins);
                $query_ins->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
                $query_ins->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
                $query_ins->bindParam(':province', $province, PDO::PARAM_STR);
                $query_ins->bindParam(':phone', $phone, PDO::PARAM_STR);
                $query_ins->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
                $query_ins->execute();
            }
        } else {
            $customer_id = $_POST["customer_id"];
            $sql_update = "UPDATE ims_customer_ar SET province = :province, phone = :phone, contact_name = :contact_name 
                           WHERE customer_id = :customer_id";
            $query_update = $conn->prepare($sql_update);
            $query_update->bindParam(':province', $province, PDO::PARAM_STR);
            $query_update->bindParam(':phone', $phone, PDO::PARAM_STR);
            $query_update->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
            $query_update->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query_update->execute();
        }

        // บันทึกข้อมูลลง ims_customer_crm_sale_check_header
        $table = "ims_customer_crm_sale_check_header";
        $doc_year = substr($doc_date, 0, 4);
        $cond = "WHERE customer_id LIKE '%" . $customer_id . "%'";
        $doc_runno = LAST_DOCUMENT_COND($conn, $table, $cond);
        $doc_id = "B-CHK-" . $customer_id . "-" . sprintf('%04s', $doc_runno);
        $create_by = $_SESSION['first_name'];
        $status = $_POST["status"];
        $purpose = $_POST["purpose"] ?? "";
        $purpose_text = $_POST["purpose_text"] ?? "";
        $latitude_longitude = $_POST["latitude_longitude"] ?? "0,0";
        $remark = $_POST["remark"];

        $agent_status = $_POST["agent_status"];
        $tbr_quantity_name1 = $_POST["tbr_quantity_name1"];
        $tbr_quantity1 = $_POST["tbr_quantity1"];
        $tbr_quantity_name2 = $_POST["tbr_quantity_name2"];
        $tbr_quantity2 = $_POST["tbr_quantity2"];
        $tbr_quantity_name3 = $_POST["tbr_quantity_name3"];
        $tbr_quantity3 = $_POST["tbr_quantity3"];
        $tbr_quantity_name4 = $_POST["tbr_quantity_name4"];
        $tbr_quantity4 = $_POST["tbr_quantity4"];
        $tbr_quantity_total = $_POST["tbr_quantity_total"];
        $tbb_quantity_name1 = $_POST["tbb_quantity_name1"];
        $tbb_quantity1 = $_POST["tbb_quantity1"];
        $tbb_quantity_name2 = $_POST["tbb_quantity_name2"];
        $tbb_quantity2 = $_POST["tbb_quantity2"];
        $tbb_quantity_name3 = $_POST["tbb_quantity_name3"];
        $tbb_quantity3 = $_POST["tbb_quantity3"];
        $tbb_quantity_name4 = $_POST["tbb_quantity_name4"];
        $tbb_quantity4 = $_POST["tbb_quantity4"];
        $tbb_quantity_total = $_POST["tbb_quantity_total"];
        $ltr_quantity_name1 = $_POST["ltr_quantity_name1"];
        $ltr_quantity1 = $_POST["ltr_quantity1"];
        $ltr_quantity_name2 = $_POST["ltr_quantity_name2"];
        $ltr_quantity2 = $_POST["ltr_quantity2"];
        $ltr_quantity_name3 = $_POST["ltr_quantity_name3"];
        $ltr_quantity3 = $_POST["ltr_quantity3"];
        $ltr_quantity_name4 = $_POST["ltr_quantity_name4"];
        $ltr_quantity4 = $_POST["ltr_quantity4"];
        $ltr_quantity_total = $_POST["ltr_quantity_total"];
        $ltb_quantity_name1 = $_POST["ltb_quantity_name1"];
        $ltb_quantity1 = $_POST["ltb_quantity1"];
        $ltb_quantity_name2 = $_POST["ltb_quantity_name2"];
        $ltb_quantity2 = $_POST["ltb_quantity2"];
        $ltb_quantity_name3 = $_POST["ltb_quantity_name3"];
        $ltb_quantity3 = $_POST["ltb_quantity3"];
        $ltb_quantity_name4 = $_POST["ltb_quantity_name4"];
        $ltb_quantity4 = $_POST["ltb_quantity4"];
        $ltb_quantity_total = $_POST["ltb_quantity_total"];
        $forecast_month = $_POST["forecast_month"];
        $tbr_forecast_name1 = $_POST["tbr_forecast_name1"];
        $tbr_forecast_quantity1 = $_POST["tbr_forecast_quantity1"];
        $tbr_forecast_name2 = $_POST["tbr_forecast_name2"];
        $tbr_forecast_quantity2 = $_POST["tbr_forecast_quantity2"];
        $tbb_forecast_name1 = $_POST["tbb_forecast_name1"];
        $tbb_forecast_quantity1 = $_POST["tbb_forecast_quantity1"];
        $tbb_forecast_name2 = $_POST["tbb_forecast_name2"];
        $tbb_forecast_quantity2 = $_POST["tbb_forecast_quantity2"];
        $ltr_forecast_name1 = $_POST["ltr_forecast_name1"];
        $ltr_forecast_quantity1 = $_POST["ltr_forecast_quantity1"];
        $ltr_forecast_name2 = $_POST["ltr_forecast_name2"];
        $ltr_forecast_quantity2 = $_POST["ltr_forecast_quantity2"];
        $ltb_forecast_name1 = $_POST["ltb_forecast_name1"];
        $ltb_forecast_quantity1 = $_POST["ltb_forecast_quantity1"];
        $ltb_forecast_name2 = $_POST["ltb_forecast_name2"];
        $ltb_forecast_quantity2 = $_POST["ltb_forecast_quantity2"];
        $financial_status = $_POST["financial_status"];
        $financial_reason = $_POST["financial_reason"];
        $candidate = $_POST["candidate"];
        $candidate_reason = $_POST["candidate_reason"];
        $sale_out = $_POST["sale_out"];
        $sale_out_reason = $_POST["sale_out_reason"];
        $stock_shop = $_POST["stock_shop"];
        $stock_shop_reason = $_POST["stock_shop_reason"];
        $buy_from1 = $_POST["buy_from1"];
        $buy_from2 = $_POST["buy_from2"];
        $buy_from3 = $_POST["buy_from3"];
        $buy_from_sac = $_POST["buy_from_sac"];


        // Handle image file uploads
        $upload_dir = '../upload_img/'; // Directory to store images
        $allowed_types = ['image/jpeg', 'image/png'];
        $uploaded_files = [];
        $max_file_size = 30 * 1024 * 1024;

        // Check if files are uploaded
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $total_files = count($_FILES['images']['name']);

            for ($i = 0; $i < $total_files; $i++) {
                $file_type = mime_content_type($_FILES['images']['tmp_name'][$i]);
                $file_size = $_FILES['images']['size'][$i];

                // Validate file type and size
                if (!in_array($file_type, $allowed_types)) {
                    echo "Invalid file type for file: " . $_FILES['images']['name'][$i];
                    continue;
                }
                if ($file_size > $max_file_size) {
                    echo "File size exceeds for file: " . $_FILES['images']['name'][$i];
                    continue;
                }

                // Create unique file name
                $file_name = uniqid() . '-' . basename($_FILES['images']['name'][$i]);
                $target_file = $upload_dir . $file_name;

                // Move the uploaded file to the server directory
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    $uploaded_files[] = $file_name;
                } else {
                    echo "Failed to upload file: " . $_FILES['images']['name'][$i];
                }
            }
        }

        $file_names = implode(',', $uploaded_files); // Join the filenames with commas

        // ตรวจสอบการซ้ำก่อนบันทึก
        $sql_find = "SELECT COUNT(*) FROM $table WHERE doc_id = :doc_id";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows == 0) {
            // Insert ข้อมูลลง ims_customer_crm_sale_check_header
            $sql = "INSERT INTO $table (
    doc_id, customer_id, doc_date, purpose, purpose_text, latitude_longitude, create_by, KeyAddData, remark, picture,
    agent_status, tbr_quantity_name1, tbr_quantity1, tbr_quantity_name2, tbr_quantity2, tbr_quantity_name3, tbr_quantity3, tbr_quantity_name4, tbr_quantity4, tbr_quantity_total, 
    tbb_quantity_name1, tbb_quantity1, tbb_quantity_name2, tbb_quantity2, tbb_quantity_name3, tbb_quantity3, tbb_quantity_name4, tbb_quantity4, tbb_quantity_total, 
    ltr_quantity_name1, ltr_quantity1, ltr_quantity_name2, ltr_quantity2, ltr_quantity_name3, ltr_quantity3, ltr_quantity_name4, ltr_quantity4, ltr_quantity_total, 
    ltb_quantity_name1, ltb_quantity1, ltb_quantity_name2, ltb_quantity2, ltb_quantity_name3, ltb_quantity3, ltb_quantity_name4, ltb_quantity4, ltb_quantity_total, 
    forecast_month, tbr_forecast_name1, tbr_forecast_quantity1, tbr_forecast_name2, tbr_forecast_quantity2, 
    tbb_forecast_name1, tbb_forecast_quantity1, tbb_forecast_name2, tbb_forecast_quantity2, 
    ltr_forecast_name1, ltr_forecast_quantity1, ltr_forecast_name2, ltr_forecast_quantity2, 
    ltb_forecast_name1, ltb_forecast_quantity1, ltb_forecast_name2, ltb_forecast_quantity2, 
    financial_status, financial_reason, 
    candidate, candidate_reason, sale_out, sale_out_reason, 
    stock_shop, stock_shop_reason,buy_from1,buy_from2,buy_from3,buy_from_sac
) VALUES (
    :doc_id, :customer_id, :doc_date, :purpose, :purpose_text, :latitude_longitude, :create_by, :KeyAddData, :remark, :picture,
    :agent_status, :tbr_quantity_name1, :tbr_quantity1, :tbr_quantity_name2, :tbr_quantity2, :tbr_quantity_name3, :tbr_quantity3, :tbr_quantity_name4, :tbr_quantity4, :tbr_quantity_total, 
    :tbb_quantity_name1, :tbb_quantity1, :tbb_quantity_name2, :tbb_quantity2, :tbb_quantity_name3, :tbb_quantity3, :tbb_quantity_name4, :tbb_quantity4, :tbb_quantity_total, 
    :ltr_quantity_name1, :ltr_quantity1, :ltr_quantity_name2, :ltr_quantity2, :ltr_quantity_name3, :ltr_quantity3, :ltr_quantity_name4, :ltr_quantity4, :ltr_quantity_total, 
    :ltb_quantity_name1, :ltb_quantity1, :ltb_quantity_name2, :ltb_quantity2, :ltb_quantity_name3, :ltb_quantity3, :ltb_quantity_name4, :ltb_quantity4, :ltb_quantity_total, 
    :forecast_month, :tbr_forecast_name1, :tbr_forecast_quantity1, :tbr_forecast_name2, :tbr_forecast_quantity2, 
    :tbb_forecast_name1, :tbb_forecast_quantity1, :tbb_forecast_name2, :tbb_forecast_quantity2, 
    :ltr_forecast_name1, :ltr_forecast_quantity1, :ltr_forecast_name2, :ltr_forecast_quantity2, 
    :ltb_forecast_name1, :ltb_forecast_quantity1, :ltb_forecast_name2, :ltb_forecast_quantity2, 
    :financial_status, :financial_reason, 
    :candidate, :candidate_reason, :sale_out, :sale_out_reason, 
    :stock_shop, :stock_shop_reason,:buy_from1,:buy_from2,:buy_from3,:buy_from_sac
)";

            $query = $conn->prepare($sql);

            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':purpose', $purpose, PDO::PARAM_STR);
            $query->bindParam(':purpose_text', $purpose_text, PDO::PARAM_STR);
            $query->bindParam(':latitude_longitude', $latitude_longitude, PDO::PARAM_STR);
            $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
            $query->bindParam(':KeyAddData', $KeyAddData, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':picture', $file_names, PDO::PARAM_STR);

            $query->bindParam(':agent_status', $agent_status, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity_name1', $tbr_quantity_name1, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity1', $tbr_quantity1, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity_name2', $tbr_quantity_name2, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity2', $tbr_quantity2, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity_name3', $tbr_quantity_name3, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity3', $tbr_quantity3, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity_name4', $tbr_quantity_name4, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity4', $tbr_quantity4, PDO::PARAM_STR);
            $query->bindParam(':tbr_quantity_total', $tbr_quantity_total, PDO::PARAM_STR);

            $query->bindParam(':tbb_quantity_name1', $tbb_quantity_name1, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity1', $tbb_quantity1, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity_name2', $tbb_quantity_name2, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity2', $tbb_quantity2, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity_name3', $tbb_quantity_name3, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity3', $tbb_quantity3, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity_name4', $tbb_quantity_name4, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity4', $tbb_quantity4, PDO::PARAM_STR);
            $query->bindParam(':tbb_quantity_total', $tbb_quantity_total, PDO::PARAM_STR);

            $query->bindParam(':ltr_quantity_name1', $ltr_quantity_name1, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity1', $ltr_quantity1, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity_name2', $ltr_quantity_name2, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity2', $ltr_quantity2, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity_name3', $ltr_quantity_name3, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity3', $ltr_quantity3, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity_name4', $ltr_quantity_name4, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity4', $ltr_quantity4, PDO::PARAM_STR);
            $query->bindParam(':ltr_quantity_total', $ltr_quantity_total, PDO::PARAM_STR);

            $query->bindParam(':ltb_quantity_name1', $ltb_quantity_name1, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity1', $ltb_quantity1, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity_name2', $ltb_quantity_name2, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity2', $ltb_quantity2, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity_name3', $ltb_quantity_name3, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity3', $ltb_quantity3, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity_name4', $ltb_quantity_name4, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity4', $ltb_quantity4, PDO::PARAM_STR);
            $query->bindParam(':ltb_quantity_total', $ltb_quantity_total, PDO::PARAM_STR);

            $query->bindParam(':forecast_month', $forecast_month, PDO::PARAM_STR);
            $query->bindParam(':tbr_forecast_name1', $tbr_forecast_name1, PDO::PARAM_STR);
            $query->bindParam(':tbr_forecast_quantity1', $tbr_forecast_quantity1, PDO::PARAM_STR);
            $query->bindParam(':tbr_forecast_name2', $tbr_forecast_name2, PDO::PARAM_STR);
            $query->bindParam(':tbr_forecast_quantity2', $tbr_forecast_quantity2, PDO::PARAM_STR);

            $query->bindParam(':tbb_forecast_name1', $tbb_forecast_name1, PDO::PARAM_STR);
            $query->bindParam(':tbb_forecast_quantity1', $tbb_forecast_quantity1, PDO::PARAM_STR);
            $query->bindParam(':tbb_forecast_name2', $tbb_forecast_name2, PDO::PARAM_STR);
            $query->bindParam(':tbb_forecast_quantity2', $tbb_forecast_quantity2, PDO::PARAM_STR);

            $query->bindParam(':ltr_forecast_name1', $ltr_forecast_name1, PDO::PARAM_STR);
            $query->bindParam(':ltr_forecast_quantity1', $ltr_forecast_quantity1, PDO::PARAM_STR);
            $query->bindParam(':ltr_forecast_name2', $ltr_forecast_name2, PDO::PARAM_STR);
            $query->bindParam(':ltr_forecast_quantity2', $ltr_forecast_quantity2, PDO::PARAM_STR);

            $query->bindParam(':ltb_forecast_name1', $ltb_forecast_name1, PDO::PARAM_STR);
            $query->bindParam(':ltb_forecast_quantity1', $ltb_forecast_quantity1, PDO::PARAM_STR);
            $query->bindParam(':ltb_forecast_name2', $ltb_forecast_name2, PDO::PARAM_STR);
            $query->bindParam(':ltb_forecast_quantity2', $ltb_forecast_quantity2, PDO::PARAM_STR);

            $query->bindParam(':financial_status', $financial_status, PDO::PARAM_STR);
            $query->bindParam(':financial_reason', $financial_reason, PDO::PARAM_STR);
            $query->bindParam(':candidate', $candidate, PDO::PARAM_STR);
            $query->bindParam(':candidate_reason', $candidate_reason, PDO::PARAM_STR);
            $query->bindParam(':sale_out', $sale_out, PDO::PARAM_STR);
            $query->bindParam(':sale_out_reason', $sale_out_reason, PDO::PARAM_STR);
            $query->bindParam(':stock_shop', $stock_shop, PDO::PARAM_STR);
            $query->bindParam(':stock_shop_reason', $stock_shop_reason, PDO::PARAM_STR);
            $query->bindParam(':buy_from1', $buy_from1, PDO::PARAM_STR);
            $query->bindParam(':buy_from2', $buy_from2, PDO::PARAM_STR);
            $query->bindParam(':buy_from3', $buy_from3, PDO::PARAM_STR);
            $query->bindParam(':buy_from_sac', $buy_from_sac, PDO::PARAM_STR);

            $query->execute();
            $lastInsertId = $conn->lastInsertId();
            echo $lastInsertId ? $save_success : $error;
        } else {
            echo $dup;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {
    if (isset($_POST["doc_id"]) && $_POST["doc_id"] !== '') {
        // ตรวจสอบข้อมูลที่รับมา
        $id = $_POST["id"];
        $doc_id = $_POST["doc_id"];
        $customer_id = $_POST["customer_id"];
        $province = $_POST["province"];
        $phone = $_POST["phone"];
        $contact_name = $_POST["contact_name"];
        $remark = $_POST["remark"];
        $purpose = !empty($_POST["purpose"]) ? $_POST["purpose"] : "";
        $purpose_text = !empty($_POST["purpose_text"]) ? $_POST["purpose_text"] : "";
        $latitude_longitude = !empty($_POST["latitude_longitude"]) ? $_POST["latitude_longitude"] : "0,0";
        $status = $_POST["status"];
        $update_date = date('Y-m-d H:i:s');

        $agent_status = $_POST["agent_status"];
        $tbr_quantity_name1 = $_POST["tbr_quantity_name1"];
        $tbr_quantity1 = $_POST["tbr_quantity1"];
        $tbr_quantity_name2 = $_POST["tbr_quantity_name2"];
        $tbr_quantity2 = $_POST["tbr_quantity2"];
        $tbr_quantity_name3 = $_POST["tbr_quantity_name3"];
        $tbr_quantity3 = $_POST["tbr_quantity3"];
        $tbr_quantity_name4 = $_POST["tbr_quantity_name4"];
        $tbr_quantity4 = $_POST["tbr_quantity4"];
        $tbr_quantity_total = $_POST["tbr_quantity_total"];
        $tbb_quantity_name1 = $_POST["tbb_quantity_name1"];
        $tbb_quantity1 = $_POST["tbb_quantity1"];
        $tbb_quantity_name2 = $_POST["tbb_quantity_name2"];
        $tbb_quantity2 = $_POST["tbb_quantity2"];
        $tbb_quantity_name3 = $_POST["tbb_quantity_name3"];
        $tbb_quantity3 = $_POST["tbb_quantity3"];
        $tbb_quantity_name4 = $_POST["tbb_quantity_name4"];
        $tbb_quantity4 = $_POST["tbb_quantity4"];
        $tbb_quantity_total = $_POST["tbb_quantity_total"];
        $ltr_quantity_name1 = $_POST["ltr_quantity_name1"];
        $ltr_quantity1 = $_POST["ltr_quantity1"];
        $ltr_quantity_name2 = $_POST["ltr_quantity_name2"];
        $ltr_quantity2 = $_POST["ltr_quantity2"];
        $ltr_quantity_name3 = $_POST["ltr_quantity_name3"];
        $ltr_quantity3 = $_POST["ltr_quantity3"];
        $ltr_quantity_name4 = $_POST["ltr_quantity_name4"];
        $ltr_quantity4 = $_POST["ltr_quantity4"];
        $ltr_quantity_total = $_POST["ltr_quantity_total"];
        $ltb_quantity_name1 = $_POST["ltb_quantity_name1"];
        $ltb_quantity1 = $_POST["ltb_quantity1"];
        $ltb_quantity_name2 = $_POST["ltb_quantity_name2"];
        $ltb_quantity2 = $_POST["ltb_quantity2"];
        $ltb_quantity_name3 = $_POST["ltb_quantity_name3"];
        $ltb_quantity3 = $_POST["ltb_quantity3"];
        $ltb_quantity_name4 = $_POST["ltb_quantity_name4"];
        $ltb_quantity4 = $_POST["ltb_quantity4"];
        $ltb_quantity_total = $_POST["ltb_quantity_total"];
        $forecast_month = $_POST["forecast_month"];
        $tbr_forecast_name1 = $_POST["tbr_forecast_name1"];
        $tbr_forecast_quantity1 = $_POST["tbr_forecast_quantity1"];
        $tbr_forecast_name2 = $_POST["tbr_forecast_name2"];
        $tbr_forecast_quantity2 = $_POST["tbr_forecast_quantity2"];
        $tbb_forecast_name1 = $_POST["tbb_forecast_name1"];
        $tbb_forecast_quantity1 = $_POST["tbb_forecast_quantity1"];
        $tbb_forecast_name2 = $_POST["tbb_forecast_name2"];
        $tbb_forecast_quantity2 = $_POST["tbb_forecast_quantity2"];
        $ltr_forecast_name1 = $_POST["ltr_forecast_name1"];
        $ltr_forecast_quantity1 = $_POST["ltr_forecast_quantity1"];
        $ltr_forecast_name2 = $_POST["ltr_forecast_name2"];
        $ltr_forecast_quantity2 = $_POST["ltr_forecast_quantity2"];
        $ltb_forecast_name1 = $_POST["ltb_forecast_name1"];
        $ltb_forecast_quantity1 = $_POST["ltb_forecast_quantity1"];
        $ltb_forecast_name2 = $_POST["ltb_forecast_name2"];
        $ltb_forecast_quantity2 = $_POST["ltb_forecast_quantity2"];
        $financial_status = $_POST["financial_status"];
        $financial_reason = $_POST["financial_reason"];
        $candidate = $_POST["candidate"];
        $candidate_reason = $_POST["candidate_reason"];
        $sale_out = $_POST["sale_out"];
        $sale_out_reason = $_POST["sale_out_reason"];
        $stock_shop = $_POST["stock_shop"];
        $stock_shop_reason = $_POST["stock_shop_reason"];
        $buy_from1 = $_POST["buy_from1"];
        $buy_from2 = $_POST["buy_from2"];
        $buy_from3 = $_POST["buy_from3"];
        $buy_from_sac = $_POST["buy_from_sac"];

        // ตรวจสอบว่ามีข้อมูลที่ต้องการอัพเดทหรือไม่
        $sql_find = "SELECT COUNT(*) FROM ims_customer_crm_sale_check_header WHERE doc_id = :doc_id";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $stmt_find->execute();
        $nRows = $stmt_find->fetchColumn();

        if ($nRows > 0) {
            // อัพเดทข้อมูลในตาราง ims_customer_crm_sale_check_header
            $sql_update_header = "UPDATE ims_customer_crm_sale_check_header 
                      SET customer_id = :customer_id, 
                          purpose = :purpose, 
                          purpose_text = :purpose_text, 
                          status = :status, 
                          update_date = :update_date,
                          remark = :remark,                          
                          agent_status = :agent_status,tbr_quantity_name1 = :tbr_quantity_name1,tbr_quantity1 = :tbr_quantity1,tbr_quantity_name2 = :tbr_quantity_name2,tbr_quantity2 = :tbr_quantity2,tbr_quantity_name3 = :tbr_quantity_name3,tbr_quantity3 = :tbr_quantity3,
                          tbr_quantity_name4 = :tbr_quantity_name4,tbr_quantity4 = :tbr_quantity4,tbr_quantity_total = :tbr_quantity_total,tbb_quantity_name1 = :tbb_quantity_name1,tbb_quantity1 = :tbb_quantity1,tbb_quantity_name2 = :tbb_quantity_name2,tbb_quantity2 = :tbb_quantity2,
                          tbb_quantity_name3 = :tbb_quantity_name3,tbb_quantity3 = :tbb_quantity3,tbb_quantity_name4 = :tbb_quantity_name4,tbb_quantity4 = :tbb_quantity4,tbb_quantity_total = :tbb_quantity_total,ltr_quantity_name1 = :ltr_quantity_name1,
                          ltr_quantity1 = :ltr_quantity1,ltr_quantity_name2 = :ltr_quantity_name2,ltr_quantity2 = :ltr_quantity2,ltr_quantity_name3 = :ltr_quantity_name3,ltr_quantity3 = :ltr_quantity3,ltr_quantity_name4 = :ltr_quantity_name4,
                          ltr_quantity4 = :ltr_quantity4,ltr_quantity_total = :ltr_quantity_total,ltb_quantity_name1 = :ltb_quantity_name1,ltb_quantity1 = :ltb_quantity1,ltb_quantity_name2 = :ltb_quantity_name2,ltb_quantity2 = :ltb_quantity2,
                          ltb_quantity_name3 = :ltb_quantity_name3,ltb_quantity3 = :ltb_quantity3,ltb_quantity_name4 = :ltb_quantity_name4,ltb_quantity4 = :ltb_quantity4,ltb_quantity_total = :ltb_quantity_total,forecast_month = :forecast_month,
                          tbr_forecast_name1 = :tbr_forecast_name1,tbr_forecast_quantity1 = :tbr_forecast_quantity1,tbr_forecast_name2 = :tbr_forecast_name2,tbr_forecast_quantity2 = :tbr_forecast_quantity2,tbb_forecast_name1 = :tbb_forecast_name1,
                          tbb_forecast_quantity1 = :tbb_forecast_quantity1,tbb_forecast_name2 = :tbb_forecast_name2,tbb_forecast_quantity2 = :tbb_forecast_quantity2,ltr_forecast_name1 = :ltr_forecast_name1,ltr_forecast_quantity1 = :ltr_forecast_quantity1,
                          ltr_forecast_name2 = :ltr_forecast_name2,ltr_forecast_quantity2 = :ltr_forecast_quantity2,ltb_forecast_name1 = :ltb_forecast_name1,ltb_forecast_quantity1 = :ltb_forecast_quantity1,ltb_forecast_name2 = :ltb_forecast_name2,
                          ltb_forecast_quantity2 = :ltb_forecast_quantity2,financial_status = :financial_status,financial_reason = :financial_reason,candidate = :candidate,candidate_reason = :candidate_reason,
                          sale_out = :sale_out,sale_out_reason = :sale_out_reason,stock_shop = :stock_shop,stock_shop_reason = :stock_shop_reason      
                          ,buy_from1 = :buy_from1,buy_from2 = :buy_from2,buy_from3 = :buy_from3,buy_from_sac = :buy_from_sac
                      WHERE doc_id = :doc_id";

            $query_header = $conn->prepare($sql_update_header);

            $query_header->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $query_header->bindParam(':purpose', $purpose, PDO::PARAM_STR);
            $query_header->bindParam(':purpose_text', $purpose_text, PDO::PARAM_STR);
            $query_header->bindParam(':status', $status, PDO::PARAM_STR);
            $query_header->bindParam(':update_date', $update_date, PDO::PARAM_STR);
            $query_header->bindParam(':remark', $remark, PDO::PARAM_STR);

            $query_header->bindParam(':agent_status', $agent_status, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity_name1', $tbr_quantity_name1, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity1', $tbr_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity_name2', $tbr_quantity_name2, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity2', $tbr_quantity2, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity_name3', $tbr_quantity_name3, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity3', $tbr_quantity3, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity_name4', $tbr_quantity_name4, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity4', $tbr_quantity4, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_quantity_total', $tbr_quantity_total, PDO::PARAM_STR);

            $query_header->bindParam(':tbb_quantity_name1', $tbb_quantity_name1, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity1', $tbb_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity_name2', $tbb_quantity_name2, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity2', $tbb_quantity2, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity_name3', $tbb_quantity_name3, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity3', $tbb_quantity3, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity_name4', $tbb_quantity_name4, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity4', $tbb_quantity4, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_quantity_total', $tbb_quantity_total, PDO::PARAM_STR);

            $query_header->bindParam(':ltr_quantity_name1', $ltr_quantity_name1, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity1', $ltr_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity_name2', $ltr_quantity_name2, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity2', $ltr_quantity2, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity_name3', $ltr_quantity_name3, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity3', $ltr_quantity3, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity_name4', $ltr_quantity_name4, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity4', $ltr_quantity4, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_quantity_total', $ltr_quantity_total, PDO::PARAM_STR);

            $query_header->bindParam(':ltb_quantity_name1', $ltb_quantity_name1, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity1', $ltb_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity_name2', $ltb_quantity_name2, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity2', $ltb_quantity2, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity_name3', $ltb_quantity_name3, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity3', $ltb_quantity3, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity_name4', $ltb_quantity_name4, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity4', $ltb_quantity4, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_quantity_total', $ltb_quantity_total, PDO::PARAM_STR);

            $query_header->bindParam(':forecast_month', $forecast_month, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_forecast_name1', $tbr_forecast_name1, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_forecast_quantity1', $tbr_forecast_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_forecast_name2', $tbr_forecast_name2, PDO::PARAM_STR);
            $query_header->bindParam(':tbr_forecast_quantity2', $tbr_forecast_quantity2, PDO::PARAM_STR);

            $query_header->bindParam(':tbb_forecast_name1', $tbb_forecast_name1, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_forecast_quantity1', $tbb_forecast_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_forecast_name2', $tbb_forecast_name2, PDO::PARAM_STR);
            $query_header->bindParam(':tbb_forecast_quantity2', $tbb_forecast_quantity2, PDO::PARAM_STR);

            $query_header->bindParam(':ltr_forecast_name1', $ltr_forecast_name1, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_forecast_quantity1', $ltr_forecast_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_forecast_name2', $ltr_forecast_name2, PDO::PARAM_STR);
            $query_header->bindParam(':ltr_forecast_quantity2', $ltr_forecast_quantity2, PDO::PARAM_STR);

            $query_header->bindParam(':ltb_forecast_name1', $ltb_forecast_name1, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_forecast_quantity1', $ltb_forecast_quantity1, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_forecast_name2', $ltb_forecast_name2, PDO::PARAM_STR);
            $query_header->bindParam(':ltb_forecast_quantity2', $ltb_forecast_quantity2, PDO::PARAM_STR);

            $query_header->bindParam(':financial_status', $financial_status, PDO::PARAM_STR);
            $query_header->bindParam(':financial_reason', $financial_reason, PDO::PARAM_STR);
            $query_header->bindParam(':candidate', $candidate, PDO::PARAM_STR);
            $query_header->bindParam(':candidate_reason', $candidate_reason, PDO::PARAM_STR);
            $query_header->bindParam(':sale_out', $sale_out, PDO::PARAM_STR);
            $query_header->bindParam(':sale_out_reason', $sale_out_reason, PDO::PARAM_STR);
            $query_header->bindParam(':stock_shop', $stock_shop, PDO::PARAM_STR);
            $query_header->bindParam(':stock_shop_reason', $stock_shop_reason, PDO::PARAM_STR);
            $query_header->bindParam(':buy_from1', $buy_from1, PDO::PARAM_STR);
            $query_header->bindParam(':buy_from2', $buy_from2, PDO::PARAM_STR);
            $query_header->bindParam(':buy_from3', $buy_from3, PDO::PARAM_STR);
            $query_header->bindParam(':buy_from_sac', $buy_from_sac, PDO::PARAM_STR);

            $query_header->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);

            if ($query_header->execute()) {
                $upload_dir = "../upload_img/";
                $existing_images = !empty($_POST["existing_images"]) ? explode(",", $_POST["existing_images"]) : [];
                $remove_images = !empty($_POST["remove_images"]) ? explode(",", $_POST["remove_images"]) : [];
                $uploaded_files = [];


                // ลบรูปภาพที่ผู้ใช้เลือกให้ลบ
                foreach ($remove_images as $file) {
                    $file_path = $upload_dir . $file;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $existing_images = array_diff($existing_images, [$file]);
                }

                // อัปโหลดไฟล์ใหม่
                if (!empty($_FILES["images"]["name"][0])) {
                    foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
                        $file_name = basename($_FILES["images"]["name"][$key]);
                        $target_file = $upload_dir . $file_name;

                        // ตรวจสอบว่าเป็นไฟล์รูปภาพ
                        $file_type = mime_content_type($tmp_name);
                        if (in_array($file_type, ["image/jpeg", "image/png"])) {
                            if (move_uploaded_file($tmp_name, $target_file)) {
                                $uploaded_files[] = $file_name;
                            }
                        }
                    }
                }

                // รวมรูปภาพเดิมที่เหลืออยู่กับรูปภาพใหม่
                $final_images = array_merge($existing_images, $uploaded_files);

                // Debug log
                /*
                                $log_data = "Existing Images: " . implode(",", $existing_images) . "\n" .
                                    "Uploaded Files: " . implode(",", $uploaded_files) . "\n" .
                                    "Final Images: " . implode(",", $final_images);
                                file_put_contents("debug_log.txt", $log_data);
                */

                // อัปเดตฐานข้อมูล
                $sql_update = "UPDATE ims_customer_crm_sale_check_header 
                               SET picture = :picture 
                               WHERE doc_id = :doc_id";
                $query = $conn->prepare($sql_update);
                $query->bindParam(':picture', implode(",", $final_images), PDO::PARAM_STR);
                $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);

                if ($query->execute()) {
                    echo "Update successful with pictures: " . implode(",", $final_images);
                } else {
                    echo "Error updating images: " . $query->errorInfo()[2];
                }

                // อัพเดทข้อมูลในตาราง ims_customer_ar
                $sql_update_customer = "UPDATE ims_customer_ar 
                                        SET province = :province, 
                                            phone = :phone, 
                                            contact_name = :contact_name 
                                        WHERE customer_id = :customer_id";

                $query_customer = $conn->prepare($sql_update_customer);
                $query_customer->bindParam(':province', $province, PDO::PARAM_STR);
                $query_customer->bindParam(':phone', $phone, PDO::PARAM_STR);
                $query_customer->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
                $query_customer->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);

                if ($query_customer->execute()) {
                    echo "Customer information updated successfully.";
                } else {
                    echo "Error: Failed to update customer information.";
                }
            } else {
                echo "Error: Failed to update document.";
            }
        } else {
            echo "Error: Document not found.";
        }
    } else {
        echo "Error: Missing document ID.";
    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_get_doc_id = "SELECT ims_customer_crm_sale_check_header.doc_id AS doc_id FROM ims_customer_crm_sale_check_header WHERE id = " . $id;

    $doc_id = GetDataValue($conn, $sql_get_doc_id);

    $sql_find = "SELECT * FROM ims_customer_crm_sale_check_header WHERE id = " . $id;

    /*
        $myfile = fopen("crm-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile,  $doc_id . " | " . $sql_get_doc_id);
        fclose($myfile);
    */
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {

            $sql_header = "DELETE FROM ims_customer_crm_sale_check_header WHERE id = " . $id;
            $query_header = $conn->prepare($sql_header);
            $query_header->execute();

            $sql_detail = "DELETE FROM ims_customer_crm_check_detail WHERE doc_id = '" . $doc_id . "'";
            $query_detail = $conn->prepare($sql_detail);
            $query_detail->execute();

            echo json_encode(['status' => 'success']);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete data.']);
        }
    }
}

if ($_POST["action"] === 'DELETE_FILE_SINGLE') {

    $file_name = $_POST['file_name'];
    $file_index = $_POST['file_index'];
    $id = $_POST['id'];

    $file_path = "../" . $file_name;

    if (file_exists($file_path)) {
        unlink($file_path);
    }

    $sql_delete = "UPDATE ims_customer_crm_sale_check_header SET $file_index = '' WHERE id = " . $id;
    $query = $conn->prepare($sql_delete);
    $query->execute();

}

if ($_POST["action"] === 'GET_CRM') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    if ($columnName === 'doc_id') {
        $columnSortOrder = "desc";
    }

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_id LIKE :doc_id or
        customer_name LIKE :customer_name ) ";
        $searchArray = array(
            'doc_id' => "%$searchValue%",
            'customer_name' => "%$searchValue%",
        );
    }

    $where_create_by = "";

    if ($_SESSION['account_type'] !== 'admin' && $_SESSION['account_type'] !== 'sale_sup') {
        $where_create_by = " AND create_by = '" . $_SESSION['user_id'] . "'";
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_crm_sale_check_header ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_customer_crm_sale_check_header WHERE 1 " . $searchQuery . $where_create_by);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $query_str = "SELECT * FROM v_ims_customer_crm_sale_check_header WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare("SELECT * FROM v_ims_customer_crm_sale_check_header WHERE 1 " . $searchQuery . $where_create_by
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
                "doc_id" => $row['doc_id'],
                "doc_date" => $row['doc_date'],
                "customer_id" => $row['customer_id'],
                "customer_name" => $row['customer_name'],
                "province" => $row['province'],
                "phone" => $row['phone'],
                "contact_name" => $row['contact_name'],
                "purpose" => $row['purpose'],
                "purpose_text" => $row['purpose_text'],
                "purepose_all" => $row['purpose'] . " " . $row['purpose_text'],
                "create_date" => $row['create_date'],
                "create_by" => $row['create_by'],
                "update_by" => $row['update_by'],
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>",
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "customer_id" => $row['customer_id'],
                "select" => "<button type='button' name='select' id='" . $row['doc_id'] . "@" . $row['customer_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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



