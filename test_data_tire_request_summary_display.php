<?php
session_start();
error_reporting(0);
include("config/connect_db.php");

$month = $_POST["month"];
$year = $_POST["year"];

$sql_curr_month = " SELECT * FROM ims_month where month = '" . $month . "'";

$month_str = str_pad($month, 2, 0, STR_PAD_LEFT);

$stmt_curr_month = $conn->prepare($sql_curr_month);
$stmt_curr_month->execute();
$MonthCurr = $stmt_curr_month->fetchAll();
foreach ($MonthCurr as $row_curr) {
    $month_name = $row_curr["month_name"];
}


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

<body onload="">


<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        text-align: left;
        padding: 8px;
    }

</style>

<style>
    /* HOVER STYLES */

    div#pop-up {
        display: none;
        position: absolute;
        width: 280px;
        padding: 10px;
        background: #eeeeee;
        color: #000000;
        border: 1px solid #1a1a1a;
        font-size: 90%;
    }
</style>

<script>
    $(function () {
        var moveLeft = 20;
        var moveDown = 10;

        $('a.trigger').hover(function (e) {
            $('div#pop-up').show();
            //.css('top', e.pageY + moveDown)
            //.css('left', e.pageX + moveLeft)
            //.appendTo('body');
        }, function () {
            $('div#pop-up').hide();
        });

        $('a.trigger').mousemove(function (e) {
            $("div#pop-up").css('top', e.pageY + moveDown).css('left', e.pageX + moveLeft);
        });

    });
</script>

<p class="card">
<div class="card-header bg-primary text-white">
    <i class="fa fa-signal" aria-hidden="true"></i> รายการยางที่ต้องการ
</div>
<input type="hidden" name="year" id="year" class="form-control" value="<?php echo $year; ?>">

<!--div class="card-body">
    <a id="myLink" href="#" onclick="PrintPage();"><i class="fa fa-print"></i> พิมพ์</a>
</div-->


<div class="card">
    <div class="card-body">
        <form id="myform" name="myform" method="post">
            <div class="card-body">
                <h4>
                    <span class="badge bg-success"><?php echo $month_name; ?> : <?php echo $year ?></span>
                </h4>
            </div>
        </form>

    </div>
</div>


<div style="overflow-x:auto;">

    <table id="example" class="display table table-striped table-bordered"
           cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>ยี่ห้อ</th>
            <th>ลาย</th>
            <th>รหัส</th>
            <th>รายการ</th>
            <?php

            for ($day = 1; $day <= 31; $day++) {

                echo "  <th>" . $day . " </th>
                <th>" . $day . " </th>
                <th>" . $day . " </th>
                <th>" . $day . " </th>
                <th>" . $day . " </th>";

            }
            ?>
            <th>สรุป</th>
        </tr>

        </thead>
        <thead>
        <tr>
            <th>สินค้า</th>
            <th>ดอกยาง</th>
            <th>สินค้า</th>
            <th>สินค้า</th>
            <?php

            for ($day = 1; $day <= 31; $day++) {

                echo "  <th>Cust.</th>
                <th>Take/Sale</th>
                <th>Stock</th>
                <th>Date In</th>
                <th>Qty Need</th>";
            }
            ?>
            <th>ยอดรวม</th>
        </tr>

        </thead>

        <tfoot>
        </tfoot>
        <tbody>
        <?php
        $date = date("d/m/Y");
        $total = 0;
        $sql_data = " SELECT date_request,tires_brand,tires_class,tires_code,tires_detail,customer_name,sale_name,remark 
 ,date_in ,date_req,month_req,year_req,
