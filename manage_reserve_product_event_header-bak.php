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
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
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
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>

                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <button type='button' name='btnSummary' id='btnSummary'
                                                    class='btn btn-success btn-xs'>สรุปยอดจอง
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่</th>
                                                    <th>ชื่อร้านค้า</th>
                                                    <th>จังหวัด</th>
                                                    <th>ชื่อเซลล์</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                            </table>
                                            <div id="result"></div>
                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>    <?php
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

        $("#DOC_DATE").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let DOC_ID = $('#DOC_ID').val();
                let DOC_DATE = $('#DOC_DATE').val();
                let formData = {action: "SEARCH", DOC_ID: DOC_ID, DOC_DATE: DOC_DATE};
                $.ajax({
                    url: 'model/manage_reserve_product_event_header_process.php',
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
            let formData = {action: "GET_DOC_ID", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "ทั้งหมด"]],
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
                'autoWidth': true,
                'searching': true,
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_reserve_product_event_header_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'DOC_ID'},
                    {data: 'DOC_DATE'},
                    {data: 'CUSTOMER_NAME'},
                    {data: 'PROVINCE'},
                    {data: 'SALE_NAME'},
                    {data: 'detail'},
                    {data: 'update'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_reserve_product_event_header_process.php',
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
                $('#DOC_ID').val("");
                $('#DOC_DATE').val("");
                $('#CUSTOMER_NAME').val("");
                $('#PHONE').val("");
                $('#PROVINCE').val("");
                $('#SALE_NAME').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>
        document.getElementById("btnAdd").addEventListener("click", function () {
            window.open("manage_reserve_product_event", "_blank");
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.detail', function () {
            let doc_id = $(this).attr("id");
            let DOC_DATE = $(this).data("doc_date");
            let CUSTOMER_NAME = $(this).data("customer_name");
            let PHONE = $(this).data("phone");
            let SALE_NAME = $(this).data("sale_name");
            let PROVINCE = $(this).data("province");

            let url = "manage_reserve_product_event_show.php?doc_id=" + encodeURIComponent(doc_id)
                + "&DOC_DATE=" + encodeURIComponent(DOC_DATE)
                + "&PHONE=" + encodeURIComponent(PHONE)
                + "&CUSTOMER_NAME=" + encodeURIComponent(CUSTOMER_NAME)
                + "&SALE_NAME=" + encodeURIComponent(SALE_NAME)
                + "&PROVINCE=" + encodeURIComponent(PROVINCE);

            window.open(url, "_blank");
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.update', function () {
            let doc_id = $(this).attr("id");
            let DOC_DATE = $(this).data("doc_date");
            let CUSTOMER_NAME = $(this).data("customer_name");
            let PHONE = $(this).data("phone");
            let SALE_NAME = $(this).data("sale_name");
            let PROVINCE = $(this).data("province");

            let url2 = "manage_reserve_product_event_sale.php?doc_id=" + encodeURIComponent(doc_id)
                + "&DOC_DATE=" + encodeURIComponent(DOC_DATE)
                + "&PHONE=" + encodeURIComponent(PHONE)
                + "&CUSTOMER_NAME=" + encodeURIComponent(CUSTOMER_NAME)
                + "&SALE_NAME=" + encodeURIComponent(SALE_NAME)
                + "&PROVINCE=" + encodeURIComponent(PROVINCE);

            window.open(url2, "_blank");
        });
    </script>

    <script>
        document.getElementById("btnSummary").addEventListener("click", function () {
            window.open("manage_reserve_product_event_summary", "_blank");
        });
    </script>

    <script>
        // รีโหลด DataTable เมื่อผู้ใช้กลับมาที่แท็บนี้
        document.addEventListener("visibilitychange", function () {
            if (document.visibilityState === "visible") {
                $('#TableRecordList').DataTable().ajax.reload(null, false); // false = ไม่ reset pagination
            }
        });
    </script>

    </body>
    </html>

<?php } ?>