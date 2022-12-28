<?php

include("config/connect_db.php");

$month = $_POST["month"];
$year = $_POST["year"];

//$month = "4";
//$year = "2022";

$month_name = "";

$sql_month = " SELECT * FROM ims_month where month = '" . $month . "'";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->execute();
$MonthRecords = $stmt_month->fetchAll();
foreach ($MonthRecords as $row) {
    $month_name = $row["month_name"];
}

//$myfile = fopen("param_post.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $month . "| month_name " . $month_name . "| SLMN_NAME = " . $_POST["SLMN_NAME"] . "| SLMN_NAME Name = "
//    . $SLMN_NAME_name . " | " . $sql_month . " | " . $sql_SLMN_NAME);
//fclose($myfile);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta date="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <script src="js/jquery-3.6.0.js"></script>
    <!--script src="js/chartjs-2.9.0.js"></script-->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css">

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script src='js/util.js'></script>

    <title>สงวนออโต้คาร์</title>

</head>

<body onload="showGraph_Data_Monthly(1);showGraph_Data_Monthly(2);showGraph_Data_Monthly(3);">

<p class="card">
<div class="card-header bg-primary text-white">
    <i class="fa fa-signal" aria-hidden="true"></i> ยอดขายเปรียบเทียบ
    <?php echo " เดือน " . $month_name . " ปี " . $year; ?>
</div>
<input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
<input type="hidden" name="year" id="year" class="form-control" value="<?php echo $year; ?>">

<div class="card-body">
    <a id="myLink" href="#" onclick="PrintPage();"><i class="fa fa-print"></i> พิมพ์</a>
</div>

<!--div class="card-body">

    <div class="card-body">
        <h4><span class="badge bg-success">ยอดขาย ยาง อะไหล่ ค่าแรง-ค่าบริการ</span></h4>
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>สาขา</th>
                <th>ยอดขาย ยาง</th>
                <th>ยอดขาย อะไหล่</th>
                <th>ยอด ค่าแรง-ค่าบริการ</th>
                <th>ยอดรวม</th>
            </tr>
            </thead>
            <tfoot>
            </tfoot>
            <tbody>
            <?php
            $date = date("d/m/Y");
            $total = 0;
            $sql_total = " SELECT *
 FROM ims_report_product_sale_summary_2 
 WHERE DI_YEAR = '" . $year . "' 
 AND DI_MONTH = '" . $month . "'
 ORDER BY SLMN_NAME";

            $statement_total = $conn->query($sql_total);
            $results_total = $statement_total->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_total

            as $row_total) { ?>

            <tr>
                <td><?php echo htmlentities($row_total['SLMN_NAME']); ?></td>
                <td align="right">
                    <p class="number"><?php echo htmlentities(number_format($row_total['tires_total_amt'], 2)); ?></p>
                </td>
                <td align="right">
                    <p class="number"><?php echo htmlentities(number_format($row_total['part_total_amt'], 2)); ?></p>
                </td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_total['svr_total_amt'], 2)); ?></p>
                </td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_total['total_amt'], 2)); ?></p>
                </td>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div-->

<div class="card">
    <div class="card-body">
        <h4><span class="badge bg-success">ยอดขาย ยางตามยี่ห้อ</span></h4>
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>SALE</th>
                <th>AT</th>
                <th>AT</th>
                <th>BF</th>
                <th>BF</th>
                <th>BKT</th>
                <th>BKT</th>
                <th>BS</th>
                <th>BS</th>
                <th>BS.</th>
                <th>BS.</th>
                <th>DBC</th>
                <th>DBC</th>
                <th>DL</th>
                <th>DL</th>
                <th>DS</th>
                <th>DS</th>
                <th>DS.</th>
                <th>DS.</th>
                <th>DT</th>
                <th>DT</th>
                <th>DT.</th>
                <th>DT.</th>
                <th>FS</th>
                <th>FS</th>
                <th>FS.</th>
                <th>FS.</th>
                <th>GY</th>
                <th>GY</th>
            </tr>
            <tr>
                <th></th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
ึ                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
            </tr>
            </thead>
            <tfoot>
            </tfoot>
            <tbody>
            <?php
            $date = date("d/m/Y");
            $total = 0;
            $sql_brand = " 
