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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
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
                                            <table id='TableRecordList' class='display dataTable'>

                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>วันที่ Due</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>Sale</th>
                                                    <th>หมายเหตุ</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>วันที่ Due</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>Sale</th>
                                                    <th>หมายเหตุ</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!--/div-->
                                        <!-- /.row -->

                                    </section>


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
                                                                <label for="date_request"
                                                                       class="control-label">วันที่ต้องการ :</label>
                                                                <input type="text" class="form-control"
                                                                       id="date_request"
                                                                       name="date_request"
                                                                       required="required"
                                                                       readonly="true"
                                                                       placeholder="วันที่ต้องการ">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="estimate_date"
                                                                       class="control-label">ประมาณการวันที่ของเข้า :</label>
                                                                <input type="text" class="form-control"
                                                                       id="estimate_date"
                                                                       name="estimate_date"
                                                                       required="required"
                                                                       readonly="true"
                                                                       placeholder="ประมาณการวันที่ของเข้า">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="date_in"
                                                                       class="control-label">ของมาวันที่ :</label>
                                                                <input type="text" class="form-control"
                                                                       id="date_in"
                                                                       name="date_in"
                                                                       required="required"
                                                                       readonly="true"
                                                                       placeholder="ของมาวันที่">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="brand" class="control-label">ยี่ห้อ</label>
                                                                <input type="text" class="form-control"
                                                                       id="brand" name="brand"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="class" class="control-label">ลายดอกยาง</label>
                                                                <input type="text" class="form-control"
                                                                       id="class" name="class"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="detail" class="control-label">รายละเอียด</label>
                                                                <input type="text" class="form-control"
                                                                       id="detail" name="detail"
                                                                       readonly="true"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="qty_need" class="control-label">จำนวนที่ต้องการ</label>
                                                                <input type="text" class="form-control"
                                                                       id="qty_need" name="qty_need"
                                                                       readonly="true"
                                                                       placeholder="">
                                                            </div>

                                                            <label for="complete_flag">สถานะ :</label>
                                                            <input type="hidden" name="complete_flag" id="complete_flag"
                                                                   required
                                                                   class="form-control">

                                                            <select id='Selcomplete_flag' class='form-control'>
                                                                <option value='N'>- เลือกสถานะ -</option>
                                                            </select>
                                                            <br>

                                                            <input type="text" name="status_detail" id="status_detail"
                                                                   readonly = "true"
                                                                   class="form-control">

                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id" id="id"/>
                                                        <input type="hidden" name="action" id="action" value=""/>
                                                        <span class="icon-input-btn">
                                                        <i class="fa fa-check"></i>
                                                        <input type="submit" name="save" id="save" class="btn btn-primary" value="Save"/>
                                                        </span>
                                                        <button type="button" class="btn btn-danger"
                                                                data-dismiss="modal">Close <i
                                                                    class="fa fa-window-close"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                    <!-- Row -->

                </div>

            </div>

        </div>


        <!---Container Fluid-->

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

    <!-- Page level plugins -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <!-- Bootstrap Datepicker -->
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->
    <script src="vendor/clock-picker/clockpicker.js"></script>


    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>

    <!-- select2 css -->
    <link href='js/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>

    <!-- select2 script -->
    <script src='js/select2/dist/js/select2.min.js'></script>

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
            /* 'scrollX': true, */
            let formData = {action: "GET_DATA_DUE_DATE", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_document_duedate_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'DI_REF'},
                    {data: 'DI_DATE'},
                    {data: 'ARD_DUE_DA'},
                    {data: 'AR_NAME'},
                    {data: 'DI_AMOUNT'},
                    {data: 'SLMN_NAME'},
                    {data: 'AR_REMARK'}
                ]
            });

        });
    </script>


    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>




    </body>

    </html>

<?php } ?>