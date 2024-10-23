<?php

include('../config/connect_db.php');  // เชื่อมต่อกับฐานข้อมูล

if (isset($_POST['action']) && $_POST['action'] === 'DELETE_BY_DATE') {

    if (isset($_POST['DI_DATE'])) {
        $di_date = $_POST['DI_DATE'];

        // Query ลบข้อมูลตามวันที่
        $sql = "DELETE FROM ims_data_sale_sac_all WHERE DI_DATE = :di_date";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':di_date', $di_date, PDO::PARAM_STR);
/*
        $myfile = fopen("ad-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $sql . " | " . $di_date);
        fclose($myfile);
*/
        if ($stmt->execute()) {

            $delete_success = "$file_Upload \n\r $upload_status \n\r จำนวนที่ Upload จาก Excel : $totalRows รายการ \n\r นำเข้าใหม่สำเร็จ: $importedRows รายการ \n\r มีข้อมูลซ้ำ: $duplicateRows รายการ";

                $sql_insert_log = "INSERT INTO log_import_data (screen_name,total_record,import_record,detail1,detail2,seq_record,create_by) VALUES (?,?,?,?,?,?,?)";
                $stmt_insert_log = $conn->prepare($sql_insert_log);
                $stmt_insert_log->execute([$table_name, $totalRows, $importedRows, $import_success, $file_Upload, $seq_record, $user_id]);


            echo "ลบข้อมูลสำเร็จ";

        } else {
            echo "เกิดข้อผิดพลาดในการลบข้อมูล";
        }
    }

}
