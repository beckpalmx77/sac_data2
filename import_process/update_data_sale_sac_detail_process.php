<?php

include '../config/connect_db.php';

$DI_YEAR = "2025";
$row_counts = 0;
// อัพเดตข้อมูลในตาราง ims_data_sale_sac_all โดยเช็คจาก ims_ar_shop

$updateDaySQL = "
UPDATE ims_data_sale_sac_all 
SET DI_DAY = TRIM(LEADING '0' FROM DI_DAY)
WHERE DI_DAY REGEXP '^0+[0-9]+' AND DI_YEAR = '" . $DI_YEAR . "'";

$stmt_day = $conn->prepare($updateDaySQL);
$stmt_day->execute();

echo "วันที่ อัปเดตเรียบร้อยแล้ว: ปี " . $DI_YEAR . " | " . $updateDaySQL . "\n\r";

$updateShopSQL = "
        UPDATE ims_data_sale_sac_all AS d
        LEFT JOIN ims_ar_shop AS s ON d.AR_CODE = s.ar_code
        SET 
            d.SHOP_TYPE_STATUS = IF(s.ar_code IS NOT NULL, 'Y', 'N'),
            d.SHOP_TYPE = IF(s.ar_code IS NOT NULL, 'SHOP', 'ร้านทั่วไป')
    ";
$stmt1 = $conn->prepare($updateShopSQL);
$stmt1->execute();

echo "SHOP/ร้านทั่วไป อัปเดตเรียบร้อยแล้ว: ปี " . $DI_YEAR . " | " . $updateShopSQL . "\n\r";

// อ่านข้อมูลจากตาราง
$stmt = $conn->query("SELECT id,AR_CODE,SKU_CODE,SHOP_TYPE_STATUS,TRD_QTY FROM ims_data_sale_sac_all WHERE DI_YEAR = '" . $DI_YEAR . "' ORDER BY id ");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {

    $row_counts++;

    $id = $row['id'];
    $AR_CODE = $row['AR_CODE'];
    $SKU_CODE = $row['SKU_CODE'];
    $SHOP_TYPE_STATUS = $row['SHOP_TYPE_STATUS'];


    $stmt1 = $conn->query("SELECT TIRES_EDGE,TRD_U_POINT,TRD_S_POINT FROM ims_sac_tires_point WHERE SKU_CODE = '" . $SKU_CODE . "'");
    $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows1 as $row1) {

        if ($SHOP_TYPE_STATUS === 'Y') {
            $TRD_S_POINT = $row1['TRD_S_POINT'];
        } else {
            $TRD_S_POINT = 0;
        }

        $TRD_U_POINT = $row1['TRD_U_POINT'];
        $TIRES_EDGE = $row1['TIRES_EDGE'];
    }

    // อัปเดตข้อมูลในตาราง
    $updateStmt = $conn->prepare("UPDATE ims_data_sale_sac_all SET TRD_S_POINT = :TRD_S_POINT , TRD_U_POINT = :TRD_U_POINT 
    , TIRES_EDGE = :TIRES_EDGE , TRD_SEQ = :TRD_SEQ  WHERE id = :id ");
    $updateStmt->bindParam(':TRD_S_POINT', $TRD_S_POINT);
    $updateStmt->bindParam(':TRD_U_POINT', $TRD_U_POINT);
    $updateStmt->bindParam(':TIRES_EDGE', $TIRES_EDGE);
    $updateStmt->bindParam(':TRD_SEQ', $row_counts);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo "id =  " . $id . " SKU POINT อัปเดตเรียบร้อยแล้ว: " . $AR_CODE . " | SKU = " . $SKU_CODE . " | TRD_U_POINT = " . $TRD_U_POINT . " | TRD_S_POINT = " . $TRD_S_POINT . " record " . $row_counts . "\n\r";

}

echo "Final อัปเดตเรียบร้อยแล้ว: ปี " . $DI_YEAR . " จำนวน " . $row_counts . "\n\r";


/*
SELECT * FROM ims_data_sale_sac_all
WHERE DI_YEAR = 2021 AND (SKU_CODE LIKE 'LE%' OR SKU_CODE LIKE 'LL%' OR SKU_CODE LIKE 'AT%') AND SKU_CODE NOT LIKE 'CL%'
ORDER BY CAST(DI_YEAR AS UNSIGNED),CAST(DI_MONTH AS UNSIGNED),CAST(DI_DAY AS UNSIGNED)
*/


