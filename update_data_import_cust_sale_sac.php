<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['sale_name_id']) == "") {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <section class="container-fluid">
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="excelFile" class="form-label">Select Excel File</label>
                                            <input class="form-control" type="file" id="excelFile" name="excelFile"
                                                   accept=".xlsx, .xls">
                                        </div>
                                        <div class="mb-12">
                                            <button type="submit" class="btn btn-primary">Import</button>
                                            <button type="button" id="showImageBtn" class="btn btn-success">ตัวอย่างไฟล์
                                                Excel Format Data สำหรับนำเข้า Update
                                            </button>
                                        </div>
                                        <div class="mb-12">
                                            <span>
                                                <div id="input_text" style="white-space: pre-wrap;"></div> <!-- เพิ่ม style white-space -->
                                            </span>
                                        </div>
                                    </form>
                                    <br>
                                    <div id="spinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>รหัสลูกค้า</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อ Sale</th>
                                                <th>ชื่อ Take</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>รหัสลูกค้า</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อ Sale</th>
                                                <th>ชื่อ Take</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                            </div>
                        </div>
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

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Vendor Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<!-- Datatables Scripts -->
<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<!-- Custom Style -->
<link href="css/spinner_over.css" rel="stylesheet"/>

<!-- Custom Script -->
<script>
    $(document).ready(function () {

        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();
            $("#spinner").show();

            let formData = new FormData(this);

            $.ajax({
                url: 'import_process/update_data_cust_sale_sac_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    $('#spinner').hide();
                    alertify.alert(response);
                    $('#TableRecordList').DataTable().ajax.reload();
                    alertify.alert("Notification", "Data imported successfully.");
                },
                error: function (xhr, status, error) {
                    $('#spinner').hide();
                    alertify.alert("Notification", "An error occurred: " + error);
                }
            });
        });

        $('#TableRecordList').DataTable({
            "lengthMenu": [[6, 10, 20, 50, 100], [6, 10, 20, 50, 100]],
            "ajax": "model/fetch_data_cust_sale_sac.php",
            "order": [[0, 'desc']],
            "columns": [
                {"data": "AR_CODE"},
                {"data": "AR_NAME"},
                {"data": "SALE_NAME"},
                {"data": "TAKE_NAME"}
            ]
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#showImageBtn').on('click', function () {
            window.open("import_process/template/template_cust_data.xlsx", "_blank");
        });
    });
</script>

</body>
</html>