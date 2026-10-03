<?php
// ส่วน header, session, connect_db.php ตามเดิม
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/Header.php');
include('config/connect_db.php'); // PDO $conn

$curr_date = date("d-m-Y");

$curr_year1 = date("Y") - 5;
$curr_year2 = date("Y") - 4;
$curr_year3 = date("Y") - 3;
$curr_year4 = date("Y") - 2;
$curr_year5 = date("Y") - 1;
$curr_year = date("Y");

$customer_code = isset($_GET['customerCode']) ? $_GET['customerCode'] : '';
$customer_name = isset($_GET['customerName']) ? $_GET['customerName'] : '';

$sql_get = "SELECT SALE_NAME , TAKE_NAME 
    FROM m_customer_reserve_sale_take 
    WHERE CUSTOMER_CODE = :CUSTOMER_CODE 
    LIMIT 1";

$stmt = $conn->prepare($sql_get);
$stmt->execute(['CUSTOMER_CODE' => $customer_code]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);  // ดึงแถวเดียว

$SALE_NAME = $row ? $row['SALE_NAME'] : null;  // เช็คถ้ามีข้อมูล
$TAKE_NAME = $row ? $row['TAKE_NAME'] : null;  // เช็คถ้ามีข้อมูล

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Product Target Point</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    <!-- Bootstrap Select -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <!-- JavaScript -->
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>

    <!-- DataTables core -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- FixedColumns extension -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">


