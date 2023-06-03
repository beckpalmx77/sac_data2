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
                  WHERE SLMN_SLT LIKE " . $manage_team_id . " 
                  LIMIT 1 ";
/*
    $stmt_cust = $conn->prepare($sql_cust);
    $stmt_cust->execute();
    $CustRecords = $stmt_cust->fetchAll();

    foreach ($CustRecords as $row) {
        $AR_CODE = $row["AR_CODE"];
        $AR_NAME = $row["AR_NAME"];
    }
*/

    $sql_customer = " SELECT * FROM v_customer_salename 
                      WHERE SLMN_SLT LIKE " . $manage_team_id . "
                      GROUP BY AR_CODE ORDER BY AR_CODE  ";

    //$myfile = fopen("qry_file_mysql_server.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_customer . " | " . $manage_team_id);
    //fclose($myfile);

/*
    $stmt_customer = $conn->prepare($sql_customer);
    $stmt_customer->execute();
    $CustomerRecords = $stmt_customer->fetchAll();

*/

    $sql_year = " SELECT DISTINCT(DI_YEAR) AS DI_YEAR
    FROM ims_product_sale_sac WHERE DI_YEAR >= 2019
    ORDER BY DI_YEAR desc ";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();


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

                                                        <form id="myform" name="myform"
                                                              action="engine/chart_data_daily.php" method="post">

                                                            <!--div class="form-group row">
                                                                <div class="col-sm-12">
                                                                    <a data-toggle="modal" href="#SearchCusCrmModal"
                                                                       class="btn btn-primary">
                                                                        Click <i class="fa fa-search"
                                                                                 aria-hidden="true"></i>
                                                                    </a>
                                                                    <br>
                                                                    <label for="customer_id"
                                                                           class="control-label">รหัสลูกค้า</label>
                                                                    <input type="text" class="form-control"
                                                                           id="customer_id"
                                                                           name="customer_id"
                                                                           readonly="true"
                                                                           required="required"
                                                                           placeholder="รหัสลูกค้า">
                                                                    <label for="f_name"
                                                                           class="control-label">ชื่อลูกค้า</label>
                                                                    <input type="text" class="form-control"
                                                                           id="f_name"
                                                                           name="f_name"
                                                                           readonly="true"
                                                                           required="required"
                                                                           placeholder="ชื่อลูกค้า">
                                                                </div>
                                                            </div-->

                                                            <div class="row">
                                                                <div class="col-sm-12">

                                                                    <label for="AR_CODE">เลือกลูกค้า :</label>
                                                                    <input type ="hidden" name="AR_CODE" id="AR_CODE" class="form-control">
                                                                    <!--select name="AR_CODE" id="AR_CODE" class="form-control"
                                                                            required>
                                                                        <option value="<?php echo $AR_CODE; ?>"
                                                                                selected><?php echo $AR_NAME . " [ " . $row["AR_CODE"] . " ]" ; ?></option>
                                                                        <?php foreach ($CustomerRecords as $row) { ?>
                                                                            <option value="<?php echo $row["AR_CODE"]; ?>">
                                                                                <?php echo $row["AR_NAME"] . " [ " . $row["AR_CODE"] . " ]" ; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select-->

                                                                    <select id='selCustomer' style='width: 600px;'>
                                                                        <option value='0'>- Search Customer -</option>
                                                                    </select>
                                                                    <br>

                                                                    <label for="year">เลือกปี :</label>
                                                                    <select name="year" id="year" class="form-control"
                                                                            required>
                                                                        <?php foreach ($YearRecords as $row) { ?>
                                                                            <option value="<?php echo $row["DI_YEAR"]; ?>">
                                                                                <?php echo $row["DI_YEAR"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>

                                                                    <br>
                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <button type="button" id="BtnData"
                                                                                    name="BtnData"
                                                                                    class="btn btn-primary mb-3">แสดงข้อมูล
                                                                            </button>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </form>


                                                        <div class="modal fade" id="SearchCusCrmModal">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title">Modal title</h4>
                                                                        <button type="button" class="close" data-dismiss="modal"
                                                                                aria-hidden="true">×
                                                                        </button>
                                                                    </div>

                                                                    <div class="container"></div>
                                                                    <div class="modal-body">

                                                                        <div class="modal-body">

                                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                                   class="display"
                                                                                   id="TableCustomerLists"
                                                                                   width="100%">
                                                                                <thead>
                                                                                <tr>
                                                                                    <th>รหัสลูกค้า</th>
                                                                                    <th>ชื่อลูกค้า</th>
                                                                                    <th>Action</th>
                                                                                </tr>
                                                                                </thead>
                                                                                <tfoot>
                                                                                <tr>
                                                                                    <th>รหัสลูกค้า</th>
                                                                                    <th>ชื่อลูกค้า</th>
                                                                                    <th>Action</th>
                                                                                </tr>
                                                                                </tfoot>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


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

    <script src="js/modal/show_customer_sale_modal.js"></script>

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

    <script>

        $("#BtnData").click(function () {
            $('#AR_CODE').val($(selCustomer).val());
            document.forms['myform'].action = 'data_sale_sac_display';
            document.forms['myform'].target = '_blank';
            document.forms['myform'].submit();
            return true;
        });

    </script>

    <script>
        $(document).ready(function () {
            $('#myCheckValue').val('N');
            $('#myCheck').click(function () {
                if ($("#myCheck").is(":checked") == true) {
                    $('#myCheckValue').val('Y');
                    $("#month").prop("disabled", true);
                } else {
                    $('#myCheckValue').val('N');
                    $("#month").prop("disabled", false);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function(){

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


    </body>

    </html>

<?php } ?>