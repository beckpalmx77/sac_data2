<?php

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_customer_ar.php");

$sql_sqlsvr = $select_query
            //. $sql_cond . " AND AR_CODE like 'SAC%' "
            . $sql_cond
            . $sql_order ;

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $contact_name = $result_sqlsvr["CT_INTL"] . " " . $result_sqlsvr["CT_NAME"] . " "
                  . $result_sqlsvr["CT_SURNME"] . " - " . $result_sqlsvr["CT_JOBTITLE"];

    $sql_find = "SELECT * FROM ims_customer_ar WHERE customer_id = '" . $result_sqlsvr["AR_CODE"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        $sql = "UPDATE ims_customer_ar SET tax_id=:tax_id,f_name=:f_name,credit=:credit,
        phone=:phone,address=:address,tumbol=:tumbol,amphure=:amphure,province=:province,zipcode=:zipcode,ARCD_NAME=:ARCD_NAME,
        sale_name=:sale_name,contact_name=:contact_name
        WHERE customer_id = :customer_id ";
        echo "Update Customer : " . $result_sqlsvr["ARCAT_CODE"] . " | " . $result_sqlsvr["AR_CODE"] . " | " . $result_sqlsvr["AR_NAME"] . "\n\r";
        $query = $conn->prepare($sql);
        $query->bindParam(':tax_id', $result_sqlsvr["ADDB_TAX_ID"], PDO::PARAM_STR);
        $query->bindParam(':f_name', $result_sqlsvr["AR_NAME"], PDO::PARAM_STR);
        $query->bindParam(':credit', $result_sqlsvr["ARS_CRE_LIM"], PDO::PARAM_STR);
        $query->bindParam(':phone', $result_sqlsvr["ADDB_PHONE"], PDO::PARAM_STR);
        $query->bindParam(':address', $result_sqlsvr["ADDB_ADDB_1"], PDO::PARAM_STR);
        $query->bindParam(':tumbol', $result_sqlsvr["ADDB_ADDB_2"], PDO::PARAM_STR);
        $query->bindParam(':amphure', $result_sqlsvr["ADDB_ADDB_3"], PDO::PARAM_STR);
        $query->bindParam(':province', $result_sqlsvr["ADDB_PROVINCE"], PDO::PARAM_STR);
        $query->bindParam(':zipcode', $result_sqlsvr["ADDB_POST"], PDO::PARAM_STR);
        $query->bindParam(':ARCD_NAME', $result_sqlsvr["ARCD_NAME"], PDO::PARAM_STR);
        $query->bindParam(':sale_name', $result_sqlsvr["SLMN_NAME"], PDO::PARAM_STR);
        $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
        $query->bindParam(':customer_id', $result_sqlsvr["AR_CODE"], PDO::PARAM_STR);
        $query->execute();
    } else {

        echo "Customer : " . $result_sqlsvr["ARCAT_CODE"] . " | " . $result_sqlsvr["AR_CODE"] . " | " . $result_sqlsvr["AR_NAME"] . "\n\r";

        $sql = "INSERT INTO ims_customer_ar(customer_id,tax_id,f_name,credit,phone,address,tumbol,amphure,province
        ,zipcode,ARCD_NAME,sale_name,contact_name)
        VALUES (:customer_id,:tax_id,:f_name,:credit,:phone,:address,:tumbol,:amphure,:province
        ,:zipcode,:ARCD_NAME,:sale_name,:contact_name)";
        $query = $conn->prepare($sql);
        $query->bindParam(':customer_id', $result_sqlsvr["AR_CODE"], PDO::PARAM_STR);
        $query->bindParam(':tax_id', $result_sqlsvr["ADDB_TAX_ID"], PDO::PARAM_STR);
        $query->bindParam(':f_name', $result_sqlsvr["AR_NAME"], PDO::PARAM_STR);
        $query->bindParam(':credit', $result_sqlsvr["ARS_CRE_LIM"], PDO::PARAM_STR);
        $query->bindParam(':phone', $result_sqlsvr["ADDB_PHONE"], PDO::PARAM_STR);
        $query->bindParam(':address', $result_sqlsvr["ADDB_ADDB_1"], PDO::PARAM_STR);
        $query->bindParam(':tumbol', $result_sqlsvr["ADDB_ADDB_2"], PDO::PARAM_STR);
        $query->bindParam(':amphure', $result_sqlsvr["ADDB_ADDB_3"], PDO::PARAM_STR);
        $query->bindParam(':province', $result_sqlsvr["ADDB_PROVINCE"], PDO::PARAM_STR);
        $query->bindParam(':zipcode', $result_sqlsvr["ADDB_POST"], PDO::PARAM_STR);
        $query->bindParam(':ARCD_NAME', $result_sqlsvr["ARCD_NAME"], PDO::PARAM_STR);
        $query->bindParam(':sale_name', $result_sqlsvr["SLMN_NAME"], PDO::PARAM_STR);
        $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo "Save OK";
        } else {
            echo "Error";
        }

/*
        $return_arr[] = array("customer_id" => $result_sqlsvr['AR_CODE'],
            "tax_id" => $result_sqlsvr['ADDB_TAX_ID'],
            "f_name" => $result_sqlsvr['AR_NAME'],
            "phone" => $result_sqlsvr['ADDB_PHONE'],
            "address" => $result_sqlsvr['ADDB_ADDB_1'],
            "tumbol" => $result_sqlsvr['ADDB_ADDB_2'],
            "amphure" => $result_sqlsvr['ADDB_ADDB_3'],
            "province" => $result_sqlsvr['ADDB_PROVINCE'],
            "zipcode" => $result_sqlsvr['ADDB_POST']);
*/
    }
/*
    $customer_data = json_encode($return_arr);
    file_put_contents("customer_data.json", $customer_data);
    echo json_encode($return_arr);
*/

}

$conn_sqlsvr=null;

