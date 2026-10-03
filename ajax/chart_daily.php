<?php
include('../config/connect_db.php');

$date = date("d/m/Y");

$sql = "SELECT SLMN_NAME, SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) AS total
        FROM ims_product_sale_sac
        WHERE DI_DATE = ?
        GROUP BY SLMN_NAME
        ORDER BY SLMN_NAME";

$stmt = $conn->prepare($sql);
$stmt->execute([$date]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
