<?php
include('includes/Header.php');
include('config/connect_db.php'); // PDO $conn

// รับ doc_id จาก URL
$doc_id = isset($_GET['doc_id']) ? trim($_GET['doc_id']) : '';
$customer_code = isset($_GET['CUSTOMER_CODE']) ? trim($_GET['CUSTOMER_CODE']) : '';

if (empty($customer_code)) {
    die("กรุณาระบุใน URL เช่น ?customer_code=xxx");
}

// ดึงข้อมูล

$sql_get = "
    SELECT e.*, t.point, t.gift_voucher
    FROM ims_product_reserve_event AS e
    LEFT JOIN ims_product_reserve_target_point AS t ON t.SKU_CODE = e.SKU_CODE
    WHERE e.CUSTOMER_CODE = :customer_code
    ORDER BY e.doc_id,e.line_no
";

$stmt = $conn->prepare($sql_get);
$stmt->execute(['customer_code' => $customer_code]);
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
                    <h1 class="h4 mb-0 text-gray-800">รายการลงทะเบียนจองยาง</h1>
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

                                            <div class="col-md-2">
                                                <label for="doc_id" class="form-label">เลขที่เอกสาร</label>
                                                <input type="text" class="form-control" id="doc_id" name="doc_id" value="<?php echo htmlspecialchars($doc_id); ?>" readonly="true">
                                            </div>

                                            <div class="col-md-4">
                                                <label for="customerName" class="form-label">ชื่อร้านค้า</label>
                                                <input type="text" class="form-control" id="customerName"
                                                       name="customerName" readonly="true">
                                            </div>
                                            <div class="col-md-2">
                                                <label for="phone" class="form-label">หมายเลขโทรศัพท์</label>
                                                <input type="text" class="form-control" id="phone" name="phone"
                                                       readonly="true">
                                            </div>
                                            <div class="col-md-2">
                                                <label for="province" class="form-label">จังหวัด</label>
                                                <input type="text" class="form-control" id="province" name="province"
                                                       readonly="true">
                                            </div>
                                            <div class="col-md-2">
                                                <label for="saleName" class="form-label">ชื่อเซลล์</label>
                                                <input type="text" class="form-control" id="saleName" name="saleName"
                                                       readonly="true">
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label>กรองตามหมวดหมู่สินค้า</label>
                                                <select id="skuCategoryFilter" class="form-control">
                                                    <option value="">-- แสดงทั้งหมด --</option>
                                                    <?php
                                                    // ดึงเฉพาะหมวดหมู่ที่ไม่ซ้ำ
                                                    $skuCatCodes = [];
                                                    foreach ($rows as $r) {
                                                        if (!in_array($r['SKU_CAT_CODE'], $skuCatCodes)) {
                                                            $skuCatCodes[] = $r['SKU_CAT_CODE'];
                                                            echo '<option value="' . htmlspecialchars($r['SKU_CAT_CODE']) . '">' . htmlspecialchars($r['SKU_CAT_CODE']) . ' - ' . htmlspecialchars($r['SKU_CAT_NAME']) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped w-100 nowrap"
                                                       id="reserveTable">
                                                    <thead class="table-primary">
                                                    <tr>
                                                        <th>ลำดับ</th>
                                                        <th>วันที่</th>
                                                        <th>รหัสสินค้า</th>
                                                        <th>ชื่อสินค้า</th>
                                                        <th>หมวดหมู่</th>
                                                        <th>ยี่ห้อ</th>
                                                        <th>คะแนนต่อเส้น</th>
                                                        <th>ยอดจอง 300 เส้น</th>
                                                        <th>ยอดจอง 120 เส้น</th>
                                                        <th>ยอดจอง 60 เส้น</th>
                                                        <th>บัตรของขวัญต่อเส้น</th>
                                                        <th>มิ.ย.</th>
                                                        <th>ก.ค.</th>
                                                        <th>ส.ค.</th>
                                                        <th>รวมจำนวน</th>
                                                        <th>บัตรของขวัญ</th>
                                                        <th>ประมาณการ 300 เส้น</th>
                                                        <th>ประมาณการ 120 เส้น</th>
                                                        <th>ประมาณการ 60 เส้น</th>
                                                        <th style="display:none;">SKU_CAT_CODE</th> <!-- 16 (hidden) -->
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
                                                            <td><?= htmlspecialchars($r['line_no']) ?></td>
                                                            <td><?= htmlspecialchars($r['DOC_DATE']) ?></td>
                                                            <td><?= htmlspecialchars($r['SKU_CODE']) ?></td>
                                                            <td><?= htmlspecialchars($r['SKU_NAME']) ?></td>
                                                            <td><?= htmlspecialchars($r['SKU_CAT_NAME']) ?></td>
                                                            <td><?= htmlspecialchars($r['BRAND']) ?></td>
                                                            <td><?= $r['point'] ?></td>
                                                            <td><?= number_format($r['price_1'],0) ?></td>
                                                            <td><?= number_format($r['price_2'],0) ?></td>
                                                            <td><?= number_format($r['price_3'],0) ?></td>
                                                            <td><?= number_format($r['gift_voucher'],0) ?></td>
                                                            <td class="text-end"><?= number_format($r['quantity_1'],0) ?></td>
                                                            <td class="text-end"><?= number_format($r['quantity_2'],0) ?></td>
                                                            <td class="text-end"><?= number_format($r['quantity_3'],0) ?></td>
                                                            <td class="text-end"><?= number_format($r['total_quantity'],0) ?></td>
                                                            <td class="text-end"><?= number_format($r['total_gift_voucher'], 0) ?></td>
                                                            <td><?= number_format($r['sale_price_1']) ?></td>
                                                            <td><?= number_format($r['sale_price_2']) ?></td>
                                                            <td><?= number_format($r['sale_price_3']) ?></td>
                                                            <th style="display:none;">SKU_CAT_CODE</th> <!-- 16 (hidden) -->
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot class="table-secondary font-weight-bold">
                                                    <tr>
                                                        <td colspan="11" class="text-end">รวม</td>
                                                        <td class="text-end"><?= number_format($total_quantity_1) ?></td>
                                                        <td class="text-end"><?= number_format($total_quantity_2) ?></td>
                                                        <td class="text-end"><?= number_format($total_quantity_3) ?></td>
                                                        <td class="text-end"><?= number_format($total_quantity_all) ?></td>
                                                        <td class="text-end"><?= number_format($total_gift_voucher_all, 0) ?></td>
                                                        <td></td> <!-- ประมาณการ 300 -->
                                                        <td></td> <!-- ประมาณการ 120 -->
                                                        <td></td> <!-- ประมาณการ 60 -->
                                                        <td style="display:none;"></td>
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

<script>
    $(document).ready(function () {
        const table = $('#reserveTable').DataTable({
            responsive: true,
            scrollX: true,
            language: {
                search: "ค้นหา:",
                lengthMenu: "แสดง _MENU_ รายการ",
                info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                paginate: {
                    previous: "ก่อนหน้า",
                    next: "ถัดไป"
                },
                zeroRecords: "ไม่พบข้อมูล",
                infoEmpty: "ไม่มีข้อมูล",
                infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)"
            },
            initComplete: function () {
                // Trigger footer update on filter
                this.api().draw();
                updateFooterSums();
            }
        });

        // ฟังก์ชันกรอง custom สำหรับ skuCategoryFilter
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            const selectedCategory = $('#skuCategoryFilter').val();
            const rowCategory = data[19] || '';

            if (!selectedCategory) {
                return true; // แสดงทั้งหมดถ้าไม่เลือก
            }
            return rowCategory === selectedCategory;
        });

        $('#skuCategoryFilter').on('change', function () {
            table.draw();
        });

        // คำนวณผลรวมตามที่แสดงอยู่
        function updateFooterSums() {
            let sum_q1 = 0, sum_q2 = 0, sum_q3 = 0, sum_total = 0, sum_gv = 0;
            const api = table.api();

            api.rows({ filter: 'applied' }).every(function () {
                const data = this.data();
                sum_q1 += parseInt(data[11].replace(/,/g, '')) || 0;
                sum_q2 += parseInt(data[12].replace(/,/g, '')) || 0;
                sum_q3 += parseInt(data[13].replace(/,/g, '')) || 0;
                sum_total += parseInt(data[14].replace(/,/g, '')) || 0;
                sum_gv += parseFloat(data[15].replace(/,/g, '')) || 0;
            });

            $(api.column(11).footer()).html(sum_q1.toLocaleString());
            $(api.column(12).footer()).html(sum_q2.toLocaleString());
            $(api.column(13).footer()).html(sum_q3.toLocaleString());
            $(api.column(14).footer()).html(sum_total.toLocaleString());
            $(api.column(15).footer()).html(sum_gv.toLocaleString(undefined, { minimumFractionDigits: 2 }));
        }

        // Recalc on every draw (eg. after search/filter)
        table.on('draw', function () {
            updateFooterSums();
        });

        // ใส่ค่าจาก query string
        const queryParams = new URLSearchParams(window.location.search);
        $('#customerName').val(queryParams.get('CUSTOMER_NAME'));
        $('#phone').val(queryParams.get('PHONE'));
        $('#province').val(queryParams.get('PROVINCE'));
        $('#saleName').val(queryParams.get('SALE_NAME'));
    });
</script>



</body>
</html>
