<?php
include "../config/connect_db.php";

$sql_get = "SELECT 
        ims.DI_REF,
        ims.DI_DATE,  
        ims.AR_CODE, 
        ims.AR_NAME, 
        ims.SALE_NAME, 
        ims.TAKE_NAME, 
        ims.TRD_AMPHUR, 
        ims.TRD_PROVINCE, 
        ims.seq_record
    FROM 
        ims_data_sale_sac_all ims
    INNER JOIN (
        SELECT 
            seq_record, 
            screen_name, 
            MAX(id) AS latest_id
        FROM 
            log_import_data
        GROUP BY 
            seq_record
    ) log ON ims.seq_record = log.seq_record
    WHERE 
        log.screen_name = 'update_sale_take_doc'
    ORDER BY 
        ims.id DESC " ;
$stmt = $conn->prepare($sql_get);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["data" => $rows]);

