<?php

date_default_timezone_set("Asia/Bangkok");

$DB_HOST = "localhost";
$DB_NAME = "sac_emp_bak";
$DB_PORT = "3307";
$DB_USER = "myadmin";
$DB_PASS = "myadmin";


try {
    $conn = new PDO("mysql:host=" . $DB_HOST . ";dbname=" . $DB_NAME . ";port=" . $DB_PORT, $DB_USER, $DB_PASS
        , array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit("Error: " . $e->getMessage());
}

$sql_insert_data = "INSERT INTO ims_user (user_id,first_name,last_name) VALUES ('T001','Test1','TestL1');" . "\n\r";
$sql_insert_data .= "INSERT INTO ims_user (user_id,first_name,last_name) VALUES ('T002','Test2','TestL2');" . "\n\r";
$sql_insert_data .= "INSERT INTO ims_user (user_id,first_name,last_name) VALUES ('T003','Test3','TestL3');" . "\n\r";

//echo $sql_insert_data;

$sql_insert_wp_tag = "INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES (" . 19900 . ",54,0" . ");" . "\n\r";

echo $sql_insert_wp_tag;
//$query_data = $conn->prepare($sql_insert_data);
//$query_data->execute();


$sql_find_terms = "SELECT * FROM wp_terms " . " WHERE name = '" . $BRN_NAME . "'";

echo "\n\r" . $sql_find_terms;

$query_terms = $conn->prepare($sql_find_terms);
$query_terms->execute();
$results_terms = $query_terms->fetchAll(PDO::FETCH_OBJ);
if ($query_terms->rowCount() >= 1) {
    foreach ($results_terms as $result_term) {
        $sql_insert_wp_slug = "INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES (" . $result1->ID . "," . $result_term->term_id . ",0" . ");" . "\n\r";
    }
}

echo "\n\r" . $sql_insert_wp_slug ;



$sql_insert_wp_tag = "";
$sql_insert_wp_tag .= "INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES (" . $result1->ID . ",54,0" . ");" . "\n\r";
$sql_insert_wp_tag .= "INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES (" . $result1->ID . ",55,0" . ");" . "\n\r";

$query_tag = $conn->prepare($sql_insert_wp_tag);
$query_tag->execute();
