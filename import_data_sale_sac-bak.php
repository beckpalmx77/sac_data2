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
            <!-- Container Fluid-->
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

                                    <!-- Form สำหรับการ Import ข้อมูล -->
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="excelFile" class="form-label">Select Excel File</label>
                                            <input class="form-control" type="file" id="excelFile" name="excelFile"
                                                   accept=".xlsx, .xls">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Import</button>
                                        <button id="showImageBtn" class="btn btn-success">Example Format Data For Import</button>
                                    </form>

                                    <!-- Spinner แสดงโหลดข้อมูล -->
                                    <div id="loadingSpinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>

                                    <br>
                                    <div class="col-md-12 col-md-offset-2">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>วัน</th>
                                                <th>เดือน</th>
                                                <th>ชื่อเดือน</th>
                                                <th>ปี</th>
                                                <th>รหัสลูกค้า</th>
                                                <th>รหัสสินค้า</th>
                                                <th>ชื่อสินค้า</th>
                                                <th>รายละเอียด</th>
                                                <th>แบรนด์</th>
                                                <th>เลขที่เอกสาร</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อพนักงานขาย</th>
                                                <th>ชื่อเทค</th>
                                                <th>จำนวนสินค้า</th>
                                                <th>ราคาต่อหน่วย</th>
                                                <th>ส่วนลด</th>
                                                <th>ราคาทั้งหมด</th>
                                                <th>ภาษีมูลค่าเพิ่ม</th>
                                                <th>ราคาสุทธิ</th>
                                                <th>คะแนนต่อเส้น1</th>
                                                <th>คะแนนที่ได้1</th>
                                                <th>รหัสคลังสินค้า</th>
                                                <th>จำนวนฟรี</th>
                                                <th>เขตอำเภอ</th>
                                                <th>จังหวัด</th>
                                                <th>หมายเหตุ</th>
                                                <th>คะแนนใช้</th>
                                                <th>คะแนนคืน</th>
                                                <th>คะแนนสะสม</th>
                                                <th>คะแนนทั้งหมด</th>
                                                <th>เปรียบเทียบ</th>
                                                <th>ร้านค้า</th>
                                                <th>โดยแอพมือถือ</th>
                                                <th>อายุสินค้า</th>
                                                <th>หมวดหมู่สินค้า</th>
                                                <th>สถานะ</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>วัน</th>
                                                <th>เดือน</th>
                                                <th>ชื่อเดือน</th>
                                                <th>ปี</th>
                                                <th>รหัสลูกค้า</th>
                                                <th>รหัสสินค้า</th>
                                                <th>ชื่อสินค้า</th>
                                                <th>รายละเอียด</th>
                                                <th>แบรนด์</th>
                                                <th>เลขที่เอกสาร</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อพนักงานขาย</th>
                                                <th>ชื่อเทค</th>
                                                <th>จำนวนสินค้า</th>
                                                <th>ราคาต่อหน่วย</th>
                                                <th>ส่วนลด</th>
                                                <th>ราคาทั้งหมด</th>
                                                <th>ภาษีมูลค่าเพิ่ม</th>
                                                <th>ราคาสุทธิ</th>
                                                <th>คะแนนต่อเส้น1</th>
                                                <th>คะแนนที่ได้1</th>
                                                <th>รหัสคลังสินค้า</th>
                                                <th>จำนวนฟรี</th>
                                                <th>เขตอำเภอ</th>
                                                <th>จังหวัด</th>
                                                <th>หมายเหตุ</th>
                                                <th>คะแนนใช้</th>
                                                <th>คะแนนคืน</th>
                                                <th>คะแนนสะสม</th>
                                                <th>คะแนนทั้งหมด</th>
                                                <th>เปรียบเทียบ</th>
                                                <th>ร้านค้า</th>
                                                <th>โดยแอพมือถือ</th>
                                                <th>อายุสินค้า</th>
                                                <th>หมวดหมู่สินค้า</th>
                                                <th>สถานะ</th>
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

        // แสดง Spinner ขณะส่งฟอร์ม
        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();

            // แสดง Spinner
            $('#loadingSpinner').show();

            let formData = new FormData(this);

            $.ajax({
                url: 'import_process/import_data_sale_sac_process.php',
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
                    alertify.alert("Notification", "Data imported successfully.");
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
            "ajax": "model/fetch_data_sale_sac.php", // ดึงข้อมูลจาก PHP
            "order": [[0, 'desc']],
            "columns": [
                {"data": "DI_DAY"},           // วัน
                {"data": "DI_MONTH"},         // เดือน
                {"data": "DI_MONTH_NAME"},    // ชื่อเดือน
                {"data": "DI_YEAR"},          // ปี
                {"data": "AR_CODE"},          // รหัสลูกค้า
                {"data": "SKU_CODE"},         // รหัสสินค้า
                {"data": "SKU_NAME"},         // ชื่อสินค้า
                {"data": "DETAIL"},           // รายละเอียดสินค้า
                {"data": "BRAND"},            // ยี่ห้อ
                {"data": "DI_REF"},           // เลขที่เอกสาร
                {"data": "AR_NAME"},          // ชื่อลูกค้า
                {"data": "SALE_NAME"},        // ชื่อพนักงานขาย
                {"data": "TAKE_NAME"},        // ชื่อพนักงานจัดสินค้า
                {"data": "TRD_QTY"},          // จำนวนสินค้า
                {"data": "TRD_PRC"},          // ราคา
                {"data": "TRD_DISCOUNT"},     // ส่วนลด
                {"data": "TRD_TOTAL_PRICE"},  // ราคารวม
                {"data": "TRD_VAT"},          // VAT
                {"data": "TRD_AMOUNT_PRICE"}, // ราคาสุทธิ
                {"data": "TRD_PER_POINT"},    // ต่อแต้ม
                {"data": "TRD_TOTALPOINT"},   // แต้มรวม
                {"data": "WL_CODE"},          // รหัสคลังสินค้า
                {"data": "TRD_Q_FREE"},       // จำนวนฟรี
                {"data": "TRD_AMPHUR"},       // อำเภอ
                {"data": "TRD_PROVINCE"},     // จังหวัด
                {"data": "TRD_MARK"},         // หมายเหตุ
                {"data": "TRD_U_POINT"},      // แต้ม U
                {"data": "TRD_R_POINT"},      // แต้ม R
                {"data": "TRD_S_POINT"},      // แต้ม S
                {"data": "TRD_T_POINT"},      // แต้ม T
                {"data": "TRD_COMPARE"},      // เปรียบเทียบ
                {"data": "TRD_SHOP"},         // ร้านค้า
                {"data": "TRD_BY_MOBAPP"},    // ผ่าน Mobile App
                {"data": "TRD_YEAR_OLD"},     // อายุสินค้า
                {"data": "SKU_CAT"}          // หมวดหมู่สินค้า
            ]
        });

        // เปิดรูปภาพในหน้าต่างใหม่
        $('#showImageBtn').on('click', function() {
            window.open("img/screenshot/stock_wh_in.png", "_blank", "width=800,height=600");
        });
    });
</script>

</body>
</html>
