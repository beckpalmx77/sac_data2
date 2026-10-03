<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit();
} else {
    include('config/connect_db.php'); // Assume $pdo is PDO instance
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Product Target Point</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css" />
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css" />
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800">Product Target Point</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Product Target Point</li>
                        </ol>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <!-- ฟอร์มเพิ่มข้อมูล -->
                            <form class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="shopName" class="form-label">ชื่อร้านค้า</label>
                                        <input type="text" class="form-control" id="shopName" name="shopName">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="phoneNumber" class="form-label">หมายเลขโทรศัพท์</label>
                                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="province" class="form-label">จังหวัด</label>
                                        <input type="text" class="form-control" id="province" name="province">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="salesName" class="form-label">ชื่อเซลล์</label>
                                        <input type="text" class="form-control" id="salesName" name="salesName">
                                    </div>
                                </div>
                            </form>

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
                                foreach ($categories as $catCode => $catName) {
                                    $active = ($catCode === 'G1') ? 'show active' : '';
                                    echo "<div class='tab-pane fade $active' id='tab$i' role='tabpanel' aria-labelledby='tab$i-tab'>";
                                    echo "<table class='table table-bordered'>
                                            <thead>
                                                <tr>
                                                    <th>SKU_CODE</th>
                                                    <th>SKU_NAME</th>
                                                    <th>BRAND</th>
                                                    <th>Price 1</th>
                                                    <th>Price 2</th>
                                                    <th>Price 3</th>
                                                    <th>Point</th>
                                                    <th>Gift Voucher</th>
                                                    <th>มิถุนายน</th>
                                                    <th>กรกฎาคม</th>
                                                    <th>สิงหาคม</th>   
                                                </tr>
                                            </thead>
                                            <tbody>";

                                    $sql = "SELECT * FROM ims_product_reserve_target_point WHERE SKU_CAT_CODE = :catCode ORDER BY id DESC";
                                    $stmt = $conn->prepare($sql);
                                    if ($stmt) {
                                        $stmt->execute(['catCode' => $catCode]);
                                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        if ($rows) {
                                            foreach ($rows as $row) {
                                                $sku = htmlspecialchars($row['SKU_CODE']);
                                                echo "<tr>
                                                    <td>{$sku}</td>
                                                    <td>" . htmlspecialchars($row['SKU_NAME']) . "</td>
                                                    <td>" . htmlspecialchars($row['BRAND']) . "</td>
                                                    <td>" . htmlspecialchars($row['price_1']) . "</td>
                                                    <td>" . htmlspecialchars($row['price_2']) . "</td>
                                                    <td>" . htmlspecialchars($row['price_3']) . "</td>
                                                    <td>" . htmlspecialchars($row['point']) . "</td>
                                                    <td>" . htmlspecialchars($row['gift_voucher']) . "</td>
                                                    <td><input type='number' class='form-control' name='quantity_1[{$sku}]' value='' min='0'></td>
                                                    <td><input type='number' class='form-control' name='quantity_2[{$sku}]' value='' min='0'></td>
                                                    <td><input type='number' class='form-control' name='quantity_3[{$sku}]' value='' min='0'></td>
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='11'>No data found</td></tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='11'>Prepare statement failed.</td></tr>";
                                    }

                                    echo "</tbody></table></div>";
                                    $i++;
                                }
                                ?>
                            </div>
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
                // หากใช้ server-side ให้เปิด options ด้านล่าง
                // processing: true,
                // serverSide: true,
                // serverMethod: 'post',
                // ajax: 'your_server_endpoint.php'
            });
        });
    </script>


    </body>
    </html>
<?php } ?>
