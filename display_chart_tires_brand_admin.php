<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            สถิติ มูลค่าการขายยาง Cockpit แต่ละยี่ห้อ
        </div>
        <div class="card-body">
            <h5 class="card-title">ปี <?php echo date("Y"); ?></h5>
            <canvas id="myChart2" width="200" height="200"></canvas>
        </div>
        <div class="card-body">
            <table id="example" class="display table table-striped table-bordered"
                   cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>ยี่ห้อ</th>
                    <?php
                    if (!$detect->isMobile()) {
                        ?>
                        <th>จำนวน (เส้น)</th>
                    <?php } ?>
                    <th>ยอดขาย</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>ยี่ห้อ</th>
                    <?php
                    if (!$detect->isMobile()) {
                        ?>
                        <th>จำนวน (เส้น)</th>
                    <?php } ?>
                    <th>ยอดขาย</th>
                </tr>
                </tfoot>
                <tbody>
                <form id="myform" name="myform" method="post">
                    <input type="hidden" name="year" id="year" class="form-control"
                           value="<?php echo date("Y"); ?>">
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="button" id="BtnSale" name="BtnSale"
                                    class="btn btn-info mb-3">แสดง
                                Chart ยอดขายยางแต่ละยี่ห้อ ตามเดือน Click
                            </button>
                        </div>
                    </div>
                </form>
                <br>
                <?php
                $year = date("Y");
                $total = 0;
                $total_sale = 0;

                //WHERE SKU_CAT IN ('2SAC01','2SAC02','2SAC03','2SAC02','2SAC04','2SAC05','2SAC06','2SAC07','2SAC08','2SAC09','2SAC10','2SAC11','2SAC12','2SAC13','2SAC14','2SAC15')

                $sql_brand = "SELECT BRN_CODE,BRN_NAME,SKU_CAT,ICCAT_NAME,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN 
                                        FROM ims_product_sale_cockpit                                        
                                        WHERE PGROUP IN ('P1')                                        
                                        AND DI_YEAR = '" . $year . "'
                                        GROUP BY BRN_CODE,BRN_NAME,SKU_CAT,ICCAT_NAME
                                        ORDER BY SKU_CAT ";

                $statement_brand = $conn->query($sql_brand);
                $results_brand = $statement_brand->fetchAll(PDO::FETCH_ASSOC);

                foreach ($results_brand

                as $row_brand) { ?>

                <tr>
                    <td><?php echo htmlentities($row_brand['BRN_NAME']); ?></td>
                    <?php
                    if (!$detect->isMobile()) {
                        ?>
                        <td><?php echo htmlentities(number_format($row_brand['TRD_QTY'], 2)); ?></td>
                    <?php } ?>
                    <?php $total = $total + $row_brand['TRD_QTY']; ?>
                    <td>
                        <p class="number"><?php echo htmlentities(number_format($row_brand['TRD_G_KEYIN'], 2)); ?></p>
                    </td>
                    <?php $total_sale = $total_sale + $row_brand['TRD_G_KEYIN']; ?>
                    <?php } ?>

                </tbody>
                <?php echo "รวม : ยางทั้งหมด  = " . number_format($total, 2) . " เส้น จำนวนเงินรวม = " . number_format($total_sale, 2) . " บาท " ?>
            </table>
        </div>
    </div>
</div>