<?php
include "../config/connect_db.php";

set_time_limit(120);
ini_set('memory_limit', '1024M');

header('Content-Type: application/json; charset=UTF-8');

try {
    $sql_get = "SELECT 
        SKU_CODE, 
        MAX(SKU_NAME) AS SKU_NAME, 
        MAX(BRAND) AS BRAND, 
        MAX(SKU_CAT) AS SKU_CAT, 
        MAX(DETAIL) AS DETAIL,
        MAX(update_date) AS update_date
    FROM ims_data_sale_sac_all 
    WHERE SKU_CODE IS NOT NULL AND TRIM(SKU_CODE) != ''
    GROUP BY SKU_CODE 
    ORDER BY SKU_CODE ASC";

    $stmt = $conn->prepare($sql_get);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["data" => $rows]);
} catch (Exception $e) {
    echo json_encode(["data" => [], "error" => $e->getMessage()]);
}
