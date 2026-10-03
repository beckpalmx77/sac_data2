<?php
include 'config/connect_db.php';

$month_num = date('m');

$sql_curr_month = "SELECT * FROM ims_month WHERE month_id = '" . $month_num . "'";

$stmt_curr_month = $conn->prepare($sql_curr_month);
$stmt_curr_month->execute();
$MonthCurr = $stmt_curr_month->fetchAll();
foreach ($MonthCurr as $row_curr) {
    $month_name = $row_curr["month_name"];
}

$sql_month = "SELECT * FROM ims_month";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->execute();
$MonthRecords = $stmt_month->fetchAll();

$sql_year = "SELECT DISTINCT(DI_YEAR) AS DI_YEAR
    FROM ims_data_sale_sac_all WHERE DI_YEAR >= 2025
    ORDER BY DI_YEAR DESC";
$stmt_year = $conn->prepare($sql_year);
$stmt_year->execute();
$YearRecords = $stmt_year->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
    <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
</head>

<body>
<div class="container-fluid mt-4">
    <form id="searchForm" class="row g-3">
        <h4><?php echo urldecode($_GET['s']) ?></h4>
        <div class="col-12 col-md-6">
            <label class="form-label">เลือกลูกค้า</label>
            <select id="customer_id" name="customer_id" class="form-control">
                <option value="">-- ค้นหาลูกค้า --</option>
                <?php
                $stmt = $conn->prepare("SELECT id,customer_id , customer_name FROM ims_customer_master ORDER BY customer_name");
                $stmt->execute();
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($customers as $customer) {
                    echo "<option value='{$customer['customer_id']}'>{$customer['customer_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label for="doc_year">เลือกปี :</label>
            <select name="doc_year" id="doc_year" class="form-control" required>
                <?php foreach ($YearRecords as $row) { ?>
                    <option value="<?php echo $row["DI_YEAR"]; ?>"><?php echo $row["DI_YEAR"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <!-- เลือกเดือน -->
        <div class="col-12 col-md-3">
            <label for="doc_month">เลือกเดือน :</label>
            <select name="doc_month" id="doc_month" class="form-control" required>
                <option value="<?php echo $month_num; ?>" selected><?php echo $month_name; ?></option>
                <?php foreach ($MonthRecords as $row) { ?>
                    <option value="<?php echo $row["month_id"]; ?>"><?php echo $row["month_name"]; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">ค้นหารายการสินค้า</button>
        </div>
    </form>

    <div id="result" class="mt-4"></div>

</div>

<script>
    $(document).ready(function () {
        $('#customer_id').select2({
            width: '100%'
        });

        $("#searchForm").submit(function (e) {
            e.preventDefault();
            let customer_id = $("#customer_id").val();
            let year = $("#doc_year").val();
            let month = $("#doc_month").val();

            if (!customer_id) {
                alert("กรุณาเลือกลูกค้า");
                return;
            }

            $.ajax({
                url: "model/get_customer_buy_product.php",
                type: "GET",
                data: {
                    customer_id,
                    year,
                    month
                },
                success: function (data) {
                    $("#result").html(data);
                },
                error: function () {
                    alert("เกิดข้อผิดพลาดในการดึงข้อมูล");
                }
            });
        });
    });
</script>
</body>

</html>
