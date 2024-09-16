<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['sale_name_id']) == "") {
    header("Location: index.php");
} else {
    ?>
<?php } ?>
<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">
    <?php
    include('includes/Side-Bar.php');
    ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php
            include('includes/Top-Bar.php');
            ?>
            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active"
                            aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            </div>
                            <div class="card-body">
                                <section class="container-fluid">

                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="excelFile" class="form-label">Select Excel File</label>
                                            <input class="form-control" type="file" id="excelFile" name="excelFile"
                                                   accept=".xlsx, .xls">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Import</button>
                                        <button id="showImageBtn" class="btn btn-success">Example Format Data For Import</button>
                                    </form>
                                    <br>
                                    <div class="col-md-12 col-md-offset-2">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>วันที่</th>
                                                <th>รหัสสินค้า</th>
                                                <th>รายละเอียด</th>
                                                <th>ประเภทรายการ</th>
                                                <th>จำนวน</th>
                                                <th>คลังปี</th>
                                                <th>สัปดาห์</th>
                                                <th>ตำแหน่ง</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>วันที่</th>
                                                <th>รหัสสินค้า</th>
                                                <th>รายละเอียด</th>
                                                <th>ประเภทรายการ</th>
                                                <th>จำนวน</th>
                                                <th>คลังปี</th>
                                                <th>สัปดาห์</th>
                                                <th>ตำแหน่ง</th>
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


<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<style>

    .icon-input-btn {
        display: inline-block;
        position: relative;
    }

    .icon-input-btn input[type="submit"] {
        padding-left: 2em;
    }

    .icon-input-btn .fa {
        display: inline-block;
        position: absolute;
        left: 0.65em;
        top: 30%;
    }
</style>
<script>
    $(document).ready(function () {
        $(".icon-input-btn").each(function () {
            let btnFont = $(this).find(".btn").css("font-size");
            let btnColor = $(this).find(".btn").css("color");
            $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
        });
    });
</script>


<script>
    $(document).ready(function () {

        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: 'import_process/import_data_stock_in.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    $('#uploadResult').html(response);
                    $('#TableRecordList').DataTable().ajax.reload();
                    alertify.alert("Notification", "Data imported successfully.");
                },
                error: function (xhr, status, error) {
                    alertify.alert("Notification", "An error occurred: " + error);
                }
            });
        });

        $('#TableRecordList').DataTable({
            "lengthMenu": [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            "ajax": "model/fetch_stock_wh_data.php",
            "order": [[0, 'desc']],
            "columns": [
                {"data": "doc_date"},
                {"data": "product_id"},
                {"data": "product_name"},
                {"data": "record_type"},
                {"data": "qty"},
                {"data": "wh"},
                {"data": "wh_week_id"},
                {"data": "location"}
            ]
        });
    });
</script>

<script>
    // JavaScript สำหรับการเปิดรูปภาพในหน้าต่างใหม่
    document.getElementById("showImageBtn").addEventListener("click", function() {
        window.open("img/screenshot/stock_wh_in.png", "_blank", "width=800,height=600");
    });
</script>

</body>
</html>