IF(date_req='01',customer_name,'-') AS 1_CUST,
IF(date_req='01',sale_name,'-') AS 1_SALE,
IF(date_req='01',remark,'-') AS 1_STOCK,
IF(date_req='01',date_in,'-') AS 1_DATE_IN,    
SUM(IF(date_req='01',qty_need,0)) AS 1_QTY,
IF(date_req='02',customer_name,'-') AS 2_CUST,
IF(date_req='02',sale_name,'-') AS 2_SALE,
IF(date_req='02',remark,'-') AS 2_STOCK,
IF(date_req='02',date_in,'-') AS 2_DATE_IN,    
SUM(IF(date_req='02',qty_need,0)) AS 2_QTY,
IF(date_req='03',customer_name,'-') AS 3_CUST,
IF(date_req='03',sale_name,'-') AS 3_SALE,
IF(date_req='03',remark,'-') AS 3_STOCK,
IF(date_req='03',date_in,'-') AS 3_DATE_IN,    
SUM(IF(date_req='03',qty_need,0)) AS 3_QTY,
IF(date_req='04',customer_name,'-') AS 4_CUST,
IF(date_req='04',sale_name,'-') AS 4_SALE,
IF(date_req='04',remark,'-') AS 4_STOCK,
IF(date_req='04',date_in,'-') AS 4_DATE_IN,
SUM(IF(date_req='04',qty_need,0)) AS 4_QTY,
IF(date_req='05',customer_name,'-') AS 5_CUST,
IF(date_req='05',sale_name,'-') AS 5_SALE,
IF(date_req='05',remark,'-') AS 5_STOCK,
IF(date_req='05',date_in,'-') AS 5_DATE_IN,
SUM(IF(date_req='05',qty_need,0)) AS 5_QTY,
IF(date_req='06',customer_name,'-') AS 6_CUST,
IF(date_req='06',sale_name,'-') AS 6_SALE,
IF(date_req='06',remark,'-') AS 6_STOCK,
IF(date_req='06',date_in,'-') AS 6_DATE_IN,
SUM(IF(date_req='06',qty_need,0)) AS 6_QTY,
IF(date_req='07',customer_name,'-') AS 7_CUST,
IF(date_req='07',sale_name,'-') AS 7_SALE,
IF(date_req='07',remark,'-') AS 7_STOCK,
IF(date_req='07',date_in,'-') AS 7_DATE_IN,
SUM(IF(date_req='07',qty_need,0)) AS 7_QTY,
IF(date_req='08',customer_name,'-') AS 8_CUST,
IF(date_req='08',sale_name,'-') AS 8_SALE,
IF(date_req='08',remark,'-') AS 8_STOCK,
IF(date_req='08',date_in,'-') AS 8_DATE_IN,
SUM(IF(date_req='08',qty_need,0)) AS 8_QTY,
IF(date_req='09',customer_name,'-') AS 9_CUST,
IF(date_req='09',sale_name,'-') AS 9_SALE,
IF(date_req='09',remark,'-') AS 9_STOCK,
IF(date_req='09',date_in,'-') AS 9_DATE_IN,
SUM(IF(date_req='09',qty_need,0)) AS 9_QTY,
IF(date_req='10',customer_name,'-') AS 10_CUST,
IF(date_req='10',sale_name,'-') AS 10_SALE,
IF(date_req='10',remark,'-') AS 10_STOCK,
IF(date_req='10',date_in,'-') AS 10_DATE_IN,
SUM(IF(date_req='10',qty_need,0)) AS 10_QTY,
IF(date_req='11',customer_name,'-') AS 11_CUST,
IF(date_req='11',sale_name,'-') AS 11_SALE,
IF(date_req='11',remark,'-') AS 11_STOCK,
IF(date_req='11',date_in,'-') AS 11_DATE_IN,
SUM(IF(date_req='11',qty_need,0)) AS 11_QTY,
IF(date_req='12',customer_name,'-') AS 12_CUST,
IF(date_req='12',sale_name,'-') AS 12_SALE,
IF(date_req='12',remark,'-') AS 12_STOCK,
IF(date_req='12',date_in,'-') AS 12_DATE_IN,
SUM(IF(date_req='12',qty_need,0)) AS 12_QTY,
IF(date_req='13',customer_name,'-') AS 13_CUST,
IF(date_req='13',sale_name,'-') AS 13_SALE,
IF(date_req='13',remark,'-') AS 13_STOCK,
IF(date_req='13',date_in,'-') AS 13_DATE_IN,
SUM(IF(date_req='13',qty_need,0)) AS 13_QTY,
IF(date_req='14',customer_name,'-') AS 14_CUST,
IF(date_req='14',sale_name,'-') AS 14_SALE,
IF(date_req='14',remark,'-') AS 14_STOCK,
IF(date_req='14',date_in,'-') AS 14_DATE_IN,
SUM(IF(date_req='14',qty_need,0)) AS 14_QTY,
IF(date_req='15',customer_name,'-') AS 15_CUST,
IF(date_req='15',sale_name,'-') AS 15_SALE,
IF(date_req='15',remark,'-') AS 15_STOCK,
IF(date_req='15',date_in,'-') AS 15_DATE_IN,
SUM(IF(date_req='15',qty_need,0)) AS 15_QTY,
IF(date_req='16',customer_name,'-') AS 16_CUST,
IF(date_req='16',sale_name,'-') AS 16_SALE,
IF(date_req='16',remark,'-') AS 16_STOCK,
IF(date_req='16',date_in,'-') AS 16_DATE_IN,
SUM(IF(date_req='16',qty_need,0)) AS 16_QTY,
IF(date_req='17',customer_name,'-') AS 17_CUST,
IF(date_req='17',sale_name,'-') AS 17_SALE,
IF(date_req='17',remark,'-') AS 17_STOCK,
IF(date_req='17',date_in,'-') AS 17_DATE_IN,
SUM(IF(date_req='17',qty_need,0)) AS 17_QTY,
IF(date_req='18',customer_name,'-') AS 18_CUST,
IF(date_req='18',sale_name,'-') AS 18_SALE,
IF(date_req='18',remark,'-') AS 18_STOCK,
IF(date_req='18',date_in,'-') AS 18_DATE_IN,
SUM(IF(date_req='18',qty_need,0)) AS 18_QTY,
IF(date_req='19',customer_name,'-') AS 19_CUST,
IF(date_req='19',sale_name,'-') AS 19_SALE,
IF(date_req='19',remark,'-') AS 19_STOCK,
IF(date_req='19',date_in,'-') AS 19_DATE_IN,
SUM(IF(date_req='19',qty_need,0)) AS 19_QTY,
IF(date_req='20',customer_name,'-') AS 20_CUST,
IF(date_req='20',sale_name,'-') AS 20_SALE,
IF(date_req='20',remark,'-') AS 20_STOCK,
IF(date_req='20',date_in,'-') AS 20_DATE_IN,
SUM(IF(date_req='20',qty_need,0)) AS 20_QTY,
IF(date_req='21',customer_name,'-') AS 21_CUST,
IF(date_req='21',sale_name,'-') AS 21_SALE,
IF(date_req='21',remark,'-') AS 21_STOCK,
IF(date_req='21',date_in,'-') AS 21_DATE_IN,
SUM(IF(date_req='21',qty_need,0)) AS 21_QTY,
IF(date_req='22',customer_name,'-') AS 22_CUST,
IF(date_req='22',sale_name,'-') AS 22_SALE,
IF(date_req='22',remark,'-') AS 22_STOCK,
IF(date_req='22',date_in,'-') AS 22_DATE_IN,
SUM(IF(date_req='22',qty_need,0)) AS 22_QTY,
IF(date_req='23',customer_name,'-') AS 23_CUST,
IF(date_req='23',sale_name,'-') AS 23_SALE,
IF(date_req='23',remark,'-') AS 23_STOCK,
IF(date_req='23',date_in,'-') AS 23_DATE_IN,
SUM(IF(date_req='23',qty_need,0)) AS 23_QTY,
IF(date_req='24',customer_name,'-') AS 24_CUST,
IF(date_req='24',sale_name,'-') AS 24_SALE,
IF(date_req='24',remark,'-') AS 24_STOCK,
IF(date_req='24',date_in,'-') AS 24_DATE_IN,
SUM(IF(date_req='24',qty_need,0)) AS 24_QTY,
IF(date_req='25',customer_name,'-') AS 25_CUST,
IF(date_req='25',sale_name,'-') AS 25_SALE,
IF(date_req='25',remark,'-') AS 25_STOCK,
IF(date_req='25',date_in,'-') AS 25_DATE_IN,
SUM(IF(date_req='25',qty_need,0)) AS 25_QTY,
IF(date_req='26',customer_name,'-') AS 26_CUST,
IF(date_req='26',sale_name,'-') AS 26_SALE,
IF(date_req='26',remark,'-') AS 26_STOCK,
IF(date_req='26',date_in,'-') AS 26_DATE_IN,
SUM(IF(date_req='26',qty_need,0)) AS 26_QTY,
IF(date_req='27',customer_name,'-') AS 27_CUST,
IF(date_req='27',sale_name,'-') AS 27_SALE,
IF(date_req='27',remark,'-') AS 27_STOCK,
IF(date_req='27',date_in,'-') AS 27_DATE_IN,
SUM(IF(date_req='27',qty_need,0)) AS 27_QTY,
IF(date_req='28',customer_name,'-') AS 28_CUST,
IF(date_req='28',sale_name,'-') AS 28_SALE,
IF(date_req='28',remark,'-') AS 28_STOCK,
IF(date_req='28',date_in,'-') AS 28_DATE_IN,
SUM(IF(date_req='28',qty_need,0)) AS 28_QTY,
IF(date_req='29',customer_name,'-') AS 29_CUST,
IF(date_req='29',sale_name,'-') AS 29_SALE,
IF(date_req='29',remark,'-') AS 29_STOCK,
IF(date_req='29',date_in,'-') AS 29_DATE_IN,
SUM(IF(date_req='29',qty_need,0)) AS 29_QTY,
IF(date_req='30',customer_name,'-') AS 30_CUST,
IF(date_req='30',sale_name,'-') AS 30_SALE,
IF(date_req='30',remark,'-') AS 30_STOCK,
IF(date_req='30',date_in,'-') AS 30_DATE_IN,
SUM(IF(date_req='30',qty_need,0)) AS 30_QTY,
IF(date_req='31',customer_name,'-') AS 31_CUST,
IF(date_req='31',sale_name,'-') AS 31_SALE,
IF(date_req='31',remark,'-') AS 31_STOCK,
IF(date_req='31',date_in,'-') AS 31_DATE_IN,
SUM(IF(date_req='31',qty_need,0)) AS 31_QTY,
SUM(qty_need) AS TOTAL_QTY_NEED
FROM v_ims_tires_request 
WHERE month_req = '" . $month_str . "'"
            . " AND year_req = '" . $year . "'"

            . " GROUP BY date_request,date_in,customer_name,sale_name,tires_brand,tires_class,tires_detail,sale_name