</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <br>
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">ลงทะเบียนจองยาง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">
                        ปิดหน้าต่าง
                    </button>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form id="targetPointForm" class="mb-4">
                            <div class="row mb-3">

                                <div class="col-md-3">
                                    <label for="customerCode"
                                           class="form-label">ร้านค้า<!--?php echo $customer_code; ?--> </label>
                                    <!-- ช่องสำหรับใส่ AR_CODE -->
                                    <input type="hidden" name="customerCode" id="customerCode"
                                           value="<?php echo htmlspecialchars($customer_code); ?>" readonly>
                                    <!-- ช่องสำหรับแสดง AR_NAME -->
                                    <input type="text" class="form-control" id="customerNameText"
                                           name="customerNameText" value="<?php echo $customer_name ?>" readonly>
                                    <input type="hidden" id="take_name" name="take_name"
                                           value="<?php echo $TAKE_NAME; ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="phone" class="form-label">หมายเลขโทรศัพท์</label>
                                    <input type="text" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="province" class="form-label">เลือกจังหวัด</label>
                                    <select id="province" name="province" class="form-control selectpicker"
                                            data-live-search="true" title="กรุณาเลือกจังหวัด">
                                        <option value="" selected disabled>-- เลือกจังหวัด --</option>
                                        <?php
                                        $sql1 = "SELECT * FROM ims_provinces ORDER BY province_name ASC";
                                        $query1 = $conn->prepare($sql1);
                                        $query1->execute();
                                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                        if ($query1->rowCount() > 0) {
                                            foreach ($results1 as $result1) {
                                                echo '<option value="' . htmlentities($result1->province_name) . '">' . htmlentities($result1->province_name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="saleName" class="form-label">ชื่อเซลล์</label>
                                    <select id="saleName" name="saleName" class="form-control selectpicker"
                                            data-live-search="true" title="กรุณาเลือกเซลล์">
                                        <option value="" disabled <?= empty($SALE_NAME) ? 'selected' : '' ?>>--
                                            ชื่อเซลล์ --
                                        </option>
                                        <?php
                                        $sql2 = "SELECT * FROM ims_sale_reserve ORDER BY id";
                                        $query2 = $conn->prepare($sql2);
                                        $query2->execute();
                                        $results2 = $query2->fetchAll(PDO::FETCH_OBJ);
                                        if ($query2->rowCount() > 0) {
                                            foreach ($results2 as $result2) {
                                                $selected = ($result2->sale_name === $SALE_NAME) ? 'selected' : '';
                                                echo '<option value="' . htmlentities($result2->sale_name) . '" ' . $selected . '>' . htmlentities($result2->sale_name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                            </div>

                            <div class="row mb-3 align-items-end">
                                <div class="col-md-2">
                                    <label for="doc_date" class="control-label">วันที่จอง</label>
                                    <input type="text" class="form-control"
                                           id="doc_date"
                                           name="doc_date"
                                           required
                                           readonly
                                           value="<?php echo $curr_date ?>"
                                           placeholder="วันที่เอกสาร">
                                </div>
                                <div class="col-md-3 d-flex">
                                    <button type="submit" class="btn btn-primary align-self-end" id="btnSave"
                                            style="height: 38px;">บันทึกข้อมูล
                                    </button>
                                </div>
                            </div>


                            <?php
                            $categories = [
                                'G1' => 'ยางรถกระบะเล็กและรถตู้',
                                'G2' => 'ยางรถเก๋งเล็กและรถเก๋งกลาง',
                                'G3' => 'ยางรถซิ่ง',
                                'G4' => 'ยางรถกระบะสูงและยางรถ SUV',
                                'G5' => 'ยาง ATLAS'
                            ];
                            ?>

                            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                                <?php foreach ($categories as $code => $name): ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= $code === 'G1' ? 'active' : '' ?>"
                                                id="tab<?= substr($code, 1) ?>-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#tab<?= substr($code, 1) ?>"
                                                type="button"
                                                role="tab"
                                                aria-controls="tab<?= substr($code, 1) ?>"
                                                aria-selected="<?= $code === 'G1' ? 'true' : 'false' ?>">
                                            <?= htmlspecialchars($name) ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="tab-content mt-3" id="productTabContent">
                                <?php
                                $i = 1;
                                foreach ($categories as $catCode => $catName):
                                    $active = ($catCode === 'G1') ? 'show active' : '';
                                    ?>

                                    <div class="tab-pane fade <?= $active ?>" id="tab<?= $i ?>" role="tabpanel"
                                         aria-labelledby="tab<?= $i ?>-tab">
                                        <div class="table-wrapper">

                                            <!-- fake scroll bar สำหรับแท็บนี้ -->
                                            <!--div id="fake-scroll<?= $i ?>" class="fake-scroll" style="overflow-x: auto; width: 100%; border: 1px solid #ccc;">
                                                <div style="width: 1200px; height: 1px;"></div>
                                            </div-->

                                            <table class="table table-bordered" id="table<?= $i ?>">
                                                <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>ยี่ห้อ</th>
                                                    <th>300 เส้น</th>
                                                    <th>120 เส้น</th>
                                                    <th>60 เส้น</th>
                                                    <th>คะแนนต่อเส้น</th>
                                                    <th>บัตรของขวัญต่อเส้น</th>
                                                    <th>ยอดซื้อปี <?php echo $curr_year1?></th>
                                                    <th>ยอดซื้อปี <?php echo $curr_year2?></th>
                                                    <th>ยอดซื้อปี <?php echo $curr_year3?></th>
                                                    <th>ยอดซื้อปี <?php echo $curr_year4?></th>
                                                    <th>ยอดซื้อปี <?php echo $curr_year5?></th>
                                                    <th>ยอดซื้อปี <?php echo $curr_year?></th>
                                                    <th>มิถุนายน</th>
                                                    <th>กรกฎาคม</th>
                                                    <th>สิงหาคม</th>
                                                    <th>รวมยาง(เส้น)</th>
                                                    <th>รวมบัตรของขวัญ</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                //$sql = "SELECT * FROM ims_product_reserve_target_point WHERE SKU_CAT_CODE = :catCode ORDER BY id DESC";

                                                $sql = "SELECT 
    p.SKU_CODE,
    p.SKU_NAME,
    p.BRAND,
    p.SKU_CAT_CODE,
    p.SKU_CAT_NAME,    
    p.point,
    p.price_1,
    p.price_2,
    p.price_3,
    p.sale_price_1,
    p.sale_price_2,
    p.sale_price_3,    
    p.gift_voucher,
    IFNULL(d.AR_CODE, '') AS AR_CODE,
    IFNULL(d.AR_NAME, '') AS AR_NAME,    
    COALESCE(SUM(CASE WHEN d.DI_YEAR = $curr_year1 THEN d.TRD_QTY ELSE 0 END), 0) AS `year1`,
    COALESCE(SUM(CASE WHEN d.DI_YEAR = $curr_year2 THEN d.TRD_QTY ELSE 0 END), 0) AS `year2`,
    COALESCE(SUM(CASE WHEN d.DI_YEAR = $curr_year3 THEN d.TRD_QTY ELSE 0 END), 0) AS `year3`,
    COALESCE(SUM(CASE WHEN d.DI_YEAR = $curr_year4 THEN d.TRD_QTY ELSE 0 END), 0) AS `year4`,
    COALESCE(SUM(CASE WHEN d.DI_YEAR = $curr_year5 THEN d.TRD_QTY ELSE 0 END), 0) AS `year5`,
    COALESCE(SUM(CASE WHEN d.DI_YEAR = $curr_year  THEN d.TRD_QTY ELSE 0 END), 0) AS `current_year`
FROM ims_product_reserve_target_point p
LEFT JOIN ims_data_sale_sac_all d 
    ON p.SKU_CODE = d.SKU_CODE 
    AND d.DI_YEAR BETWEEN $curr_year1 AND $curr_year 
    AND d.AR_CODE = '" . $customer_code . "'
WHERE p.SKU_CAT_CODE = :catCode
GROUP BY 
    p.SKU_CODE, 
    p.SKU_NAME, 
    p.BRAND,
    p.SKU_CAT_CODE,
    p.SKU_CAT_NAME,
    p.point,
    p.price_1,
    p.price_2,
    p.price_3,
    p.sale_price_1,
    p.sale_price_2,
    p.sale_price_3,
    p.gift_voucher,
    d.AR_CODE,
    d.AR_NAME
ORDER BY p.SKU_CAT_CODE,p.order_group";
                                                // ORDER BY p.SKU_NAME,p.SKU_CODE";

                                                $stmt = $conn->prepare($sql);
                                                $stmt->execute(['catCode' => $catCode]);
                                                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                if ($rows) {
                                                    foreach ($rows as $row) {
                                                        $sku = htmlspecialchars($row['SKU_CODE']);
                                                        echo "<tr>
                                                        <td style='min-width: 200px; white-space: nowrap;'>" . htmlspecialchars($row['SKU_CODE']) . "</td>
                                                        <td style='min-width: 200px; white-space: nowrap;'>" . htmlspecialchars($row['SKU_NAME']) . "</td>
                                                        <td>" . htmlspecialchars($row['BRAND']) . "</td>
                                                        <td>" . number_format($row['price_1'], 0) . "</td>
                                                        <td>" . number_format($row['price_2'], 0) . "</td>
                                                        <td>" . number_format($row['price_3'], 0) . "</td>
                                                        <td>" . htmlspecialchars($row['point']) . "</td>
                                                        <td>" . htmlspecialchars($row['gift_voucher']) . "</td>
                                                        <td>" . htmlspecialchars($row['year1'] ?: '0') . "</td>
                                                        <td>" . htmlspecialchars($row['year2'] ?: '0') . "</td>
                                                        <td>" . htmlspecialchars($row['year3'] ?: '0') . "</td>
                                                        <td>" . htmlspecialchars($row['year4'] ?: '0') . "</td>
                                                        <td>" . htmlspecialchars($row['year5'] ?: '0') . "</td>
                                                        <td>" . htmlspecialchars($row['current_year'] ?: '0') . "</td>
                                                        <td><input type='number' class='form-control input-3-digit' name='quantity_1[{$sku}]' min='0' value='0'></td>
                                                        <td><input type='number' class='form-control input-3-digit' name='quantity_2[{$sku}]' min='0' value='0'></td>
                                                        <td><input type='number' class='form-control input-3-digit' name='quantity_3[{$sku}]' min='0' value='0'></td>
                                                        <td><input type='number' class='form-control input-3-digit' name='total_quantity[{$sku}]' min='0' value='0' readonly='true'></td>                                                        
                                                        <td><input type=\"number\" class=\"form-control text-end\" name=\"total_gift_voucher[{$sku}]\" min=\"0\" step=\"1\" value=\"0\" readonly style=\"width: 120px;\"></td>
                                                        <td style='min-width: 200px; white-space: nowrap;'>" . htmlspecialchars($row['SKU_CODE']) . "</td>
                                                        <td style='min-width: 200px; white-space: nowrap;'>" . htmlspecialchars($row['SKU_NAME']) . "</td>
                                                    </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='11'>ไม่มีข้อมูล</td></tr>";
                                                }
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                            </div>
                        </form>

                        <div id="alertMessage" class="mt-3"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include('includes/Modal-Logout.php'); ?>
<?php include('includes/Footer.php'); ?>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/v11/dataTables.buttons.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<!-- Bootstrap Select -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<!-- CSS -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<!-- Theme (optional) -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>

<!-- JS -->
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<!-- อย่าลืมใส่ CSS และ JS ที่จำเป็นก่อน -->
<!-- FixedColumns CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
<!-- FixedColumns JS -->
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
<script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
<!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
<link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>


<style>
    .table-wrapper {
        overflow-x: auto;
        width: 100%;
    }
</style>

<style>
    .input-3-digit {
        min-width: 80px;
        text-align: right; /* เพื่อให้ตัวเลขชิดขวา */
    }
</style>


<!--script>
    $(document).ready(function () {
        // 1. DataTable config
        $('table.table').DataTable({
            searching: false, // ปิดระบบค้นหา
            ordering: false, // ปิดการเรียงลำดับทั้งหมด
            lengthMenu: [[500], [500]],
            language: {
                search: 'ค้นหา',
                lengthMenu: ' ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด *MAX* รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป',
                    last: 'สุดท้าย'
                }
            },
            columnDefs: [{
                targets: [8, 9, 10],
                orderable: false
            }]
        });

        $('.selectpicker').selectpicker();

        // 2. เก็บค่า input ของแต่ละ tab ก่อนเปลี่ยน tab
        let tabData = {};

        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            const oldTabId = $(e.relatedTarget).attr('href'); // tab เก่า เช่น #tab1
            const newTabId = $(e.target).attr('href');       // tab ใหม่ เช่น #tab2

            if (oldTabId) {
                tabData[oldTabId] = {};
                $(`${oldTabId} table tbody tr`).each(function (index) {
                    tabData[oldTabId][index] = {};
                    $(this).find('input').each(function () {
                        tabData[oldTabId][index][$(this).attr('name')] = $(this).val();
                    });
                });
            }

            // ใส่ข้อมูลกลับใน tab ใหม่ (ถ้ามี)
            if (tabData[newTabId]) {
                $(`${newTabId} table tbody tr`).each(function (index) {
                    if (tabData[newTabId][index]) {
                        $(this).find('input').each(function () {
                            const name = $(this).attr('name');
                            if (tabData[newTabId][index][name] !== undefined) {
                                $(this).val(tabData[newTabId][index][name]);
                            }
                        });
                    }
                });
            }
        });

        // 3. คำนวณจำนวนรวมและบัตรของขวัญ
        $(document).on('input', 'input[name^="quantity_1"], input[name^="quantity_2"], input[name^="quantity_3"]', function () {
            let $row = $(this).closest('tr');
            let quantity1 = parseInt($row.find('input[name^="quantity_1"]').val()) || 0;
            let quantity2 = parseInt($row.find('input[name^="quantity_2"]').val()) || 0;
            let quantity3 = parseInt($row.find('input[name^="quantity_3"]').val()) || 0;

            let totalQuantity = quantity1 + quantity2 + quantity3;
            $row.find('input[name^="total_quantity"]').val(totalQuantity);

            let giftVoucherPerUnit = parseFloat($row.find('td:eq(7)').text()) || 0;
            let totalGiftVoucher = totalQuantity * giftVoucherPerUnit;
            $row.find('input[name^="total_gift_voucher"]').val(totalGiftVoucher.toFixed(2));
        });

        // 4. เปลี่ยนค่าชื่อลูกค้าเมื่อตัวเลือกเปลี่ยน
        const selectEl = document.getElementById('customerName');
        const codeInput = document.getElementById('customerCode');
        const nameInput = document.getElementById('customerNameText');

        if (selectEl) {
            selectEl.addEventListener('change', function () {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const arCode = selectedOption.value;
                const arName = selectedOption.getAttribute('data-name');

                codeInput.value = arCode;
                nameInput.value = arName;
            });
        }

        // 5. กด submit เก็บข้อมูลจากทุก tab และส่ง ajax
        $('#targetPointForm').on('submit', function (e) {
            e.preventDefault();

            let customerData = {
                DOC_DATE: $('#doc_date').val(),
                CUSTOMER_CODE: $('#customerCode').val(),
                CUSTOMER_NAME: $('#customerNameText').val(),
                PHONE: $('#phone').val(),
                PROVINCE: $('#province').val(),
                SALE_NAME: $('#saleName').val(),
                TAKE_NAME: $('#take_name').val()
            };

            let items = [];

            $('#productTabContent .tab-pane').each(function () {
                const catCode = Object.keys(<?php echo json_encode($categories); ?>)[$(this).index()];
                // const catName = $(this).find('button.nav-link.active').text().trim(); // ไม่ได้ใช้จริง

                $(this).find('table tbody tr').each(function () {
                    let sku = $(this).find('td:eq(0)').text().trim();
                    let skuName = $(this).find('td:eq(1)').text().trim();
                    let brand = $(this).find('td:eq(2)').text().trim();
                    let price_1 = parseFloat($(this).find('td:eq(3)').text().trim()) || 0;
                    let price_2 = parseFloat($(this).find('td:eq(4)').text().trim()) || 0;
                    let price_3 = parseFloat($(this).find('td:eq(5)').text().trim()) || 0;
                    let quantity_1 = parseInt($(this).find('input[name^="quantity_1"]').val()) || 0;
                    let quantity_2 = parseInt($(this).find('input[name^="quantity_2"]').val()) || 0;
                    let quantity_3 = parseInt($(this).find('input[name^="quantity_3"]').val()) || 0;
                    let total_quantity = quantity_1 + quantity_2 + quantity_3;
                    let total_gift_voucher = parseInt($(this).find('input[name^="total_gift_voucher"]').val()) || 0;

                    if (quantity_1 > 0 || quantity_2 > 0 || quantity_3 > 0) {
                        items.push({
                            DOC_DATE: customerData.DOC_DATE,
                            CUSTOMER_CODE: customerData.CUSTOMER_CODE,
                            CUSTOMER_NAME: customerData.CUSTOMER_NAME,
                            PHONE: customerData.PHONE,
                            PROVINCE: customerData.PROVINCE,
                            SALE_NAME: customerData.SALE_NAME,
                            TAKE_NAME: customerData.TAKE_NAME,
                            SKU_CODE: sku,
                            SKU_NAME: skuName,
                            BRAND: brand,
                            price_1: price_1,
                            price_2: price_2,
                            price_3: price_3,
                            quantity_1: quantity_1,
                            quantity_2: quantity_2,
                            quantity_3: quantity_3,
                            total_quantity: total_quantity,
                            total_gift_voucher: total_gift_voucher,
                            SKU_CAT_CODE: catCode,
                            SKU_CAT_NAME: <?php echo json_encode($categories); ?>[catCode]
                        });
                    }
                });
            });

            if (items.length === 0) {
                alertify.error('กรุณากรอกจำนวนสินค้าขั้นต่ำ 1 รายการ');
                return;
            }

            $.ajax({
                url: 'model/manage_reserve_product_event_process.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(items),
                dataType: 'json',
                beforeSend: function () {
                    $('#btnSave').prop('disabled', true).text('กำลังบันทึก...');
                },
                success: function (res) {
                    $('#btnSave').prop('disabled', false).text('บันทึกข้อมูล');
                    if (res.status === 'success') {
                        $('#alertMessage').html('<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>');
                        $('#targetPointForm')[0].reset();
                        $('table.table').DataTable().clear().draw();
                        alertify.success('บันทึกข้อมูลสำเร็จ');
                        setTimeout(() => {
                            window.location.href = window.location.pathname;
                            window.close();
                        }, 1000);
                    } else {
                        $('#alertMessage').html('<div class="alert alert-danger">' + (res.message || 'เกิดข้อผิดพลาด') + '</div>');
                    }
                },
                error: function () {
                    $('#btnSave').prop('disabled', false).text('บันทึกข้อมูล');
                    $('#alertMessage').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อข้อมูล</div>');
                }
            });
        });
    });
</script-->

<script>
    $(document).ready(function () {
        // 1. DataTable config
        $('table.table').DataTable({
            searching: false,
            ordering: false,
            lengthMenu: [[500], [500]],
            language: {
                search: 'ค้นหา',
                lengthMenu: ' ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด *MAX* รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป',
                    last: 'สุดท้าย'
                }
            },
            columnDefs: [{
                targets: [8, 9, 10],
                orderable: false
            }]
        });

        $('.selectpicker').selectpicker();

        // 2. เก็บค่า input ของแต่ละ tab ก่อนเปลี่ยน tab
        let tabData = {};

        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            const oldTabId = $(e.relatedTarget).attr('href');
            const newTabId = $(e.target).attr('href');

            if (oldTabId) {
                tabData[oldTabId] = {};
                $(`${oldTabId} table tbody tr`).each(function (index) {
                    tabData[oldTabId][index] = {};
                    $(this).find('input').each(function () {
                        tabData[oldTabId][index][$(this).attr('name')] = $(this).val();
                    });
                });
            }

            if (tabData[newTabId]) {
                $(`${newTabId} table tbody tr`).each(function (index) {
                    if (tabData[newTabId][index]) {
                        $(this).find('input').each(function () {
                            const name = $(this).attr('name');
                            if (tabData[newTabId][index][name] !== undefined) {
                                $(this).val(tabData[newTabId][index][name]);
                            }
                        });
                    }
                });
            }
        });

        // 3. คำนวณจำนวนรวมและบัตรของขวัญ
        $(document).on('input', 'input[name^="quantity_1"], input[name^="quantity_2"], input[name^="quantity_3"]', function () {
            let $row = $(this).closest('tr');
            let quantity1 = parseInt($row.find('input[name^="quantity_1"]').val()) || 0;
            let quantity2 = parseInt($row.find('input[name^="quantity_2"]').val()) || 0;
            let quantity3 = parseInt($row.find('input[name^="quantity_3"]').val()) || 0;

            let totalQuantity = quantity1 + quantity2 + quantity3;
            $row.find('input[name^="total_quantity"]').val(totalQuantity);

            let giftVoucherPerUnit = parseFloat($row.find('td:eq(7)').text()) || 0;
            let totalGiftVoucher = totalQuantity * giftVoucherPerUnit;
            $row.find('input[name^="total_gift_voucher"]').val(totalGiftVoucher.toFixed(2));
        });

        // 4. เปลี่ยนค่าชื่อลูกค้าเมื่อตัวเลือกเปลี่ยน
        const selectEl = document.getElementById('customerName');
        const codeInput = document.getElementById('customerCode');
        const nameInput = document.getElementById('customerNameText');

        if (selectEl) {
            selectEl.addEventListener('change', function () {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const arCode = selectedOption.value;
                const arName = selectedOption.getAttribute('data-name');

                codeInput.value = arCode;
                nameInput.value = arName;
            });
        }

        // 5. ป้องกันการกด Enter แล้ว submit ฟอร์มโดยไม่ตั้งใจ

        $('#targetPointForm input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // ป้องกัน submit form

                // หา input ถัดไป แล้ว focus
                let inputs = $('#targetPointForm').find('input:visible:not([readonly]):not([disabled])');
                let idx = inputs.index(this);
                if (idx > -1 && idx + 1 < inputs.length) {
                    inputs.eq(idx + 1).focus();
                }
            }
        });


        // 6. กด submit เก็บข้อมูลจากทุก tab และส่ง ajax
        $('#targetPointForm').on('submit', function (e) {
            e.preventDefault();

            let customerData = {
                DOC_DATE: $('#doc_date').val(),
                CUSTOMER_CODE: $('#customerCode').val(),
                CUSTOMER_NAME: $('#customerNameText').val(),
                PHONE: $('#phone').val(),
                PROVINCE: $('#province').val(),
                SALE_NAME: $('#saleName').val(),
                TAKE_NAME: $('#take_name').val()
            };

            let items = [];

            $('#productTabContent .tab-pane').each(function () {
                const catCode = Object.keys(<?php echo json_encode($categories); ?>)[$(this).index()];

                $(this).find('table tbody tr').each(function () {
                    let sku = $(this).find('td:eq(0)').text().trim();
                    let skuName = $(this).find('td:eq(1)').text().trim();
                    let brand = $(this).find('td:eq(2)').text().trim();
                    let price_1 = parseFloat($(this).find('td:eq(3)').text().trim()) || 0;
                    let price_2 = parseFloat($(this).find('td:eq(4)').text().trim()) || 0;
                    let price_3 = parseFloat($(this).find('td:eq(5)').text().trim()) || 0;
                    let quantity_1 = parseInt($(this).find('input[name^="quantity_1"]').val()) || 0;
                    let quantity_2 = parseInt($(this).find('input[name^="quantity_2"]').val()) || 0;
                    let quantity_3 = parseInt($(this).find('input[name^="quantity_3"]').val()) || 0;
                    let total_quantity = quantity_1 + quantity_2 + quantity_3;
                    let total_gift_voucher = parseInt($(this).find('input[name^="total_gift_voucher"]').val()) || 0;

                    if (quantity_1 > 0 || quantity_2 > 0 || quantity_3 > 0) {
                        items.push({
                            DOC_DATE: customerData.DOC_DATE,
                            CUSTOMER_CODE: customerData.CUSTOMER_CODE,
                            CUSTOMER_NAME: customerData.CUSTOMER_NAME,
                            PHONE: customerData.PHONE,
                            PROVINCE: customerData.PROVINCE,
                            SALE_NAME: customerData.SALE_NAME,
                            TAKE_NAME: customerData.TAKE_NAME,
                            SKU_CODE: sku,
                            SKU_NAME: skuName,
                            BRAND: brand,
                            price_1: price_1,
                            price_2: price_2,
                            price_3: price_3,
                            quantity_1: quantity_1,
                            quantity_2: quantity_2,
                            quantity_3: quantity_3,
                            total_quantity: total_quantity,
                            total_gift_voucher: total_gift_voucher,
                            SKU_CAT_CODE: catCode,
                            SKU_CAT_NAME: <?php echo json_encode($categories); ?>[catCode]
                        });
                    }
                });
            });

            if (items.length === 0) {
                alertify.error('กรุณากรอกจำนวนสินค้าขั้นต่ำ 1 รายการ');
                return;
            }

            $.ajax({
                url: 'model/manage_reserve_product_event_process.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(items),
                dataType: 'json',
                beforeSend: function () {
                    $('#btnSave').prop('disabled', true).text('กำลังบันทึก...');
                },
                success: function (res) {
                    $('#btnSave').prop('disabled', false).text('บันทึกข้อมูล');
                    if (res.status === 'success') {
                        $('#alertMessage').html('<div class="alert alert-success">บันทึกข้อมูลสำเร็จ</div>');
                        $('#targetPointForm')[0].reset();
                        $('table.table').DataTable().clear().draw();
                        alertify.success('บันทึกข้อมูลสำเร็จ');
                        setTimeout(() => {
                            window.location.href = window.location.pathname;
                            window.close();
                        }, 1000);
                    } else {
                        $('#alertMessage').html('<div class="alert alert-danger">' + (res.message || 'เกิดข้อผิดพลาด') + '</div>');
                    }
                },
                error: function () {
                    $('#btnSave').prop('disabled', false).text('บันทึกข้อมูล');
                    $('#alertMessage').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อข้อมูล</div>');
                }
            });
        });
    });
</script>

</body>
</html>