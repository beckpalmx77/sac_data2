<?php
// ตรวจสอบและรวมไฟล์ Header, Session
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    // รวมไฟล์เชื่อมต่อฐานข้อมูล
    include("config/connect_db.php");

    // ***********************************************
    // กำหนดตัวแปรและ Logic การค้นหา Serial Number
    // ***********************************************
    $results = [];
    $search_value = '';
    $error_message = '';
    $table_name = "ims_data_sale_sac_all"; // ตารางที่ใช้ค้นหา
    $field_name = "serial_tires_number";   // ฟิลด์ที่ใช้ค้นหา

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_serial'])) {

        $search_value = trim($_POST['search_serial']);

        if (!empty($search_value)) {
            try {
                // คำสั่ง SQL ใช้ FIND_IN_SET เพื่อค้นหา Serial Number ในฟิลด์ที่คั่นด้วยคอมมา
                $sql = "
                    SELECT 
                        DI_REF, 
                        DI_DSATE, 
                        AR_NAME, 
                        SKU_CODE,
                        serial_tires_number 
                    FROM 
                        {$table_name} 
                    WHERE 
                        FIND_IN_SET(:search_value, {$field_name}) > 0
                ";

                $stmt = $conn->prepare($sql);
                // ผูกค่า (Bind Parameter) เพื่อป้องกัน SQL Injection
                $stmt->bindParam(':search_value', $search_value, PDO::PARAM_STR);
                $stmt->execute();

                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                $error_message = "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage();
            }
        } else {
            $error_message = "กรุณาป้อนค่า Serial Number ที่ต้องการค้นหา";
        }
    }

    // ปิดการเชื่อมต่อ (ถ้าใช้ PDO)
    $conn = null;
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

    <style>
        /* CSS เพิ่มเติมเพื่อให้ตาราง DataTables แสดงผลถูกต้อง */
        .table-responsive { overflow-x: auto; }
        .dataTables_wrapper .row { margin-bottom: 15px; }
    </style>

    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800">ค้นหา Serial Number</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">ค้นหา Serial Number</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ระบบค้นหาข้อมูลการขาย</h6>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form method="POST" action="">
                                                            <div class="row mb-3">
                                                                <div class="col-sm-4">
                                                                    <label for="search_serial">ป้อน Serial Tires Number ที่ต้องการค้นหา:</label>
                                                                    <input
                                                                        type="text"
                                                                        id="search_serial"
                                                                        name="search_serial"
                                                                        value="<?php echo htmlspecialchars($search_value); ?>"
                                                                        placeholder="เช่น ADC154222"
                                                                        required
                                                                        class="form-control"
                                                                    >
                                                                </div>
                                                                <div class="col-sm-4" style="padding-top: 25px;">
                                                                    <button type="submit" class="btn btn-primary">
                                                                        <i class="fa fa-search"></i> ค้นหา
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>

                                                        <?php if ($error_message): ?>
                                                            <p class="text-danger font-weight-bold"><?php echo htmlspecialchars($error_message); ?></p>
                                                        <?php endif; ?>

                                                        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($search_value)): ?>

                                                            <hr>
                                                            <h5 class="mt-4">ผลลัพธ์การค้นหาสำหรับ Serial: **<?php echo htmlspecialchars($search_value); ?>**</h5>

                                                            <?php if (count($results) > 0): ?>
                                                                <div class="table-responsive">
                                                                    <table id="resultsTable" class="display table table-bordered table-striped" style="width:100%">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>DI_REF</th>
                                                                            <th>DI_DSATE</th>
                                                                            <th>AR_NAME</th>
                                                                            <th>SKU_CODE</th>
                                                                            <th>Serial Numbers ทั้งหมด</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        <?php foreach ($results as $row): ?>
                                                                            <tr>
                                                                                <td><?php echo htmlspecialchars($row['DI_REF']); ?></td>
                                                                                <td><?php echo htmlspecialchars($row['DI_DSATE']); ?></td>
                                                                                <td><?php echo htmlspecialchars($row['AR_NAME']); ?></td>
                                                                                <td><?php echo htmlspecialchars($row['SKU_CODE']); ?></td>
                                                                                <td><?php echo htmlspecialchars($row['serial_tires_number']); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            <?php else: ?>
                                                                <p class="mt-3">ไม่พบข้อมูลที่ตรงกับ Serial Number ที่ค้นหาในระบบ</p>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
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

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialization DataTables
            if ($.fn.DataTable.isDataTable('#resultsTable')) {
                $('#resultsTable').DataTable().destroy();
            }

            // ตรวจสอบว่ามีตารางและผลลัพธ์ถึงจะ Initialize DataTables
            if ($('#resultsTable').length && $('#resultsTable tbody tr').length > 0) {
                $('#resultsTable').DataTable({
                    "language": {
                        "url": "https://cdn.datatables.net/plug-ins/2.0.8/i18n/th.json" // ใช้ภาษาไทย
                    },
                    "pageLength": 10,
                    "order": [[ 1, "desc" ]] // จัดเรียงตามวันที่ขาย
                });
            }
        });
    </script>

    </body>
    </html>

<?php } ?>