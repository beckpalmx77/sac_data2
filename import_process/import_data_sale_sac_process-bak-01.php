<?php
// Include necessary files for database connection and PhpSpreadsheet
include '../config/connect_db.php'; // Include your database connection
require '../vendor/autoload.php'; // Load PhpSpreadsheet library
include '../util/record_util.php'; // Include any utility files
include '../util/month_convert_util.php'; // Include any utility files

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileType = mime_content_type($fileTmp); // ตรวจสอบ MIME type

    if ($fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
        $fileType !== 'application/vnd.ms-excel') {
        echo "Invalid file type.";
        exit;
    }

    try {
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $table_name = "ims_data_sale_sac_all";
        $importedRows = 0;
        $duplicateRows = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header row

            // Map data from Excel row to your table structure
            $data = [];
            for ($i = 0; $i <= 32; $i++) {
                $data[$i] = empty($row[$i]) ? "-" : str_replace(",", "", $row[$i]);
                $txt .= $i . " = " . $data[$i] . " | " ;
                $my_file = fopen("import_wh_param-0.txt", "w") or die("Unable to open file!");
                fwrite($my_file, $txt);
                fclose($my_file);
            }

            $DI_DAY = $data[0];
            $DI_MONTH_NAME = $data[1];
            $DI_MONTH = convertMonthToNumber($DI_MONTH_NAME);
            $DI_YEAR = $data[2];
            $AR_CODE = $data[3];
            $SKU_CODE = $data[4];
            $DI_REF = $data[8];
            $WL_CODE = $data[20];
            $TRD_QTY = $data[12];

            // Validate required fields
            //if ($DI_DAY !== "-" && $DI_MONTH !== "-" && $DI_YEAR !== "-" && $AR_CODE !== "-" && $SKU_CODE !== "-") {
                // Check for duplicates
                $statement = $conn->prepare("SELECT COUNT(*) FROM " . $table_name . " WHERE DI_DAY = ? AND DI_MONTH_NAME = ? AND DI_YEAR = ? AND DI_REF = ? 
                                    AND AR_CODE = ? AND SKU_CODE = ? AND WL_CODE = ? AND TRD_QTY = ?");
                $statement->execute([$DI_DAY, $DI_MONTH_NAME, $DI_YEAR, $DI_REF, $AR_CODE, $SKU_CODE, $WL_CODE, $TRD_QTY]);
                $row = $statement->fetchColumn();
                if ($row=== 0) {
                    // Insert new record
                    $sql_insert = "INSERT INTO $table_name (DI_DAY,DI_MONTH_NAME,DI_YEAR,AR_CODE,SKU_CODE,SKU_NAME,DETAIL
                    ,BRAND,DI_REF,AR_NAME,SALE_NAME,TAKE_NAME,TRD_QTY,TRD_PRC,TRD_DISCOUNT,TRD_TOTAL_PRICE,TRD_VAT,TRD_AMOUNT_PRICE
                    ,TRD_PER_POINT,TRD_TOTALPOINT,WL_CODE,TRD_Q_FREE,TRD_AMPHUR,TRD_PROVINCE,TRD_MARK,TRD_U_POINT,TRD_R_POINT
                    ,TRD_S_POINT,TRD_T_POINT,TRD_COMPARE,TRD_SHOP,TRD_BY_MOBAPP,TRD_YEAR_OLD,SKU_CAT) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $txt = $row . " | row " . $sql_insert ;
                    $my_file = fopen("import_wh_param_row.txt", "w") or die("Unable to open file!");
                    fwrite($my_file, $txt);
                    fclose($my_file);

                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->execute(array_values($data));

                    $txt = $DI_DAY . " | " . $DI_MONTH_NAME . " | " . $DI_MONTH . " | " . $DI_YEAR . " | " . $AR_CODE . " | " . $SKU_CODE . " | " . $DI_REF . " | " . $WL_CODE
                        . " | " . $TRD_QTY;
                    $my_file = fopen("import_wh_param_ins.txt", "w") or die("Unable to open file!");
                    fwrite($my_file, $txt);
                    fclose($my_file);
                    $importedRows++;

                } else {

                    // Update existing record
                    $sql_update = "UPDATE $table_name SET DI_MONTH_NAME = ?, SKU_NAME = ?, DETAIL = ?, BRAND = ?
                    , AR_NAME = ?, SALE_NAME = ?, TAKE_NAME = ?, TRD_PRC = ?, TRD_DISCOUNT = ?, TRD_TOTAL_PRICE = ?, TRD_VAT = ?
                    , TRD_AMOUNT_PRICE = ?, TRD_PER_POINT = ?, TRD_TOTAL_POINT = ?, TRD_Q_FREE = ?, TRD_AMPHUR = ?
                    , TRD_PROVINCE = ?, TRD_MARK = ?, TRD_U_POINT = ?, TRD_R_POINT = ?, TRD_S_POINT = ?, TRD_T_POINT = ?
                    , TRD_COMPARE = ?, TRD_SHOP = ?, TRD_BY_MOBAPP = ?, TRD_YEAR_OLD = ?, SKU_CAT = ?
                    , DI_REF = ? WHERE DI_DAY = ? AND DI_MONTH_NAME = ? AND DI_YEAR = ? AND AR_CODE = ? AND SKU_CODE = ?";
                    //$stmt_update = $conn->prepare($sql_update);
                    //$stmt_update->execute(array_merge(array_slice($data, 1), [$DI_DAY, $DI_MONTH_NAME, $DI_YEAR, $AR_CODE, $SKU_CODE]));
                    $duplicateRows++;

                }
            //}
        }

        echo "Import completed. Imported rows: $importedRows, Duplicated rows: $duplicateRows.";

    } catch (Exception $e) {
        error_log("Error processing file: " . $e->getMessage());
        $txt = $e->getMessage();
        $my_file = fopen("import_wh_param_err.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
        echo "Error processing file.";
    }
} else {
    echo "Error uploading file.";
}
