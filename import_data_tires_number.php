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

                                    <div id="loadingSpinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>

                                    <div id="uploadResult" class="my-3 font-weight-bold"></div>

                                    <br>
                                    <div class="col-md-12 col-md-offset-2">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>ชื่อลูกค้า</th>
                                                <th>วันที่ส่งสินค้า</th>
                                                <th>ยี่ห้อยาง</th>
                                                <th>ขนาดยาง</th>
                                                <th>รุ่นยาง</th>
                                                <th>เบอร์ยาง</th>
                                                <th>ปีผลิต</th>
                                                <th>จัดการ</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>ชื่อลูกค้า</th>
                                                <th>วันที่ส่งสินค้า</th>
                                                <th>ยี่ห้อยาง</th>
                                                <th>ขนาดยาง</th>
                                                <th>รุ่นยาง</th>
                                                <th>เบอร์ยาง</th>
                                                <th>ปีผลิต</th>
                                                <th>จัดการ</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                include('includes/Footer.php');
                ?>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/Modal-Logout.php');
?>
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

        // แสดง Spinner ขณะส่งฟอร์ม
        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();

            // แสดง Spinner
            $('#loadingSpinner').show();
            $('#uploadResult').html('');

            let formData = new FormData(this);

            $.ajax({
                url: 'import_process/import_data_tires_number_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    // ซ่อน Spinner เมื่อ import สำเร็จ
                    $('#loadingSpinner').hide();

                    // แสดงผลลัพธ์การ import
                    $('#uploadResult').html(response);

                    // รีโหลดข้อมูลในตาราง
                    $('#TableRecordList').DataTable().ajax.reload();

                    // แสดงข้อความแจ้งเตือน
                    alertify.alert("Notification", "Data import process completed.");
                },
                error: function (xhr, status, error) {
                    // ซ่อน Spinner เมื่อเกิดข้อผิดพลาด
                    $('#loadingSpinner').hide();

                    // แสดงข้อความแจ้งเตือนเมื่อเกิดข้อผิดพลาด
                    alertify.alert("Notification", "An error occurred: " + error);
                }
            });
        });

        // โหลดข้อมูลลงใน DataTable
        $('#TableRecordList').DataTable({
            "lengthMenu": [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            "ajax": "model/fetch_tires_number_data.php",
            "order": [[0, 'desc']],
            "columns": [
                {"data": 0}, // ชื่อลูกค้า
                {"data": 1}, // วันที่ส่งสินค้า
                {"data": 2}, // ยี่ห้อยาง
                {"data": 3}, // ขนาดยาง
                {"data": 4}, // รุ่นยาง
                {"data": 5}, // เบอร์ยาง
                {"data": 6}, // ปีผลิต
                {"data": 7, "orderable": false, "searchable": false} // Action column (จัดการ)
            ]
        });

        // เปิดรูปภาพในหน้าต่างใหม่
        $('#showImageBtn').on('click', function() {
            window.open("img/screenshot/import_tires_number.png", "_blank", "width=800,height=600");
        });

        // *** ฟังก์ชันสำหรับจัดการการลบข้อมูล (Delete Handler) ***
        $('#TableRecordList').on('click', '.delete-btn', function() {
            var recordId = $(this).data('id');

            bootbox.confirm({
                message: "คุณต้องการลบรายการนี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้",
                buttons: {
                    confirm: {
                        label: 'ใช่, ลบเลย',
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: 'ยกเลิก',
                        className: 'btn-secondary'
                    }
                },
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: 'import_process/delete_tires_number_process.php', // ตำแหน่งไฟล์สำหรับลบ
                            type: 'POST',
                            data: { id: recordId },
                            success: function(response) {
                                try {
                                    // *** 🔑 แก้ไข: ให้ JSON.parse ทำงานอย่างระมัดระวังมากขึ้น ***
                                    var jsonResponse = (typeof response === 'string') ? JSON.parse(response) : response;

                                    if (jsonResponse.status === 'success') {
                                        $('#TableRecordList').DataTable().ajax.reload();
                                        alertify.success(jsonResponse.message);
                                    } else {
                                        alertify.error(jsonResponse.message || "การลบไม่สำเร็จ");
                                    }
                                } catch(e) {
                                    alertify.error("ข้อผิดพลาดในการรับข้อมูล Server: โปรดตรวจสอบ Console หรือ Network Tab");
                                    console.log("Server Response:", response);
                                }
                            },
                            error: function() {
                                alertify.error("เกิดข้อผิดพลาดในการติดต่อ Server");
                            }
                        });
                    }
                }
            });
        });
        // *** สิ้นสุดฟังก์ชัน Delete Handler ***
    });
</script>

</body>
</html>