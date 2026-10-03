<?php

ini_set('display_errors', 1);
error_reporting(~0);

// เริ่มจับเวลา
$start_time = microtime(true);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include('../util/month_util.php');

$yesterday = date("Y/m/d", strtotime("yesterday"));
$today = date("Y/m/d");

echo "Today is " . $today . "\n\r";
echo "Yesterday is " . $yesterday . "\n\r";

// เพิ่ม WITH (NOLOCK) หลัง Table ทุกตัวในนี้
$sql_query_data = " SELECT DOCTYPE.DT_DOCCODE,DOCTYPE.DT_THAIDESC,DOCINFO.DI_REF,DOCINFO.DI_DATE,DOCINFO.DI_AMOUNT,ARFILE.AR_CODE,ARFILE.AR_NAME
,ARDETAIL.ARD_BIL_DA,ARDETAIL.ARD_DUE_DA,ARDETAIL.ARD_CHQ_DA,ARFILE.AR_SLMNCODE,SALESMAN.SLMN_NAME,ARFILE.AR_REMARK ,DOCINFO.DI_REMARK,DOCINFO.DI_ACTIVE 
FROM DOCINFO WITH (NOLOCK)
LEFT JOIN ARDETAIL WITH (NOLOCK) ON DOCINFO.DI_KEY = ARDETAIL.ARD_DI
LEFT JOIN ARFILE WITH (NOLOCK) ON ARDETAIL.ARD_AR = ARFILE.AR_KEY 
LEFT JOIN SALESMAN WITH (NOLOCK) ON SALESMAN.SLMN_CODE = ARFILE.AR_SLMNCODE
LEFT JOIN DOCTYPE WITH (NOLOCK) ON DOCTYPE.DT_KEY = DOCINFO.DI_DT ";

$query_daily_cond_doc_type = " WHERE (DOCTYPE.DT_DOCCODE in ('DS','DS1','IV01','DS02','30','DS4','DDS5','IV3','/SAC','S.1','S.2','S.3','S.4','S.5','S.6')) ";
$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '" . $yesterday . "' AND '" . $today . "'";

$sql_sqlsvr = $sql_query_data . $query_daily_cond_doc_type . $select_query_daily_cond . " ORDER BY DOCINFO.DI_DATE , DOCINFO.DI_REF ";

// 1. OPTIMIZATION: เตรียม Prepared Statements ไว้นอก Loop
$sql_check = "SELECT 1 FROM ims_document_bill WHERE DI_REF = :DI_REF LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

$sql_update = " UPDATE ims_document_bill SET 
    DI_AMOUNT = :DI_AMOUNT, 
    DI_ACTIVE = :DI_ACTIVE, 
    AR_SLMNCODE = :AR_SLMNCODE, 
    SLMN_NAME = :SLMN_NAME 
    WHERE DI_REF = :DI_REF ";
$stmt_update = $conn->prepare($sql_update);

$sql_insert = " INSERT INTO ims_document_bill (
    DT_DOCCODE, DT_THAIDESC, DI_REF, DI_DATE, DI_AMOUNT, AR_CODE, AR_NAME,
    ARD_BIL_DA, ARD_DUE_DA, ARD_CHQ_DA, AR_SLMNCODE, SLMN_NAME, AR_REMARK, DI_REMARK, DI_ACTIVE
) VALUES (
    :DT_DOCCODE, :DT_THAIDESC, :DI_REF, :DI_DATE, :DI_AMOUNT, :AR_CODE, :AR_NAME,
    :ARD_BIL_DA, :ARD_DUE_DA, :ARD_CHQ_DA, :AR_SLMNCODE, :SLMN_NAME, :AR_REMARK, :DI_REMARK, :DI_ACTIVE
) ";
$stmt_insert = $conn->prepare($sql_insert);

// 2. OPTIMIZATION: Pre-fetch existing DI_REFs เพื่อลด SQL query รายแถว
$sql_existing = "SELECT DI_REF FROM ims_document_bill WHERE DI_DATE >= :yesterday";
$stmt_existing = $conn->prepare($sql_existing);
$stmt_existing->execute([':yesterday' => $yesterday]);
$existing_refs = array_flip($stmt_existing->fetchAll(PDO::FETCH_COLUMN));

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

// 3. OPTIMIZATION: ใช้ Transaction ลด Disk I/O
$conn->beginTransaction();

$count_insert = 0;
$count_update = 0;

try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $di_ref = $result_sqlsvr["DI_REF"];

        // Fast Lookup ใน PHP memory
        $exists = isset($existing_refs[$di_ref]);
        if (!$exists) {
            $stmt_check->execute([':DI_REF' => $di_ref]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $stmt_update->execute([
                ':DI_AMOUNT'   => $result_sqlsvr["DI_AMOUNT"],
                ':DI_ACTIVE'   => $result_sqlsvr["DI_ACTIVE"],
                ':AR_SLMNCODE' => $result_sqlsvr["AR_SLMNCODE"],
                ':SLMN_NAME'   => $result_sqlsvr["SLMN_NAME"],
                ':DI_REF'      => $di_ref
            ]);
            $count_update++;
        } else {
            $stmt_insert->execute([
                ':DT_DOCCODE'  => $result_sqlsvr["DT_DOCCODE"],
                ':DT_THAIDESC' => $result_sqlsvr["DT_THAIDESC"],
                ':DI_REF'      => $di_ref,
                ':DI_DATE'     => $result_sqlsvr["DI_DATE"],
                ':DI_AMOUNT'   => $result_sqlsvr["DI_AMOUNT"],
                ':AR_CODE'     => $result_sqlsvr["AR_CODE"],
                ':AR_NAME'     => $result_sqlsvr["AR_NAME"],
                ':ARD_BIL_DA'  => $result_sqlsvr["ARD_BIL_DA"],
                ':ARD_DUE_DA'  => $result_sqlsvr["ARD_DUE_DA"],
                ':ARD_CHQ_DA'  => $result_sqlsvr["ARD_CHQ_DA"],
                ':AR_SLMNCODE' => $result_sqlsvr["AR_SLMNCODE"],
                ':SLMN_NAME'   => $result_sqlsvr["SLMN_NAME"],
                ':AR_REMARK'   => $result_sqlsvr["AR_REMARK"],
                ':DI_REMARK'   => $result_sqlsvr["DI_REMARK"],
                ':DI_ACTIVE'   => $result_sqlsvr["DI_ACTIVE"]
            ]);
            $count_insert++;
            $existing_refs[$di_ref] = true;
        }
    }

    $conn->commit();
    echo "Process Completed successfully.\n";
    echo "Updated: " . $count_update . " rows.\n";
    echo "Inserted: " . $count_insert . " rows.\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

$table_name = "ims_data_sale_sac_all";
$sql_check_invalid = "SELECT 1 FROM " . $table_name . " WHERE DI_YEAR NOT REGEXP '^[0-9]+$' LIMIT 1";
if ($conn->query($sql_check_invalid)->fetchColumn()) {
    $sql_delete = "DELETE FROM " . $table_name . " WHERE DI_YEAR NOT REGEXP '^[0-9]+$' ";
    $conn->exec($sql_delete);
}

$conn_sqlsvr = null;
$conn = null;

// แสดงเวลาที่ใช้ไป
echo "\nExecution time: " . (microtime(true) - $start_time) . " seconds\n";