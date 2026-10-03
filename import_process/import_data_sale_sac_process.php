<?php
session_start();
error_reporting(0); // ปิด Error หน้าเว็บ (แต่ควรดู log ถ้ามีปัญหา)
//error_reporting(E_ALL); // เปิดบรรทัดนี้เมื่อต้องการ Debug

// 1. ปรับแต่งค่า Server เพื่อรองรับไฟล์ใหญ่และลดโอกาส Timeout
ini_set('memory_limit', '2048M');
set_time_limit(0); // สำคัญ: ปลดล็อกเวลาการทำงานให้รันได้ยาวนานจนกว่าจะเสร็จ

include '../config/connect_db.php';
require '../vendor/autoload.php';
include '../util/record_util.php';
include '../util/month_convert_util.php';
include '../util/check_format_number.php';
include '../util/GetData.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// ตรวจสอบว่ามีการส่งไฟล์เข้ามาหรือไม่
if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $file_Upload = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileType = mime_content_type($fileTmp);

    // กำหนดโฟลเดอร์ปลายทาง
    $uploadDir = '../uploads/';

    // 2. ตรวจสอบและสร้างโฟลเดอร์ uploads อัตโนมัติหากไม่มี
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            echo "Error: ไม่สามารถสร้างโฟลเดอร์ uploads ได้ (กรุณาตรวจสอบ Permission ของ Server)";
            exit;
        }
    }

    $uploadFile = $uploadDir . basename($file_Upload);

    // ตรวจสอบ MIME type (รองรับ xlsx และ xls)
    if ($fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
        $fileType !== 'application/vnd.ms-excel') {
        echo "Invalid file type. อนุญาตเฉพาะไฟล์ Excel (.xlsx, .xls)";
        exit;
    }

    // ย้ายไฟล์
    if (move_uploaded_file($fileTmp, $uploadFile)) {
        $upload_status = "Upload File สำเร็จ";
    } else {
        echo "ผิดพลาด Upload File ไม่สำเร็จ (Permission Denied หรือ File Size เกิน)";
        exit;
    }

    try {
        // Ensure database indexes exist for high performance
        try { $conn->exec("CREATE INDEX idx_di_date ON ims_data_sale_sac_all (DI_YEAR, DI_MONTH_NAME, DI_DAY)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_di_ref ON ims_data_sale_sac_all (DI_REF)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_ar_code ON ims_data_sale_sac_all (AR_CODE)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_sku_code ON ims_data_sale_sac_all (SKU_CODE)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_ar_code ON ims_ar_shop (ar_code)"); } catch (Exception $e) {}

        // 3. ใช้ Reader แบบ ReadDataOnly เพื่อประหยัด RAM
        $fileTypeIdentified = IOFactory::identify($uploadFile);
        $reader = IOFactory::createReader($fileTypeIdentified);
        $reader->setReadDataOnly(true); // อ่านเฉพาะข้อมูล ไม่เอา Format สี/ฟอนต์
        $spreadsheet = $reader->load($uploadFile);

        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $user_id = $_SESSION['user_id'];
        $table_name = "ims_data_sale_sac_all";
        $importedRows = 0;
        $duplicateRows = 0;
        $totalRows = 0;
        $status = "";

        $str = rand();
        $seq_record = md5($str);

        // ตัวแปร Cache
        $ar_shop_cache = [];
        $has_new_user = false; // Flag ตรวจสอบว่ามี user ใหม่หรือไม่

        // ดึงข้อมูล User/Sale/Take ทั้งหมดไว้ใน Memory ก่อนเข้า Loop เพื่อลด Query (ความเร็วเพิ่มขึ้นมหาศาล)
        $stmt_names = $conn->query("SELECT f_name, type FROM ims_sale_take_name");
        $existing_names = [];
        while ($r = $stmt_names->fetch(PDO::FETCH_ASSOC)) {
            $existing_names[$r['type']][$r['f_name']] = true;
        }

        // ดึงข้อมูลความสัมพันธ์ User สำหรับตรวจสอบบัญชีผู้ใช้งาน
        $stmt_users = $conn->query("SELECT first_name, account_type FROM ims_user");
        $existing_users = [];
        while ($r = $stmt_users->fetch(PDO::FETCH_ASSOC)) {
            $existing_users[$r['account_type']][$r['first_name']] = true;
        }

        // รวบรวมวันที่ที่มีทั้งหมดใน Excel เพื่อดึงข้อมูลซ้ำมาเช็คทีเดียวในหน่วยความจำ
        $unique_dates = [];
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if (trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) continue;

            $DI_DAY = empty($row[1]) ? "0" : trim($row[1]);
            $DI_MONTH_NAME = empty($row[2]) ? "0" : trim($row[2]);
            $DI_YEAR = empty($row[3]) ? "0" : trim($row[3]);
            
            $date_key = "$DI_DAY|$DI_MONTH_NAME|$DI_YEAR";
            $unique_dates[$date_key] = [
                'day' => $DI_DAY,
                'month' => $DI_MONTH_NAME,
                'year' => $DI_YEAR
            ];
        }

        // คิวรี่ข้อมูลทั้งหมดที่อาจจะซ้ำตามวันเวลาที่มีใน Excel มาไว้ในหน่วยความจำ
        $existing_records = [];
        if (!empty($unique_dates)) {
            $conditions = [];
            $params = [];
            foreach ($unique_dates as $ud) {
                $conditions[] = "(DI_DAY = ? AND DI_MONTH_NAME = ? AND DI_YEAR = ?)";
                $params[] = $ud['day'];
                $params[] = $ud['month'];
                $params[] = $ud['year'];
            }
            $sql = "SELECT DI_DAY, DI_MONTH_NAME, DI_YEAR, DI_REF, AR_CODE, SKU_CODE, SKU_CAT, DETAIL, WL_CODE, TRD_QTY, TRD_PRC, TRD_AMOUNT_PRICE, TRD_SEQ 
                    FROM $table_name 
                    WHERE " . implode(" OR ", $conditions);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $key = implode('|', [
                    trim($r['DI_DAY']),
                    trim($r['DI_MONTH_NAME']),
                    trim($r['DI_YEAR']),
                    trim($r['DI_REF']),
                    trim($r['AR_CODE']),
                    trim($r['SKU_CODE']),
                    trim($r['SKU_CAT']),
                    trim($r['DETAIL']),
                    trim($r['WL_CODE']),
                    trim($r['TRD_QTY']),
                    trim($r['TRD_PRC']),
                    trim($r['TRD_AMOUNT_PRICE']),
                    trim($r['TRD_SEQ'])
                ]);
                $existing_records[$key] = true;
            }
        }

        // Prepare Statements Outside Loop for High Performance
        $query_find = $conn->prepare("SELECT COUNT(*) FROM ims_ar_shop WHERE ar_code = ?");
        $sql_insert = "INSERT INTO $table_name (DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, SKU_NAME, DETAIL
        , BRAND, DI_REF, AR_NAME, SALE_NAME, TAKE_NAME, TRD_QTY, TRD_PRC, TRD_DISCOUNT, TRD_TOTAL_PRICE, TRD_VAT, TRD_AMOUNT_PRICE
        , TRD_PER_POINT, TRD_TOTAL_POINT, WL_CODE, TRD_Q_FREE, TRD_AMPHUR, TRD_PROVINCE, ZONE, TRD_U_POINT, TRD_R_POINT
        , TRD_S_POINT, TRD_T_POINT, TRD_COMPARE, TRD_SHOP, TRD_BY_MOBAPP, TRD_YEAR_OLD, SKU_CAT,DI_MONTH,DI_DATE,seq_record,TRD_SEQ,SHOP_TYPE,SHOP_TYPE_STATUS,DI_DATE_BLANK,SALE_NAME_BLANK,TAKE_NAME_BLANK) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt_insert = $conn->prepare($sql_insert);

        // 4. เริ่ม Transaction (Database ทำงานเร็วขึ้น 10-100 เท่า)
        $conn->beginTransaction();

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // ข้าม Header

            // เช็คแถวว่าง
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if (trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) continue;

            $totalRows++;

            // Map ข้อมูล Excel
            $data = [];
            for ($i = 0; $i <= 36; $i++) {
                $value = isset($row[$i]) && trim($row[$i]) !== '' ? $row[$i] : "0";
                $data[$i] = str_replace(["#", ","], "", ($value === "#N/A" || $value === "#REF!" ? "0" : $value));
            }

            // กำหนดตัวแปร
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

            // ตรวจสอบข้อมูลซ้ำในหน่วยความจำ (เร็วกว่าการ Query ทีละแถวอย่างมาก)
            $check_key = implode('|', [
                $DI_DAY,
                $DI_MONTH_NAME,
                $DI_YEAR,
                $DI_REF,
                $AR_CODE,
                $SKU_CODE,
                $SKU_CAT,
                $DETAIL,
                $WL_CODE,
                $TRD_QTY,
                $TRD_PRC,
                $TRD_AMOUNT_PRICE,
                $totalRows
            ]);

            if (!isset($existing_records[$check_key])) {

                // ใช้ Cache เช็ค Shop Type ลดการ Query ซ้ำๆ
                if (isset($ar_shop_cache[$AR_CODE])) {
                    $shop_data = $ar_shop_cache[$AR_CODE];
                } else {
                    $query_find->execute([$AR_CODE]);
                    $is_shop = (int)$query_find->fetchColumn() > 0;
                    $shop_data = ['type' => $is_shop ? "SHOP" : "ร้านทั่วไป", 'status' => $is_shop ? "Y" : "N"];
                    $ar_shop_cache[$AR_CODE] = $shop_data;
                }

                $shop_type = $shop_data['type'];
                $shop_type_status = $shop_data['status'];

                // Insert ข้อมูลการขาย
                $stmt_insert->execute([$DI_DAY, $DI_MONTH_NAME, $DI_YEAR, $AR_CODE, $SKU_CODE, $SKU_NAME, $DETAIL,
                    $BRAND, $DI_REF, $AR_NAME, $SALE_NAME, $TAKE_NAME, $TRD_QTY, $TRD_PRC,
                    $TRD_DISCOUNT, $TRD_TOTAL_PRICE, $TRD_VAT, $TRD_AMOUNT_PRICE, $TRD_PER_POINT,
                    $TRD_TOTAL_POINT, $WL_CODE, $TRD_Q_FREE, $TRD_AMPHUR, $TRD_PROVINCE, $TRD_MARK,
                    $TRD_U_POINT, $TRD_R_POINT, $TRD_S_POINT, $TRD_T_POINT, $TRD_COMPARE, $TRD_SHOP,
                    $TRD_BY_MOBAPP, $TRD_YEAR_OLD, $SKU_CAT, $DI_MONTH, $DI_DATE, $seq_record, $totalRows, $shop_type, $shop_type_status,$DI_DATE_BLANK,$SALE_NAME_BLANK,$TAKE_NAME_BLANK]);
                $importedRows++;
                $status = "Y";
            } else {
                $duplicateRows++;
            }

            // จัดการ User (Sale/Take)
            if ($SALE_NAME !== 'READY QUICK' && strpos($SALE_NAME, "RQ") === false && $TAKE_NAME !== 'READY QUICK' && strpos($TAKE_NAME, "RQ") === false) {
                for ($loop = 1; $loop <= 2; $loop++) {
                    if ($loop === 1) {
                        $f_name = $SALE_NAME;
                        $type = 'SALE';
                        $user_text = 'sale';
                        $account_type = "sale_user";
                    } else {
                        $f_name = $TAKE_NAME;
                        $type = 'TAKE';
                        $user_text = 'take';
                        $account_type = "take";
                    }

                    if (!empty($f_name)) {
                        if (!isset($existing_names[$type][$f_name])) {
                            // ถ้ายังไม่มี User ให้สร้างใหม่
                            $stmt = $conn->prepare("SELECT MAX(type_order) FROM ims_sale_take_name WHERE type = :type");
                            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                            $stmt->execute();
                            $latest_type_order = $stmt->fetchColumn();
                            $new_type_order = ($latest_type_order === null) ? 1 : (int)$latest_type_order + 1;

                            $formattedUserOrder = str_pad($new_type_order, 3, '0', STR_PAD_LEFT);
                            $user_id_acc = $user_text . $formattedUserOrder . "@sac.com";
                            $password = password_hash("123456", PASSWORD_DEFAULT);
                            $user_status = 'Active';
                            $u_status = 'Y';
                            $picture = "img/icon/user-001.png";

                            $stmt = $conn->prepare("INSERT INTO ims_sale_take_name (type, type_order, f_name, status) VALUES (:type, :type_order, :f_name, :u_status)");
                            $stmt->execute([':type' => $type, ':type_order' => $new_type_order, ':f_name' => $f_name, ':u_status' => $u_status]);

                            // อัปเดต Cache ใน Memory
                            $existing_names[$type][$f_name] = true;

                            if (!isset($existing_users[$account_type][$f_name])) {
                                $sql = "INSERT INTO ims_user(user_id,email,password,first_name,last_name,account_type,picture,status)
                                VALUES (:user_id,:email,:password,:first_name,'SAC',:account_type,:picture,:user_status)";
                                $query = $conn->prepare($sql);
                                $query->execute([
                                    ':user_id' => $user_id_acc, ':email' => $user_id_acc, ':password' => $password,
                                    ':first_name' => $f_name, ':account_type' => $account_type,
                                    ':picture' => $picture, ':user_status' => $user_status
                                ]);

                                // อัปเดต Cache ใน Memory
                                $existing_users[$account_type][$f_name] = true;
                                $has_new_user = true;
                            }
                        }
                    }
                }
            }
        }

        // 6. Commit Transaction เดียวจบ (เร็วมาก)
        $conn->commit();

        // 7. อัปเดต Line No นอก Loop (ทำครั้งเดียวถ้ามี user ใหม่)
        if ($has_new_user) {
            $sql_line_no_update = "SET @new_line_no = 0; UPDATE ims_user SET line_no = (@new_line_no := @new_line_no + 1) ORDER BY id;";
            $conn->exec($sql_line_no_update);
        }

        // Clean Data
        $sql_update = "UPDATE " . $table_name . " SET DI_DAY = CAST(DI_DAY AS UNSIGNED) WHERE DI_DAY IN (01, 02, 03, 04, 05, 06, 07, 08, 09) ";
        $conn->exec($sql_update);
        $sql_delete = "DELETE FROM " . $table_name . " WHERE DI_YEAR NOT REGEXP '^[0-9]+$' ";
        $conn->exec($sql_delete);

        $import_success = "$file_Upload \n\r $upload_status \n\r จำนวนที่ Upload จาก Excel : $totalRows รายการ \n\r นำเข้าใหม่สำเร็จ: $importedRows รายการ \n\r มีข้อมูลซ้ำ: $duplicateRows รายการ";

        if ($status === 'Y') {
            $sql_insert_log = "INSERT INTO log_import_data (screen_name,total_record,import_record,detail1,detail2,seq_record,create_by) VALUES (?,?,?,?,?,?,?)";
            $stmt_insert_log = $conn->prepare($sql_insert_log);
            $stmt_insert_log->execute([$table_name, $totalRows, $importedRows, $import_success, $file_Upload, $seq_record, $user_id]);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'file_name' => $file_Upload,
            'upload_status' => $upload_status,
            'total_rows' => $totalRows,
            'imported_rows' => $importedRows,
            'duplicate_rows' => $duplicateRows,
            'message' => $import_success
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error processing file: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Error processing file: " . $e->getMessage()
        ]);
    }
} else {
    // 8. แสดง Error อย่างละเอียดเพื่อ Debug บน Windows Server
    $msg = "Error uploading file. ";
    if (empty($_FILES)) {
        $msg .= "สาเหตุ: ไม่พบข้อมูลไฟล์ (เป็นไปได้ว่าไฟล์ใหญ่เกินค่า post_max_size ใน php.ini)";
    } else {
        $msg .= "Error Code: " . $_FILES['excelFile']['error'] . ". ";
        switch ($_FILES['excelFile']['error']) {
            case UPLOAD_ERR_INI_SIZE: $msg .= "สาเหตุ: ไฟล์ใหญ่เกินค่า upload_max_filesize ใน php.ini"; break;
            case UPLOAD_ERR_FORM_SIZE: $msg .= "สาเหตุ: ไฟล์ใหญ่เกินค่า MAX_FILE_SIZE ใน HTML Form"; break;
            case UPLOAD_ERR_PARTIAL: $msg .= "สาเหตุ: อัปโหลดไฟล์มาไม่ครบ"; break;
            case UPLOAD_ERR_NO_FILE: $msg .= "สาเหตุ: ไม่มีการเลือกไฟล์"; break;
            case UPLOAD_ERR_NO_TMP_DIR: $msg .= "สาเหตุ: Server หาโฟลเดอร์ชั่วคราว (Temp) ไม่เจอ"; break;
            case UPLOAD_ERR_CANT_WRITE: $msg .= "สาเหตุ: Server เขียนไฟล์ลง Disk ไม่ได้ (ติด Permission)"; break;
            default: $msg .= "สาเหตุ: Unknown error"; break;
        }
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $msg
    ]);
}