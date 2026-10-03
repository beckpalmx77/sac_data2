<?php
include('includes/Header.php');
// ตรวจสอบการเข้าสู่ระบบ
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['sale_name_id']) == "") {
    header("Location: index.php");
} else {
    // ไม่มีโค้ดเพิ่มเติมในส่วนนี้
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
                                </section>
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

<div class="modal fade" id="editRecordModal" tabindex="-1" role="dialog" aria-labelledby="editRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRecordModalLabel">แก้ไขข้อมูลยาง</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="editRecordForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-group">
                        <label for="edit_customer_name">ชื่อลูกค้า</label>
                        <input type="text" class="form-control" id="edit_customer_name" name="customer_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_delivery_date">วันที่ส่งสินค้า</label>
                        <input type="text" class="form-control" id="edit_delivery_date" name="delivery_date">
                    </div>
                    <div class="form-group">
                        <label for="edit_brand">ยี่ห้อยาง</label>
                        <input type="text" class="form-control" id="edit_brand" name="brand">
                    </div>
                    <div class="form-group">
                        <label for="edit_size">ขนาดยาง</label>
                        <input type="text" class="form-control" id="edit_size" name="size">
                    </div>
                    <div class="form-group">
                        <label for="edit_model">รุ่นยาง</label>
                        <input type="text" class="form-control" id="edit_model" name="model">
                    </div>
                    <div class="form-group">
                        <label for="edit_tires_number">เบอร์ยาง</label>
                        <input type="text" class="form-control" id="edit_tires_number" name="tires_number">
                    </div>
                    <div class="form-group">
                        <label for="edit_production_year">ปีผลิต</label>
                        <input type="number" class="form-control" id="edit_production_year" name="production_year">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">ยกเลิก</button>
                    <button class="btn btn-primary" type="submit" id="saveEditBtn">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

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
        // โหลดข้อมูลลงใน DataTable
        let dataTable = $('#TableRecordList').DataTable({
            "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            // ⚠️ ตรวจสอบชื่อไฟล์ Ajax นี้ให้ตรงกับไฟล์ PHP ที่ดึงข้อมูล
            "ajax": "model/fetch_tires_number_data_update.php",
            "order": [[1, 'desc']],
            "columns": [
                {"data": 0}, // 0: ชื่อลูกค้า
                {"data": 1}, // 1: วันที่ส่งสินค้า
                {"data": 2}, // 2: ยี่ห้อยาง
                {"data": 3}, // 3: ขนาดยาง
                {"data": 4}, // 4: รุ่นยาง
                {"data": 5}, // 5: เบอร์ยาง
                {"data": 6}, // 6: ปีผลิต
                {"data": 7, "orderable": false, "searchable": false}, // 7: Action column (จัดการ)
                {"data": 8, "visible": false, "searchable": false} // 8: ID (ซ่อนไว้)
            ]
        });

        // *** 🛠️ ฟังก์ชันสำหรับเปิด Modal แก้ไขและดึงข้อมูลเดิม (Edit Handler) ***
        $('#TableRecordList').on('click', '.edit-btn', function() {
            // ดึงข้อมูลแถวทั้งหมดจาก Datatables
            let rowData = dataTable.row($(this).closest('tr')).data();

            if (!rowData || rowData.length < 9) {
                alert("เกิดข้อผิดพลาดในการดึงข้อมูลแถว โปรดตรวจสอบการตั้งค่าคอลัมน์");
                return;
            }

            $('#edit_id').val(rowData[8]);
            $('#edit_customer_name').val(rowData[0]);

            let datePart = rowData[1].split(' ')[0]; // แยกเอาแค่ส่วนวันที่ "YYYY-MM-DD"
            $('#edit_delivery_date').val(datePart);

            $('#edit_brand').val(rowData[2]);
            $('#edit_size').val(rowData[3]);
            $('#edit_model').val(rowData[4]);
            $('#edit_tires_number').val(rowData[5]);
            $('#edit_production_year').val(rowData[6]);

            $('#editRecordModal').modal('show'); // เปิด Modal
        });
        // *** สิ้นสุดฟังก์ชัน Edit Handler (ดึงข้อมูล) ***


        // *** 💾 ฟังก์ชันสำหรับบันทึกการแก้ไข (Save Edit Handler) ***
        $('#editRecordForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'import_process/update_tires_number_process.php',
                type: 'POST',
                data: $(this).serialize(), // ส่งข้อมูลฟอร์มทั้งหมด
                success: function(response) {
                    try {
                        let jsonResponse = (typeof response === 'string') ? JSON.parse(response) : response;

                        if (jsonResponse.status === 'success') {
                            $('#editRecordModal').modal('hide'); // ปิด Modal
                            dataTable.ajax.reload(); // โหลดข้อมูล DataTable ใหม่
                            alertify.success(jsonResponse.message);
                        } else {
                            alertify.error(jsonResponse.message || "การแก้ไขไม่สำเร็จ");
                        }
                    } catch(e) {
                        alertify.error("ข้อผิดพลาดในการรับข้อมูล Server: โปรดตรวจสอบ Console");
                        console.log("Server Response:", response);
                    }
                },
                error: function() {
                    alertify.error("เกิดข้อผิดพลาดในการติดต่อ Server");
                }
            });
        });
        // *** สิ้นสุดฟังก์ชัน Save Edit Handler ***

        // *** ฟังก์ชันสำหรับจัดการการลบข้อมูล (Delete Handler) ***
        $('#TableRecordList').on('click', '.delete-btn', function() {
            let recordId = $(this).data('id');

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
                                    let jsonResponse = (typeof response === 'string') ? JSON.parse(response) : response;

                                    if (jsonResponse.status === 'success') {
                                        dataTable.ajax.reload();
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