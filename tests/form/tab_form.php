<?php
include('../../includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include('../../config/connect_db.php');
    ?>
    <!DOCTYPE html>
    <html lang="th">
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
                            <!-- 🔽 ฟอร์มเพิ่มข้อมูล -->
                            <form class="mb-4">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="shopName">ชื่อร้านค้า</label>
                                        <input type="text" class="form-control" id="shopName" name="shopName" placeholder="ชื่อร้านค้า">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="phoneNumber">หมายเลขโทรศัพท์</label>
                                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="หมายเลขโทรศัพท์">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="province">จังหวัด</label>
                                        <input type="text" class="form-control" id="province" name="province" placeholder="จังหวัด">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="salesName">ชื่อเซลล์</label>
                                        <input type="text" class="form-control" id="salesName" name="salesName" placeholder="ชื่อเซลล์">
                                    </div>
                                </div>
                            </form>
                            <!-- 🔼 จบฟอร์ม -->

                            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab">CAT001</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab">CAT002</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab">CAT003</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab4-tab" data-toggle="tab" href="#tab4" role="tab">CAT004</a></li>
                                <li class="nav-item"><a class="nav-link" id="tab5-tab" data-toggle="tab" href="#tab5" role="tab">CAT005</a></li>
                            </ul>
                            <div class="tab-content mt-3" id="productTabContent">
                                <?php
                                $categories = ['CAT001', 'CAT002', 'CAT003', 'CAT004', 'CAT005'];
                                foreach ($categories as $index => $cat) {
                                    $active = $index === 0 ? 'show active' : '';
                                    echo "<div class='tab-pane fade $active' id='tab" . ($index + 1) . "' role='tabpanel'>";
                                    echo "<table class='table table-bordered'>
                                        <thead><tr>
                                            <th>SKU_CODE</th>
                                            <th>SKU_NAME</th>
                                            <th>BRAND</th>
                                            <th>Price 1</th>
                                            <th>Price 2</th>
                                            <th>Price 3</th>
                                            <th>Point</th>
                                            <th>Gift Voucher</th>
                                            <th>Remark</th>
                                        </tr></thead>
                                        <tbody>";
                                    $sql = "SELECT * FROM ims_product_reserve_target_point WHERE SKU_CAT_CODE = '$cat' ORDER BY id DESC";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                <td>{$row['SKU_CODE']}</td>
                                                <td>{$row['SKU_NAME']}</td>
                                                <td>{$row['BRAND']}</td>
                                                <td>{$row['price_1']}</td>
                                                <td>{$row['price_2']}</td>
                                                <td>{$row['price_3']}</td>
                                                <td>{$row['point']}</td>
                                                <td>{$row['gift_voucher']}</td>
                                                <td>{$row['remark']}</td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9'>No data found</td></tr>";
                                    }
                                    echo "</tbody></table></div>";
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
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    </body>
    </html>
<?php } ?>
