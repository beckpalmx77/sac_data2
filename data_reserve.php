<?php
include('includes/Header.php');
include('config/connect_db.php'); // PDO $conn

$doc_id = isset($_GET['doc_id']) ? trim($_GET['doc_id']) : '';
if (empty($doc_id)) {
    die("\n        <div class='container mt-5'>
            <div class='alert alert-danger' role='alert'>กรุณาระบุเลขที่เอกสารใน URL เช่น ?doc_id=xxx</div>
        </div>");
}

$sql_get = "
    SELECT e.*, t.point, t.gift_voucher
    FROM ims_product_reserve_event AS e
    LEFT JOIN ims_product_reserve_target_point AS t ON t.SKU_CODE = e.SKU_CODE
    WHERE e.DOC_ID = :doc_id
    ORDER BY e.line_no
";

$stmt = $conn->prepare($sql_get);
$stmt->execute(['doc_id' => $doc_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_quantity_1 = $total_quantity_2 = $total_quantity_3 = $total_quantity_all = $total_gift_voucher_all = 0;

?>
<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">รายการลงทะเบียนจองยาง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">ปิดหน้าต่าง</button>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <form method="post" id="MainrecordForm" name="MainrecordForm" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">เลขที่เอกสาร</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($doc_id) ?>" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">ชื่อร้านค้า</label>
                                            <input type="text" class="form-control" id="customerName" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">หมายเลขโทรศัพท์</label>
                                            <input type="text" class="form-control" id="phone" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">จังหวัด</label>
                                            <input type="text" class="form-control" id="province" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">ชื่อเซลล์</label>
                                            <input type="text" class="form-control" id="saleName" readonly>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="reserveTable">
                                            <thead class="table-primary">
                                            <tr>
                                                <th>ลำดับ</th>
                                                <th>วันที่</th>
                                                <th>รหัสสินค้า</th>
                                                <th>ชื่อสินค้า</th>
                                                <th>หมวดหมู่</th>
                                                <th>ยี่ห้อ</th>
                                                <th>คะแนนต่อเส้น</th>
                                                <th>300 เส้น</th>
                                                <th>120 เส้น</th>
                                                <th>60 เส้น</th>
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
                                                    <td><?= htmlspecialchars($r['line_no']) ?></td>
                                                    <td><?= htmlspecialchars($r['DOC_DATE']) ?></td>
                                                    <td><?= htmlspecialchars($r['SKU_CODE']) ?></td>
                                                    <td><?= htmlspecialchars($r['SKU_NAME']) ?></td>
                                                    <td><?= htmlspecialchars($r['SKU_CAT_NAME']) ?></td>
                                                    <td><?= htmlspecialchars($r['BRAND']) ?></td>
                                                    <td><?= $r['point'] ?></td>
                                                    <td><?= number_format($r['price_1']) ?></td>
                                                    <td><?= number_format($r['price_2']) ?></td>
                                                    <td><?= number_format($r['price_3']) ?></td>
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
                                                <td colspan="10" class="text-end">รวม</td>
                                                <td><?= $total_quantity_1 ?></td>
                                                <td><?= $total_quantity_2 ?></td>
                                                <td><?= $total_quantity_3 ?></td>
                                                <td><?= $total_quantity_all ?></td>
                                                <td><?= number_format($total_gift_voucher_all, 2) ?></td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('includes/Modal-Logout.php'); include('includes/Footer.php'); ?>
    </div>
</div>
<script>
    $(document).ready(function () {
        const params = new URLSearchParams(window.location.search);
        $('#customerName').val(params.get('CUSTOMER_NAME'));
        $('#phone').val(params.get('PHONE'));
        $('#province').val(params.get('PROVINCE'));
        $('#saleName').val(params.get('SALE_NAME'));

        $('#reserveTable').DataTable({
            responsive: true,
            scrollX: true,
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
            }
        });
    });
</script>
</body>
</html>
