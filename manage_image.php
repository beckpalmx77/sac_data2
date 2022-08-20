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

                                        <form method="post" id="ImageForm" enctype="multipart/form-data">
                                            <input type="hidden" class="form-control" id="img_array" name="img_array"
                                                   value="">
                                            <input type="hidden" class="form-control" id="file_up" name="file_up"
                                                   value="">
                                            <input type="hidden" class="form-control" id="table_name" name="table_name"
                                                   value="">
                                            <div class="modal-body">
                                                <div class="modal-body">
                                                    <div class="form-group row">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="default"
                                                                           class="col-sm-12 control-label">เลือกรูปภาพ
                                                                        นามสกุล jpg หรือ png เท่านั้น</label>
                                                                    <img id="img_file"
                                                                         src=""
                                                                         width="200" height="200" alt="">
                                                                    <input type='file' id='file' name='file'
                                                                           class='file'
                                                                           accept="image/png, image/jpeg"
                                                                           onchange="readURL(this);"/>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <input type="submit" name="submit"
                                                                           value="เพิ่มรูปภาพ Add Image"
                                                                           class="btn btn-primary btn-xs">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <div id="myDIV"></div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <input type="hidden" name="id" id="id"/>
                                                    <input type="hidden" name="save_status" id="save_status"/>
                                                    <input type="hidden" name="action" id="action"
                                                           value=""/>
                                                    <button type="button" class="btn btn-danger"
                                                            id="btnClose">Close <i
                                                                class="fa fa-window-close"></i>
                                                    </button>
                                                </div>
                                        </form>
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
    <script src="vendor/select2/dist/js/select2.min.js"></script>


    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>

    <script src="js/modal/show_customer_modal.js"></script>
    <script src="js/modal/show_product_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>
    <!-- Javascript for this page -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

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
                window.opener = self;
                window.close();
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
            $("#img_array").val(queryString["img"]);
            $("#id").val(queryString["id"]);

            Load_Image();
            Check_Image();

        });

    </script>

    <script>
        $(document).ready(function () {
            $('input[type="file"]').change(function (e) {
                let fileName = e.target.files[0].name;

                //alert("Change = " + fileName);

                $('#file_up').val(fileName);
                let img_array = $('#img_array').val();

                if (img_array !== "") {
                    $('#img_array').val(img_array + "," + fileName);
                } else {
                    $('#img_array').val(fileName);
                }
            });
        });

    </script>

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $('#img_file')
                        .attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                let filename_upload = $('#file_up').val();
                let filename_save = $('#img_array').val();
                $('#action').val("UPDATE_IMAGE");
                $('#table_name').val("ims_home_model");

                if (filename_upload !== '') {
                    //alert("OK = " + filename_upload + " | " + filename_save);
                    let formData = $('#ImageForm').serialize();
                    $.ajax({
                        url: 'model/manage_image_process.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {
                            alertify.success(data);
                        }
                    })
                } else {
                    alert("Not OK");
                }

            });
        });
    </script>

    <script>
        $('#ImageForm')
            .submit(function (e) {
                $('#action').val("UPDATE_IMAGE");
                $('#table_name').val("ims_home_model");
                $.ajax({
                    url: 'model/manage_image_process.php',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (message) {
                        Load_Image();
                        alertify.success(message);
                    }
                });
                e.preventDefault();
            });
    </script>

    <script>
        function Load_Image() {
            document.getElementById("myDIV").innerHTML = "";
            let img = $("#img_array").val().split(",");
            let img_gallery = "&nbsp;<div class='card'><div class='card-body'><div class='card-columns'>";
            for (let i = 0; i < img.length; i++) {
                if (img[i] !== "") {
                    img_gallery = img_gallery + "<img src='gallery/" + img[i] + "' style=' width:100%'>&nbsp;";
                    img_gallery = img_gallery + "<a href='javascript:Delete_Image(" + i + ")' class='icon-block'>ลบ <i class='fa fa-times' aria-hidden='true'></i></a>";
                }
            }
            img_gallery = img_gallery + "</div></div></div>";
            //alert(img_gallery);
            document.getElementById("myDIV").innerHTML = img_gallery;
        }
    </script>

    <script>
        function Delete_Image_Bak(del_img) {
            let img = $('#img_array').val().split(",");
            let filename = del_img.src.split("/").pop();
            let index = img.indexOf(filename);
            if (index !== -1) {
                img.splice(index, 1);
                $('#img_array').val(img);
            }
            Load_Image();
            Check_Image();
        }
    </script>

    <script>
        function Check_Image() {
            let img = $('#img_array').val();
            if (img === '') {
                let img_gallery = "&nbsp;<img src='gallery/upload.png' style=' width:100%'>&nbsp;";
                document.getElementById("myDIV").innerHTML = img_gallery;
            }

        }
    </script>


    <script>
        function Delete_Image(index) {
            if (confirm("ต้องการลบรูปภาพนี้ ?")) {
                let img = $('#img_array').val().split(",");
                let fileName = img[index];
                $('#file_up').val(fileName);
                if (index !== -1) {
                    img.splice(index, 1);
                    $('#img_array').val(img);
                    Delete_Image_DB();
                }
                Load_Image();
                Check_Image();
            }
        }
    </script>

    <script>

        function Delete_Image_DB() {
            $('#action').val("DELETE_IMAGE");
            $('#table_name').val("ims_home_model");
            let formData = $('#ImageForm').serialize();
            //alert(formData);
            $.ajax({
                url: 'model/manage_image_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    alertify.success(data);
                }
            });
        }

    </script>



    </body>

    </html>

<?php } ?>


