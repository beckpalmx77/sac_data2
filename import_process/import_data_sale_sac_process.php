<?php
// Include necessary files for database connection and PhpSpreadsheet
include '../config/connect_db.php'; // Include your database connection
require '../vendor/autoload.php'; // Load PhpSpreadsheet library
include '../util/record_util.php'; // Include any utility files
include '../util/month_convert_util.php'; // Include any utility files

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];

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
            $DI_DAY = empty($row[0]) ? "-" : trim($row[0]);
            $DI_MONTH_NAME = empty($row[1]) ? "-" : trim($row[1]);
            $DI_MONTH = convertMonthToNumber($DI_MONTH_NAME);
            $DI_YEAR = empty($row[2]) ? "-" : trim($row[2]);
            $AR_CODE = empty($row[3]) ? "-" : trim($row[3]);
            $SKU_CODE = empty($row[4]) ? "-" : trim($row[4]);
            $SKU_NAME = empty($row[5]) ? "-" : trim($row[5]);
            $DETAIL = empty($row[6]) ? "-" : trim($row[6]);
            $BRAND = empty($row[7]) ? "-" : trim($row[7]);
            $AR_NAME = empty($row[8]) ? "-" : trim($row[8]);
            $SALE_NAME = empty($row[9]) ? "-" : trim($row[9]);
            $TAKE_NAME = empty($row[10]) ? "-" : trim($row[10]);
            $TRD_QTY = empty($row[11]) ? "0" : trim($row[11]);
            $TRD_PRC = empty($row[12]) ? "0" : trim($row[12]);
            $TRD_DISCOUNT = empty($row[13]) ? "0" : trim($row[13]);
            $TRD_TOTAL_PRICE = empty($row[14]) ? "0" : trim($row[14]);
            $TRD_VAT = empty($row[15]) ? "0" : trim($row[15]);
            $TRD_AMOUNT_PRICE = empty($row[16]) ? "0" : trim($row[16]);
            $TRD_PER_POINT = empty($row[17]) ? "0" : trim($row[17]);
            $TRD_TOTALPOINT = empty($row[18]) ? "0" : trim($row[18]);
            $WL_CODE = empty($row[19]) ? "-" : trim($row[19]);
            $TRD_Q_FREE = empty($row[20]) ? "0" : trim($row[20]);
            $TRD_AMPHUR = empty($row[21]) ? "-" : trim($row[21]);
            $TRD_PROVINCE = empty($row[22]) ? "-" : trim($row[22]);
            $TRD_MARK = empty($row[23]) ? "-" : trim($row[23]);
            $TRD_U_POINT = empty($row[24]) ? "0" : trim($row[24]);
            $TRD_R_POINT = empty($row[25]) ? "0" : trim($row[25]);
            $TRD_S_POINT = empty($row[26]) ? "0" : trim($row[26]);
            $TRD_T_POINT = empty($row[27]) ? "0" : trim($row[27]);
            $TRD_COMPARE = empty($row[28]) ? "-" : trim($row[28]);
            $TRD_SHOP = empty($row[29]) ? "-" : trim($row[29]);
            $TRD_BY_MOBAPP = empty($row[30]) ? "-" : trim($row[30]);
            $TRD_YEAR_OLD = empty($row[31]) ? "-" : trim($row[31]);
            $SKU_CAT = empty($row[32]) ? "-" : trim($row[32]);
            $DI_REF = empty($row[33]) ? "-" : trim($row[33]);

            // Check for duplicates based on the unique combination of DI_DAY, DI_MONTH, DI_YEAR, DI_REF, AR_CODE, SKU_CODE, WL_CODE, TRD_QTY
            $statement = $conn->prepare("SELECT COUNT(*) FROM " . $table_name . " WHERE DI_DAY = ? AND DI_MONTH = ? AND DI_YEAR = ? AND DI_REF = ? 
                                    AND AR_CODE = ? AND SKU_CODE = ? AND WL_CODE = ? AND TRD_QTY = ?");
            $statement->execute([$DI_DAY, $DI_MONTH, $DI_YEAR, $DI_REF, $AR_CODE, $SKU_CODE, $WL_CODE, $TRD_QTY]);

            if ((int)$statement->fetchColumn() === 0) {
                // Prepare and execute the SQL Insert statement
                $stmt_insert = $conn->prepare("INSERT INTO "  . $table_name . " (DI_DAY, DI_MONTH, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, SKU_NAME, DETAIL, BRAND, AR_NAME, 
                    SALE_NAME, TAKE_NAME, TRD_QTY, TRD_PRC, TRD_DISCOUNT, TRD_TOTAL_PRICE, TRD_VAT, TRD_AMOUNT_PRICE, 
                    TRD_PER_POINT, TRD_TOTALPOINT, WL_CODE, TRD_Q_FREE, TRD_AMPHUR, TRD_PROVINCE, TRD_MARK, 
                    TRD_U_POINT, TRD_R_POINT, TRD_S_POINT, TRD_T_POINT, TRD_COMPARE, TRD_SHOP, TRD_BY_MOBAPP, 
                    TRD_YEAR_OLD, SKU_CAT, DI_REF) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt_insert->execute([
                    $DI_DAY, $DI_MONTH, $DI_MONTH_NAME, $DI_YEAR, $AR_CODE, $SKU_CODE, $SKU_NAME, $DETAIL, $BRAND, $AR_NAME,
                    $SALE_NAME, $TAKE_NAME, $TRD_QTY, $TRD_PRC, $TRD_DISCOUNT, $TRD_TOTAL_PRICE, $TRD_VAT, $TRD_AMOUNT_PRICE,
                    $TRD_PER_POINT, $TRD_TOTALPOINT, $WL_CODE, $TRD_Q_FREE, $TRD_AMPHUR, $TRD_PROVINCE, $TRD_MARK,
                    $TRD_U_POINT, $TRD_R_POINT, $TRD_S_POINT, $TRD_T_POINT, $TRD_COMPARE, $TRD_SHOP, $TRD_BY_MOBAPP,
                    $TRD_YEAR_OLD, $SKU_CAT, $DI_REF
                ]);

                $importedRows++;
            } else {
                // Prepare and execute the SQL Update statement
                $stmt_update = $conn->prepare("UPDATE "  . $table_name . " SET DI_MONTH = ?, DI_MONTH_NAME = ?, SKU_NAME = ?, DETAIL = ?, BRAND = ?, AR_NAME = ?, SALE_NAME = ?, TAKE_NAME = ?, 
                        TRD_PRC = ?, TRD_DISCOUNT = ?, TRD_TOTAL_PRICE = ?, TRD_VAT = ?, TRD_AMOUNT_PRICE = ?, 
                        TRD_PER_POINT = ?, TRD_TOTALPOINT = ?, TRD_Q_FREE = ?, TRD_AMPHUR = ?, TRD_PROVINCE = ?, TRD_MARK = ?, 
                        TRD_U_POINT = ?, TRD_R_POINT = ?, TRD_S_POINT = ?, TRD_T_POINT = ?, TRD_COMPARE = ?, TRD_SHOP = ?, 
                        TRD_BY_MOBAPP = ?, TRD_YEAR_OLD = ?, SKU_CAT = ?, DI_REF = ? 
                    WHERE DI_DAY = ? AND DI_MONTH = ? AND DI_YEAR = ? AND DI_REF = ?
                        AND AR_CODE = ? AND SKU_CODE = ? AND WL_CODE = ? AND TRD_QTY = ?");

                $stmt_update->execute([
                    $DI_MONTH, $DI_MONTH_NAME, $SKU_NAME, $DETAIL, $BRAND, $AR_NAME, $SALE_NAME, $TAKE_NAME, $TRD_PRC, $TRD_DISCOUNT,
                    $TRD_TOTAL_PRICE, $TRD_VAT, $TRD_AMOUNT_PRICE, $TRD_PER_POINT, $TRD_TOTALPOINT, $TRD_Q_FREE,
                    $TRD_AMPHUR, $TRD_PROVINCE, $TRD_MARK, $TRD_U_POINT, $TRD_R_POINT, $TRD_S_POINT, $TRD_T_POINT,
                    $TRD_COMPARE, $TRD_SHOP, $TRD_BY_MOBAPP, $TRD_YEAR_OLD, $SKU_CAT, $DI_REF,
                    $DI_DAY, $DI_MONTH, $DI_YEAR, $DI_REF, $AR_CODE, $SKU_CODE, $WL_CODE, $TRD_QTY
                ]);

                $duplicateRows++;
            }
        }

        echo "Import completed. Imported rows: $importedRows, Duplicated rows: $duplicateRows.";

    } catch (Exception $e) {
        error_log("Error processing file: " . $e->getMessage());
        echo "Error processing file.";
    }
} else {
    echo "Error uploading file.";
}