ORDER BY tires_detail ,  CONVERT(year_req, FLOAT),CONVERT(date_request, FLOAT) ";

        //$myfile = fopen("param_post_sql.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $sql_data);
        //fclose($myfile);

        $statement_data = $conn->query($sql_data);
        $results_data = $statement_data->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results_data

        as $row_data) { ?>

        <tr>

            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['tires_brand']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['tires_class']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['tires_code']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['tires_detail']); ?></p>
            </td>

            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['1_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['1_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['1_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['1_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['1_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['2_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['2_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['2_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['2_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['2_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['3_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['3_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['3_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['3_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['3_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['4_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['4_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['4_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['4_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['4_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['5_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['5_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['5_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['5_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['5_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['6_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['6_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['6_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['6_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['6_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['7_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['7_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['7_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['7_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['7_QTY'], 2)); ?></p>
            </td>

            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['8_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['8_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['8_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['8_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['8_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['9_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['9_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['9_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['9_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['9_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['10_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['10_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['10_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['10_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['10_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['11_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['11_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['11_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['11_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['11_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['12_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['12_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['12_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['12_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['12_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['13_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['13_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['13_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['13_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['13_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['14_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['14_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['14_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['14_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['14_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['15_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['15_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['15_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['15_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['15_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['16_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['16_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['16_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['16_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['16_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['17_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['17_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['17_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['17_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['17_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['18_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['18_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['18_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['18_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['18_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['19_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['19_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['19_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['19_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['19_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['20_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['20_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['20_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['20_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['20_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['21_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['21_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['21_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['21_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['21_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['22_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['22_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['22_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['22_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['22_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['23_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['23_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['23_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['23_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['23_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['24_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['24_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['24_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['24_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['24_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['25_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['25_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['25_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['25_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['25_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['26_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['26_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['26_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['26_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['26_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['27_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['27_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['27_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['27_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['27_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['28_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['28_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['28_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['28_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['28_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['29_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['29_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['29_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['29_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['29_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['30_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['30_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['30_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['30_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['30_QTY'], 2)); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['31_CUST']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['31_SALE']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['31_STOCK']); ?></p>
            </td>
            <td align="left"><p
                        class="text-center"><?php echo htmlentities($row_data['31_DATE_IN']); ?></p>
            </td>
            <td align="right"><p
                        class="number"><?php echo htmlentities(number_format($row_data['31_QTY'], 2)); ?></p>
            </td>
            <td align="right"><a href="#"
                                 class="trigger"><?php echo htmlentities(number_format($row_data['TOTAL_QTY_NEED'], 2)); ?></a>


</div>
</td>

<td align="right"><p
            class="text-center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
</td>


<?php } ?>

</tbody>
</table>

</div>

</body>
</html>

