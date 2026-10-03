<?php
include('includes/Header.php');
include('config/connect_db.php'); // PDO $conn

// รับ doc_id จาก URL
/*
$doc_id = isset($_GET['doc_id']) ? trim($_GET['doc_id']) : '';

if (empty($doc_id)) {
    die("กรุณาระบุเลขที่เอกสารใน URL เช่น ?doc_id=xxx");
}
*/

// ดึงข้อมูล

$sql_get = " SELECT * FROM v_sum_product_reserve_event ORDER BY id ASC ";

$stmt = $conn->prepare($sql_get);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">

<body id="page-top">
<div id="wrapper">


    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <br>
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">สรุปรายการลงทะเบียนจองยาง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">
                        ปิดหน้าต่าง
                    </button>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div>
                            <div class="card-body">
                                <section class="container-fluid">
                                    <form method="post" id="MainrecordForm" name="MainrecordForm"
                                          enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped w-100 nowrap"
                                                       id="reserveTable">
                                                    <thead class="table-primary">
                                                    <tr>
                                                        <th>ลำดับ</th>
                                                        <th>วันที่</th>
                                                        <th>ชื่อร้านค้า</th>
                                                        <th>รหัสสินค้า</th>
                                                        <th>ชื่อสินค้า</th>
                                                        <th>หมวดหมู่</th>
                                                        <th>ยี่ห้อ</th>
                                                        <th>คะแนนต่อเส้น</th>
                                                        <th>300 เส้น</th>
                                                        <th>120 เส้น</th>
                                                        <th>60 เส้น</th>
                                                        <th>บัตรของขวัญต่อเส้น</th>
                                                        <th>มิ.ย.</th>
                                                        <th>ก.ค.</th>
                                                        <th>ส.ค.</th>
                                                        <th>รวมจำนวน</th>
                                                        <th>บัตรของขวัญ</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($rows as $r):
                                                        $total_quantity_1 += (int)$r['quantity_1'];
                                                        $total_quantity_2 += (int)$r['quantity_2'];
                                                        $total_quantity_3 += (int)$r['quantity_3'];
                                                        $total_quantity_all += (int)$r['total_quantity'];
                                                        $total_gift_voucher_all += (float)$r['total_gift_voucher'];
                                                        ?>
                                                        <tr>
                                                            <td></td> <!-- ลำดับ: จัดการโดย JavaScript -->
                                                            <td><?= htmlspecialchars($r['DOC_DATE']) ?></td>
                                                            <td><?= htmlspecialchars($r['CUSTOMER_NAME']) ?></td>
                                                            <td><?= htmlspecialchars($r['SKU_CODE']) ?></td>
                                                            <td><?= htmlspecialchars($r['SKU_NAME']) ?></td>
                                                            <td><?= htmlspecialchars($r['SKU_CAT_NAME']) ?></td>
                                                            <td><?= htmlspecialchars($r['BRAND']) ?></td>
                                                            <td><?= $r['point'] ?></td>
                                                            <td><?= number_format($r['price_1']) ?></td>
                                                            <td><?= number_format($r['price_2']) ?></td>
                                                            <td><?= number_format($r['price_3']) ?></td>
                                                            <td><?= number_format($r['gift_voucher']) ?></td>
                                                            <td class="text-end"><?= number_format($r['quantity_1']) ?></td>
                                                            <td class="text-end"><?= number_format($r['quantity_2']) ?></td>
                                                            <td class="text-end"><?= number_format($r['quantity_3']) ?></td>
                                                            <td class="text-end"><?= number_format($r['total_quantity']) ?></td>
                                                            <td class="text-end"><?= number_format($r['total_gift_voucher'], 2) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot class="table-secondary font-weight-bold">
                                                    <tr>
                                                        <td colspan="12" class="text-end">รวม</td>
                                                        <td class="text-end"><?= number_format($total_quantity_1) ?></td>
                                                        <td class="text-end"><?= number_format($total_quantity_2) ?></td>
                                                        <td class="text-end"><?= number_format($total_quantity_3) ?></td>
                                                        <td class="text-end"><?= number_format($total_quantity_all) ?></td>
                                                        <td class="text-end"><?= number_format($total_gift_voucher_all, 2) ?></td>
                                                    </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                    </form>

                                </section>
                            </div>


                        </div>

                    </div>

                </div>
                <!--Row-->

                <!-- Row -->

            </div>

            <!---Container Fluid-->

        </div>

        <?php
        include('includes/Modal-Logout.php');
        include('includes/Footer.php');
        ?>

    </div>
