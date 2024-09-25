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
            $DI_DAY = empty($row[0]) || $row[0] === null || $row[0] === "" ? "-" : str_replace(",", "", $row[0]);
            $DI_MONTH_NAME = empty($row[1]) || $row[1] === null || $row[1] === "" ? "-" : str_replace(",", "", $row[1]);
            $DI_MONTH = convertMonthToNumber($DI_MONTH_NAME);
            $DI_YEAR = empty($row[2]) || $row[2] === null || $row[2] === "" ? "-" : str_replace(",", "", $row[2]);
            $AR_CODE = empty($row[3]) || $row[3] === null || $row[3] === "" ? "-" : str_replace(",", "", $row[3]);
            $SKU_CODE = empty($row[4]) || $row[4] === null || $row[4] === "" ? "-" : str_replace(",", "", $row[4]);
            $SKU_NAME = empty($row[5]) || $row[5] === null || $row[5] === "" ? "-" : str_replace(",", "", $row[5]);
            $DETAIL = empty($row[6]) || $row[6] === null || $row[6] === "" ? "-" : str_replace(",", "", $row[6]);
            $BRAND = empty($row[7]) || $row[7] === null || $row[7] === "" ? "-" : str_replace(",", "", $row[7]);
            $DI_REF = empty($row[8]) || $row[8] === null || $row[8] === "" ? "-" : str_replace(",", "", $row[8]);
            $AR_NAME = empty($row[9]) || $row[9] === null || $row[9] === "" ? "-" : str_replace(",", "", $row[9]);
            $SALE_NAME = empty($row[10]) || $row[10] === null || $row[10] === "" ? "-" : str_replace(",", "", $row[10]);
            $TAKE_NAME = empty($row[11]) || $row[11] === null || $row[11] === "" ? "-" : str_replace(",", "", $row[11]);
            $TRD_QTY = empty($row[12]) || $row[12] === null || $row[12] === "" ? "0" : str_replace(",", "", $row[12]);
            $TRD_PRC = empty($row[13]) || $row[13] === null || $row[13] === "" ? "0" : str_replace(",", "", $row[13]);
            $TRD_DISCOUNT = empty($row[14]) || $row[14] === null || $row[14] === "" ? "0" : str_replace(",", "", $row[14]);
            $TRD_TOTAL_PRICE = empty($row[15]) || $row[15] === null || $row[15] === "" ? "0" : str_replace(",", "", $row[15]);
            $TRD_VAT = empty($row[16]) || $row[16] === null || $row[16] === "" ? "0" : str_replace(",", "", $row[16]);
            $TRD_AMOUNT_PRICE = empty($row[17]) || $row[17] === null || $row[17] === "" ? "0" : str_replace(",", "", $row[17]);
            $TRD_PER_POINT = empty($row[18]) || $row[18] === null || $row[18] === "" ? "0" : str_replace(",", "", $row[18]);
            $TRD_TOTAL_POINT = empty($row[19]) || $row[19] === null || $row[19] === "" ? "0" : str_replace(",", "", $row[19]);
            $WL_CODE = empty($row[20]) || $row[20] === null || $row[20] === "" ? "-" : str_replace(",", "", $row[20]);
            $TRD_Q_FREE = empty($row[21]) || $row[21] === null || $row[21] === "" ? "0" : str_replace(",", "", $row[21]);
            $TRD_AMPHUR = empty($row[22]) || $row[22] === null || $row[22] === "" ? "-" : str_replace(",", "", $row[22]);
            $TRD_PROVINCE = empty($row[23]) || $row[23] === null || $row[23] === "" ? "-" : str_replace(",", "", $row[23]);
            $TRD_MARK = empty($row[24]) || $row[24] === null || $row[24] === "" ? "-" : str_replace(",", "", $row[24]);
            $TRD_U_POINT = empty($row[25]) || $row[25] === null || $row[25] === "" ? "0" : str_replace(",", "", $row[25]);
            $TRD_R_POINT = empty($row[26]) || $row[26] === null || $row[26] === "" ? "0" : str_replace(",", "", $row[26]);
            $TRD_S_POINT = empty($row[27]) || $row[27] === null || $row[27] === "" ? "0" : str_replace(",", "", $row[27]);
            $TRD_T_POINT = empty($row[28]) || $row[28] === null || $row[28] === "" ? "0" : str_replace(",", "", $row[28]);
            $TRD_COMPARE = empty($row[29]) || $row[29] === null || $row[29] === "" ? "-" : str_replace(",", "", $row[29]);
            $TRD_SHOP = empty($row[30]) || $row[30] === null || $row[30] === "" ? "-" : str_replace(",", "", $row[30]);
            $TRD_BY_MOBAPP = empty($row[31]) || $row[31] === null || $row[31] === "" ? "-" : str_replace(",", "", $row[31]);
            $TRD_YEAR_OLD = empty($row[32]) || $row[32] === null || $row[32] === "" ? "-" : str_replace(",", "", $row[32]);
            $SKU_CAT = empty($row[33]) || $row[33] === null || $row[33] === "" ? "-" : str_replace(",", "", $row[33]);

            if ($DI_DAY !== "-" && $DI_MONTH !== "-" && $DI_YEAR !== "-" && $DI_REF !== "-" && $AR_CODE !== "-" && $SKU_CODE !== "-") {

                // Check for duplicates based on the unique combination of DI_DAY, DI_MONTH, DI_YEAR, DI_REF, AR_CODE, SKU_CODE, WL_CODE, TRD_QTY
                $statement = $conn->prepare("SELECT COUNT(*) FROM " . $table_name . " WHERE DI_DAY = ? AND DI_MONTH = ? AND DI_YEAR = ? AND DI_REF = ? 
                                    AND AR_CODE = ? AND SKU_CODE = ? AND WL_CODE = ? AND TRD_QTY = ?");
                $statement->execute([$DI_DAY, $DI_MONTH, $DI_YEAR, $DI_REF, $AR_CODE, $SKU_CODE, $WL_CODE, $TRD_QTY]);

                if ((int)$statement->fetchColumn() === 0) {
                    // Prepare and execute the SQL Insert statement
                    $sql_insert = "INSERT INTO " . $table_name . " (DI_DAY, DI_MONTH, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, SKU_NAME, DETAIL, BRAND, AR_NAME, 
                    SALE_NAME, TAKE_NAME, TRD_QTY, TRD_PRC, TRD_DISCOUNT, TRD_TOTAL_PRICE, TRD_VAT, TRD_AMOUNT_PRICE, 
                    TRD_PER_POINT, TRD_TOTAL_POINT, WL_CODE, TRD_Q_FREE, TRD_AMPHUR, TRD_PROVINCE, TRD_MARK, 
                    TRD_U_POINT, TRD_R_POINT, TRD_S_POINT, TRD_T_POINT, TRD_COMPARE, TRD_SHOP, TRD_BY_MOBAPP,TRD_YEAR_OLD, SKU_CAT, DI_REF) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt_insert = $conn->prepare($sql_insert);

                    $txt .= $DI_DAY . " | " . $DI_MONTH . " | " . $DI_MONTH_NAME . " | " . $DI_YEAR . " | " . $AR_CODE . " | " . $SKU_CODE . " | " . $SKU_NAME . " | " . $DETAIL . " | " . $BRAND . " | " . $AR_NAME . " | " .
                        $SALE_NAME . " | " . $TAKE_NAME . " | " . $TRD_QTY . " | " . $TRD_PRC . " | " . $TRD_DISCOUNT . " | " . $TRD_TOTAL_PRICE . " | " . $TRD_VAT . " | " . $TRD_AMOUNT_PRICE . " | " .
                        $TRD_PER_POINT . " | " . $TRD_TOTAL_POINT . " | " . $WL_CODE . " | " . $TRD_Q_FREE . " | " . $TRD_AMPHUR . " | " . $TRD_PROVINCE . " | " . $TRD_MARK . " | " .
                        $TRD_U_POINT . " | " . $TRD_R_POINT . " | " . $TRD_S_POINT . " | " . $TRD_T_POINT . " | " . $TRD_COMPARE . " | " . $TRD_SHOP . " | " . $TRD_BY_MOBAPP . " | " .
                        $TRD_YEAR_OLD . " | " . $SKU_CAT . " | " . $DI_REF . "\n\r";

                    $stmt_insert->execute([
                        $DI_DAY, $DI_MONTH, $DI_MONTH_NAME, $DI_YEAR, $AR_CODE, $SKU_CODE, $SKU_NAME, $DETAIL, $BRAND, $AR_NAME, $SALE_NAME, $TAKE_NAME, $TRD_QTY, $TRD_PRC
                        , $TRD_DISCOUNT, $TRD_TOTAL_PRICE, $TRD_VAT, $TRD_AMOUNT_PRICE, $TRD_PER_POINT, $TRD_TOTAL_POINT, $WL_CODE, $TRD_Q_FREE, $TRD_AMPHUR
                        , $TRD_PROVINCE, $TRD_MARK, $TRD_U_POINT, $TRD_R_POINT, $TRD_S_POINT, $TRD_T_POINT, $TRD_COMPARE, $TRD_SHOP, $TRD_BY_MOBAPP, $TRD_YEAR_OLD, $SKU_CAT, $DI_REF
                    ]);

                    $importedRows++;

                } else {
                    // Prepare and execute the SQL Update statement
                    $sql_update = "UPDATE " . $table_name . " SET DI_MONTH = ?, DI_MONTH_NAME = ?, SKU_NAME = ?, DETAIL = ?, BRAND = ?, AR_NAME = ?, SALE_NAME = ?, TAKE_NAME = ?, 
                        TRD_PRC = ?, TRD_DISCOUNT = ?, TRD_TOTAL_PRICE = ?, TRD_VAT = ?, TRD_AMOUNT_PRICE = ?, 
                        TRD_PER_POINT = ?, TRD_TOTAL_POINT = ?, TRD_Q_FREE = ?, TRD_AMPHUR = ?, TRD_PROVINCE = ?, TRD_MARK = ?, 
                        TRD_U_POINT = ?, TRD_R_POINT = ?, TRD_S_POINT = ?, TRD_T_POINT = ?, TRD_COMPARE = ?, TRD_SHOP = ?, 
                        TRD_BY_MOBAPP = ?, TRD_YEAR_OLD = ?, SKU_CAT = ?, DI_REF = ? 
                    WHERE DI_DAY = ? AND DI_MONTH = ? AND DI_YEAR = ? AND DI_REF = ?
                        AND AR_CODE = ? AND SKU_CODE = ? AND WL_CODE = ? AND TRD_QTY = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->execute([
                        $DI_MONTH, $DI_MONTH_NAME, $SKU_NAME, $DETAIL, $BRAND, $AR_NAME, $SALE_NAME, $TAKE_NAME, $TRD_PRC, $TRD_DISCOUNT,
                        $TRD_TOTAL_PRICE, $TRD_VAT, $TRD_AMOUNT_PRICE, $TRD_PER_POINT, $TRD_TOTAL_POINT, $TRD_Q_FREE,
                        $TRD_AMPHUR, $TRD_PROVINCE, $TRD_MARK, $TRD_U_POINT, $TRD_R_POINT, $TRD_S_POINT, $TRD_T_POINT,
                        $TRD_COMPARE, $TRD_SHOP, $TRD_BY_MOBAPP, $TRD_YEAR_OLD, $SKU_CAT, $DI_REF,
                        $DI_DAY, $DI_MONTH, $DI_YEAR, $DI_REF, $AR_CODE, $SKU_CODE, $WL_CODE, $TRD_QTY
                    ]);

                    $duplicateRows++;
                }

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

