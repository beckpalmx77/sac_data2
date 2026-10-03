<?php
session_start();
error_reporting(0);
//error_reporting(E_ALL);

// Include necessary files for database connection and PhpSpreadsheet

ini_set('memory_limit', '2048M');

include '../config/connect_db.php'; // Include your database connection
require '../vendor/autoload.php'; // Load PhpSpreadsheet library
include '../util/record_util.php'; // Include any utility files
include '../util/month_convert_util.php'; // Include any utility files
include '../util/check_format_number.php'; // Include any utility files
include '../util/GetData.php'; // Include any utility files

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $file_Upload = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileType = mime_content_type($fileTmp); // ตรวจสอบ MIME type

    // กำหนดโฟลเดอร์ปลายทางที่คุณต้องการเก็บไฟล์
    $uploadDir = '../uploads/';
    $uploadFile = $uploadDir . basename($file_Upload);

    // ตรวจสอบ MIME type ของไฟล์ Excel
    if ($fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
        $fileType !== 'application/vnd.ms-excel') {
        $upload_status = "Invalid file type.";
        exit;
    }

    // ย้ายไฟล์จากตำแหน่งชั่วคราวไปยังโฟลเดอร์ที่กำหนด
    if (move_uploaded_file($fileTmp, $uploadFile)) {
        $upload_status = "Upload File สำเร็จ";
    } else {
        $upload_status = "ผิดพลาด Upload File ไม่สำเร็จ";
        exit;
    }

    try {
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($uploadFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $user_id = $_SESSION['user_id'];
        $table_name = "ims_data_sale_sac_all";
        $importedRows = 0;
        $duplicateRows = 0;
        $totalRows = 0;
        $TRD_SEQ = 0;
        $shop_type = "";
        $shop_type_status = "";
        $status = "";

        $str = rand();
        $seq_record = md5($str);

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header row

            // ตรวจสอบแถวว่าง ถ้าแถวทั้งหมดเป็นค่าว่าง จะข้ามไป
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if (trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) continue; // ข้ามแถวว่าง

            // นับจำนวนแถวทั้งหมด (ไม่รวม Header)
            if ($index <> 0) {
                $totalRows++;
            }

// Map data from Excel row to your table structure
            $data = [];
// ปรับลูปให้ครอบคลุมถึง index 36
            for ($i = 0; $i <= 36; $i++) {
                // ตรวจสอบว่า cell ใน Excel มีค่าเป็น null หรือว่างเปล่าหรือไม่
                $value = isset($row[$i]) && trim($row[$i]) !== '' ? $row[$i] : "0";

                // Replace commas and check for #N/A
                $data[$i] = str_replace(["#", ","], "", ($value === "#N/A" ? "0" : $value));
            }

// กำหนดตัวแปรต่าง ๆ ตามลำดับคอลัมน์ 0 - 36
            $DI_DATE_BLANK = empty($data[0]) ? "-" : trim($data[0]);
            $DI_DAY = empty($data[1]) ? "0" : trim($data[1]);
            $DI_MONTH_NAME = empty($data[2]) ? "0" : $data[2];
            $DI_YEAR = empty($data[3]) ? "0" : $data[3];
            $AR_CODE = empty($data[4]) ? "-" : $data[4];
            $SKU_CODE = empty($data[5]) ? "-" : $data[5];
            $SKU_NAME = empty($data[6]) ? "-" : $data[6];
            $DETAIL = empty($data[7]) ? "-" : $data[7];
            $BRAND = empty($data[8]) ? "-" : $data[8];
            $DI_REF = empty($data[9]) ? "-" : $data[9];
            $AR_NAME = empty($data[10]) ? "-" : $data[10];

            $SALE_NAME = empty($data[11]) ? "-" : $data[11];
            $TAKE_NAME = empty($data[12]) ? "-" : $data[12];
            $SALE_NAME_BLANK = empty($data[13]) ? "-" : $data[13];
            $TAKE_NAME_BLANK = empty($data[14]) ? "-" : $data[14];

            $TRD_QTY = empty($data[15]) ? "0" : $data[15];
            $TRD_PRC = empty($data[16]) ? "0" : $data[16];
            $TRD_DISCOUNT = empty($data[17]) ? "0" : $data[17];
            $TRD_TOTAL_PRICE = empty($data[18]) ? "0" : $data[18];
            $TRD_VAT = empty($data[19]) ? "0" : $data[19];
            $TRD_AMOUNT_PRICE = empty($data[20]) ? "0" : $data[20];
            $TRD_PER_POINT = empty($data[21]) ? "0" : $data[21];
            $TRD_TOTAL_POINT = empty($data[22]) ? "0" : $data[22];

            $WL_CODE = (empty($data[23]) || $data[23] === "0") ? "-" : $data[23];
            $TRD_Q_FREE = (empty($data[24]) || $data[24] === "0") ? "-" : $data[24];
            $TRD_AMPHUR = (empty($data[25]) || $data[25] === "0") ? "-" : $data[25];
            $TRD_PROVINCE = (empty($data[26]) || $data[26] === "0") ? "-" : $data[26];
            $TRD_MARK = (empty($data[27]) || $data[27] === "0") ? "-" : $data[27];

            $TRD_U_POINT = empty($data[28]) ? "0" : $data[28];
            $TRD_R_POINT = empty($data[29]) ? "0" : $data[29];
            $TRD_S_POINT = empty($data[30]) ? "0" : $data[30];
            $TRD_T_POINT = empty($data[31]) ? "0" : $data[31];

            $TRD_COMPARE = (empty($data[32]) || $data[32] === "0") ? "-" : $data[32];
            $TRD_SHOP = (empty($data[33]) || $data[33] === "0") ? "-" : $data[33];
            $TRD_BY_MOBAPP = (empty($data[34]) || $data[34] === "0") ? "-" : $data[34];
            $TRD_YEAR_OLD = (empty($data[35]) || $data[35] === "0") ? "-" : $data[35];
            $SKU_CAT = (empty($data[36]) || $data[36] === "0") ? "-" : $data[36];


            $DI_MONTH = convertMonthToNumberSingle(trim($DI_MONTH_NAME));
            $DI_DATE = formatNumber($DI_DAY, 2) . "-" . formatNumber($DI_MONTH, 2) . "-" . $DI_YEAR;

            $statement = $conn->prepare("SELECT COUNT(*) FROM " . $table_name . " WHERE DI_DAY = ? AND DI_MONTH_NAME = ? 
            AND DI_YEAR = ? AND DI_REF = ? AND AR_CODE = ? AND SKU_CODE = ? AND SKU_CAT = ? AND DETAIL = ?
            AND WL_CODE = ? AND TRD_QTY = ? AND TRD_PRC = ? AND TRD_AMOUNT_PRICE = ? AND TRD_SEQ = ?");
            $statement->execute([$DI_DAY, $DI_MONTH_NAME, $DI_YEAR, $DI_REF, $AR_CODE, $SKU_CODE, $SKU_CAT, $DETAIL, $WL_CODE
                , $TRD_QTY, $TRD_PRC, $TRD_AMOUNT_PRICE, $totalRows]);
            $row = $statement->fetchColumn();
            if ($row === 0) {

                $sql_find = "SELECT * FROM ims_ar_shop WHERE ar_code = ?";
                $query_find = $conn->prepare($sql_find);
                $query_find->execute([$AR_CODE]);

                $shop_type = $query_find->rowCount() > 0 ? "SHOP" : "ร้านทั่วไป";
                $shop_type_status = $query_find->rowCount() > 0 ? "Y" : "N";

                // Insert new record
                $sql_insert = "INSERT INTO $table_name (DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, SKU_NAME, DETAIL
        , BRAND, DI_REF, AR_NAME, SALE_NAME, TAKE_NAME, TRD_QTY, TRD_PRC, TRD_DISCOUNT, TRD_TOTAL_PRICE, TRD_VAT, TRD_AMOUNT_PRICE
        , TRD_PER_POINT, TRD_TOTAL_POINT, WL_CODE, TRD_Q_FREE, TRD_AMPHUR, TRD_PROVINCE, TRD_MARK, TRD_U_POINT, TRD_R_POINT
        , TRD_S_POINT, TRD_T_POINT, TRD_COMPARE, TRD_SHOP, TRD_BY_MOBAPP, TRD_YEAR_OLD, SKU_CAT,DI_MONTH,DI_DATE,seq_record,TRD_SEQ,SHOP_TYPE,SHOP_TYPE_STATUS,DI_DATE_BLANK,SALE_NAME_BLANK,TAKE_NAME_BLANK) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->execute([$DI_DAY, $DI_MONTH_NAME, $DI_YEAR, $AR_CODE, $SKU_CODE, $SKU_NAME, $DETAIL,
                    $BRAND, $DI_REF, $AR_NAME, $SALE_NAME, $TAKE_NAME, $TRD_QTY, $TRD_PRC,
                    $TRD_DISCOUNT, $TRD_TOTAL_PRICE, $TRD_VAT, $TRD_AMOUNT_PRICE, $TRD_PER_POINT,
                    $TRD_TOTAL_POINT, $WL_CODE, $TRD_Q_FREE, $TRD_AMPHUR, $TRD_PROVINCE, $TRD_MARK,
                    $TRD_U_POINT, $TRD_R_POINT, $TRD_S_POINT, $TRD_T_POINT, $TRD_COMPARE, $TRD_SHOP,
                    $TRD_BY_MOBAPP, $TRD_YEAR_OLD, $SKU_CAT, $DI_MONTH, $DI_DATE, $seq_record, $totalRows, $shop_type, $shop_type_status,$DI_DATE_BLANK,$SALE_NAME_BLANK,$TAKE_NAME_BLANK]);
                $importedRows++; // นับแถวที่นำเข้าสำเร็จ
                $status = "Y";
            }

            if ($SALE_NAME !== 'READY QUICK' && strpos($SALE_NAME, "RQ") === false && $TAKE_NAME !== 'READY QUICK' && strpos($TAKE_NAME, "RQ") === false) {
                for ($loop = 1; $loop <= 2; $loop++) {
                    if ($loop === 1) {
                        $f_name = $SALE_NAME;
                        $type = 'SALE'; // กำหนดค่า type เป็น SALE
                        $user_text = 'sale'; // กำหนดค่า text เป็น SALE
                        $account_type = "sale_user";
                    } else {
                        $f_name = $TAKE_NAME;
                        $type = 'TAKE'; // กำหนดค่า type เป็น TAKE
                        $user_text = 'take'; // กำหนดค่า text เป็น SALE
                        $account_type = "take";
                    }

                    if (!empty($f_name)) {
                        // ตรวจสอบว่ามี f_name อยู่ใน ims_sale_take_name หรือไม่
                        $stmt = $conn->prepare("SELECT f_name FROM ims_sale_take_name WHERE f_name = :f_name AND type = :type");
                        $stmt->bindParam(':f_name', $f_name, PDO::PARAM_STR);
                        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$result) {
                            // ดึง type_order ล่าสุดเฉพาะประเภทนั้น ๆ
                            $stmt = $conn->prepare("SELECT MAX(type_order) FROM ims_sale_take_name WHERE type = :type");
                            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                            $stmt->execute();
                            $latest_type_order = $stmt->fetchColumn();

                            // ตรวจสอบลำดับล่าสุด และกำหนดลำดับใหม่
                            if ($latest_type_order === null) {
                                // หากไม่มีลำดับ ให้เริ่มที่ 10
                                $new_type_order = 1;
                            } else {
                                // เพิ่ม 1 จากลำดับล่าสุด
                                $new_type_order = (int)$latest_type_order + 1;
                            }


                            $formattedUserOrder = str_pad($new_type_order, 3, '0', STR_PAD_LEFT);
                            $user_id = $user_text . $formattedUserOrder . "@sac.com";
                            $default_password = "123456";
                            $password = password_hash($default_password, PASSWORD_DEFAULT);
                            $user_status = 'Active';
                            $u_status = 'Y';
                            $last_name = "SAC";
                            $picture = "img/icon/user-001.png";


                            // INSERT ข้อมูลใหม่ลงใน ims_sale_take_name
                            $stmt = $conn->prepare("INSERT INTO ims_sale_take_name (type, type_order, f_name, status) VALUES (:type, :type_order, :f_name, :u_status)");
                            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                            $stmt->bindParam(':type_order', $new_type_order, PDO::PARAM_INT);
                            $stmt->bindParam(':f_name', $f_name, PDO::PARAM_STR);
                            $stmt->bindParam(':u_status', $u_status, PDO::PARAM_STR);
                            $stmt->execute();

                            // INSERT ข้อมูลใหม่ลงใน ims_user เพื่อสร้าง User Login
                            $sql = "INSERT INTO ims_user(user_id,email,password,first_name,last_name,account_type,picture,status)
                            VALUES (:user_id,:email,:password,:first_name,:last_name,:account_type,:picture,:user_status)";
                            $query = $conn->prepare($sql);
                            $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                            $query->bindParam(':email', $user_id, PDO::PARAM_STR);
                            $query->bindParam(':password', $password, PDO::PARAM_STR);
                            $query->bindParam(':first_name', $f_name, PDO::PARAM_STR);
                            $query->bindParam(':last_name', $last_name, PDO::PARAM_STR);
                            $query->bindParam(':account_type', $account_type, PDO::PARAM_STR);
                            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
                            $query->bindParam(':user_status', $user_status, PDO::PARAM_STR);
                            $query->execute();

                            $sql_line_no_update = "SET @new_line_no = 0;
                            UPDATE ims_user
                            SET line_no = (@new_line_no := @new_line_no + 1)
                            ORDER BY id;";
                            $stmt_line_no_update = $conn->prepare($sql_line_no_update);
                            $stmt_line_no_update->execute();

                        }
                    }
                }
            }


        }

        $sql_update = "UPDATE " . $table_name . " SET DI_DAY = CAST(DI_DAY AS UNSIGNED) WHERE DI_DAY IN (01, 02, 03, 04, 05, 06, 07, 08, 09) ";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute();

        $sql_delete = "DELETE FROM " . $table_name . " WHERE DI_YEAR NOT REGEXP '^[0-9]+$' ";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->execute();


        $import_success = "$file_Upload \n\r $upload_status \n\r จำนวนที่ Upload จาก Excel : $totalRows รายการ \n\r นำเข้าใหม่สำเร็จ: $importedRows รายการ \n\r มีข้อมูลซ้ำ: $duplicateRows รายการ";
        if ($status === 'Y') {
            $sql_insert_log = "INSERT INTO log_import_data (screen_name,total_record,import_record,detail1,detail2,seq_record,create_by) VALUES (?,?,?,?,?,?,?)";
            $stmt_insert_log = $conn->prepare($sql_insert_log);
            $stmt_insert_log->execute([$table_name, $totalRows, $importedRows, $import_success, $file_Upload, $seq_record, $user_id]);
        }

        echo $import_success;

    } catch (Exception $e) {
        error_log("Error processing file: " . $e->getMessage());
        echo "Error processing file." . $e->getMessage();
    }
} else {
    echo "Error uploading file.";
}