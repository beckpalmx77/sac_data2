<?php
include('includes/Header.php');
include('config/connect_db.php'); // PDO $conn
include('util/month_util.php');

$DOC_ID = isset($_GET['DOC_ID']) ? trim($_GET['DOC_ID']) : '';
$MONTH = isset($_GET['MONTH']) ? trim($_GET['MONTH']) : '';
$YEAR = isset($_GET['YEAR']) ? trim($_GET['YEAR']) : '';

if (empty($DOC_ID)) {
    die("ไม่พบข้อมูล");
}

$curr_year = date("Y");
$prev_year1 = $curr_year - 1;
$prev_year2 = $curr_year - 2;
$prev_year3 = $curr_year - 3;
$prev_year4 = $curr_year - 4;
$prev_year5 = $curr_year - 5;

// Build the dynamic SELECT list for each year
$select_years = "";
for ($i = 0; $i <= 5; $i++) {
    $year = $curr_year - $i;
    $select_years .= "SUM(CASE WHEN s.DI_YEAR = {$year} THEN s.TRD_QTY ELSE 0 END) AS `sale_year_{$year}`, ";
}

$select_years = rtrim($select_years, ", ");

$sql = "
    SELECT
        h.id,  
        h.DOC_ID,
        h.DOC_DATE,
        h.MONTH,
        h.YEAR,
        h.CUSTOMER_NAME,
        h.AMPHUR,
        h.PROVINCE,
        h.SKU_CODE,
        h.BRAND,
        h.TARGET,
        h.VOLUME,
        h.TARGET * h.VOLUME AS TOTAL_VOLUME,
        h.seq_record,
        SUM(CASE WHEN s.DI_YEAR = {$YEAR} AND s.DI_MONTH = {$MONTH} THEN s.TRD_QTY ELSE 0 END) AS `month_sale`,
        {$select_years}
    FROM v_ims_sale_logistic_plan_detail AS h
    LEFT JOIN ims_data_sale_sac_all AS s
        ON h.SKU_CODE = s.SKU_CODE AND h.CUSTOMER_NAME LIKE CONCAT('%', s.AR_NAME, '%')
    WHERE h.DOC_ID = :DOC_ID
    GROUP BY
        h.id,  
        h.SKU_CODE,
        h.BRAND,
        h.CUSTOMER_NAME,
        h.AMPHUR,
        h.PROVINCE,
        h.TARGET,
        h.VOLUME
    ORDER BY
        h.CUSTOMER_NAME,
        h.SKU_CODE
";