SELECT
SLMN_NAME,
SUM(IF(BRN_CODE='AT',TRD_QTY,0)) AS AT_QTY,
SUM(IF(BRN_CODE='AT',TRD_G_KEYIN,0)) AS AT_AMT,
SUM(IF(BRN_CODE='BF',TRD_QTY,0)) AS BF_QTY,
SUM(IF(BRN_CODE='BF',TRD_G_KEYIN,0)) AS BF_AMT,
SUM(IF(BRN_CODE='BKT',TRD_QTY,0)) AS BKT_QTY,
SUM(IF(BRN_CODE='BKT',TRD_G_KEYIN,0)) AS BKT_AMT,
SUM(IF(BRN_CODE='BS',TRD_QTY,0)) AS BS_QTY,
SUM(IF(BRN_CODE='BS',TRD_G_KEYIN,0)) AS BS_AMT,
SUM(IF(BRN_CODE='BS.',TRD_QTY,0)) AS BS__QTY,
SUM(IF(BRN_CODE='BS.',TRD_G_KEYIN,0)) AS BS__AMT,
SUM(IF(BRN_CODE='DBC',TRD_QTY,0)) AS DBC_QTY,
SUM(IF(BRN_CODE='DBC',TRD_G_KEYIN,0)) AS DBC_AMT,
SUM(IF(BRN_CODE='DL',TRD_QTY,0)) AS DL_QTY,
SUM(IF(BRN_CODE='DL',TRD_G_KEYIN,0)) AS DL_AMT,
SUM(IF(BRN_CODE='DS',TRD_QTY,0)) AS DS_QTY,
SUM(IF(BRN_CODE='DS',TRD_G_KEYIN,0)) AS DS_AMT,
SUM(IF(BRN_CODE='DS.',TRD_QTY,0)) AS DS__QTY,
SUM(IF(BRN_CODE='DS.',TRD_G_KEYIN,0)) AS DS__AMT,
SUM(IF(BRN_CODE='DT',TRD_QTY,0)) AS DT_QTY,
SUM(IF(BRN_CODE='DT',TRD_G_KEYIN,0)) AS DT_AMT,
SUM(IF(BRN_CODE='DT.',TRD_QTY,0)) AS DT__QTY,
SUM(IF(BRN_CODE='DT.',TRD_G_KEYIN,0)) AS DT__AMT,
SUM(IF(BRN_CODE='FS',TRD_QTY,0)) AS FS_QTY,
SUM(IF(BRN_CODE='FS',TRD_G_KEYIN,0)) AS FS_AMT,
SUM(IF(BRN_CODE='FS.',TRD_QTY,0)) AS FS__QTY,
SUM(IF(BRN_CODE='FS.',TRD_G_KEYIN,0)) AS FS__AMT,
SUM(IF(BRN_CODE='GY',TRD_QTY,0)) AS GY_QTY,
SUM(IF(BRN_CODE='GY',TRD_G_KEYIN,0)) AS GY_AMT     
 FROM ims_product_sale_sac 
 WHERE DI_YEAR = '" . $year . "'
 AND DI_MONTH = '" . $month . "'  
 AND PGROUP like '%P1'
 GROUP BY SLMN_NAME 
 ORDER BY SLMN_NAME";

            $statement_brand = $conn->query($sql_brand);
            $results_brand = $statement_brand->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_brand

            as $row_brand) { ?>

            <tr>
                <td><?php echo htmlentities($row_brand['SLMN_NAME']); ?></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['AT_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['AT_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BF_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BF_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BKT_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BKT_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BS_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BS_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BS__QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['BS__AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DBC_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DBC_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DL_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DL_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DS_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DS_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DS__QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DS__AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DT_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DT_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DT__QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['DT__AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['FS_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['FS_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['FS__QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['FS__AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['GY_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['GY_AMT'], 2)); ?></p></td>
                <?php } ?>

            </tbody>
        </table>
    </div>
</div>



<div class="card">
    <div class="card-body">
        <h4><span class="badge bg-success">ยอดขาย ยางตามยี่ห้อ</span></h4>
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>SALE</th>
                <th>HT</th>
                <th>HT</th>
                <th>LE</th>
                <th>LE</th>
                <th>LL</th>
                <th>LL</th>
                <th>LLIT</th>
                <th>LLIT</th>
                <th>ML</th>
                <th>ML.</th>
                <th>MT</th>
                <th>MT</th>
                <th>MX</th>
                <th>MX</th>
                <th>PL</th>
                <th>PL</th>
                <th>SP</th>
                <th>SP</th>
                <th>VB</th>
                <th>VB</th>
                <th>WTL</th>
                <th>WTL</th>
                <th>YK</th>
                <th>YK</th>
            </tr>
            <tr>
                <th></th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
                <th>(เส้น)</th>
                <th>(บาท)</th>
            </tr>
            </thead>
            <tfoot>
            </tfoot>
            <tbody>
            <?php
            $date = date("d/m/Y");
            $total = 0;
            $sql_brand = " 
SELECT
SLMN_NAME,
SUM(IF(BRN_CODE='HT',TRD_QTY,0)) AS HT_QTY,
SUM(IF(BRN_CODE='HT',TRD_G_KEYIN,0)) AS HT_AMT,
SUM(IF(BRN_CODE='LE',TRD_QTY,0)) AS LE_QTY,
SUM(IF(BRN_CODE='LE',TRD_G_KEYIN,0)) AS LE_AMT,
SUM(IF(BRN_CODE='LL',TRD_QTY,0)) AS LL_QTY,
SUM(IF(BRN_CODE='LL',TRD_G_KEYIN,0)) AS LL_AMT,
SUM(IF(BRN_CODE='LLIT',TRD_QTY,0)) AS LLIT_QTY,
SUM(IF(BRN_CODE='LLIT',TRD_G_KEYIN,0)) AS LLIT_AMT,
SUM(IF(BRN_CODE='ML',TRD_QTY,0)) AS ML_QTY,
SUM(IF(BRN_CODE='ML',TRD_G_KEYIN,0)) AS ML_AMT,
SUM(IF(BRN_CODE='ML.',TRD_QTY,0)) AS ML__QTY,
SUM(IF(BRN_CODE='ML.',TRD_G_KEYIN,0)) AS ML__AMT,
SUM(IF(BRN_CODE='MX',TRD_QTY,0)) AS MX_QTY,
SUM(IF(BRN_CODE='MX',TRD_G_KEYIN,0)) AS MX_AMT,
SUM(IF(BRN_CODE='PL',TRD_QTY,0)) AS PL_QTY,
SUM(IF(BRN_CODE='PL',TRD_G_KEYIN,0)) AS PL_AMT,
SUM(IF(BRN_CODE='SP',TRD_QTY,0)) AS SP_QTY,
SUM(IF(BRN_CODE='SP',TRD_G_KEYIN,0)) AS SP_AMT,
SUM(IF(BRN_CODE='VB',TRD_QTY,0)) AS VB_QTY,
SUM(IF(BRN_CODE='VB',TRD_G_KEYIN,0)) AS VB_AMT,
SUM(IF(BRN_CODE='WTL',TRD_QTY,0)) AS WTL_QTY,
SUM(IF(BRN_CODE='WTL',TRD_G_KEYIN,0)) AS WTL_AMT,
SUM(IF(BRN_CODE='YK',TRD_QTY,0)) AS YK_QTY,
SUM(IF(BRN_CODE='YK',TRD_G_KEYIN,0)) AS YK_AMT                 
 FROM ims_product_sale_sac 
 WHERE DI_YEAR = '" . $year . "'
 AND DI_MONTH = '" . $month . "'  
 AND PGROUP like '%P1'
 GROUP BY SLMN_NAME 
 ORDER BY SLMN_NAME";

            $statement_brand = $conn->query($sql_brand);
            $results_brand = $statement_brand->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_brand

            as $row_brand) { ?>

            <tr>
                <td><?php echo htmlentities($row_brand['SLMN_NAME']); ?></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['HT_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['HT_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['LE_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['LE_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['LL_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['LL_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['LLIT_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['LLIT_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['ML_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['ML_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['ML__QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['ML__AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['MX_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['MX_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['PL_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['PL_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['SP_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['SP_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['VB_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['VB_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['WTL_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['WTL_AMT'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['YK_QTY'], 2)); ?></p></td>
                <td align="right"><p class="number"><?php echo htmlentities(number_format($row_brand['YK_AMT'], 2)); ?></p></td>

                <?php } ?>

            </tbody>
        </table>
    </div>
</div>



</body>
</html>

