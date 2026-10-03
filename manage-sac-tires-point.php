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
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page']?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <!-- Column for Table Master CRUD -->
                        <div class="col-lg-12 mb-4">
                            <div class="card">
                                <div class="card-header py-3 bg-primary text-white d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-tasks me-1"></i> จัดการฐานข้อมูลคะแนนสะสมยาง (Tires Point Master)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 d-flex flex-wrap">
                                        <button type='button' name='btnAdd' id='btnAdd' class='btn btn-primary mr-2 me-2 mb-2'>
                                            <i class="fa fa-plus me-1"></i> Add Record
                                        </button>
                                        <a href="manual_manage_tires_point.html" target="_blank" class="btn btn-outline-danger font-weight-bold mb-2">
                                            <i class="fas fa-info-circle mr-1 me-1"></i> คู่มือการใช้งานระบบ
                                        </a>
                                    </div>

                                    <div class="table-responsive">
                                        <table id='TableRecordList' class='table table-bordered table-striped table-hover w-100'>
                                            <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ขนาดยาง/ดอกยาง</th>
                                                    <th>ยี่ห้อ</th>
                                                    <th>กลุ่มสินค้า</th>
                                                    <th>ขอบ</th>
                                                    <th>คะแนนร้านทั่วไป</th>
                                                    <th>คะแนน Shop</th>
                                                    <th>Edit</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ขนาดยาง/ดอกยาง</th>
                                                    <th>ยี่ห้อ</th>
                                                    <th>กลุ่มสินค้า</th>
                                                    <th>ขอบ</th>
                                                    <th>คะแนนร้านทั่วไป</th>
                                                    <th>คะแนน Shop</th>
                                                    <th>Edit</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div id="result"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for CRUD Form -->
                    <div class="modal fade" id="recordModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h4 class="modal-title font-weight-bold text-white">Modal title</h4>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <form method="post" id="recordForm">
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label for="SKU_CODE" class="control-label font-weight-bold text-dark">รหัสสินค้า</label>
                                            <input type="text" class="form-control" id="SKU_CODE" name="SKU_CODE" required="required" placeholder="รหัสสินค้า">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="SKU_NAME" class="control-label font-weight-bold text-dark">ขนาดยาง/ดอกยาง</label>
                                            <input type="text" class="form-control" id="SKU_NAME" name="SKU_NAME" required="required" placeholder="ขนาดยาง/ดอกยาง">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="BRAND" class="control-label font-weight-bold text-dark">ยี่ห้อ</label>
                                            <input type="text" class="form-control" id="BRAND" name="BRAND" required="required" placeholder="ยี่ห้อ">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="SKU_CAT" class="control-label font-weight-bold text-dark">กลุ่มสินค้า</label>
                                            <input type="text" class="form-control" id="SKU_CAT" name="SKU_CAT" required="required" placeholder="กลุ่มสินค้า">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="TIRES_EDGE" class="control-label font-weight-bold text-dark">ขอบ</label>
                                            <input type="text" class="form-control" id="TIRES_EDGE" name="TIRES_EDGE" required="required" placeholder="ขอบ">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="TRD_U_POINT" class="control-label font-weight-bold text-dark">คะแนนร้านค้าทั่วไป</label>
                                            <input type="text" class="form-control" id="TRD_U_POINT" name="TRD_U_POINT" required="required" placeholder="คะแนนร้านค้าทั่วไป">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="TRD_S_POINT" class="control-label font-weight-bold text-dark">คะแนน SHOP</label>
                                            <input type="text" class="form-control" id="TRD_S_POINT" name="TRD_S_POINT" required="required" placeholder="คะแนน SHOP">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="hidden" name="id" id="id"/>
                                        <input type="hidden" name="action" id="action" value=""/>
                                        <button type="submit" name="save" id="save" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save</button>
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close <i class="fa fa-times ms-1"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php
                    include('includes/Footer.php');
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    ?>
    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Vendor Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Datatables Scripts -->
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- Custom Style -->
    <link href="css/spinner_over.css" rel="stylesheet"/>

    <script>
        $("#SKU_NAME").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let SKU_CODE = $('#SKU_CODE').val();
                let SKU_NAME = $('#SKU_NAME').val();
                let formData = {action: "SEARCH", SKU_CODE: SKU_CODE, SKU_NAME: SKU_NAME};
                $.ajax({
                    url: 'model/manage-sac-tires-point_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        if (data == 2) {
                            alert("Duplicate มีข้อมูลนี้แล้วในระบบ กรุณาตรวจสอบ");
                        }
                    }
                })
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_TIRES_POINT", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[6, 10, 20, 50, 100], [6, 10, 20, 50, 100]],
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
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage-sac-tires-point_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'SKU_CODE'},
                    {data: 'SKU_NAME'},
                    {data: 'BRAND'},
                    {data: 'SKU_CAT'},
                    {data: 'TIRES_EDGE'},
                    {data: 'TRD_U_POINT'},
                    {data: 'TRD_S_POINT'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage-sac-tires-point_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })
            });
            <!-- *** FOR SUBMIT FORM *** -->
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#SKU_CODE').val("");
                $('#SKU_NAME').val("");
                $('#BRAND').val("");
                $('#SKU_CAT').val("");
                $('#TIRES_EDGE').val("");
                $('#TRD_U_POINT').val("");
                $('#TRD_S_POINT').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage-sac-tires-point_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let SKU_CODE = response[i].SKU_CODE;
                        let SKU_NAME = response[i].SKU_NAME;
                        let BRAND = response[i].BRAND;
                        let SKU_CAT = response[i].SKU_CAT;
                        let TIRES_EDGE = response[i].TIRES_EDGE;
                        let TRD_U_POINT = response[i].TRD_U_POINT;
                        let TRD_S_POINT = response[i].TRD_S_POINT;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#SKU_CODE').val(SKU_CODE);
                        $('#SKU_NAME').val(SKU_NAME);
                        $('#BRAND').val(BRAND);
                        $('#SKU_CAT').val(SKU_CAT);
                        $('#TIRES_EDGE').val(TIRES_EDGE);
                        $('#TRD_U_POINT').val(TRD_U_POINT);
                        $('#TRD_S_POINT').val(TRD_S_POINT);
                        $('.modal-title').html("<i class='fa fa-edit'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage-sac-tires-point_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let SKU_CODE = response[i].SKU_CODE;
                        let SKU_NAME = response[i].SKU_NAME;
                        let BRAND = response[i].BRAND;
                        let SKU_CAT = response[i].SKU_CAT;
                        let TIRES_EDGE = response[i].TIRES_EDGE;
                        let TRD_U_POINT = response[i].TRD_U_POINT;
                        let TRD_S_POINT = response[i].TRD_S_POINT;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#SKU_CODE').val(SKU_CODE);
                        $('#SKU_NAME').val(SKU_NAME);
                        $('#BRAND').val(BRAND);
                        $('#SKU_CAT').val(SKU_CAT);
                        $('#TIRES_EDGE').val(TIRES_EDGE);
                        $('#TRD_U_POINT').val(TRD_U_POINT);
                        $('#TRD_S_POINT').val(TRD_S_POINT);
                        $('.modal-title').html("<i class='fa fa-trash'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    </body>
    </html>

<?php } ?>