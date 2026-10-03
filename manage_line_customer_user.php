<?php include('includes/Header.php'); ?>

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
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active"
                            aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            </div>
                            <div class="card-body">
                                <section class="container-fluid">
                                    <div class="col-md-12 col-md-offset-2">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>line user id</th>
                                                <th>ชื่อ user line</th>
                                                <th>รหัสลูกค้า</th>
                                                <th>ชื่อร้าน-บริษัท ลูกค้า</th>
                                                <th>ประเภทผู้ใช้</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                        </table>

                                        <div id="result"></div>

                                    </div>

                                    <div class="modal fade" id="recordModal">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Modal title</h4>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true">×
                                                    </button>
                                                </div>
                                                <form method="post" id="recordForm">
                                                    <div class="modal-body">
                                                        <div class="modal-body">

                                                            <div class="form-group">
                                                                <label for="line_user_id" class="control-label">line
                                                                    user id</label>
                                                                <input type="line_user_id" class="form-control"
                                                                       id="line_user_id" name="line_user_id"
                                                                       readonly="true"
                                                                       placeholder="สร้างอัตโนมัติ">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="line_user_name"
                                                                       class="control-label">ชื่อ user line</label>
                                                                <input type="text" class="form-control"
                                                                       id="line_user_name"
                                                                       name="line_user_name"
                                                                       readonly="true"
                                                                       placeholder="ชื่อ user line">
                                                            </div>

                                                            <div class="mb-4 text-start">
                                                                <label class="form-label"><b>เลือกลูกค้า:</b></label>
                                                                <select id="customer_select" class="form-control"
                                                                        style="width: 100%;">
                                                                    <option value="">-- ค้นหารายชื่อลูกค้า --</option>
                                                                    <?php
                                                                    $sql = "SELECT * FROM ims_customer_master";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute();
                                                                    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                    foreach ($customers as $customer) {
                                                                        echo "<option value='{$customer['customer_id']}' data-name='{$customer['customer_name']}'>{$customer['customer_name']}</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="customer_id"
                                                                       class="control-label">รหัสลูกค้า</label>
                                                                <input type="text" class="form-control"
                                                                       id="customer_id"
                                                                       name="customer_id"
                                                                       placeholder="รหัสลูกค้า"
                                                                       readonly="true">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="customer_name"
                                                                       class="control-label">ชื่อลูกค้า</label>
                                                                <input type="text" class="form-control"
                                                                       id="customer_name"
                                                                       name="customer_name"
                                                                       placeholder="ชื่อลูกค้า"
                                                                       readonly="true">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="status"
                                                                       class="control-label">Status</label>
                                                                <select id="status" name="status"
                                                                        class="form-control" data-live-search="true"
                                                                        title="Please select">
                                                                    <option>Y</option>
                                                                    <option>N</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id" id="id"/>
                                                        <input type="hidden" name="action" id="action" value=""/>
                                                        <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                        <button type="button" class="btn btn-danger"
                                                                data-dismiss="modal">Close <i
                                                                    class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>

                                </section>
                            </div>
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


<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<!-- Page level plugins -->

<!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
<script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>

    .icon-input-btn {
        display: inline-block;
        position: relative;
    }

    .icon-input-btn input[type="submit"] {
        padding-left: 2em;
    }

    .icon-input-btn .fa {
        display: inline-block;
        position: absolute;
        left: 0.65em;
        top: 30%;
    }
</style>
<script>
    $(document).ready(function () {
        $(".icon-input-btn").each(function () {
            let btnFont = $(this).find(".btn").css("font-size");
            let btnColor = $(this).find(".btn").css("color");
            $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
        });
    });
</script>

<script>
    $(document).ready(function () {
        let formData = {action: "GET_LINE_CUSTOMER_USER", sub_action: "GET_MASTER"};
        let dataRecords = $('#TableRecordList').DataTable({
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
            },
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url': 'model/manage-line-customer-user-process.php',
                'data': formData
            },
            'columns': [
                {data: 'line_user_id'},
                {data: 'line_user_name'},
                {data: 'customer_id'},
                {data: 'customer_name'},
                {data: 'user_type'},
                {data: 'status'},
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
                url: 'model/manage-line-customer-user-process.php',
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

    $("#TableRecordList").on('click', '.update', function () {
        let id = $(this).attr("id");
        //alert(id);
        let formData = {action: "GET_DATA", id: id};
        $.ajax({
            type: "POST",
            url: 'model/manage-line-customer-user-process.php',
            dataType: "json",
            data: formData,
            success: function (response) {
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let id = response[i].id;
                    let line_user_id = response[i].line_user_id;
                    let line_user_name = response[i].line_user_name;
                    let customer_id = response[i].customer_id;
                    let customer_name = response[i].customer_name;
                    let status = response[i].status;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#line_user_id').val(line_user_id);
                    $('#line_user_name').val(line_user_name);
                    $('#customer_id').val(customer_id);
                    $('#customer_name').val(customer_name);
                    $('#status').val(status);
                    $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
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
            url: 'model/manage-line-customer-user-process.php',
            dataType: "json",
            data: formData,
            success: function (response) {
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let id = response[i].id;
                    let line_user_id = response[i].line_user_id;
                    let line_user_name = response[i].line_user_name;
                    let customer_id = response[i].customer_id;
                    let customer_name = response[i].customer_name;
                    let status = response[i].status;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#line_user_id').val(line_user_id);
                    $('#line_user_name').val(line_user_name);
                    $('#customer_id').val(customer_id);
                    $('#customer_name').val(customer_name);
                    $('#status').val(status);
                    $('.modal-title').html("<i class='fa fa-plus'></i> Delete Record");
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initializing Select2
        $('#customer_select').select2({
            placeholder: "ค้นหารายชื่อลูกค้า",
            allowClear: true,
        });

        // On customer selection, update the hidden inputs
        $('#customer_select').on('change', function () {
            let selectedOption = $(this).find('option:selected');
            let customerId = selectedOption.val();
            let customerName = selectedOption.data('name');

            // Set the hidden fields with the selected customer's data
            $('#customer_id').val(customerId);
            $('#customer_name').val(customerName);
        });
    });
</script>

</body>
</html>