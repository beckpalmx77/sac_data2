<?php

include('includes/Header.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transaction Report</title>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Page level plugins -->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
</head>
<body>
<div class="container mt-5">
    <h2>Stock Transaction Report</h2>
    <!-- Filter Form -->
    <form id="filterForm" class="form-inline mb-4">
        <div class="form-group mr-3">
            <label for="start_date" class="mr-2">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control">
        </div>
        <div class="form-group mr-3">
            <label for="end_date" class="mr-2">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control">
        </div>
        <div class="form-group mr-3">
            <label for="product_name" class="mr-2">Product Name:</label>
            <input type="text" id="product_name" name="product_name" class="form-control">
        </div>
        <button type="button" id="filterButton" class="btn btn-primary">Filter</button>
    </form>

    <!-- DataTable -->
    <table id="stockTable" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Warehouse</th>
            <th>Week ID</th>
            <th>Location</th>
            <th>Total Quantity</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        let table = $('#stockTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "model/fetch_stock_balance.php", // PHP file to fetch data
                "type": "POST",
                "data": function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.product_name = $('#product_name').val();
                }
            },
            "columns": [
                { "data": "product_id" },
                { "data": "product_name" },
                { "data": "wh" },
                { "data": "wh_week_id" },
                { "data": "location" },
                { "data": "total_qty" }
            ]
        });

        // Filter button click event
        $('#filterButton').click(function() {
            table.ajax.reload(); // Reload data in DataTable
        });
    });
</script>
</body>
</html>

<?php } ?>