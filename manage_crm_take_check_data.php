<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include 'config/connect_db.php';
    $sql_province = " SELECT DISTINCT(province) AS province
    FROM ims_customer_ar WHERE 1 
    order by id ";
    $stmt_province = $conn->prepare($sql_province);
    $stmt_province->execute();
    $ProvinceRecords = $stmt_province->fetchAll();
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <body id="page-top">
    <div id="wrapper">


        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><span id="title"></span></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><span id="main_menu"></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><span id="sub_menu"></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <form id="MainrecordForm" name="MainrecordForm" method="POST"
                                              enctype="multipart/form-data">
                                            <input type="hidden" class="form-control" id="KeyAddData" name="KeyAddData"
                                                   value="">
                                            <input type="hidden" id="img" name="img">
                                            <div class="modal-body">
                                                <div class="modal-body">
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <label for="doc_id"
                                                                   class="control-label">เลขที่เอกสาร</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_id" name="doc_id"
                                                                   readonly="true"
                                                                   placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-2">
                                                            <label for="doc_date"
                                                                   class="control-label">วันที่</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_date"
                                                                   name="doc_date"
                                                                   required="required"
                                                                   readonly="true"
                                                                   value=""
                                                                   placeholder="">
                                                            <div class="input-group-addon">
                                                                <span class="glyphicon glyphicon-th"></span>
                                                            </div>
                                                        </div>

                                                        <input type="hidden" class="form-control"
                                                               id="customer_id"
                                                               name="customer_id">
                                                        <div class="col-sm-6">
                                                            <label for="customer_name"
                                                                   class="control-label">ชื่อลูกค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="customer_name"
                                                                   name="customer_name"
                                                                   required="required"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <input type="hidden" id="myCheckValue"
                                                                   name="myCheckValue">
                                                            <input type="checkbox" id="myCheck"
                                                                   name="myCheck" value="N">
                                                            <label class="form-check-label"
                                                                   for="flexCheckChecked">ลูกค้าใหม่
                                                            </label>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="CusModal"
                                                                   class="control-label"> เลือกชื่อลูกค้า </label>
                                                            <a data-toggle="modal" href="#SearchCusModal"
                                                               class="btn btn-primary" id="btnSearchCus">
                                                                Click <i class="fa fa-search"
                                                                         aria-hidden="true"></i>
                                                            </a>
                                                        </div>

                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="province">จังหวัด :</label>
                                                            <select name="province" id="province" class="form-control"
                                                                    required>
                                                                <option value="-"
                                                                        selected>-
                                                                </option>
                                                                <?php foreach ($ProvinceRecords as $row) { ?>
                                                                    <option value="<?php echo $row["province"]; ?>">
                                                                        <?php echo $row["province"]; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="contact_name"
                                                                   class="control-label">ผู้ติดต่อ</label>
                                                            <input type="text" class="form-control"
                                                                   id="contact_name"
                                                                   name="contact_name"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="phone"
                                                                   class="control-label">หมายเลขโทรศัพท์</label>
                                                            <input type="text" class="form-control"
                                                                   id="phone"
                                                                   name="phone"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="purpose" class="form-label">วัตถุประสงค์</label>
                                                            <select id="purpose" name="purpose" class="form-control">
                                                                <option value="">เลือกวัตถุประสงค์</option>
                                                                <option value="แนะนำสินค้า">แนะนำสินค้า</option>
                                                                <option value="รับคำสังซื้อสินค้า">รับคำสั่งซื้อสินค้า
                                                                </option>
                                                                <option value="เสนอโปรโมชั่นใหม่">เสนอโปรโมชั่นใหม่
                                                                </option>
                                                                <option value="ตามยอดค้างชำระ">ตามยอดค้างชำระ</option>
                                                                <option value="ปิดยอดขาย">ปิดยอดขาย</option>
                                                                <option value="เรื่องยางเคลม">เรื่องยางเคลม</option>
                                                                <option value="ปัญหาที่ลูกค้าแจ้ง">ปัญหาที่ลูกค้าแจ้ง
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <label for="purpose_text"
                                                                   class="control-label">อื่นๆ</label>
                                                            <input type="text" class="form-control"
                                                                   id="purpose_text"
                                                                   name="purpose_text"
                                                                   value=""
                                                                   placeholder="">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="contact_method"
                                                                   class="form-label">วิธีการติดต่อ</label>
                                                            <select id="contact_method" name="contact_method"
                                                                    class="form-control">
                                                                <option value="">วิธีการติดต่อ</option>
                                                                <option value="โทรศัพท์">โทรศัพท์</option>
                                                                <option value="ไลน์">ไลน์</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-12">
                                                            <label for="remark"
                                                                   class="control-label">รายละเอียดการสนทนา</label>
                                                            <textarea class="form-control" id="remark" name="remark"
                                                                      rows="4" placeholder=""></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="modal-footer">
                                                <input type="hidden" name="id" id="id"/>
                                                <input type="hidden" name="save_status" id="save_status"/>

                                                <input type="hidden" name="latitude_longitude" id="latitude_longitude"
                                                       value=""/>

                                                <input type="hidden" name="action" id="action" value=""/>
                                                <button type="button" class="btn btn-primary"
                                                        id="btnSave">Save <i
                                                            class="fa fa-check"></i>
                                                </button>

                                                <button type="button" class="btn btn-danger"
                                                        id="btnClose">Close <i
                                                            class="fa fa-times"></i>
                                                </button>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="files" class="control-label">เลือก File รูปภาพ
                                                        JPG หรือ PNG ที่ต้องการ Upload</label>
                                                    <input type="file" class="form-control" id="images" name="images[]"
                                                           multiple accept="image/*">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <!-- พื้นที่สำหรับแสดงภาพ Preview -->
                                                    <div id="imagePreview"
                                                         style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                                                </div>
                                            </div>

                                            <div id="existingImages"></div>
                                            <input type="hidden" id="existing_images" name="existing_images" value="">
                                            <input type="hidden" id="remove_images" name="remove_images" value="">

                                        </form>

                                        <div class="modal fade" id="SearchCusModal">
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
                                                                   id="TableCusCRMList"
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

                                        <div id="result"></div>

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

    <!--script src="vendor/jquery/jquery.min.js"></script-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>


    <script src="js/modal/show_cust_crm_modal.js"></script>


    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="//github.com/fyneworks/multifile/blob/master/jquery.MultiFile.min.js" type="text/javascript"
            language="javascript"></script>

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
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date').val(doc_date);
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

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
            $("#btnClose").click(function () {
                if ($('#save_status').val() !== '') {
                    window.opener = self;
                    window.close();
                } else {
                    alertify.error("กรุณากด save อีกครั้ง");
                }
            });
        });
    </script>

    <script type="text/javascript">
        let queryString = new Array();
        $(function () {
            if (queryString.length == 0) {
                if (window.location.search.split('?').length > 1) {
                    let params = window.location.search.split('?')[1].split('&');
                    for (let i = 0; i < params.length; i++) {
                        let key = params[i].split('=')[0];
                        let value = decodeURIComponent(params[i].split('=')[1]);
                        queryString[key] = value;
                    }
                }
            }

            let data = "<b>" + queryString["title"] + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#action').val(queryString["action"]);

            $('#save_status').val("before");

            if (queryString["action"] === 'ADD') {
                let KeyData = generate_token(15);
                $('#KeyAddData').val(KeyData + ":" + Date.now());
                $('#save_status').val("add");
            }

            if (queryString["action"] === 'ADD') {
                let KeyData = generate_token(15);
                $('#KeyAddData').val(KeyData + ":" + Date.now());
                $('#save_status').val("add");

                // Disable ปุ่ม BTNSave ถ้า action เป็น 'ADD'
                $('#btnSave').prop('disabled', false);
                $('#myCheck').prop('disabled', false);
                $('#btnUpload').prop('disabled', true);
                $('#btnSearchCus').css('opacity', '1'); // ลดความทึบของปุ่มให้ดูเหมือนถูกปิดใช้งาน
                $('#customer_name').prop('readonly', true);
            } else {
                // ถ้าไม่ใช่ 'ADD' ก็ให้ Enable ปุ่ม BTNSave
                $('#btnSave').prop('disabled', false);
                $('#myCheck').prop('disabled', true);
                $('#btnUpload').prop('disabled', false);
                $('#btnSearchCus').css('opacity', '0.5'); // ลดความทึบของปุ่มให้ดูเหมือนถูกปิดใช้งาน
                $('#customer_name').prop('readonly', true);
            }

            if (queryString["doc_id"] != null && queryString["customer_name"] != null) {
                $('#id').val(queryString["id"]);
                $('#doc_id').val(queryString["doc_id"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#customer_id').val(queryString["customer_id"]);
                $('#customer_name').val(queryString["customer_name"]);
                $('#purpose').val(queryString["purpose"]);
                $('#purpose_text').val(queryString["purpose_text"]);
                $('#province').val(queryString["province"]);
                $('#phone').val(queryString["phone"]);
                $('#contact_name').val(queryString["contact_name"]);
                $('#contact_method').val(queryString["contact_method"]);
                $('#remark').val(queryString["remark"]);
                $('#img').val(queryString["picture"]);

            }
        });
    </script>

    <script>
        function getLocation(callback) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        callback(latitude, longitude); // ส่งค่าไปที่ callback
                    },
                    function (error) {
                        console.error('Error Code: ' + error.code + ' - ' + error.message);
                        callback('0', '0'); // ส่งค่าเริ่มต้นในกรณีเกิดข้อผิดพลาด
                    }
                );
            } else {
                console.log('Geolocation is not supported by this browser.');
                callback('0', '0'); // ส่งค่าเริ่มต้นในกรณีที่เบราว์เซอร์ไม่รองรับ
            }
        }

        // เรียกใช้งานฟังก์ชัน getLocation พร้อม callback
        getLocation(function (latitude, longitude) {
            console.log('Latitude:', latitude);
            console.log('Longitude:', longitude);
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnSave").on("click", function () {
                let doc_date = $('#doc_date').val();
                let customer_name = $('#customer_name').val();

                if (doc_date !== "" && customer_name !== "") {
                    $(this).prop("disabled", true); // ป้องกันการกดซ้ำ

                    // ตั้งค่า Latitude และ Longitude เป็น 0
                    let latitude_longitude = "0,0";
                    $('#latitude_longitude').val(latitude_longitude);

                    // สร้าง FormData
                    let form = $("#MainrecordForm")[0];
                    let formData = new FormData(form);

                    // ตรวจสอบว่า FormData มีข้อมูลหรือไม่
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ', ' + pair[1]);
                    }

                    // ส่งข้อมูลไปยังเซิร์ฟเวอร์
                    $.ajax({
                        url: 'model/manage_crm_take_check_process.php',
                        method: "POST",
                        data: formData,
                        processData: false, // ต้องตั้งค่า false เพื่อส่งข้อมูลแบบ FormData
                        contentType: false, // ปิด Content-Type อัตโนมัติ
                        success: function (response) {
                            alertify.success("บันทึกข้อมูลสำเร็จ");
                            if ($("#KeyAddData").val() !== '') {
                                // ใช้ค่าจาก queryString เพื่อสร้าง URL
                                let url = `manage-crm-take-check?m=${queryString["main_menu"]}&s=${queryString["sub_menu"]}`;
                                window.location.href = url;
                            }
                            console.log(response); // ตรวจสอบ response จากเซิร์ฟเวอร์
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            alertify.alert("มีข้อผิดพลาด: " + textStatus + " - " + errorThrown);
                        },
                        complete: function () {
                            $("#btnSave").prop("disabled", false); // เปิดปุ่มอีกครั้ง
                        }
                    });
                } else {
                    alertify.alert("กรุณากรอกข้อมูลวันที่เอกสารและชื่อลูกค้าให้ครบถ้วน");
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#myCheckValue').val('N');
            $('#myCheck').click(function () {
                if ($("#myCheck").is(":checked") == true) {
                    $('#myCheckValue').val('Y');
                    $('#btnSearchCus').css('pointer-events', 'none');
                    $('#btnSearchCus').css('opacity', '0.5'); // ลดความทึบของปุ่มให้ดูเหมือนถูกปิดใช้งาน
                    $('#customer_name').prop('readonly', false);
                } else {
                    $('#myCheckValue').val('Y');
                    // Enable ปุ่มโดยการตั้งค่า pointer-events เป็น 'auto'
                    $('#btnSearchCus').css('pointer-events', 'auto');
                    $('#btnSearchCus').css('opacity', '1'); // รีเซ็ตความทึบของปุ่มให้กลับมาเหมือนเดิม
                    $('#customer_name').prop('readonly', true);
                }
            });
        });
    </script>

    <script>
        $('#SearchCusModal').on('hidden.bs.modal', function () {
            const customer_id = $('#customer_id').val();
            //alert(customer_id);
            if (customer_id !== '') {
                let formData = {action: "GET_DATA", customer_id: customer_id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_customer_ar.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        if (response.length > 0) {
                            let contact_name = response[0].contact_name;
                            let phone = response[0].phone;
                            let province = response[0].province;
                            $('#contact_name').val(contact_name);
                            $('#phone').val(phone);
                            $('#province').val(province);
                        } else {
                            alertify.error("No customer data found.");
                        }
                    },
                    error: function (xhr, status, error) {
                        alertify.error("Error: " + xhr.responseText);
                    }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#images").on("change", function () {
                let files = this.files;
                let previewContainer = $("#imagePreview");

                // ล้าง Preview เดิม
                previewContainer.empty();

                // ตรวจสอบว่าเลือกไฟล์
                if (files.length > 0) {
                    Array.from(files).forEach((file, index) => {
                        // ตรวจสอบประเภทไฟล์
                        if (file.type.startsWith("image/")) {
                            let reader = new FileReader();
                            reader.onload = function (e) {
                                // สร้าง element สำหรับแสดงภาพ
                                let imgElement = $(
                                    `<div style="position: relative; width: 100px; height: 100px;">
                                <img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; cursor: pointer; font-size: 14px;" data-index="${index}">×</span>
                            </div>`
                                );

                                // เพิ่มภาพเข้าไปใน Preview
                                previewContainer.append(imgElement);
                            };
                            reader.readAsDataURL(file);
                        } else {
                            alertify.error("กรุณาเลือกไฟล์รูปภาพเท่านั้น");
                        }
                    });
                }
            });

            // ลบภาพออกจาก Preview
            $("#imagePreview").on("click", "span", function () {
                let indexToRemove = $(this).data("index");
                let fileInput = document.getElementById("images");

                // ใช้ DataTransfer เพื่อแก้ไขรายการไฟล์
                let dataTransfer = new DataTransfer();
                Array.from(fileInput.files).forEach((file, index) => {
                    if (index !== indexToRemove) {
                        dataTransfer.items.add(file);
                    }
                });

                // อัปเดตรายการไฟล์
                fileInput.files = dataTransfer.files;

                // รีโหลด Preview
                $(fileInput).trigger("change");
            });
        });

    </script>

    <script>

        $(document).ready(function () {
            let existingImages = $('#img').val();
            if (existingImages) {
                let imageArray = existingImages.split(",");
                let imageContainer = $("#existingImages");

                imageArray.forEach(image => {
                    imageContainer.append(`
                <div class="image-wrapper" data-name="${image}">
                    Click ที่ภาพเพื่อขยาย
                    <a href="upload_img/${image}" target="_blank">
                        <img src="upload_img/${image}" width="200">
                    </a>
                    <button type="button" class="remove-image" data-name="${image}">ลบ</button>
                </div>
                <br>
            `);
                });

                // อัปเดต hidden input
                $("#existing_images").val(existingImages);
            }

            // ลบรูปภาพเดิม
            $(document).on("click", ".remove-image", function () {
                const imageName = $(this).data("name");

                // เพิ่มชื่อไฟล์ที่ต้องการลบใน hidden input
                let removedImages = $("#remove_images").val();
                removedImages = removedImages ? removedImages.split(",") : [];
                removedImages.push(imageName);
                $("#remove_images").val(removedImages.join(","));

                // ลบจากหน้าจอ
                $(this).closest(".image-wrapper").remove();

                // อัปเดต existing_images โดยลบชื่อไฟล์ออก
                let currentImages = $("#existing_images").val().split(",");
                currentImages = currentImages.filter(image => image !== imageName);
                $("#existing_images").val(currentImages.join(","));
            });

            // แสดงภาพ Preview ของไฟล์ใหม่
            $("#images").on("change", function () {
                let files = this.files;
                let previewContainer = $("#imagePreview");
                previewContainer.empty();

                Array.from(files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        previewContainer.append(`<img src="${e.target.result}" width="100" style="margin: 5px;">`);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });

    </script>

    </body>

    </html>

<?php } ?>