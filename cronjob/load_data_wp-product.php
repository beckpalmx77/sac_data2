<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db_wp.php");
include('../cond_file/query-product-price-main.php');

include("../util/getdata_field.php");

$IMG_DIR = "http://171.100.56.194:8999/sac_tires/wp-content/uploads/products/";

//$price_code = $_POST['price_code'];
$price_code = "S3";

$sql_where_ext = " AND SKUMASTER.SKU_ENABLE = 'Y' AND ICCAT_CODE  in ('1SAC14','4SAC01','3SAC01','1SAC06','1SAC05','1SAC01','1SAC02','1SAC03','1SAC04','1SAC08','1SAC07',
'1SAC09','1SAC10','1SAC11','1SAC12','1SAC13','2SAC09','2SAC04','2SAC13','2SAC14','2SAC02','2SAC03',
'2SAC10','2SAC06','2SAC05','2SAC07','2SAC08','3SAC02','3SAC06','3SAC03','3SAC04','4SAC02','4SAC03',
'4SAC04','4SAC06','3SAC05','4SAC05') AND ARPRB_CODE like '" . $price_code . "'";

$sql_order = " ORDER BY SKU_KEY DESC ";

$sql_sqlsvr = $select_query . $sql_cond . $sql_where_ext . $sql_order;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM wp_postmeta "
        . " WHERE meta_key = '_sku' AND meta_value = '" . $result_sqlsvr["SKU_CODE"] . "'" ;
    $query = $conn->prepare($sql_find);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() >= 1) {
        foreach ($results as $result) {
            $sql_update = "";
            $sql_update .= "UPDATE wp_postmeta set meta_value = " . $result_sqlsvr["ARPLU_U_PRC"] . " WHERE post_id = " . $result->post_id . " AND meta_key = '_regular_price' ;";

            $sql_update .= "UPDATE wp_postmeta set meta_value = " . $result_sqlsvr["ARPLU_U_PRC"] . " WHERE post_id = " . $result->post_id . " AND meta_key = '_price' ;";

            $query_u = $conn->prepare($sql_update);
            $query_u->execute();
            $query_u->closeCursor();
        }
    } else {
        $author = 1;
        $post_type = "product";
        $post_status = "publish";
        $comment_status = "open";
        $ping_status = "closed";
        $post_parent = 7844;

        //echo $result_sqlsvr["SKU_CODE"] . " - " . $result_sqlsvr["SKU_NAME"] . " - Insert " . "\n\r";

        $sql_insert = " INSERT INTO wp_posts (post_author,post_type,post_title,post_content,sku,post_name,post_parent,post_status,comment_status,ping_status)
        VALUES (:post_author,:post_type,:post_title,:post_content,:sku,:post_name,:post_parent,:post_status,:comment_status,:ping_status) ";

        //echo "\n\r" . "SQL = " . $sql_insert ;


        $post_name = str_replace("/", "-", $result_sqlsvr['SKU_CODE']);

        $post_name = str_replace(" ", "-", $post_name);

        $post_name = strtolower($post_name);

        $query_ins = $conn->prepare($sql_insert);
        $query_ins->bindParam(':post_author', $author, PDO::PARAM_STR);
        $query_ins->bindParam(':post_type', $post_type, PDO::PARAM_STR);
        $query_ins->bindParam(':post_title', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
        $query_ins->bindParam(':post_content', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
        $query_ins->bindParam(':sku', $result_sqlsvr["SKU_CODE"], PDO::PARAM_STR);
        $query_ins->bindParam(':post_name', $post_name, PDO::PARAM_STR);
        $query_ins->bindParam(':post_parent', $post_parent, PDO::PARAM_STR);
        $query_ins->bindParam(':post_status', $post_status, PDO::PARAM_STR);
        $query_ins->bindParam(':comment_status', $comment_status, PDO::PARAM_STR);
        $query_ins->bindParam(':ping_status', $ping_status, PDO::PARAM_STR);

        $query_ins->execute();

        $sql_find_content = "SELECT * FROM wp_posts " . " WHERE sku = '" . $result_sqlsvr["SKU_CODE"] . "'";
        //echo "\n\r" . $sql_find_content ;
        $query1 = $conn->prepare($sql_find_content);
        $query1->execute();
        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
        if ($query1->rowCount() >= 1) {

            foreach ($results1 as $result1) {

                $BRN_CODE = substr($result_sqlsvr['BRN_CODE'],0,2);

                $post_id = $result1->ID;

                include 'cond_brand.php';

                $guid = "http://171.100.56.194:8999/sac_tires/?post_type=product&#038;p=" .  $result1->ID;
                $sql_update1 = "UPDATE wp_posts set guid = '" . $guid
                . "' WHERE sku = '" . $result_sqlsvr["SKU_CODE"] . "'" ;


                $query_update1 = $conn->prepare($sql_update1);
                $query_update1->execute();


                $sql_insert_meta = "";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_product_version','8.02');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_wc_review_count','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_wc_average_rating','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_stock_status','onbackorder');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_stock','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_download_expiry','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_download_limit','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_downloadable','no');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_virtual','no');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_sold_individually','no');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_backorders','yes');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_manage_stock','yes');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'total_sales','yes');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_tax_status','taxable');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_tax_class','');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_sku','" . $result_sqlsvr["SKU_CODE"] . "');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_wp_old_slug','');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_regular_price'," . $result_sqlsvr["ARPLU_U_PRC"] . ");" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_low_stock_amount','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_weight','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_length','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_width','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_height','0');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_purchase_note','');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_thumbnail_id',". $thumbnail_id . ");" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_product_attributes','a:1:{s:0:\"\";a:6:{s:4:\"name\";s:1:\" \";s:5:\"value\";s:0:\"\";s:8:\"position\";i:0;s:10:\"is_visible\";i:0;s:12:\"is_variation\";i:0;s:11:\"is_taxonomy\";i:0;}}');" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_postmeta (post_id,meta_key,meta_value) VALUES (" . $result1->ID . ",'_price'," . $result_sqlsvr["ARPLU_U_PRC"] . ");" . "\n\r";

                $sql_insert_meta .= "INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES (" . $result1->ID . ",54,0" . ");" . "\n\r";
                $sql_insert_meta .= "INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES (" . $result1->ID . ",55,0" . ");" . "\n\r";

                $query_m = $conn->prepare($sql_insert_meta);
                $query_m->execute();
                $query_m->closeCursor();

                $post_id = $result1->ID;

                $sql_find_terms = "SELECT term_id FROM wp_terms " . " WHERE name = '" . $BRN_NAME . "'";

                $term_id = GetDataValue($conn,$sql_find_terms);

                echo "\n\r" . "Post ID = " . $post_id . " | Term ID = " .  $term_id ;

                $sql_insert_tag = "INSERT INTO wp_term_relationships (object_id,term_taxonomy_id,term_order) VALUES (" . $post_id . "," . $term_id . ",0" . ");" . "\n\r";

                echo "\n\r" . "sql_insert_tag = " . $sql_insert_tag ;

                $query_ins_tag = $conn->prepare($sql_insert_tag);
                $query_ins_tag->execute();
                $query_ins_tag->closeCursor();

            }
        }

    }

}

echo "\n\rend ";

$conn_sqlsvr = null;

