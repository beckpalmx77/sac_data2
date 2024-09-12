<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transaction Report</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
        var table = $('#stockTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "fetch_data.php", // PHP file to fetch data
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
