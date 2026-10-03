<?php
include('../config/connect_db.php');

$sql = "SELECT SLMN_NAME, SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) AS total
        FROM ims_product_sale_sac
        WHERE DI_MONTH = ?
        AND DI_YEAR = ?
        GROUP BY SLMN_NAME
        ORDER BY SLMN_NAME";

$stmt = $conn->prepare($sql);
$stmt->execute([date("n"), date("Y")]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
