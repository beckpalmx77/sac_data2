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
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page']?>">Home</a></li>
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

                                                        <form id="from_data">

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

                                                                    <label for="AR_CODE">เลือกยี่ห้อ :</label>
                                                                    <input type="hidden" name="tires_brand"
                                                                           id="tires_brand"
                                                                           class="form-control">
                                                                    <select id='selTiresBrand' class='form-control'>
                                                                        <option value='0'>- ค้นหายี่ห้อ -</option>
                                                                    </select>
                                                                    <br>
                                                                    <br>

                                                                    <label for="AR_CODE">เลือกลายดอก :</label>
                                                                    <input type="hidden" name="tires_class"
                                                                           id="tires_class"
                                                                           class="form-control">
                                                                    <select id='selTiresClass' class='form-control'>
                                                                        <option value='0'>- ค้นหาลายดอก -</option>
                                                                    </select>
                                                                    <br>
                                                                    <br>

                                                                    <label for="AR_CODE">เลือกยาง :</label>
                                                                    <input type="hidden" name="tires_id"
                                                                           id="tires_id"
                                                                           class="form-control">
                                                                    <select id='selTires' class='form-control'>
                                                                        <option value='0'>- ค้นหายาง -</option>
                                                                    </select>
                                                                    <br>
                                                                    <br>
                                                                    <input type="hidden" id="myCheckValue" name="myCheckValue">

                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <!--div class="form-check"-->
                                                                            <input type="checkbox" id="myCheck"
                                                                                   name="myCheck" value="N">
                                                                            <label class="form-check-label"
                                                                                   for="flexCheckChecked">
                                                                                กรณียางไม่เคยมีขาย
                                                                            </label>
                                                                            <!--/div-->
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group has-success">
                                                                        <label for="success" class="control-label">
                                                                        </label>
                                                                        <div class="">
                                                                            <input type="text" name="other_tires_request"
                                                                                   class="form-control"
                                                                                   id="other_tires_request">
                                                                        </div>
                                                                    </div>

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
                                                                           class="form-control">
                                                                    <select id='selCustomer' class='form-control'>
                                                                        <option value='0'>- ค้นหารายชื่อลูกค้า -
                                                                        </option>
                                                                    </select>
                                                                    <br>
                                                                    <br>

                                                                    <label for="AR_CODE">เลือกชื่อ SALE/TAKE :</label>
                                                                    <input type="hidden" name="sale_name" id="sale_name"
                                                                           class="form-control">
                                                                    <select id='selSale' class='form-control'>
                                                                        <option value='0'>- ค้นหารายชื่อ SALE/TAKE -
                                                                        </option>
                                                                    </select>
                                                                    <br>
                                                                    <br>

                                                                    <label for="AR_CODE">STOCK :</label>
                                                                    <input type="hidden" name="remark" id="remark"
                                                                           class="form-control">
                                                                    <select id='selRemark' class='form-control'>
                                                                        <option value='0'>- เลือกเหตุผล -
                                                                        </option>
                                                                    </select>
                                                                    <br>
                                                                    <br>

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
                                                                    <!--div class="col-sm-12">
                                                                        <button type="button" id="BtnSale" name="BtnSale" class="btn btn-primary mb-3">แสดง
                                                                            Test
                                                                        </button>
                                                                    </div-->

                                                                </div>
                                                            </div>

                                                        </form>

                                                        <div id="result"></div>


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
        $(document).ready(function () {
            $('#myCheckValue').val('N');
            $('#myCheck').click(function () {
                if ($("#myCheck").is(":checked") == true) {
                    $('#myCheckValue').val('Y');
                    $( "#selTires" ).prop( "disabled", true );
                    $( "#other_tires_request" ).prop( "disabled", false );
                } else {
                    $('#myCheckValue').val('N');
                    $( "#selTires" ).prop( "disabled", false );
                    $( "#other_tires_request" ).prop( "disabled", true );
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

            $("#selTiresBrand").select2({
                ajax: {
                    url: "model/get_tires_brand_ajaxfile.php",
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

            $("#selTiresClass").select2({
                ajax: {
                    url: "model/get_tires_class_ajaxfile.php",
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

            $("#selRemark").select2({
                ajax: {
                    url: "model/get_reason_ajaxfile.php",
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
            $("form").on("submit", function (event) {
                event.preventDefault();
                $('#AR_CODE').val($(selCustomer).val());
                $('#tires_id').val($(selTires).val());
                $('#tires_brand').val($(selTiresBrand).val());
                $('#tires_class').val($(selTiresClass).val());
                $('#sale_name').val($(selSale).val());
                $('#remark').val($(selRemark).val());
                $('#action').val("SAVE");
                let formValues = $(this).serialize();

                $.post("model/manage_data_tires_process.php", formValues, function (response) {
                    if (response == 1) {
                        document.getElementById("from_data").reset();
                        alertify.success("บันทึกข้อมูลเรียบร้อย Save Data Success");
                    } else if (response == 2) {
                        document.getElementById("from_data").reset();
                        alertify.success("แก้ไขข้อมูลเรียบร้อย Edit Data Success");
                    } else {
                        alertify.error("ไม่สามารถบันทึกข้อมูลได้ DB Error ");
                    }
                });

            });
        });
    </script>


    </body>

    </html>

<?php } ?>