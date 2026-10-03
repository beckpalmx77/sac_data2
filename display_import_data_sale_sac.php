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
    <?php // include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper" style="padding-top: 20px;">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h4 class="m-0 font-weight-bold text-primary">แสดงข้อมูล Import (Popup)</h4>
                            </div>
                            <div class="card-body">
                                <section class="container-fluid">
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="row mb-4">
                                            <div class="col-md-3">
                                                <label for="searchDate" class="form-label">เลือกวันที่ (DI_DATE)</label>
                                                <input type="date" class="form-control" id="searchDate">
                                            </div>
                                            <div class="col-md-8 align-self-end">
                                                <button type="button" id="btnSearch" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> ค้นหา
                                                </button>
                                                <button type="button" id="btnReset" class="btn btn-secondary">
                                                    <i class="fas fa-sync"></i> รีเซ็ตเป็นวันนี้
                                                </button>
                                                <button type="button" id="deleteButton" class="btn btn-warning">
                                                    <i class="fa fa-trash"></i> ลบข้อมูลตามวันที่
                                                </button>
                                                <button type="button" id="btnClose" class="btn btn-danger" style="margin-left: 10px;">
                                                    <i class="fas fa-times-circle"></i> ปิดหน้าต่าง
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div id="spinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <span class="text-primary ml-2">กำลังประมวลผล...</span>
                                    </div>

                                    <div class="col-md-12">
                                        <table id='TableRecordList' class='display dataTable' style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>วันที่</th>
                                                <th>เลขที่เอกสาร</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อSale</th>
                                                <th>รหัสสินค้า</th>
                                                <th>รายละเอียด</th>
                                                <th>จำนวน</th>
                                                <th>ประเภท</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/Modal-Logout.php'); ?>
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<link href="css/spinner_over.css" rel="stylesheet"/>

<style>
    input[type="date"].form-control {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        color: #333;
        background-color: #f9f9f9;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    input[type="date"].form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        outline: none;
    }

    /* บังคับตารางไม่ตัดคำ */
    #TableRecordList th, #TableRecordList td {
        white-space: nowrap;
    }
</style>

<script>
    // ฟังก์ชันแปลงวันที่ YYYY-MM-DD -> DD-MM-YYYY
    function formatToDDMMYYYY(dateString) {
        if (!dateString) return "";
        let parts = dateString.split("-");
        if (parts.length === 3) {
            return parts[2] + "-" + parts[1] + "-" + parts[0];
        }
        return dateString;
    }

    // ฟังก์ชันหาวันที่ปัจจุบัน YYYY-MM-DD
    function getCurrentDate() {
        let now = new Date();
        let day = ("0" + now.getDate()).slice(-2);
        let month = ("0" + (now.getMonth() + 1)).slice(-2);
        return now.getFullYear() + "-" + (month) + "-" + (day);
    }

    $(document).ready(function () {
        let today = getCurrentDate();
        // กำหนดค่าเริ่มต้น
        $('#searchDate').val(today);

        // ตั้งค่า DataTable
        let table = $('#TableRecordList').DataTable({
            "scrollX": true,
            "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "ajax": {
                "url": "model/fetch_data_sale_sac_condition.php",
                "type": "GET",
                "data": function (d) {
                    d.t = new Date().getTime(); // ป้องกัน Cache
                    let searchDateRaw = $('#searchDate').val(); // ดึงค่าจาก searchDate
                    // ส่งค่าแบบ DD-MM-YYYY ไปที่ API
                    d.search_date = formatToDDMMYYYY(searchDateRaw);
                }
            },
            "order": [[0, 'desc']],
            "columns": [
                {"data": "DI_DATE"},
                {"data": "DI_REF"},
                {"data": "AR_NAME"},
                {"data": "SALE_NAME"},
                {"data": "SKU_CODE"},
                {"data": "SKU_NAME"},
                {"data": "TRD_QTY"},
                {"data": "SKU_CAT"}
            ]
        });

        // *** เพิ่มส่วน Auto Load เมื่อเปลี่ยนวันที่ ***
        $('#searchDate').on('change', function () {
            table.ajax.reload();
        });

        // ปุ่มค้นหา (กดเอง)
        $('#btnSearch').click(function () {
            table.ajax.reload();
        });

        // ปุ่ม Reset
        $('#btnReset').click(function () {
            $('#searchDate').val(today);
            table.ajax.reload();
        });

        $('#btnClose').click(function () {
            window.close();
        });

        // ----------------------------------------------------
        // ส่วนจัดการปุ่มลบข้อมูล
        // ----------------------------------------------------
        $('#deleteButton').on('click', function () {
            let di_date_raw = $('#searchDate').val(); // ค่าดิบ: YYYY-MM-DD

            if (di_date_raw === "") {
                alert("กรุณาเลือกวันที่ที่จะลบ");
                return;
            }

            // แปลงเป็น DD-MM-YYYY เพื่อส่งไปลบ
            let formattedDate = formatToDDMMYYYY(di_date_raw);

            if (confirm("คุณต้องการลบข้อมูลวันที่ " + formattedDate + " จริงหรือไม่?\n(ข้อมูลที่ลบแล้วจะไม่สามารถกู้คืนได้)")) {

                // แสดง Spinner และปิดปุ่มชั่วคราว
                $("#spinner").show();
                $('#deleteButton').prop('disabled', true);

                $.ajax({
                    url: 'model/manage_data_sale_sac.php',
                    type: 'POST',
                    data: {
                        action: 'DELETE_BY_DATE',
                        DI_DATE: formattedDate
                    },
                    success: function (response) {
                        $("#spinner").hide();
                        $('#deleteButton').prop('disabled', false);

                        // แจ้งผลลัพธ์
                        // ถ้ามี alertify ให้ใช้ alertify, ถ้าไม่มีใช้ alert ธรรมดา
                        if (typeof alertify !== 'undefined') {
                            alertify.alert("ผลการทำงาน: " + response);
                        } else {
                            alert("ผลการทำงาน: " + response);
                        }

                        // Reload ตาราง
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr, status, error) {
                        $("#spinner").hide();
                        $('#deleteButton').prop('disabled', false);
                        alert("เกิดข้อผิดพลาดในการเชื่อมต่อ: " + error);
                    }
                });
            }
        });
        // ----------------------------------------------------
    });
</script>

</body>
</html>