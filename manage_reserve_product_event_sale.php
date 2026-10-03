<?php
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
                                    <input type="text" class="form-control" id="doc_id"
                                           value="<?= htmlspecialchars($doc_id); ?>" readonly>
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
                                        <th>คะแนนรวม</th> <!-- 19 -->
                                        <th>ประมาณการ 300 เส้น</th> <!-- 20 -->
                                        <th>ประมาณการ 120 เส้น</th> <!-- 21 -->
                                        <th>ประมาณการ 60 เส้น</th> <!-- 22 -->
                                        <!--th style="display:none;"></th-->
                                        <th>แก้ไข</th> <!-- 23 -->
                                        <th>ลบ</th> <!-- 24 -->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $total_quantity_1 = $total_quantity_2 = $total_quantity_3 = $total_quantity_all = $total_gift_voucher_all = $total_point_all = 0;
                                    foreach ($rows as $r):
                                        $quantity = (int)$r['total_quantity'];

                                        // กำหนด gift voucher ตามเงื่อนไขจำนวนเส้น
                                        if ($quantity >= 60 && $quantity <= 300) {
                                            $gift_voucher = (float)$r['gift_voucher'];
                                        } elseif ($quantity > 300 && $quantity <= 600) {
                                            $gift_voucher = (float)$r['gift_voucher1'];
                                        } elseif ($quantity > 600) {
                                            $gift_voucher = (float)$r['gift_voucher2'];
                                        } else {
                                            $gift_voucher = 0;
                                        }

                                        // รวมยอดต่างๆ
                                        $total_quantity_1 += (int)$r['quantity_1'];
                                        $total_quantity_2 += (int)$r['quantity_2'];
                                        $total_quantity_3 += (int)$r['quantity_3'];
                                        $total_quantity_all += $quantity;
                                        $total_gift_voucher_all += $gift_voucher * $quantity; // สมมุติว่า voucher คูณจำนวนเส้น
                                        $line_point = $quantity * ((float)$r['point']);
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
                                            <td class="text-end"><?= number_format($r['price_1'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['price_2'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['price_3'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['gift_voucher'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['gift_voucher1'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['gift_voucher2'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['quantity_1'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['quantity_2'], 0) ?></td>
                                            <td class="text-end"><?= number_format($r['quantity_3'], 0) ?></td>
                                            <td class="text-end"><?= number_format($quantity, 0) ?></td>
                                            <td class="text-end"><?= number_format($gift_voucher * $quantity, 0) ?></td>
                                            <!-- ปรับตรงนี้ -->
                                            <td style="display:none;"><?= htmlspecialchars($r['SKU_CAT_CODE']) ?></td>
                                            <td class="text-end"><?= number_format($line_point, 0) ?></td>
                                            <td><?= number_format($r['sale_price_1']) ?></td>
                                            <td><?= number_format($r['sale_price_2']) ?></td>
                                            <td><?= number_format($r['sale_price_3']) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                                        data-id="<?= $r['id'] ?>"
                                                        data-qty1="<?= $r['quantity_1'] ?>"
                                                        data-qty2="<?= $r['quantity_2'] ?>"
                                                        data-qty3="<?= $r['quantity_3'] ?>">
                                                    แก้ไข
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                        data-id="<?= $r['id'] ?>">
                                                    ลบ
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-secondary font-weight-bold">
                                    <tr>
                                        <td colspan="13" class="text-end">รวม</td>
                                        <td class="text-end"><?= number_format($total_quantity_1, 0) ?></td>
                                        <td class="text-end"><?= number_format($total_quantity_2, 0) ?></td>
                                        <td class="text-end"><?= number_format($total_quantity_3, 0) ?></td>
                                        <td class="text-end"><?= number_format($total_quantity_all, 0) ?></td>
                                        <td class="text-end"><?= number_format($total_gift_voucher_all, 0) ?></td>
                                        <td style="display:none;"></td> <!-- SKU_CAT_CODE -->
                                        <td class="text-end"><?= number_format($total_point_all, 0) ?></td>
                                        <td></td> <!-- ประมาณการ 300 -->
                                        <td></td> <!-- ประมาณการ 120 -->
                                        <td></td> <!-- ประมาณการ 60 -->
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="modal fade" id="editQtyModal" tabindex="-1" role="dialog"
                     aria-labelledby="editQtyModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form id="editQtyForm">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editQtyModalLabel">แก้ไขจำนวน</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="ปิด">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="edit-id" name="id">
                                    <div class="form-group">
                                        <label>มิ.ย.</label>
                                        <input type="number" class="form-control" id="edit-qty1" name="quantity_1">
                                    </div>
                                    <div class="form-group">
                                        <label>ก.ค.</label>
                                        <input type="number" class="form-control" id="edit-qty2" name="quantity_2">
                                    </div>
                                    <div class="form-group">
                                        <label>ส.ค.</label>
                                        <input type="number" class="form-control" id="edit-qty3" name="quantity_3">
                                    </div>
                                    <div class="form-group">
                                        <label>รวม</label>
                                        <input type="number" class="form-control" id="edit-sum" readonly>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก
                                    </button>
                                    <button type="submit" class="btn btn-primary">บันทึก</button>
                                </div>
                            </form>
                        </div>
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

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- เพิ่ม CSS สำหรับ FixedColumns -->
<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet"/>
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
        const skuCatCodeColumnIndex = 18;

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
                {targets: [skuCatCodeColumnIndex], visible: false}
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
                    .column(colIndex, {filter: 'applied'})
                    .data()
                    .reduce((a, b) => a + parseNum(b), 0);

                // คำนวณ sum เฉพาะแถวที่กรองแล้ว
                const total_quantity_1 = sumCol(14);
                const total_quantity_2 = sumCol(15);
                const total_quantity_3 = sumCol(16);
                const total_quantity_all = sumCol(17);
                const total_gift_voucher_all = sumCol(18);
                const total_point_all = sumCol(19);  // เพิ่มค่านี้

                // แสดงผลใน footer
                $(api.column(14).footer()).html(total_quantity_1.toLocaleString());
                $(api.column(15).footer()).html(total_quantity_2.toLocaleString());
                $(api.column(16).footer()).html(total_quantity_3.toLocaleString());
                $(api.column(17).footer()).html(total_quantity_all.toLocaleString());
                $(api.column(18).footer()).html(total_gift_voucher_all.toLocaleString());
                $(api.column(19).footer()).html(total_point_all.toLocaleString());  // แสดงผลคะแนนรวม
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

<script>
    $(document).on('click', '.edit-btn', function () {
        let id = $(this).data('id');
        let qty1 = parseInt($(this).data('qty1')) || 0;
        let qty2 = parseInt($(this).data('qty2')) || 0;
        let qty3 = parseInt($(this).data('qty3')) || 0;

        $('#edit-id').val(id);
        $('#edit-qty1').val(qty1);
        $('#edit-qty2').val(qty2);
        $('#edit-qty3').val(qty3);
        $('#edit-sum').val(qty1 + qty2 + qty3);

        $('#editQtyModal').modal('show');
    });

    $('#editQtyForm input[type="number"]').on('input', function () {
        let q1 = parseInt($('#edit-qty1').val()) || 0;
        let q2 = parseInt($('#edit-qty2').val()) || 0;
        let q3 = parseInt($('#edit-qty3').val()) || 0;
        $('#edit-sum').val(q1 + q2 + q3);
    });

</script>

<script>
    $('#editQtyForm').on('submit', function (e) {
        e.preventDefault();

        let id = $('#edit-id').val();
        let qty1 = parseInt($('#edit-qty1').val()) || 0;
        let qty2 = parseInt($('#edit-qty2').val()) || 0;
        let qty3 = parseInt($('#edit-qty3').val()) || 0;
        let total = qty1 + qty2 + qty3;

        // ส่งข้อมูลไปยัง PHP ด้วย AJAX
        $.post('model/manage_reserve_product_event_sale.php', {
            action: "UPDATE_QUANTITY",
            id: id,
            quantity_1: qty1,
            quantity_2: qty2,
            quantity_3: qty3
        }, function (response) {
            // ถ้าบันทึกสำเร็จ อัปเดตตาราง
            $('#reserveTable tbody tr').each(function () {
                if ($(this).find('.edit-btn').data('id') == id) {
                    $(this).find('td:eq(13)').text(qty1.toLocaleString()); // มิ.ย.
                    $(this).find('td:eq(14)').text(qty2.toLocaleString()); // ก.ค.
                    $(this).find('td:eq(15)').text(qty3.toLocaleString()); // ส.ค.
                    $(this).find('td:eq(16)').text(total.toLocaleString()); // รวม

                    // อัปเดตค่าใน data-* ของปุ่ม
                    $(this).find('.edit-btn')
                        .data('qty1', qty1)
                        .data('qty2', qty2)
                        .data('qty3', qty3);
                }
            });

            $('#editQtyModal').modal('hide');
        }).fail(function (xhr) {
            alert('เกิดข้อผิดพลาด: ' + xhr.responseText);
        });
    });
</script>

<!--script>
    $(document).ready(function () {
        $('.delete-btn').on('click', function () {
            const id = $(this).data('id');

            if (confirm('คุณต้องการลบรายการนี้หรือไม่?' + id)) {
                $.ajax({
                    url: 'model/manage_reserve_product_event_sale.php',
                    method: 'POST',
                    data: {
                        action: 'DELETE_DETAIL',
                        id: id
                    },
                    success: function (res) {
                        alert(res);
                        location.reload(); // โหลดหน้าใหม่หลังลบสำเร็จ
                    },
                    error: function (xhr) {
                        alert('เกิดข้อผิดพลาดในการลบ: ' + xhr.responseText);
                    }
                });
            }
        });
    });
</script-->

<script>
    // ใช้ event delegation โดยผูก event listener กับ <body> หรือ <table>
    // เมื่อมีการคลิกที่ปุ่มที่มี class 'delete-btn' ที่อยู่ภายใต้ <body>
    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');

        if (confirm('คุณต้องการลบรายการนี้หรือไม่?' + id)) {
            $.ajax({
                url: 'model/manage_reserve_product_event_sale.php',
                method: 'POST',
                data: {
                    action: 'DELETE_DETAIL',
                    id: id
                },
                success: function (res) {
                    alert(res);
                    location.reload(); // โหลดหน้าใหม่หลังลบสำเร็จ
                },
                error: function (xhr) {
                    alert('เกิดข้อผิดพลาดในการลบ: ' + xhr.responseText);
                }
            });
        }
    });
</script>


</body>
</html>