<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_customer_ar.php");

$sql_sqlsvr = $select_query
            //. $sql_cond . " AND AR_CODE like 'SAC%' "
            . $sql_cond
            . $sql_order ;

// Pre-fetch existing customer_ids into PHP Memory lookup array
$existing_customers = array_flip($conn->query("SELECT customer_id FROM ims_customer_ar")->fetchAll(PDO::FETCH_COLUMN));

// Prepared check statement ONCE outside loop
$sql_check = "SELECT 1 FROM ims_customer_ar WHERE customer_id = :customer_id LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

// Prepare UPDATE and INSERT statements ONCE outside the loop
$sql_update = "UPDATE ims_customer_ar SET tax_id=:tax_id,f_name=:f_name,credit=:credit,
phone=:phone,address=:address,tumbol=:tumbol,amphure=:amphure,province=:province,zipcode=:zipcode,ARCD_NAME=:ARCD_NAME,
sale_name=:sale_name,contact_name=:contact_name
WHERE customer_id = :customer_id ";
$stmt_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO ims_customer_ar(customer_id,tax_id,f_name,credit,phone,address,tumbol,amphure,province
,zipcode,ARCD_NAME,sale_name,contact_name)
VALUES (:customer_id,:tax_id,:f_name,:credit,:phone,:address,:tumbol,:amphure,:province
,:zipcode,:ARCD_NAME,:sale_name,:contact_name)";
$stmt_insert = $conn->prepare($sql_insert);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$inserted_count = 0;
$updated_count = 0;

$conn->beginTransaction();
try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $contact_name = $result_sqlsvr["CT_INTL"] . " " . $result_sqlsvr["CT_NAME"] . " "
                      . $result_sqlsvr["CT_SURNME"] . " - " . $result_sqlsvr["CT_JOBTITLE"];

        $customer_id = $result_sqlsvr["AR_CODE"];

        $exists = isset($existing_customers[$customer_id]);
        if (!$exists) {
            $stmt_check->execute([':customer_id' => $customer_id]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $stmt_update->execute([
                ':tax_id' => $result_sqlsvr["ADDB_TAX_ID"],
                ':f_name' => $result_sqlsvr["AR_NAME"],
                ':credit' => $result_sqlsvr["ARS_CRE_LIM"],
                ':phone' => $result_sqlsvr["ADDB_PHONE"],
                ':address' => $result_sqlsvr["ADDB_ADDB_1"],
                ':tumbol' => $result_sqlsvr["ADDB_ADDB_2"],
                ':amphure' => $result_sqlsvr["ADDB_ADDB_3"],
                ':province' => $result_sqlsvr["ADDB_PROVINCE"],
                ':zipcode' => $result_sqlsvr["ADDB_POST"],
                ':ARCD_NAME' => $result_sqlsvr["ARCD_NAME"],
                ':sale_name' => $result_sqlsvr["SLMN_NAME"],
                ':contact_name' => $contact_name,
                ':customer_id' => $customer_id
            ]);
            $updated_count++;
        } else {
            $stmt_insert->execute([
                ':customer_id' => $customer_id,
                ':tax_id' => $result_sqlsvr["ADDB_TAX_ID"],
                ':f_name' => $result_sqlsvr["AR_NAME"],
                ':credit' => $result_sqlsvr["ARS_CRE_LIM"],
                ':phone' => $result_sqlsvr["ADDB_PHONE"],
                ':address' => $result_sqlsvr["ADDB_ADDB_1"],
                ':tumbol' => $result_sqlsvr["ADDB_ADDB_2"],
                ':amphure' => $result_sqlsvr["ADDB_ADDB_3"],
                ':province' => $result_sqlsvr["ADDB_PROVINCE"],
                ':zipcode' => $result_sqlsvr["ADDB_POST"],
                ':ARCD_NAME' => $result_sqlsvr["ARCD_NAME"],
                ':sale_name' => $result_sqlsvr["SLMN_NAME"],
                ':contact_name' => $contact_name
            ]);
            $existing_customers[$customer_id] = true;
            $inserted_count++;
        }
    }
    $conn->commit();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Import Customer AR Summary:\n";
echo "Inserted: $inserted_count\n";
echo "Updated: $updated_count\n";
echo "Execution Time: {$execution_time} seconds\n";


