<?php
session_start();
if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    header('Location: login.php');
    exit;
}
$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการนำเข้าข้อมูลวางแผนการขาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; padding: 20px; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background-color: #0d6efd; color: white; border-radius: 15px 15px 0 0 !important; }
        .user-info { font-size: 0.9rem; color: #ffeb3b; font-weight: bold; }
        th { background-color: #f1f3f5 !important; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i> รายการนำเข้าข้อมูลวางแผนการขาย</h4>
                    <span class="user-info"><i class="fas fa-user-circle me-1"></i> ผู้ใช้งาน: <?php echo $first_name; ?> (สิทธิ์: <?php echo $account_type; ?>)</span>
                </div>
                <div>
                    <a href="forecast_import_plan.php" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-file-import me-1"></i> นำเข้าข้อมูล
                    </a>
                    <a href="forecast_plan_display_new.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> กลับหน้าจัดการ
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reportListTable" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>รหัสลูกค้า</th>
                                <th>ชื่อลูกค้า</th>
                                <th>เดือน/ปี วางแผนการขาย</th>
                                <th>พนักงานขาย</th>
                                <th>คนเทค</th>
                                <th>วันที่นำเข้า</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#reportListTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "forecast_report_list_fetch_new.php",
                    "type": "POST"
                },
                "columns": [
                    { "data": "ar_code" },
                    { "data": "ar_name" },
                    { "data": "forecast_period" },
                    { "data": "sale_name" },
                    { "data": "take_name" },
                    { "data": "import_date" },
                    { "data": "actions", "orderable": false }
                ],
                "order": [[5, "desc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json"
                }
            });

            // Handle View Detail click
            $('#reportListTable').on('click', '.btn-view', function() {
                var month = $(this).data('month');
                var year = $(this).data('year');
                var sale = $(this).data('sale');
                var customer = $(this).data('customer');

                // Create a temporary form to POST data to forecast_plan_display_new.php
                // Or use URL parameters if forecast_plan_display_new.php supports them.
                // Based on initial read, it uses POST in DataTables but we can pass initial filter values via localStorage or Session
                
                // Method: Save filters to localStorage and redirect
                localStorage.setItem('forecast_filter_month', month);
                localStorage.setItem('forecast_filter_year', year);
                localStorage.setItem('forecast_filter_sale', sale);
                localStorage.setItem('forecast_filter_customer', customer);
                
                window.location.href = 'forecast_plan_display_new.php?auto_filter=1';
            });
        });
    </script>
</body>
</html>
