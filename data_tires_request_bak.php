<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
} else {

    $company = ($_SESSION['company'] === '-') ? "%" : "%" . $_SESSION['company'] . "%";

    $manage_team_id = ($_SESSION['manage_team_id'] === '-') ? "'%'" : "'%" . $_SESSION['manage_team_id'] . "%'";

    include("config/connect_db.php");

    $month_num = str_replace('0', '', date('m'));

    $sql_curr_month = " SELECT * FROM ims_month where month = '" . $month_num . "'";

    $stmt_curr_month = $conn->prepare($sql_curr_month);
    $stmt_curr_month->execute();
    $MonthCurr = $stmt_curr_month->fetchAll();
    foreach ($MonthCurr as $row_curr) {
        $month_name = $row_curr["month_name"];
    }

    //$myfile = fopen("param.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, "month_num = " . $month_num . "| month_name" . $month_name . " | " . $sql_curr_month);
    //fclose($myfile);

    $sql_cust = " SELECT * FROM v_customer_salename                   
                  LIMIT 1 ";


    $sql_customer = " SELECT * FROM v_customer_salename
                      GROUP BY AR_CODE ORDER BY AR_CODE  ";


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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) . " [ " . $_SESSION['SLT_CODE'] . "]" ?></h1>
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
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="myform" name="myform">

                                                            <div class="row">
                                                                            <div class="col-sm-12">

                                                                                <label for="date_request"
                                                                                       class="control-label">วันที่ต้องการยาง
                                                                                    :</label>
                                                                                <input type="text" class="form-control"
                                                                                       id="date_request"
                                                                                       name="date_request"
                                                                                       readonly="true"
                                                                                       placeholder="วันที่">
                                                                                <br>
                                                                                <label for="AR_CODE">เลือกยาง :</label>
                                                                                <input type="text" name="tires_code"
                                                                                       id="tires_code"
                                                                                       class="form-control">
                                                                                <select id='selTires' class='form-control'>
                                                                                <option value='0'>- ค้นหายาง -</option>
                                                                    </select>
                                                                    <br>
                                                                    <br>

                                                                    <div class="form-group has-success">
                                                                        <label for="success" class="control-label">จำนวนที่ต้องการ
                                                                        </label>
                                                                        <div class="">
                                                                            <input type="text" name="qty_need"
                                                                                   class="form-control"
                                                                                   required="required" id="qty_need">
                                                                        </div>
                                                                    </div>

                                                                    <label for="AR_CODE">เลือกลูกค้า :</label>
                                                                    <input type="hidden" name="AR_CODE" id="AR_CODE"
                                                                           required
                                                                           class="form-control">
                                                                    <select id='selCustomer' class='form-control'>
                                                                        <option value='0'>- ค้นหารายชื่อลูกค้า -
                                                                        </option>
                                                                    </select>
                                                                    <br>
                                                                    <br>

                                                                    <label for="AR_CODE">เลือกชื่อ SALE/TAKE :</label>
                                                                    <input type="text" name="sale_name" id="sale_name"
                                                                           class="form-control">
                                                                    <select id='selSale' class='form-control'>
                                                                        <option value='0'>- ค้นหารายชื่อ SALE/TAKE -
                                                                        </option>
                                                                    </select>
                                                                    <br>
                                                                    <br>
                                                                    <div class="form-group has-success">
                                                                        <label for="success" class="control-label">STOCK
                                                                        </label>
                                                                        <div class="">
                                                                            <input type="text" name="remark"
                                                                                   class="form-control"
                                                                                   required="required" id="remark">
                                                                        </div>
                                                                    </div>

                                                                    <label for="date_in"
                                                                           class="control-label">ของมาวันที่ :</label>
                                                                    <input type="text" class="form-control"
                                                                           id="date_in"
                                                                           name="date_in"
                                                                           required="required"
                                                                           readonly="true"
                                                                           placeholder="ของมาวันที่">
                                                                    <br>

                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <input type="hidden" name="action"
                                                                             id="action" value=""/>
                                                                            <span class="icon-input-btn">
                                                                                <i class="fa fa-check"></i>
                                                                                <input type="submit" name="save" id="save"
                                                                                class="btn btn-primary" value="Save"/>
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </form>

                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.col-md-8 col-md-offset-2 -->
                                        </div>
                                        <!-- /.row -->

                                    </section>


                                </div>

                            </div>

                        </div>

                    </div>

                    <!--Row-->

                    <!-- Row -->

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

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>
    <!-- Javascript for this page -->

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>

    <!-- select2 css -->
    <link href='js/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>

    <!-- select2 script -->
    <script src='js/select2/dist/js/select2.min.js'></script>

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

    <script src="js/MyFrameWork/framework_util.js"></script>

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

        $("#BtnData-BAK").click(function () {

            $('#AR_CODE').val($(selCustomer).val());


            if ($('#year').val() !== "") {
                document.forms['myform'].action = 'data_sale_sac_display';
                document.forms['myform'].target = '_blank';
                document.forms['myform'].submit();
                return true;
            }

        });

    </script>

    <script>

        $("#BtnData").click(function () {

            $('#AR_CODE').val($(selCustomer).val());
            $('#year').val($(yearSel).val());

            let formData = {action: "GET_DATA", year: $('#year').val(), AR_CODE: $('#AR_CODE').val()};

            $.ajax({
                type: "POST",
                url: 'model/get_data_sac.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {

                        if (response[i].rec_num <= 0) {
                            alertify.error("error : " + "ไม่พบข้อมุูล");
                        } else {
                            document.forms['myform'].action = 'data_sale_sac_display';
                            document.forms['myform'].target = '_blank';
                            document.forms['myform'].submit();
                            return true;
                        }

                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });

        });

    </script>

    <script>
        $(document).ready(function () {

            $("#selCustomer").select2({
                ajax: {
                    url: "model/customer_ajaxfile.php",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {

            $("#selSale").select2({
                ajax: {
                    url: "model/get_salename_ajaxfile.php",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {

            $("#selTires").select2({
                ajax: {
                    url: "model/get_tires_master_ajaxfile.php",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            let today = new Date();
            let date_request = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#date_request').val(date_request);
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#date_request').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#date_in').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>

        <!-- *** FOR SUBMIT FORM *** -->

        $("#recordModal").on('submit', '#recordForm', function (event) {

            $('#AR_CODE').val($(selCustomer).val());
            $('#tires_code').val($(selTires).val());

            event.preventDefault();

            $('#save').attr('disabled', 'disabled');
/*
            let formData = $(this).serialize();
            $.ajax({
                url: 'model/manage_data_tires_process.php',
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

*/

        });

    </script>


    </body>

    </html>

<?php } ?>