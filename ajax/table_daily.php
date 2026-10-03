<?php
include('../config/connect_db.php');

$date = date("d/m/Y");
$total = 0;

$sql = "SELECT SLMN_NAME, SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) AS total
        FROM ims_product_sale_sac
        WHERE DI_DATE = ?
        GROUP BY SLMN_NAME";

$stmt = $conn->prepare($sql);
$stmt->execute([$date]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Sale</th>
        <th class="text-end">ยอดขาย</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row): $total += $row['total']; ?>
        <tr>
            <td><?= htmlentities($row['SLMN_NAME']) ?></td>
            <td class="text-end"><?= number_format($row['total'],2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<strong>รวมทั้งหมด <?= number_format($total,2) ?> บาท</strong>
