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
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">
                                                        <form id="from_data" method="post" action=""
                                                              enctype="multipart/form-data">
                                                            <div class="modal-body">
                                                                <h5 class="modal-title">ข้อมูลรายการขาย SAC</h5>

                                                                <!-- แถว 1: จากวันที่, ถึงวันที่ -->
                                                                <div class="form-group row mb-3">
                                                                    <!-- จากวันที่ -->
                                                                    <div class="col-sm-3">
                                                                        <label for="doc_date_start"
                                                                               class="control-label">จากวันที่</label>
                                                                        <div class="input-group">
                                                                            <input type="text" class="form-control"
                                                                                   id="doc_date_start"
                                                                                   name="doc_date_start"
                                                                                   required="required" readonly="true"
                                                                                   placeholder="จากวันที่">
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text"><i
                                                                                            class="fa fa-calendar"
                                                                                            aria-hidden="true"></i></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- ถึงวันที่ -->
                                                                    <div class="col-sm-3">
                                                                        <label for="doc_date_to" class="control-label">ถึงวันที่</label>
                                                                        <div class="input-group">
                                                                            <input type="text" class="form-control"
                                                                                   id="doc_date_to" name="doc_date_to"
                                                                                   required="required" readonly="true"
                                                                                   placeholder="ถึงวันที่">
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text"><i
                                                                                            class="fa fa-calendar"
                                                                                            aria-hidden="true"></i></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- แถว 2: ค้นหาชื่อลูกค้า, อำเภอ, จังหวัด -->
                                                                <div class="form-group row mb-3">
                                                                    <!-- ค้นหาชื่อลูกค้า -->
                                                                    <div class="col-sm-3">
                                                                        <label for="AR_NAME" class="control-label">ค้นหาชื่อ
                                                                            ลูกค้า</label>
                                                                        <select id="AR_NAME" name="AR_NAME"
                                                                                class="form-control">
                                                                            <option value="-">ค้นหาชื่อ ลูกค้า</option>
                                                                        </select>
                                                                    </div>
                                                                    <!-- อำเภอ -->
                                                                    <div class="col-sm-3">
                                                                        <label for="TRD_AMPHUR" class="control-label">อำเภอ</label>
                                                                        <select id="TRD_AMPHUR" name="TRD_AMPHUR"
                                                                                class="form-control">
                                                                            <option value="-">อำเภอ</option>
                                                                        </select>
                                                                    </div>
                                                                    <!-- จังหวัด -->
                                                                    <div class="col-sm-3">
                                                                        <label for="TRD_PROVINCE" class="control-label">จังหวัด</label>
                                                                        <select id="TRD_PROVINCE" name="TRD_PROVINCE"
                                                                                class="form-control">
                                                                            <option value="-">จังหวัด</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <!-- แถว 3: ประเภท, ยี่ห้อ -->
                                                                <div class="form-group row mb-3">
                                                                    <!-- ประเภท -->
                                                                    <div class="col-sm-3">
                                                                        <label for="SKU_CAT" class="control-label">ประเภท</label>
                                                                        <select id="SKU_CAT" name="SKU_CAT"
                                                                                class="form-control">
                                                                            <option value="-">ประเภท</option>
                                                                        </select>
                                                                    </div>
                                                                    <!-- ยี่ห้อ -->
                                                                    <div class="col-sm-3">
                                                                        <label for="BRAND"
                                                                               class="control-label">ยี่ห้อ</label>
                                                                        <select id="BRAND" name="BRAND"
                                                                                class="form-control">
                                                                            <option value="-">ยี่ห้อ</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <!-- แถว 4: ค้นหาชื่อ SALE (แบบ multiple) -->
                                                                <div class="form-group row mb-3">
                                                                    <div class="col-sm-12">
                                                                        <label for="SALE_NAME" class="control-label">ค้นหาชื่อ
                                                                            SALE</label>
                                                                        <select id="SALE_NAME" name="SALE_NAME[]"
                                                                                multiple="multiple"
                                                                                class="form-control">
                                                                            <option value="-">ค้นหาชื่อ SALE</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Footer: ปุ่ม Export และ Spin Loader -->
                                                            <div class="modal-footer">
                                                                <input type="hidden" name="id" id="id"/>
                                                                <input type="hidden" name="save_status"
                                                                       id="save_status"/>
                                                                <input type="hidden" name="action" id="action"
                                                                       value=""/>
                                                                <button type="button" class="btn btn-success"
                                                                        id="btnExport" onclick="Export_Data();">
                                                                    Export <i class="fa fa-check"></i>
                                                                </button>
                                                                <!-- Spin Loader -->
                                                                <div id="spinner" class="spinner-overlay"
                                                                     style="display:none;">
                                                                    <div class="spinner-border text-primary"
                                                                         role="status">
                                                                        <span class="sr-only">Loading...</span>
                                                                    </div>
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Bootstrap Datepicker -->
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->
    <script src="vendor/clock-picker/clockpicker.js"></script>
    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <!-- Javascript for this page -->

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="js/MyFrameWork/framework_util.js"></script>

    <script src="js/util.js"></script>

    <style>
        .select2-container {
            width: 100% !important; /* ปรับให้ขนาดเต็ม 100% ของพื้นที่ */
        }

        .select2-selection--single {
            height: 38px !important; /* ปรับความสูงให้ตรงกับ Text Input */
            padding: 0.375rem 0.75rem !important; /* เพิ่มระยะห่างภายในให้เหมือน Text Input */
            font-size: 1rem !important; /* ปรับขนาดตัวอักษรให้ตรงกับ Text Input */
            line-height: 1.5 !important; /* ปรับ line-height ให้สอดคล้อง */
        }

        .select2-selection__rendered {
            line-height: 38px !important; /* ปรับ line-height ของข้อความที่เลือกใน Select2 */
        }

        .select2-selection__arrow {
            height: 38px !important; /* ปรับลูกศรให้มีความสูงเท่ากับ Select2 */
        }
    </style>

    <style>
        /* Spin Loader CSS */
        #spinner {
            display: none; /* ซ่อน spinner ตอนแรก */
            position: fixed;
            z-index: 999;
            left: 50%;
            top: 50%;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        $(document).ready(function () {
            // ตั้งค่าวันที่ปัจจุบันเป็นค่าเริ่มต้น
            let today = new Date();
            let doc_date_start = "01" + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_start').val(doc_date_start);
            $('#doc_date_to').val(doc_date);

            // ตั้งค่า datepicker สำหรับวันที่เริ่มต้นและสิ้นสุด
            $('#doc_date_start, #doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            // ดึงข้อมูล Sale Name ผ่าน AJAX
            $.ajax({
                url: 'model/get_sale_take_name.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#SALE_NAME');
                    select.empty(); // ล้างค่าเก่าออกก่อน
                    select.append('<option value="">ค้นหาชื่อ SALE</option>'); // เพิ่ม option เริ่มต้น

                    // เพิ่มข้อมูลใหม่จากฐานข้อมูล
                    $.each(data, function (index, sale_take) {
                        select.append($('<option>', {
                            value: sale_take.NAME,
                            text: sale_take.NAME // แสดงชื่อ
                        }));
                    });

                    // ใช้งาน Select2
                    $('#SALE_NAME').select2({
                        placeholder: "เลือกชื่อ Sale",
                        allowClear: true,
                        width: '100%' // ปรับให้เต็มความกว้าง
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_sku_cat.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#SKU_CAT');
                    $.each(data, function (index, sku_cat) {
                        select.append($('<option>', {
                            value: sku_cat.SKU_CAT,
                            text: sku_cat.SKU_CAT, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': sku_cat.SKU_CAT // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#SKU_CAT').select2({
                        placeholder: "เลือกประเภท",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_ar_name.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#AR_NAME');
                    $.each(data, function (index, ar_name) {
                        select.append($('<option>', {
                            value: ar_name.AR_NAME,
                            text: ar_name.AR_NAME, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': ar_name.AR_NAME // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#AR_NAME').select2({
                        placeholder: "เลือกชื่อลูกค้า",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_province_name.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#TRD_PROVINCE');
                    $.each(data, function (index, province_name) {
                        select.append($('<option>', {
                            value: province_name.TRD_PROVINCE,
                            text: province_name.TRD_PROVINCE, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': province_name.TRD_PROVINCE // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#TRD_PROVINCE').select2({
                        placeholder: "เลือกจังหวัด",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_sku_brand.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#BRAND');
                    $.each(data, function (index, sku_brand) {
                        select.append($('<option>', {
                            value: sku_brand.BRAND,
                            text: sku_brand.BRAND, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': sku_brand.BRAND // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#BRAND').select2({
                        placeholder: "เลือกยี่ห้อ",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_amphur_name.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#TRD_AMPHUR');
                    $.each(data, function (index, amphur_name) {
                        select.append($('<option>', {
                            value: amphur_name.TRD_AMPHUR,
                            text: amphur_name.TRD_AMPHUR, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': amphur_name.TRD_AMPHUR // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#TRD_AMPHUR').select2({
                        placeholder: "เลือกอำเภอ",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        function Export_Data() {
            // แสดง loader
            document.getElementById('spinner').style.display = 'block';

            // ตั้งค่าการส่งแบบฟอร์ม
            document.forms['from_data'].action = 'export_process/export_data_sale_sac_csv.php';
            document.forms['from_data'].submit();

            // ซ่อน loader หลังจากการส่ง
            // ใช้ setTimeout เพื่อจำลองการทำงาน
            setTimeout(function () {
                document.getElementById('spinner').style.display = 'none';
            }, 4000); // ปรับเวลาตามต้องการ

            return true;
        }
    </script>

    <script>
        $(document).ready(function () {
            $('.SALE_NAME').select2();
        });
    </script>

    </body>

    </html>

<?php } ?>