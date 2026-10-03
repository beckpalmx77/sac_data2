<?php
include('includes/Header.php');
include('config/connect_sqlserver.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
}

$stmt1 = $conn_sqlsvr->prepare("SELECT DISTINCT ICCAT_CODE, ICCAT_NAME FROM ICCAT ORDER BY ICCAT_CODE ASC");
$stmt1->execute();
$iccats = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $conn_sqlsvr->prepare("SELECT DISTINCT BRN_CODE, BRN_NAME FROM BRAND ORDER BY BRN_CODE ASC");
$stmt2->execute();
$brands = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmt3 = $conn_sqlsvr->prepare("SELECT DISTINCT WH_CODE, WH_NAME FROM WAREHOUSE ORDER BY WH_CODE ASC");
$stmt3->execute();
$warehouses = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$stmt4 = $conn_sqlsvr->prepare("SELECT DISTINCT WL_CODE, WL_NAME FROM WARELOCATION ORDER BY WL_CODE ASC");
$stmt4->execute();
$warelocations = $stmt4->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Export Filter</title>

    <!-- ใส่ CSS เพิ่มขนาดตัวอักษรและ checkbox -->
    <style>
        label {
            font-size: 1.25rem;
        }

        input[type="checkbox"] {
            width: 22px;
            height: 22px;
            margin-right: 8px;
            transform: scale(1.3);
            vertical-align: middle;
        }

        fieldset legend {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .btn {
            font-size: 1.25rem;
            padding: 10px 24px;
        }
    </style>
</head>

<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <form method="post" action="export_process/export_data_stock_balance_all_process.php">

                                    <!-- ICCAT -->
                                    <fieldset class="border p-2 mb-3">
                                        <legend class="w-auto px-2 text-info">เลือกหมวดหมู่สินค้า (ICCAT)</legend>
                                        <div class="row">
                                            <?php foreach ($iccats as $row): ?>
                                                <div class="col-md-6">
                                                    <label>
                                                        <input type="checkbox" name="icc_codes[]"
                                                               value="<?= htmlspecialchars($row['ICCAT_CODE']) ?>">
                                                        <?= htmlspecialchars($row['ICCAT_CODE']) ?>
                                                        - <?= htmlspecialchars($row['ICCAT_NAME']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                    <!-- BRAND -->
                                    <fieldset class="border p-2 mb-3">
                                        <legend class="w-auto px-2 text-success">เลือกยี่ห้อสินค้า (BRAND)</legend>
                                        <div class="row">
                                            <?php foreach ($brands as $row): ?>
                                                <div class="col-md-6">
                                                    <label>
                                                        <input type="checkbox" name="brn_codes[]"
                                                               value="<?= htmlspecialchars($row['BRN_CODE']) ?>">
                                                        <?= htmlspecialchars($row['BRN_CODE']) ?>
                                                        - <?= htmlspecialchars($row['BRN_NAME']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                    <!-- WAREHOUSE -->
                                    <fieldset class="border p-2 mb-3">
                                        <legend class="w-auto px-2 text-primary">เลือกคลังสินค้า (WAREHOUSE)</legend>
                                        <div class="row">
                                            <?php foreach ($warehouses as $row): ?>
                                                <div class="col-md-6">
                                                    <label>
                                                        <input type="checkbox" name="wh_codes[]"
                                                               value="<?= htmlspecialchars($row['WH_CODE']) ?>">
                                                        <?= htmlspecialchars($row['WH_CODE']) ?>
                                                        - <?= htmlspecialchars($row['WH_NAME']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                    <!-- WARELOCATION -->
                                    <fieldset class="border p-2 mb-3">
                                        <legend class="w-auto px-2 text-danger">เลือกตำแหน่งจัดเก็บ (WARELOCATION)</legend>
                                        <div class="row">
                                            <?php foreach ($warelocations as $row): ?>
                                                <div class="col-md-6">
                                                    <label>
                                                        <input type="checkbox" name="wl_codes[]"
                                                               value="<?= htmlspecialchars($row['WL_CODE']) ?>">
                                                        <?= htmlspecialchars($row['WL_CODE']) ?>
                                                        - <?= htmlspecialchars($row['WL_NAME']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                    <div class="text-right mt-4">
                                        <button type="submit" class="btn btn-success">
                                            Export <i class="fa fa-download"></i>
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <?php
        include('includes/Modal-Logout.php');
        include('includes/Footer.php');
        ?>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/bootstrap-datepicker/locales/bootstrap-datepicker.th.min.js"></script>
<link rel="stylesheet" href="vendor/bootstrap-datepicker/css/bootstrap-datepicker.css"/>

<script>
    $(function () {
        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let yyyy = today.getFullYear();
        let current = dd + '-' + mm + '-' + yyyy;
        $('#doc_date_start, #doc_date_to').val(current).datepicker({
            format: "dd-mm-yyyy",
            todayHighlight: true,
            language: "th",
            autoclose: true
        });
    });
</script>

</body>
</html>