</div>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!--script src="vendor/jquery/jquery.min.js"></script-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Select2 -->
<script src="vendor/select2/dist/js/select2.min.js"></script>


<script src="js/modal/show_cust_crm_modal.js"></script>


<!-- Bootstrap Touchspin -->
<script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
<!-- ClockPicker -->

<!-- RuangAdmin Javascript -->
<script src="js/myadmin.min.js"></script>
<script src="js/util.js"></script>
<script src="js/Calculate.js"></script>

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

<script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
<script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
<!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
<link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

<script src="//github.com/fyneworks/multifile/blob/master/jquery.MultiFile.min.js" type="text/javascript"
        language="javascript"></script>

<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css"/>

<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- Buttons for export -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

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

<style>
    .table-responsive {
        overflow-x: auto;
        width: 100%;
    }
</style>

<script>
    $(document).ready(function () {
        let today = new Date();
        let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
        $('#doc_date').val(doc_date);
    });
</script>

<script>
    $(document).ready(function () {
        $('#doc_date').datepicker({
            format: "dd-mm-yyyy",
            todayHighlight: true,
            language: "th",
            autoclose: true
        });
    });
</script>

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
        $("#btnClose").click(function () {
            if ($('#save_status').val() !== '') {
                window.opener = self;
                window.close();
            } else {
                alertify.error("กรุณากด save อีกครั้ง");
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // ฟังก์ชันแปลง query string เป็น object
        function getQueryParams() {
            let query = window.location.search.substring(1);
            let vars = query.split("&");
            let params = {};
            for (let i = 0; i < vars.length; i++) {
                let pair = vars[i].split("=");
                params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || "");
            }
            return params;
        }

        let queryParams = getQueryParams();

        // กำหนดค่าใส่ใน input
        $('#customerName').val(queryParams["CUSTOMER_NAME"]);
        $('#phone').val(queryParams["PHONE"]);
        $('#province').val(queryParams["PROVINCE"]);
        $('#saleName').val(queryParams["SALE_NAME"]);
    });
</script>

<!--script>
    $(document).ready(function() {
        const table = $('#reserveTable').DataTable({
            responsive: true,
            scrollX: true,
            lengthMenu: [[10, 20, 50, 100, 250, 300, 500, 1000], [10, 20, 50, 100, 250, 300, 500, 1000]],
            language: {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป',
                    last: 'สุดท้าย',
                    first: 'แรก'
                }
            },
            columnDefs: [
                {targets: [8, 9], orderable: false} // ปิดการ sort ของ 2 คอลัมน์สุดท้าย
            ],
            order: [[1, 'asc']], // เริ่มจากเรียงตามวันที่เริ่มต้น
            drawCallback: function(settings) {
                let api = this.api();
                let startIndex = api.page.info().start;
                api.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                    cell.innerHTML = startIndex + i + 1;
                });
            }
        });
    });
</-->

<script>
    $('#reserveTable').DataTable({
        dom: 'Bfrtip', // เพิ่ม DOM สำหรับปุ่ม
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export Excel',
                title: 'สรุปรายการจองยาง',
                exportOptions: {
                    columns: ':visible' // หรือกำหนดเฉพาะคอลัมน์ เช่น [0,1,2,...]
                }
            }
        ],
        responsive: true,
        scrollX: true,
        lengthMenu: [[10, 20, 50, 100, 250, 300, 500, 1000], [10, 20, 50, 100, 250, 300, 500, 1000]],
        language: {
            search: 'ค้นหา',
            lengthMenu: 'แสดง _MENU_ รายการ',
            info: 'หน้าที่ _PAGE_ จาก _PAGES_',
            infoEmpty: 'ไม่มีข้อมูล',
            zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
            infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
            paginate: {
                previous: 'ก่อนหน้า',
                next: 'ต่อไป',
                last: 'สุดท้าย',
                first: 'แรก'
            }
        },
        columnDefs: [
            {targets: [8, 9], orderable: false}
        ],
        order: [[1, 'asc']],
        drawCallback: function(settings) {
            let api = this.api();
            let startIndex = api.page.info().start;
            api.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                cell.innerHTML = startIndex + i + 1;
            });
        }
    });

</script>


</body>
</html>
