<?php
session_start();
error_reporting(0);

include('includes/Header.php');
include('config/connect_db.php');

$and_sale = "";

if ($_SESSION['account_type'] === "sale_user") {
    $and_sale = " AND SALE_NAME = '" . $_SESSION['first_name']  . "'";
}

if ($_SESSION['account_type'] === "take") {
    $and_sale = " AND TAKE_NAME = '" . addslashes($_SESSION['first_name']) . "'";
}

$sql_get = " SELECT * FROM v_sum_product_reserve_event WHERE 1=1 "  . $and_sale . " ORDER BY id ASC ";
$stmt = $conn->prepare($sql_get);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_quantity_1 = 0;
$total_quantity_2 = 0;
$total_quantity_3 = 0;
$total_quantity_all = 0;
$total_gift_voucher_all = 0.0;
$total_point = 0.0;
$total_point_all = 0.0;

// ฟังก์ชันคำนวณ gift voucher ตามช่วงจำนวนเส้นยาง
function calculateGiftVoucher($total_quantity, $gift_voucher, $gift_voucher1, $gift_voucher2) {
    $gift_voucher_total = 0;

    if ($total_quantity >= 60 && $total_quantity <= 300) {
        $gift_voucher_total = $gift_voucher * $total_quantity;
    } elseif ($total_quantity >= 301 && $total_quantity <= 600) {
        $gift_voucher_total = $gift_voucher1 * $total_quantity;
    } elseif ($total_quantity >= 601 && $total_quantity <= 1000) {
        $gift_voucher_total = $gift_voucher2 * $total_quantity;
    }

    return $gift_voucher_total;
}

?>

<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <br>
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">สรุปรายการลงทะเบียนจองยาง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">ปิดหน้าต่าง</button>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped w-100 nowrap" id="reserveTable">
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
                                            <th>บัตรของขวัญต่อ 60-300 เส้น</th>
                                            <th>บัตรของขวัญต่อ 301-600 เส้น</th>
                                            <th>บัตรของขวัญต่อ 601-1000 เส้น</th>
                                            <th>มิ.ย.</th>
                                            <th>ก.ค.</th>
                                            <th>ส.ค.</th>
                                            <th>รวมจำนวน</th>
                                            <th>บัตรของขวัญ</th>
                                            <th>รวมคะแนน</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($rows as $i => $r):
                                            $total_quantity_1 += (int)$r['quantity_1'];
                                            $total_quantity_2 += (int)$r['quantity_2'];
                                            $total_quantity_3 += (int)$r['quantity_3'];
                                            $total_quantity_all += (int)$r['total_quantity'];

                                            // คำนวณ gift voucher ใหม่ ตามเงื่อนไข
                                            $calculated_gift_voucher = calculateGiftVoucher(
                                                (int)$r['total_quantity'],
                                                (float)$r['gift_voucher'],
                                                (float)$r['gift_voucher1'],
                                                (float)$r['gift_voucher2']
                                            );
                                            $total_gift_voucher_all += $calculated_gift_voucher;

                                            $total_point = (int)$r['point'] * (int)$r['total_quantity'];
                                            $total_point_all += $total_point;
                                            ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
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
                                                <td><?= number_format($r['gift_voucher1']) ?></td>
                                                <td><?= number_format($r['gift_voucher2']) ?></td>
                                                <td class="text-end"><?= number_format($r['quantity_1']) ?></td>
                                                <td class="text-end"><?= number_format($r['quantity_2']) ?></td>
                                                <td class="text-end"><?= number_format($r['quantity_3']) ?></td>
                                                <td class="text-end"><?= number_format($r['total_quantity']) ?></td>
                                                <td class="text-end"><?= number_format($calculated_gift_voucher, 2) ?></td>
                                                <td class="text-end"><?= number_format($total_point) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-secondary font-weight-bold">
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-end"><strong>รวม</strong></td>
                                            <td class="text-end">
                                                <strong><?= number_format($total_quantity_1) ?></strong></td>
                                            <td class="text-end">
                                                <strong><?= number_format($total_quantity_2) ?></strong></td>
                                            <td class="text-end">
                                                <strong><?= number_format($total_quantity_3) ?></strong></td>
                                            <td class="text-end">
                                                <strong><?= number_format($total_quantity_all) ?></strong></td>
                                            <td class="text-end">
                                                <strong><?= number_format($total_gift_voucher_all, 2) ?></strong></td>
                                            <td class="text-end">
                                                <strong><?= number_format($total_point_all, 2) ?></strong></td>

                                        </tr>
                                        </tfoot>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/Modal-Logout.php');
            include('includes/Footer.php'); ?>
        </div>
    </div>

    <!-- DataTables & Export -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

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
    </style>

    <script>
        $(document).ready(function () {
            $('#reserveTable').DataTable({
                scrollX: true,
                scrollY: "400px",
                scrollCollapse: true,
                paging: true,
                fixedColumns: {
                    leftColumns: 3
                },
                fixedHeader: true,
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5'
                ],
                order: [[1, 'desc']]
            });
        });
    </script>
</body>
</html>