$stmt = $conn->prepare($sql);
$stmt->execute(['DOC_ID' => $DOC_ID]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$header_data = !empty($rows) ? $rows[0] : null;

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการเป้าการขายและแผนการส่ง</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/datatables/v11/jquery.dataTables.min.css" rel="stylesheet">
    <link href="vendor/datatables/v11/buttons.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet">
    <style>
        th, td {
            white-space: nowrap;
        }

        .table-responsive {
            overflow: auto;
            position: relative;
        }

        #dataTable {
            width: 100% !important;
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
                    <h1 class="h4 mb-0 text-gray-800">รายการเป้าการขายและแผนการส่ง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">
                        ปิดหน้าต่าง
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label>เลขที่เอกสาร</label>
                                <input type="text" class="form-control" id="doc_id"
                                       value="<?= htmlspecialchars($header_data['DOC_ID'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label>เดือน</label>
                                <input type="text" class="form-control" id="MONTH"
                                       value="<?= htmlspecialchars($month_arr[$header_data['MONTH']] ?? ''); ?>"
                                       readonly>
                            </div>
                            <div class="col-md-2">
                                <label>ปี</label>
                                <input type="text" class="form-control" id="YEAR"
                                       value="<?= htmlspecialchars($header_data['YEAR'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Sequence</label>
                                <input type="text" class="form-control" id="seq_record"
                                       value="<?= htmlspecialchars($header_data['seq_record'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped w-100 nowrap" id="dataTable">
                                <thead class="table-primary">
                                <tr>
                                    <th>ลูกค้า</th>
                                    <th>อำเภอ</th>
                                    <th>จังหวัด</th>
                                    <th>ยี่ห้อ</th>
                                    <th>รหัสสินค้า</th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year5) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year4) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year3) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year2) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year1) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($curr_year) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year5) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year4) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year3) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year2) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year1) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($curr_year) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year5) ?>
                                        - <?= htmlspecialchars($prev_year1) ?></th>
                                    <th>เป้าการขาย</th>
                                    <th>ปริมาตร/เส้น</th>
                                    <th>ปริมาตร รวม</th>
                                    <th>
                                        ยอดขายเดือน <?= htmlspecialchars($month_arr[$header_data['MONTH']] ?? ''); ?> </th>
                                    <th>หักลบตั้งเป้า ขาดจำนวนเส้น (เส้น)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <?php
                                    // Calculate averages here to make the code cleaner
                                    $avg_year5 = $r["sale_year_{$prev_year5}"] / 12;
                                    $avg_year4 = $r["sale_year_{$prev_year4}"] / 12;
                                    $avg_year3 = $r["sale_year_{$prev_year3}"] / 12;
                                    $avg_year2 = $r["sale_year_{$prev_year2}"] / 12;
                                    $avg_year1 = $r["sale_year_{$prev_year1}"] / 12;
                                    $avg_curr_year = $r["sale_year_{$curr_year}"] / 12;

                                    // Calculate the 5-year average
                                    $total_sale_5yr = $r["sale_year_{$prev_year5}"] + $r["sale_year_{$prev_year4}"] + $r["sale_year_{$prev_year3}"] + $r["sale_year_{$prev_year2}"] + $r["sale_year_{$prev_year1}"];
                                    $avg_5yr = $total_sale_5yr / 60; // 5 years * 12 months

                                    ?>
                                    <tr data-id="<?= htmlspecialchars($r['id']) ?>"
                                        data-volume="<?= htmlspecialchars($r['VOLUME']) ?>">
                                        <td><?= htmlspecialchars($r['CUSTOMER_NAME']) ?></td>
                                        <td><?= htmlspecialchars($r['AMPHUR']) ?></td>
                                        <td><?= htmlspecialchars($r['PROVINCE']) ?></td>
                                        <td><?= htmlspecialchars($r['BRAND']) ?></td>
                                        <td><?= htmlspecialchars($r['SKU_CODE']) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year5}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year4}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year3}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year2}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year1}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$curr_year}"], 0) ?></td>
                                        <td><?= number_format($avg_year5, 2) ?></td>
                                        <td><?= number_format($avg_year4, 2) ?></td>
                                        <td><?= number_format($avg_year3, 2) ?></td>
                                        <td><?= number_format($avg_year2, 2) ?></td>
                                        <td><?= number_format($avg_year1, 2) ?></td>
                                        <td><?= number_format($avg_curr_year, 2) ?></td>
                                        <td><?= number_format($avg_5yr, 2) ?></td>
                                        <td class="editable-target">
                                            <input type="number" class="form-control form-control-sm"
                                                   value="<?= htmlspecialchars($r['TARGET']) ?>">
                                        </td>
                                        <td><?= number_format($r['VOLUME'], 2) ?></td>
                                        <td class="total-volume"><?= number_format($r['TOTAL_VOLUME'], 2) ?></td>
                                        <td class="month-sale"><?= number_format($r['month_sale'], 0) ?></td>
                                        <?php $TOTAL_REMAIN_QTY = $r['month_sale'] - $r['TARGET'] ?>
                                        <td class="remaining-qty"><?= number_format($TOTAL_REMAIN_QTY, 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
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

<!-- Custom Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">ยืนยันการอัปเดต</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                คุณต้องการยืนยันการแก้ไขข้อมูลหรือไม่?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelUpdateBtn">ยกเลิก
                </button>
                <button type="button" class="btn btn-primary" id="confirmUpdateBtn">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<style>
    th, td {
        white-space: nowrap;
    }

    .table-responsive {
        overflow: auto;
        position: relative;
    }

    #dataTable {
        width: 100% !important;
    }
