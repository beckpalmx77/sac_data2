<?php
include 'config/connect_db.php';

try {


    // ดึงข้อมูลซ้ำ
    $sql = "
        SELECT 
            DI_DAY, DI_MONTH_NAME, DI_YEAR, DI_REF, AR_CODE, SKU_CODE, SKU_CAT, DETAIL,
            WL_CODE, TRD_QTY, TRD_PRC, TRD_AMOUNT_PRICE, TRD_SEQ,
            COUNT(*) AS duplicate_count
        FROM ims_data_sale_sac_all
        GROUP BY 
            DI_DAY, DI_MONTH_NAME, DI_YEAR, DI_REF, AR_CODE, SKU_CODE, SKU_CAT, DETAIL,
            WL_CODE, TRD_QTY, TRD_PRC, TRD_AMOUNT_PRICE, TRD_SEQ
        HAVING COUNT(*) > 1
        ORDER BY duplicate_count DESC
    ";

    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>รายการข้อมูลซ้ำ</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #f0f0f0; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background-color: #333; }
        tr:nth-child(even) { background-color: #1e1e1e; }
    </style>
</head>
<body>

<h2>รายการข้อมูลซ้ำใน ims_data_sale_sac_all</h2>

<table>
    <tr>
        <th>DI_DAY</th><th>DI_MONTH_NAME</th><th>DI_YEAR</th><th>DI_REF</th>
        <th>AR_CODE</th><th>SKU_CODE</th><th>WL_CODE</th><th>TRD_QTY</th>
        <th>TRD_PRC</th><th>TRD_AMOUNT_PRICE</th><th>TRD_SEQ</th><th>จำนวนซ้ำ</th>
    </tr>
    <?php foreach ($rows as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['DI_DAY']) ?></td>
        <td><?= htmlspecialchars($row['DI_MONTH_NAME']) ?></td>
        <td><?= htmlspecialchars($row['DI_YEAR']) ?></td>
        <td><?= htmlspecialchars($row['DI_REF']) ?></td>
        <td><?= htmlspecialchars($row['AR_CODE']) ?></td>
        <td><?= htmlspecialchars($row['SKU_CODE']) ?></td>
        <td><?= htmlspecialchars($row['WL_CODE']) ?></td>
        <td><?= htmlspecialchars($row['TRD_QTY']) ?></td>
        <td><?= htmlspecialchars($row['TRD_PRC']) ?></td>
        <td><?= htmlspecialchars($row['TRD_AMOUNT_PRICE']) ?></td>
        <td><?= htmlspecialchars($row['TRD_SEQ']) ?></td>
        <td><?= $row['duplicate_count'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
