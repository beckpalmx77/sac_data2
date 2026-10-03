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

                                        <!--div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>

                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div-->

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>วันที่สร้างเอกสาร</th>
                                                    <th>Sequence</th>
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
        $(document).ready(function () {
            let formData = {action: "GET_SALE_LOGISTIC", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
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
                    'url': 'model/manage_sale_logistic_plan_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'DOC_ID'},
                    {data: 'DOC_DATE'},
                    {data: 'MONTH'},
                    {data: 'YEAR'},
                    {data: 'create_date'},
                    {data: 'seq_record'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });
        });
    </script>

    <!--script>

        $("#TableRecordList").on('click', '.update', function () {
            let url = "manage_sale_logistic_plan_data_show?" + $.param({DOC_ID: $(this).attr("DOC_ID")});
            window.open(url, "_blank");
        });

    </script-->

    <script>
        $("#TableRecordList").on('click', '.update', function () {
            // ใช้ .data() เพื่อดึงค่าจาก data-doc-id
            let docId = $(this).data("doc-id");
            let Month = $(this).data("month");
            let Year = $(this).data("year");

            // ตรวจสอบว่ามีค่าก่อนส่ง เพื่อป้องกันข้อผิดพลาด
            if (docId) {
                let url = "manage_sale_logistic_plan_data_show?" + $.param({
                    DOC_ID: docId,
                    MONTH: Month,
                    YEAR: Year
                });
                window.open(url, "_blank");
            } else {
                console.error("DOC_ID not found.");
            }
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.delete', function () {
            if (!confirm("ยืนยันการลบรายการนี้?")) return;

            let id = $(this).attr("id");

            //alert(id);

            $.ajax({
                url: "model/manage_sale_logistic_plan_process.php",
                type: "POST",
                data: {action: "DELETE", id: id},
                success: function (response) {
                    alert("ลบเรียบร้อยแล้ว");
                    // รีเฟรช DataTable
                    $("#TableRecordList").DataTable().ajax.reload();
                },
                error: function () {
                    alert("เกิดข้อผิดพลาดในการลบข้อมูล");
                }
            });
        });

    </script>

    </body>
    </html>

<?php } ?>