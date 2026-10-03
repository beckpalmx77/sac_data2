<?php

session_start();
error_reporting(0);

include('includes/Header.php');
include('config/connect_db.php'); // PDO $conn

$doc_id = isset($_GET['doc_id']) ? trim($_GET['doc_id']) : '';
$customer_code = isset($_GET['CUSTOMER_CODE']) ? trim($_GET['CUSTOMER_CODE']) : '';

if (empty($customer_code)) {
    die("กรุณาระบุใน URL เช่น ?CUSTOMER_CODE=xxx");
}

$sql_get = "
    SELECT e.*, t.point, t.gift_voucher , t.gift_voucher1 , t.gift_voucher2
    FROM ims_product_reserve_event AS e
    LEFT JOIN ims_product_reserve_target_point AS t ON t.SKU_CODE = e.SKU_CODE
    WHERE e.CUSTOMER_CODE = :customer_code 
    ORDER BY e.doc_id, e.line_no
";

$stmt = $conn->prepare($sql_get);
$stmt->execute(['customer_code' => $customer_code]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการจองยาง</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/datatables/v11/jquery.dataTables.min.css" rel="stylesheet">
    <link href="vendor/datatables/v11/buttons.dataTables.min.css" rel="stylesheet">
    <style>
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <br>
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">รายการลงทะเบียนจองยาง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">ปิดหน้าต่าง</button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="post" id="MainrecordForm" name="MainrecordForm">
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>เลขที่เอกสาร</label>
                                    <input type="text" class="form-control" id="doc_id" value="<?= htmlspecialchars($doc_id); ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>ชื่อร้านค้า</label>
                                    <input type="text" class="form-control" id="customerName" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label>โทรศัพท์</label>
                                    <input type="text" class="form-control" id="phone" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label>จังหวัด</label>
                                    <input type="text" class="form-control" id="province" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label>ชื่อเซลล์</label>
                                    <input type="text" class="form-control" id="saleName" readonly>
                                </div>
                            </div>

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

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped w-100 nowrap" id="reserveTable">
                                    <thead class="table-primary">
                                    <tr>
                                        <th>ลำดับ</th>               <!-- 0 -->
                                        <th>วันที่</th>              <!-- 1 -->
                                        <th>รหัสสินค้า</th>         <!-- 2 -->
                                        <th>ชื่อสินค้า</th>          <!-- 3 -->
                                        <th>หมวดหมู่</th>           <!-- 4 -->
                                        <th>ยี่ห้อ</th>             <!-- 5 -->
                                        <th>คะแนนต่อเส้น</th>       <!-- 6 -->
                                        <th>300 เส้น</th>           <!-- 7 -->
                                        <th>120 เส้น</th>           <!-- 8 -->
                                        <th>60 เส้น</th>            <!-- 9 -->
                                        <th>บัตรของขวัญต่อ 60-300 เส้น</th> <!-- 10 -->
                                        <th>บัตรของขวัญต่อ 301-600 เส้น</th> <!-- 11 -->
                                        <th>บัตรของขวัญต่อ 601-1000 เส้น</th> <!-- 12 -->
                                        <th>มิ.ย.</th>             <!-- 13 -->
                                        <th>ก.ค.</th>              <!-- 14 -->
                                        <th>ส.ค.</th>              <!-- 15 -->
                                        <th>รวม</th>               <!-- 16 -->
                                        <th>บัตรของขวัญ</th>        <!-- 17 -->
                                        <th style="display:none;">SKU_CAT_CODE</th> <!-- 18 (hidden) -->
                                        <th>คะแนนรวม</th> <!-- คอลัมน์ใหม่ --> <!-- 19 -->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $total_quantity_1 = $total_quantity_2 = $total_quantity_3 = $total_quantity_all = $total_gift_voucher_all = $total_point_all = 0;
                                    foreach ($rows as $r):
                                        $total_quantity_1 += (int)$r['quantity_1'];
                                        $total_quantity_2 += (int)$r['quantity_2'];
                                        $total_quantity_3 += (int)$r['quantity_3'];
                                        $total_quantity_all += (int)$r['total_quantity'];
                                        $total_gift_voucher_all += (float)$r['total_gift_voucher'];
                                        $line_point = ((int)$r['total_quantity']) * ((float)$r['point']);
                                        $total_point_all += $line_point;
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['line_no']) ?></td>
                                            <td><?= htmlspecialchars($r['DOC_DATE']) ?></td>
                                            <td><?= htmlspecialchars($r['SKU_CODE']) ?></td>
                                            <td><?= htmlspecialchars($r['SKU_NAME']) ?></td>
                                            <td><?= htmlspecialchars($r['SKU_CAT_NAME']) ?></td>
                                            <td><?= htmlspecialchars($r['BRAND']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars($r['point']) ?></td>
                                            <td class="text-end"><?= number_format($r['price_1'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['price_2'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['price_3'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['gift_voucher'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['gift_voucher1']) ?></td>
                                            <td class="text-end"><?= number_format($r['gift_voucher2']) ?></td>
                                            <td class="text-end"><?= number_format($r['quantity_1'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['quantity_2'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['quantity_3'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['total_quantity'],0) ?></td>
                                            <td class="text-end"><?= number_format($r['total_gift_voucher'],0) ?></td>
                                            <td style="display:none;"><?= htmlspecialchars($r['SKU_CAT_CODE']) ?></td>
                                            <td class="text-end"><?= number_format($line_point, 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-secondary font-weight-bold">
                                    <tr>
                                        <td colspan="13" class="text-end">รวม</td> <!-- ใช้ colspan=13 ครอบถึงก่อน quantity -->
                                        <td class="text-end"><?= number_format($total_quantity_1,0) ?></td> <!-- มิ.ย. -->
                                        <td class="text-end"><?= number_format($total_quantity_2,0) ?></td> <!-- ก.ค. -->
                                        <td class="text-end"><?= number_format($total_quantity_3,0) ?></td> <!-- ส.ค. -->
                                        <td class="text-end"><?= number_format($total_quantity_all,0) ?></td> <!-- รวม -->
                                        <td class="text-end"><?= number_format($total_gift_voucher_all,0) ?></td> <!-- บัตรของขวัญ -->
                                        <td style="display:none;"></td> <!-- SKU_CAT_CODE (hidden) -->
                                        <td class="text-end"><?= number_format($total_point_all, 0) ?></td> <!-- คะแนนรวม -->
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        <?php
        include('includes/Modal-Logout.php');
        include('includes/Footer.php');
        ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>

<!-- เพิ่ม CSS สำหรับ FixedColumns -->
<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet" />
<!-- เพิ่ม JS สำหรับ FixedColumns -->
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">

<!-- JS -->
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<style>
    th, td {
        white-space: nowrap;
    }
    .table-responsive {
        overflow: auto;
        position: relative;
    }
    #reserveTable {
        width: 100% !important;
    }
</style>

<script>
    $(document).ready(function () {
        const skuCatCodeColumnIndex = 16;

        const table = $('#reserveTable').DataTable({
            responsive: true,
            scrollX: true,
            paging: true,
            fixedHeader: true, // ตรึง header
            fixedColumns: {
                leftColumns: 3
            },
            language: {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ถัดไป'
                }
            },
            columnDefs: [
                { targets: [skuCatCodeColumnIndex], visible: false }
            ],
            // เพิ่ม footer callback เพื่อคำนวณยอดรวมตอนโหลดและตอน filter
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                // ฟังก์ชันแปลงค่า string เป็น number
                const parseNum = val => {
                    if (typeof val === 'string') {
                        val = val.replace(/[, ]+/g, '');
                    }
                    return val ? parseFloat(val) : 0;
                };

                // รวมแต่ละคอลัมน์ที่ต้องการ (columns index ตาม table)
                const sumCol = colIndex => api
                    .column(colIndex, { filter: 'applied' })
                    .data()
                    .reduce((a, b) => a + parseNum(b), 0);

                // คำนวณ sum เฉพาะแถวที่กรองแล้ว
                const total_quantity_1 = sumCol(13);
                const total_quantity_2 = sumCol(14);
                const total_quantity_3 = sumCol(15);
                const total_quantity_all = sumCol(16);
                const total_gift_voucher_all = sumCol(17);
                const total_point_all = sumCol(18);

                // แสดงผลใน footer
                $(api.column(13).footer()).html(total_quantity_1.toLocaleString());
                $(api.column(14).footer()).html(total_quantity_2.toLocaleString());
                $(api.column(15).footer()).html(total_quantity_3.toLocaleString());
                $(api.column(16).footer()).html(total_quantity_all.toLocaleString());
                $(api.column(17).footer()).html(total_gift_voucher_all.toLocaleString());
                $(api.column(18).footer()).html(total_point_all.toLocaleString());
            }
        });

        // ฟังก์ชันกรอง custom สำหรับ skuCategoryFilter
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            const selectedCategory = $('#skuCategoryFilter').val();
            const rowCategory = data[skuCatCodeColumnIndex] || '';

            if (!selectedCategory) {
                return true; // แสดงทั้งหมดถ้าไม่เลือก
            }
            return rowCategory === selectedCategory;
        });

        $('#skuCategoryFilter').on('change', function () {
            table.draw();
        });

        // เติมค่า readonly input จาก URL (เหมือนเดิม)
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
        $('#customerName').val(queryParams["CUSTOMER_NAME"] || '');
        $('#phone').val(queryParams["PHONE"] || '');
        $('#province').val(queryParams["PROVINCE"] || '');
        $('#saleName').val(queryParams["SALE_NAME"] || '');
    });

</script>

</body>
</html>
