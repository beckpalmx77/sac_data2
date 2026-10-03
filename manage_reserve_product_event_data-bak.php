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

$customer_code = "SAC.0000272";
$customer_name = "คุณเกรียงไกร พุ่มกุมาร (เกรียงไกรการยาง)";

$sql_get = "
    SELECT SALE_NAME
    FROM ims_data_sale_sac_all 
    WHERE AR_CODE = :AR_CODE 
    LIMIT 1";

$stmt = $conn->prepare($sql_get);
$stmt->execute(['AR_CODE' => $customer_code]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
$SALE_NAME = $row ? $row['SALE_NAME'] : null;

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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>


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
                                    <label for="customerCode" class="form-label">ร้านค้า</label>
                                    <input type="hidden" name="customerCode" id="customerCode" value="<?php echo htmlspecialchars($customer_code); ?>" readonly>
                                    <input type="text" class="form-control" id="customerNameText" name="customerNameText" value="<?php echo htmlspecialchars($customer_name); ?>" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label for="phone" class="form-label">หมายเลขโทรศัพท์</label>
                                    <input type="text" class="form-control" id="phone" name="phone" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="province" class="form-label">เลือกจังหวัด</label>
                                    <select id="province" name="province" class="form-control selectpicker" data-live-search="true" title="กรุณาเลือกจังหวัด" required>
                                        <option value="" selected disabled>-- เลือกจังหวัด --</option>
                                        <?php
                                        $sql1 = "SELECT * FROM ims_provinces ORDER BY province_name ASC";
                                        $query1 = $conn->prepare($sql1);
                                        $query1->execute();
                                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                        foreach ($results1 as $result1) {
                                            echo '<option value="' . htmlspecialchars($result1->province_name) . '">' . htmlspecialchars($result1->province_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="saleName" class="form-label">ชื่อเซลล์</label>
                                    <select id="saleName" name="saleName" class="form-control selectpicker" data-live-search="true" title="กรุณาเลือกเซลล์" required>
                                        <option value="" disabled <?= empty($SALE_NAME) ? 'selected' : '' ?>>-- ชื่อเซลล์ --</option>
                                        <?php
                                        $sql2 = "SELECT * FROM ims_sale_reserve ORDER BY id";
                                        $query2 = $conn->prepare($sql2);
                                        $query2->execute();
                                        $results2 = $query2->fetchAll(PDO::FETCH_OBJ);
                                        foreach ($results2 as $result2) {
                                            $selected = ($result2->sale_name === $SALE_NAME) ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($result2->sale_name) . '" ' . $selected . '>' . htmlspecialchars($result2->sale_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-end">
                                <div class="col-md-2">
                                    <label for="doc_date" class="control-label">วันที่จอง</label>
                                    <input type="text" class="form-control" id="doc_date" name="doc_date" required readonly value="<?php echo htmlspecialchars($curr_date); ?>" placeholder="วันที่เอกสาร">
                                </div>
                                <div class="col-md-3 d-flex">
                                    <button type="submit" class="btn btn-primary align-self-end" id="btnSave" style="height: 38px;">บันทึกข้อมูล</button>
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

                                    <div class="tab-pane fade <?= $active ?>" id="tab<?= $i ?>" role="tabpanel" aria-labelledby="tab<?= $i ?>-tab">
                                        <div class="table-wrapper">
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
                                                    <th>ยอดซื้อปี <?= $curr_year1 ?></th>
                                                    <th>ยอดซื้อปี <?= $curr_year2 ?></th>
                                                    <th>ยอดซื้อปี <?= $curr_year3 ?></th>
                                                    <th>ยอดซื้อปี <?= $curr_year4 ?></th>
                                                    <th>ยอดซื้อปี <?= $curr_year5 ?></th>
                                                    <th>ยอดซื้อปี <?= $curr_year ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $sqlProd = "SELECT * FROM ims_product_target_point WHERE PRODUCT_CATEGORY = :cat AND PRODUCT_STATUS = 'Y' ORDER BY id";
                                                $stmtProd = $conn->prepare($sqlProd);
                                                $stmtProd->execute(['cat' => $catCode]);
                                                $rowsProd = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($rowsProd as $row):
                                                    $prodCode = $row['SKU_CODE'];
                                                    $prodName = $row['SKU_NAME'];
                                                    $brand = $row['BRAND_NAME'];
                                                    $point = $row['POINT'];
                                                    $giftVoucher = $row['GIFT_VOUCHER'];

                                                    $purchase1 = $row["PURCHASE_{$curr_year1}"] ?? 0;
                                                    $purchase2 = $row["PURCHASE_{$curr_year2}"] ?? 0;
                                                    $purchase3 = $row["PURCHASE_{$curr_year3}"] ?? 0;
                                                    $purchase4 = $row["PURCHASE_{$curr_year4}"] ?? 0;
                                                    $purchase5 = $row["PURCHASE_{$curr_year5}"] ?? 0;
                                                    $purchaseCurr = $row["PURCHASE_{$curr_year}"] ?? 0;
                                                    ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($prodCode) ?></td>
                                                        <td><?= htmlspecialchars($prodName) ?></td>
                                                        <td><?= htmlspecialchars($brand) ?></td>
                                                        <td><input type="number" min="0" step="1" class="form-control input-3-digit" name="<?= $catCode ?>_300[]" value="0"></td>
                                                        <td><input type="number" min="0" step="1" class="form-control input-3-digit" name="<?= $catCode ?>_120[]" value="0"></td>
                                                        <td><input type="number" min="0" step="1" class="form-control input-3-digit" name="<?= $catCode ?>_60[]" value="0"></td>
                                                        <td><?= htmlspecialchars($point) ?></td>
                                                        <td><?= htmlspecialchars($giftVoucher) ?></td>
                                                        <td><?= htmlspecialchars($purchase1) ?></td>
                                                        <td><?= htmlspecialchars($purchase2) ?></td>
                                                        <td><?= htmlspecialchars($purchase3) ?></td>
                                                        <td><?= htmlspecialchars($purchase4) ?></td>
                                                        <td><?= htmlspecialchars($purchase5) ?></td>
                                                        <td><?= htmlspecialchars($purchaseCurr) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
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

<script>
    $(document).ready(function () {
        $('table.table').DataTable({
            lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
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
                    last: 'สุดท้าย'
                }
            },
            columnDefs: [{
                targets: [8, 9, 10],
                orderable: false
            }]
        });

        $('.selectpicker').selectpicker();
    });
</script>

</body>
</html>
