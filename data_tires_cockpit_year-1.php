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
//fwrite($myfile, $month . "| month_name " . $month_name . "| branch = " . $_POST["branch"] . "| Branch Name = "
//    . $branch_name . " | " . $sql_month . " | " . $sql_branch);
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

<div class="card-body">

    <div class="card-body">
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
 FROM ims_report_product_sale_summary 
 WHERE DI_YEAR = '" . $year . "' 
 AND DI_MONTH = '" . $month . "'
 ORDER BY BRANCH";

            $statement_total = $conn->query($sql_total);
            $results_total = $statement_total->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_total

            as $row_total) { ?>

            <tr>
                <td><?php echo htmlentities($row_total['BRANCH']); ?></td>
                <td>
                    <p class="number"><?php echo htmlentities(number_format($row_total['tires_total_amt'], 2)); ?></p>
                </td>
                <td>
                    <p class="number"><?php echo htmlentities(number_format($row_total['part_total_amt'], 2)); ?></p>
                </td>
                <td><p class="number"><?php echo htmlentities(number_format($row_total['svr_total_amt'], 2)); ?></p>
                </td>
                <td><p class="number"><?php echo htmlentities(number_format($row_total['total_amt'], 2)); ?></p>
                </td>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>สาขา</th>
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
                <th>(เส้น)</th>
                <th>(บาท)</th>
            </tr>
            </tr>
            </thead>
            <tfoot>
            </tfoot>
            <tbody>
            <?php
            $date = date("d/m/Y");
            $total = 0;
            $sql_brand = " 
SELECT BRANCH,
SUM(IF(DI_MONTH='1',TRD_QTY,0)) AS 1_QTY,
SUM(IF(DI_MONTH='1',TRD_G_KEYIN,0)) AS 1_AMT,
SUM(IF(DI_MONTH='2',TRD_QTY,0)) AS 2_QTY,
SUM(IF(DI_MONTH='2',TRD_G_KEYIN,0)) AS 2_AMT,
SUM(IF(DI_MONTH='3',TRD_QTY,0)) AS 3_QTY,
SUM(IF(DI_MONTH='3',TRD_G_KEYIN,0)) AS 3_AMT,
SUM(IF(DI_MONTH='4',TRD_QTY,0)) AS 4_QTY,
SUM(IF(DI_MONTH='4',TRD_G_KEYIN,0)) AS 4_AMT,
SUM(IF(DI_MONTH='5',TRD_QTY,0)) AS 5_QTY,
SUM(IF(DI_MONTH='5',TRD_G_KEYIN,0)) AS 5_AMT,
SUM(IF(DI_MONTH='6',TRD_QTY,0)) AS 6_QTY,
SUM(IF(DI_MONTH='6',TRD_G_KEYIN,0)) AS 6_AMT,
SUM(IF(DI_MONTH='7',TRD_QTY,0)) AS 7_QTY,
SUM(IF(DI_MONTH='7',TRD_G_KEYIN,0)) AS 7_AMT,
SUM(IF(DI_MONTH='8',TRD_QTY,0)) AS 8_QTY,
SUM(IF(DI_MONTH='8',TRD_G_KEYIN,0)) AS 8_AMT,
SUM(IF(DI_MONTH='9',TRD_QTY,0)) AS 9_QTY,
SUM(IF(DI_MONTH='9',TRD_G_KEYIN,0)) AS 9_AMT,
SUM(IF(DI_MONTH='10',TRD_QTY,0)) AS 10_QTY,
SUM(IF(DI_MONTH='10',TRD_G_KEYIN,0)) AS 10_AMT,
SUM(IF(DI_MONTH='11',TRD_QTY,0)) AS 11_QTY,
SUM(IF(DI_MONTH='11',TRD_G_KEYIN,0)) AS 11_AMT,
SUM(IF(DI_MONTH='12',TRD_QTY,0)) AS 12_QTY,
SUM(IF(DI_MONTH='12',TRD_G_KEYIN,0)) AS 12_AMT
 FROM ims_product_sale_cockpit 
 WHERE DI_YEAR = '" . $year . "' 
 AND PGROUP like '%P1'
 GROUP BY BRANCH 
 ORDER BY BRANCH ";

            $statement_brand = $conn->query($sql_brand);
            $results_brand = $statement_brand->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_brand

            as $row_brand) { ?>

            <tr>
                <td><?php echo htmlentities($row_brand['BRANCH']); ?></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['1_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['1_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['2_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['2_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['3_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['3_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['4_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['4_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['5_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['5_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['6_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['6_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['7_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['7_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['8_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['8_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['9_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['9_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['10_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['10_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['11_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['11_AMT'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['12_QTY'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['12_AMT'], 2)); ?></p></td>
                <?php } ?>

            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
    <input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
    <div class="card-body">
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>สาขา</th>
                <th>อะไหล่ยางใหญ่</th>
                <th>อะไหล่นอก ยางใหญ่</th>
                <th>อะไหล่ยางเล็ก</th>
                <th>อะไหล่นอก ยางเล็ก</th>
                <th>น้ำมันเครื่อง</th>
                <th>อะไหล่</th>
            </tr>
            </thead>
            <tfoot>
            <!--tr>
                <th>สาขา</th>
                <th>อะไหล่ยางใหญ่</th>
                <th>อะไหล่นอก ยางใหญ่</th>
                <th>อะไหล่ยางเล็ก</th>
                <th>อะไหล่นอก ยางเล็ก</th>
                <th>น้ำมันเครื่อง</th>
                <th>อะไหล่</th>
            </tr-->
            </tfoot>
            <tbody>
            <?php
            $total = 0;
            $total_sale = 0;
            $sql_part = " SELECT BRANCH,
SUM(IF(SKU_CAT='8BTCA01-001',TRD_G_KEYIN,0)) AS PART_1,
SUM(IF(SKU_CAT='8BTCA01-002',TRD_G_KEYIN,0)) AS PART_2,
SUM(IF(SKU_CAT='8CPA01-001',TRD_G_KEYIN,0)) AS PART_3,
SUM(IF(SKU_CAT='8CPA01-002',TRD_G_KEYIN,0)) AS PART_4,
SUM(IF(SKU_CAT='8SAC11',TRD_G_KEYIN,0)) AS PART_5,
SUM(IF(SKU_CAT='TA01-001',TRD_G_KEYIN,0)) AS PART_6
 FROM ims_product_sale_cockpit 
 WHERE DI_YEAR = '" . $year . "' 
 AND DI_MONTH = '" . $month . "'
 AND PGROUP like '%P2'
 GROUP BY BRANCH 
 ORDER BY BRANCH";

            $statement_part = $conn->query($sql_part);
            $results_part = $statement_part->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_part

            as $row_part) { ?>

            <tr>
                <td><?php echo htmlentities($row_part['BRANCH']); ?></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_part['PART_1'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_part['PART_2'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_part['PART_3'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_part['PART_4'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_part['PART_5'], 2)); ?></p></td>
                <td><p class="number"><?php echo htmlentities(number_format($row_part['PART_6'], 2)); ?></p></td>

                <?php } ?>

            </tbody>
        </table>
    </div>


</div>

</body>
</html>

