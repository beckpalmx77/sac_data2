<?php
session_start();
error_reporting(0);
include("config/connect_db.php");

$month = $_POST["month"];
$year = $_POST["year"];
$customer_id = $_POST["AR_CODE"];

$sql_cust = " SELECT * FROM v_customer_salename
                  WHERE AR_CODE = '" . $_POST["AR_CODE"] . "'";
$stmt_cust = $conn->prepare($sql_cust);
$stmt_cust->execute();
$CustRecords = $stmt_cust->fetchAll();
foreach ($CustRecords as $row) {
    $customer_name = $row["AR_NAME"];
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
    <script type="text/javascript" charset="utf-8"></script>

    <script src='js/util.js'></script>

    <title>สงวนออโต้คาร์</title>


</head>

<body onload="">

<p class="card">
<div class="card-header bg-primary text-white">
    <i class="fa fa-signal" aria-hidden="true"></i> ยอดขายเปรียบเทียบ
</div>
<input type="hidden" name="year" id="year" class="form-control" value="<?php echo $year; ?>">

<!--div class="card-body">
    <a id="myLink" href="#" onclick="PrintPage();"><i class="fa fa-print"></i> พิมพ์</a>
</div-->


<div class="card">
    <div class="card-body">
        <form id="myform" name="myform" method="post">
            <input type="hidden" id="branch" name="branch">
            <input type="hidden" name="product_group" id="product_group" class="form-control"
                   value="<?php echo $customer_id; ?>">
            <input type="hidden" name="product_group_name" id="product_group_name" class="form-control"
                   value="<?php echo $customer_id; ?>">

            <div class="card-body">
                <h4>
                    <span class="badge bg-success"><?php echo $customer_id; ?> : <?php echo $customer_name ?> ปี <?php echo $year ?></span>
                </h4>
                <!--a id="myChartLink" href="#" onclick="Chart_Page('<?php echo $customer_id ?>');">
                    <button type="button" class="btn btn-outline-primary">ดู Graph</button>
                </a-->
            </div>
        </form>
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>รายละเอียดสินค้า</th>
                <th><p style="color:rgb(8,153,23);">ราคาขาย</p></th>
                <th>มค</th>
                <th>กพ</th>
                <th>มีค</th>
                <th>เมย</th>
                <th>พค</th>
                <th>มิย</th>
                <th>กค</th>
                <th>สค</th>
                <th>กย</th>
                <th>ตค</th>
                <th>พย</th>
                <th>ธค</th>
                <th><p style="color:rgb(255,0,0);">รวม</p></th>
                <th>มค</th>
                <th>กพ</th>
                <th>มีค</th>
                <th>เมย</th>
                <th>พค</th>
                <th>มิย</th>
                <th>กค</th>
                <th>สค</th>
                <th>กย</th>
                <th>ตค</th>
                <th>พย</th>
                <th>ธค</th>
                <th><p style="color:rgb(255,0,0);">รวม</p></th>
            </tr>
            </tr>
            </thead>
            <tfoot>
            </tfoot>
            <tbody>
            <?php
            $date = date("d/m/Y");
            $total = 0;
            $sql_data = " SELECT AR_CODE,SKU_CODE,SKU_NAME,TRD_U_PRC,
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
SUM(IF(DI_MONTH='12',TRD_G_KEYIN,0)) AS 12_AMT,
SUM(TRD_QTY) AS TOTAL_QTY,
SUM(TRD_G_KEYIN) AS TOTAL_AMT

FROM ims_product_sale_sac 
WHERE AR_CODE = '" . $customer_id . "'"
                . " AND DI_YEAR = '" . $year . "'"
//. " GROUP BY SKU_CODE,SKU_NAME,DI_MONTH,DI_YEAR,TRD_U_PRC
                . " GROUP BY SKU_CODE,SKU_NAME,DI_YEAR,TRD_U_PRC
ORDER BY SKU_CODE ,  CONVERT(DI_YEAR, FLOAT),CONVERT(DI_MONTH, FLOAT) ";

            //$myfile = fopen("param_post_sql.txt", "w") or die("Unable to open file!");
            //fwrite($myfile, $sql_data);
            //fclose($myfile);

            $statement_data = $conn->query($sql_data);
            $results_data = $statement_data->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_data

            as $row_data) { ?>

            <tr>
                <td><?php echo htmlentities($row_data['SKU_NAME']); ?></td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['TRD_U_PRC'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['1_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['2_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['3_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['4_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['5_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['6_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['7_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['8_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['9_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['10_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['11_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['12_QTY'], 2)); ?></p>
                </td>

                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['TOTAL_QTY'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['1_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['2_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['3_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['4_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['5_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['6_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['7_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['8_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['9_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['10_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['11_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['12_AMT'], 2)); ?></p>
                </td>
                <td align="right"><p
                            class="number"><?php echo htmlentities(number_format($row_data['TOTAL_AMT'], 2)); ?></p>
                </td>


                <?php } ?>

            </tbody>
        </table>
    </div>
</div>


<!--div class="card-body">
    <a id="myLink" href="#" onclick="PrintPage();"><i class="fa fa-print"></i> พิมพ์</a>
</div-->


</body>
</html>

