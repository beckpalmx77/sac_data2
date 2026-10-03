<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">

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

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800">รายชื่อผู้ใช้งานทีมขาย (Sale User / Sale Sup)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">ระบบผู้ใช้งาน</li>
                            <li class="breadcrumb-item active" aria-current="page">ผู้ใช้งานทีมขาย</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <form id="from_data" method="post" action="export_process/export_sale_user.php">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-auto">
                                                    <button type='submit' name='btnExport' id='btnExport'
                                                            class='btn btn-success btn-xs'>Export Excel
                                                        <i class="fa fa-external-link-square"></i>
                                                    </button>
                                                </div>
                                                <div class="col-auto d-flex align-items-center">
                                                    <label for="account_type_filter" class="mr-2 mb-0 font-weight-bold" style="white-space: nowrap;">ตัวกรองประเภทบัญชี:</label>
                                                    <select name="account_type_filter" id="account_type_filter" class="form-control form-control-sm" style="width: 180px;">
                                                        <option value="all">ทั้งหมด (All)</option>
                                                        <option value="sale_user">sale_user</option>
                                                        <option value="sale_sup">sale_sup</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>ลำดับ</th>
                                                    <th>User ID</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Account Type</th>
                                                    <th>Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $sql = "SELECT u.*, p.permission_detail 
                                                        FROM ims_user u
                                                        LEFT JOIN ims_permission p ON u.account_type = p.permission_id
                                                        WHERE (u.account_type = 'sale_user' OR u.account_type = 'sale_sup') 
                                                          AND u.status = 'Active' 
                                                        ORDER BY u.id ASC";
                                                $query = $conn->prepare($sql);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                $line_no = 0;
                                                if (count($results) > 0) {
                                                    foreach ($results as $result) {
                                                        $line_no++;
                                                        $acc_type_label = !empty($result->permission_detail) ? $result->permission_detail . " (" . $result->account_type . ")" : $result->account_type;
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlentities($line_no); ?></td>
                                                            <td><?php echo htmlentities($result->user_id); ?></td>
                                                            <td><?php echo htmlentities($result->first_name); ?></td>
                                                            <td><?php echo htmlentities($result->last_name); ?></td>
                                                            <td><?php echo htmlentities($acc_type_label); ?></td>
                                                            <td><div class="text-success"><?php echo htmlentities($result->status); ?></div></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <th>ลำดับ</th>
                                                    <th>User ID</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Account Type</th>
                                                    <th>Status</th>
                                                </tr>
                                                </tfoot>
                                            </table>
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

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script>
        $(document).ready(function () {
            let table = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                }
            });

            // Filter DataTable by account_type
            $('#account_type_filter').on('change', function () {
                let selectedVal = $(this).val();
                if (selectedVal === 'all') {
                    table.column(4).search('').draw();
                } else {
                    table.column(4).search(selectedVal).draw();
                }
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
