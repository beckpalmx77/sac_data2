<?php
include('includes/Header.php');
include('includes/Side-Bar.php');
include('includes/Top-Bar.php');
?>

<div class="container-fluid">

    <div class="row">
        <div class="col-md-6">
            <h5>ยอดขายรายวัน</h5>
            <canvas id="myChartDaily"></canvas>

            <div id="daily-table"></div>
        </div>

        <div class="col-md-6">
            <h5>ยอดขายรายเดือน</h5>
            <canvas id="myChartMonthly"></canvas>

            <div id="monthly-table"></div>
        </div>
    </div>

</div>

<?php include('includes/Footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>
