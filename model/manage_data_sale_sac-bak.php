<?php

include('../config/connect_db.php');  // เชื่อมต่อกับฐานข้อมูล

if (isset($_POST['action']) && $_POST['action'] === 'DELETE_BY_DATE') {

    if (isset($_POST['DI_DATE'])) {
        $di_date = $_POST['DI_DATE'];

        // Query ลบข้อมูลตามวันที่
        $sql = "DELETE FROM ims_data_sale_sac_all WHERE DI_DATE = :di_date";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':di_date', $di_date, PDO::PARAM_STR);
/*
        $myfile = fopen("ad-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $sql . " | " . $di_date);
        fclose($myfile);
*/
        if ($stmt->execute()) {
            echo "ลบข้อมูลสำเร็จ";
        } else {
            echo "เกิดข้อผิดพลาดในการลบข้อมูล";
        }
    }

}

if (isset($_POST['action']) && $_POST['action'] === 'UPDATE_SHOP_TYPE') {

    $sql_find = "SELECT * FROM ims_ar_shop WHERE 1 " ;
    $query = $conn->prepare($sql_find);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() >= 1) {
        foreach ($results as $result) {

            if ($result->status==='Y') {
                $shop_type = "SHOP";
            } else {
                $shop_type = "ร้านทั่วไป";
            }

            $sql = "UPDATE FROM ims_data_sale_sac_all WHERE SHOP_TYPE = :shop_type";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':shop_type', $shop_type, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

}

