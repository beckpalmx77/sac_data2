<?php

$month = $_POST["month"];
$year = $_POST["year"];
$branch = $_POST["branch"];

$total = 0;
$total_sale = 0;

$sql_data = "  SELECT BRANCH,PGROUP,ims_pgroup.pgroup_name,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 LEFT JOIN ims_pgroup
 ON ims_pgroup.pgroup_id = ims_product_sale_cockpit.pgroup
 WHERE DI_MONTH = '" . $month . "' AND DI_YEAR = '" . $year . "' AND BRANCH = '" . $branch . "' AND TRD_G_KEYIN > 0  
 GROUP BY  BRANCH,PGROUP,pgroup_name 
 ORDER BY PGROUP  ";

?>
<br>

<table id="example" class="display table table-striped table-bordered"
       cellspacing="0" width="100%">
    <thead>
    <tr>
        <th>#</th>
        <th>รายการ</th>
        <th>จำนวนเงิน</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th>#</th>
        <th>รายการ</th>
        <th>จำนวนเงิน</th>
    </tr>
    </tfoot>
    <tbody>
    <?php

    $statement_data = $conn->query($sql_data);
    $results_data = $statement_data->fetchAll(PDO::FETCH_ASSOC);
    $cnt = 1 ;
    foreach ($results_data
    as $row_data) { ?>
    <tr>
        <td><?php echo htmlentities($cnt); ?></td>
        <td><?php echo htmlentities($row_data['pgroup_name']); ?></td>
        <td><p class="number"><?php echo htmlentities(number_format($row_data['TRD_G_KEYIN'], 2)); ?></p></td>
        <?php $total_sale = $total_sale + $row_data['TRD_G_KEYIN']; ?>
        <?php $cnt = $cnt+1; ?>
        <?php } ?>
    </tbody>
    <div><b><?php echo "ยอดรวมรายการ ตามกลุ่มสินค้า ทั้งหมด  = " . number_format($total_sale, 2) . " บาท " ?></b></div>
</table>
