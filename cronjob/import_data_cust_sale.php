<?php

$start_time = microtime(true);

$customer_id = isset($customer_id) ? $customer_id : '';
$year = isset($year) ? $year : '';

$sql_data = " SELECT AR_CODE,SKU_CODE,SKU_NAME,TRD_U_PRC,
SUM(IF(DI_MONTH='1',TRD_QTY,0)) AS 1_QTY,
SUM(IF(DI_MONTH='1',TRD_G_KEYIN,0)) AS 1_AMT,
SUM(IF(DI_MONTH='2',TRD_QTY,0)) AS 2_QTY,
SUM(IF(DI_MONTH='2',TRD_G_KEYIN,0)) AS 2_AMT,
SUM(IF(DI_MONTH='3',TRD_QTY,0)) AS 3_QTY,
SUM(IF(DI_MONTH='3',TRD_G_KEYIN,0)) AS 3_AMT,
SUM(IF(DI_MONTH='4',TRD_QTY,0)) AS 4_QTY,
SUM(IF(DI_MONTH='4',TRD_G_KEYIN,0)) AS 4_AMT,
SUM(IF(DI_MONTH='5',TRD_QTY,0)) AS 5_QTY,
SUM(IF(DI_MONTH='5',TRD_G_KEYIN,0)) AS 5_AMT,
SUM(IF(DI_MONTH='6',TRD_QTY,0)) AS 6_QTY,
SUM(IF(DI_MONTH='6',TRD_G_KEYIN,0)) AS 6_AMT,
SUM(IF(DI_MONTH='7',TRD_QTY,0)) AS 7_QTY,
SUM(IF(DI_MONTH='7',TRD_G_KEYIN,0)) AS 7_AMT,
SUM(IF(DI_MONTH='8',TRD_QTY,0)) AS 8_QTY,
SUM(IF(DI_MONTH='8',TRD_G_KEYIN,0)) AS 8_AMT,
SUM(IF(DI_MONTH='9',TRD_QTY,0)) AS 9_QTY,
SUM(IF(DI_MONTH='9',TRD_G_KEYIN,0)) AS 9_AMT,
SUM(IF(DI_MONTH='10',TRD_QTY,0)) AS 10_QTY,
SUM(IF(DI_MONTH='10',TRD_G_KEYIN,0)) AS 10_AMT,
SUM(IF(DI_MONTH='11',TRD_QTY,0)) AS 11_QTY,
SUM(IF(DI_MONTH='11',TRD_G_KEYIN,0)) AS 11_AMT,
SUM(IF(DI_MONTH='12',TRD_QTY,0)) AS 12_QTY,
SUM(IF(DI_MONTH='12',TRD_G_KEYIN,0)) AS 12_AMT

FROM ims_product_sale_sac 
WHERE AR_CODE = '" . $customer_id . "'"
    . " AND DI_YEAR = '" . $year . "'"
    . " GROUP BY SKU_CODE,SKU_NAME,DI_MONTH,DI_YEAR,TRD_U_PRC
ORDER BY SKU_CODE ,  CONVERT(DI_YEAR, FLOAT),CONVERT(DI_MONTH, FLOAT) ";

$results_data = array();
$inserted_count = 0;
$updated_count = 0;

if (isset($conn)) {
    // Prepared check statement ONCE outside loop
    $sql_check = "SELECT 1 FROM ims_cust_sale WHERE AR_CODE = :AR_CODE AND SKU_CODE = :SKU_CODE AND DI_YEAR = :DI_YEAR LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);

    // Pre-fetch existing record keys into memory
    $existing_keys = array();
    try {
        $stmt_preload = $conn->query("SELECT CONCAT(AR_CODE, '|', SKU_CODE, '|', DI_YEAR) FROM ims_cust_sale");
        if ($stmt_preload) {
            $existing_keys = array_flip($stmt_preload->fetchAll(PDO::FETCH_COLUMN));
        }
    } catch (Exception $e) {
        $existing_keys = array();
    }

    // Prepared UPDATE and INSERT statements ONCE outside loop
    $sql_update = "UPDATE ims_cust_sale SET SKU_NAME = :SKU_NAME, TRD_U_PRC = :TRD_U_PRC WHERE AR_CODE = :AR_CODE AND SKU_CODE = :SKU_CODE AND DI_YEAR = :DI_YEAR";
    $stmt_update = $conn->prepare($sql_update);

    $sql_insert = "INSERT INTO ims_cust_sale (AR_CODE, SKU_CODE, SKU_NAME, TRD_U_PRC, DI_YEAR) VALUES (:AR_CODE, :SKU_CODE, :SKU_NAME, :TRD_U_PRC, :DI_YEAR)";
    $stmt_insert = $conn->prepare($sql_insert);

    $statement_data = $conn->query($sql_data);
    if ($statement_data) {
        $results_data = $statement_data->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results_data as $row) {
            $key = $row["AR_CODE"] . "|" . $row["SKU_CODE"] . "|" . $year;
            $exists = isset($existing_keys[$key]);
            if (!$exists) {
                $stmt_check->execute([
                    ':AR_CODE' => $row["AR_CODE"],
                    ':SKU_CODE' => $row["SKU_CODE"],
                    ':DI_YEAR' => $year
                ]);
                $exists = (bool)$stmt_check->fetchColumn();
            }

            if ($exists) {
                $stmt_update->execute([
                    ':SKU_NAME' => $row["SKU_NAME"],
                    ':TRD_U_PRC' => $row["TRD_U_PRC"],
                    ':AR_CODE' => $row["AR_CODE"],
                    ':SKU_CODE' => $row["SKU_CODE"],
                    ':DI_YEAR' => $year
                ]);
                $updated_count++;
            } else {
                $stmt_insert->execute([
                    ':AR_CODE' => $row["AR_CODE"],
                    ':SKU_CODE' => $row["SKU_CODE"],
                    ':SKU_NAME' => $row["SKU_NAME"],
                    ':TRD_U_PRC' => $row["TRD_U_PRC"],
                    ':DI_YEAR' => $year
                ]);
                $existing_keys[$key] = true;
                $inserted_count++;
            }
        }
    }
}

$execution_time = round(microtime(true) - $start_time, 4);
echo "Import Cust Sale Summary:\n";
echo "Records Fetched: " . count($results_data) . "\n";
echo "Inserted: $inserted_count\n";
echo "Updated: $updated_count\n";
echo "Execution Time: {$execution_time} seconds\n";