</style>

<script>
    $(document).ready(function () {
        $('#dataTable').DataTable({
            responsive: true,
            scrollX: true,
            paging: true,
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
            // Configure FixedColumns to freeze the first 5 columns
            fixedColumns: {
                leftColumns: 5
            }
        });

        // Store the data of the row being edited
        let currentUpdateData = null;
        let initialTargetValue = null;
        let activeInput = null;

        // Event handler when the target input field value changes
        $('#dataTable').on('change', '.editable-target input', function () {
            activeInput = $(this);
            initialTargetValue = activeInput.data('initialValue') || activeInput.val();

            const row = activeInput.closest('tr');
            const newTarget = parseFloat(activeInput.val());
            const id = row.data('id');
            const volume = parseFloat(row.data('volume'));
            const monthSaleText = row.find('.month-sale').text();
            const monthSale = parseFloat(monthSaleText.replace(/,/g, ''));

            // Validate input
            if (isNaN(newTarget)) {
                $('#confirmModal .modal-body').text('โปรดป้อนค่าเป้าการขายเป็นตัวเลขที่ถูกต้อง');
                $('#confirmUpdateBtn').hide();
                $('#cancelUpdateBtn').show();
                $('#confirmModal').modal('show');
                return;
            }

            // Store the data for confirmation
            currentUpdateData = {
                id: id,
                new_target: newTarget,
                volume: volume,
                monthSale: monthSale,
                row: row
            };

            // Show confirmation modal
            $('#confirmModal .modal-body').text('คุณต้องการยืนยันการแก้ไขข้อมูลหรือไม่?');
            $('#confirmUpdateBtn').show();
            $('#cancelUpdateBtn').show();
            $('#confirmModal').modal('show');
        });

        // Handle the confirmation button click in the modal
        $('#confirmUpdateBtn').on('click', function () {
            if (currentUpdateData) {
                const {id, new_target, volume, monthSale, row} = currentUpdateData;

                // Perform new calculations
                const newTotalVolume = new_target * volume;
                const newRemainingQty = monthSale - new_target;

                // Update the display for the calculated columns
                row.find('.total-volume').text(newTotalVolume.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                row.find('.remaining-qty').text(newRemainingQty.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }));

                // Update the initial value for the next change
                activeInput.data('initialValue', new_target);

                // Send data to the server using AJAX
                $.ajax({
                    url: 'model/manage_update_sale_logistic_plan.php',
                    type: 'POST',
                    data: {
                        id: id,
                        new_target: new_target
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            console.log(response.message);
                        } else {
                            console.error(response.message);
                            $('#confirmModal .modal-body').text('เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' + response.message);
                            $('#confirmUpdateBtn').hide();
                            $('#cancelUpdateBtn').hide(); // No need to cancel, just an error
                            $('#confirmModal').modal('show');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        $('#confirmModal .modal-body').text('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
                        $('#confirmUpdateBtn').hide();
                        $('#cancelUpdateBtn').hide(); // No need to cancel, just an error
                        $('#confirmModal').modal('show');
                    }
                });
            }
            // Close the modal
            $('#confirmModal').modal('hide');
        });

        // Handle the cancel button click in the modal
        $('#cancelUpdateBtn').on('click', function () {
            if (activeInput && initialTargetValue !== null) {
                activeInput.val(initialTargetValue);
                // Re-calculate and display the values based on the initial value
                const row = activeInput.closest('tr');
                const volume = parseFloat(row.data('volume'));
                const monthSaleText = row.find('.month-sale').text();
                const monthSale = parseFloat(monthSaleText.replace(/,/g, ''));

                const newTotalVolume = initialTargetValue * volume;
                const newRemainingQty = monthSale - initialTargetValue;

                row.find('.total-volume').text(newTotalVolume.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                row.find('.remaining-qty').text(newRemainingQty.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }));
            }
            $('#confirmModal').modal('hide');
        });

        // Store initial values on page load
        $('#dataTable .editable-target input').each(function () {
            $(this).data('initialValue', $(this).val());
        });
    });
</script>

</body>
</html>
