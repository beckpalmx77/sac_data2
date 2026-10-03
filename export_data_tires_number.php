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
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="from_data" method="post"
                                                              action="export_process/export_data_tires_number_process.php"
                                                              enctype="multipart/form-data">

                                                            <div class="modal-body">

                                                                <div class="modal-body">
                                                                    <div class="form-group row">

                                                                        <div class="col-sm-3">
                                                                            <label for="doc_date_start"
                                                                                   class="control-label">จากวันที่ (วันที่ส่ง)</label>
                                                                            <i class="fa fa-calendar"
                                                                               aria-hidden="true"></i>
                                                                            <input type="text" class="form-control"
                                                                                   id="doc_date_start"
                                                                                   name="doc_date_start"
                                                                                   required="required"
                                                                                   readonly="true"
                                                                                   placeholder="จากวันที่">
                                                                        </div>

                                                                        <div class="col-sm-3">
                                                                            <label for="doc_date_to"
                                                                                   class="control-label">ถึงวันที่ (วันที่ส่ง)</label>
                                                                            <i class="fa fa-calendar"
                                                                               aria-hidden="true"></i>
                                                                            <input type="text" class="form-control"
                                                                                   id="doc_date_to"
                                                                                   name="doc_date_to"
                                                                                   required="required"
                                                                                   readonly="true"
                                                                                   placeholder="ถึงวันที่">
                                                                        </div>

                                                                        <div class="col-sm-6">
                                                                            <label for="customer_name_select">ลูกค้า :</label>
                                                                            <input
                                                                                    type="text"
                                                                                    id="customer_name_select"
                                                                                    name="customer_name_select"
                                                                                    placeholder="พิมพ์ชื่อลูกค้าเพื่อค้นหา"
                                                                                    class="form-control"
                                                                                    list="customer-list"
                                                                            >
                                                                            <datalist id="customer-list"></datalist>
                                                                        </div>


                                                                    </div>
                                                                </div>


                                                            </div>

                                                            <div class="modal-footer">
                                                                <input type="hidden" name="id" id="id"/>
                                                                <input type="hidden" name="save_status"
                                                                       id="save_status"/>
                                                                <input type="hidden" name="action" id="action"
                                                                       value=""/>
                                                                <button type="submit" class="btn btn-success"
                                                                        id="btnExport"> Export <i
                                                                            class="fa fa-check"></i>
                                                                </button>
                                                            </div>


                                                        </form>


                                                        <div id="result"></div>

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
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="vendor/select2/dist/js/select2.min.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <script src="vendor/clock-picker/clockpicker.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="js/MyFrameWork/framework_util.js"></script>

    <script src="js/util.js"></script>

    <script>
        $(document).ready(function () {

            // *** 🔑 Autocomplete Logic สำหรับ input text (โดยใช้ Datalist) ***
            $('#customer_name_select').on('input', function() {
                let searchTerm = $(this).val();
                let datalist = $('#customer-list');

                // เริ่มค้นหาเมื่อมีอย่างน้อย 1 ตัวอักษร
                if (searchTerm.length >= 1) {
                    $.ajax({
                        url: 'model/fetch_customer_names.php',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            search: searchTerm
                        },
                        success: function(data) {
                            datalist.empty(); // ล้างรายการเดิม
                            // เพิ่มตัวเลือก "ไม่ระบุ (ทั้งหมด)"
                            datalist.append($('<option>', {
                                value: '',
                                text: '-- ไม่ระบุ (ทั้งหมด) --'
                            }));

                            // เพิ่มรายการลูกค้าที่ค้นหาได้
                            if (data && data.length > 0) {
                                $.each(data, function(index, item) {
                                    datalist.append($('<option>', {
                                        value: item.name, // ค่าที่ถูกเลือกและส่งในฟอร์ม
                                        text: item.name // ข้อความที่แสดงใน Datalist
                                    }));
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching customer names:", error);
                        }
                    });
                } else if (searchTerm.length === 0) {
                    // ล้าง datalist เมื่อไม่มีข้อความ
                    datalist.empty();
                }
            });
            // *** สิ้นสุด Autocomplete Logic ***


            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_start').val(doc_date);
            $('#doc_date_to').val(doc_date);
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date_start').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    </body>

    </html>

<?php } ?>