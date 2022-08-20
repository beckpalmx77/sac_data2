<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <script src="js/popup.js"></script>

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
                        <input type="hidden" id="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                        <input type="hidden" id="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
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

                                        <div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>

                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>ชื่อโครงการ</th>
                                                    <th>แบบบ้าน</th>
                                                    <th>เนื้อที่</th>
                                                    <th>ชั้น</th>
                                                    <th>ห้องนอน</th>
                                                    <th>ห้องน้ำ</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ชื่อโครงการ</th>
                                                    <th>แบบบ้าน</th>
                                                    <th>เนื้อที่</th>
                                                    <th>ชั้น</th>
                                                    <th>ห้องนอน</th>
                                                    <th>ห้องน้ำ</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

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

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="home_id" class="control-label">รหัสแบบบ้าน</label>
                                                                        <input type="home_id" class="form-control"
                                                                               id="home_id" name="home_id"
                                                                               required="required"
                                                                               placeholder="รหัสแบบบ้าน">
                                                                    </div>

                                                                    <div class="col-sm-8">
                                                                        <label for="home_model_name"
                                                                               class="control-label">ชื่อแบบบ้าน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="home_model_name"
                                                                               name="home_model_name"
                                                                               required="required"
                                                                               placeholder="ชื่อแบบบ้าน">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <input type="hidden" class="form-control"
                                                                           id="project_id"
                                                                           name="project_id">
                                                                    <div class="col-sm-10">
                                                                        <label for="quantity"
                                                                               class="control-label">โครงการ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="project_name"
                                                                               name="project_name"
                                                                               required="required"
                                                                               placeholder="โครงการ">
                                                                    </div>

                                                                    <div class="col-sm-2">
                                                                        <label for="project_select"
                                                                               class="control-label">เลือก</label>

                                                                        <a data-toggle="modal" href="#Search-PG-Modal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="area"
                                                                               class="control-label">ขนาดพื้นที่</label>
                                                                        <input type="text" class="form-control"
                                                                               id="area"
                                                                               name="area"
                                                                               required="required"
                                                                               placeholder="ขนาดพื้นที่">
                                                                    </div>

                                                                    <div class="col-sm-5">
                                                                        <label for="floor"
                                                                               class="control-label">จำนวนชั้น</label>
                                                                        <input type="text" class="form-control"
                                                                               id="floor"
                                                                               name="floor"
                                                                               required="required"
                                                                               placeholder="จำนวนชั้น">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="bathroom"
                                                                               class="control-label">จำนวนห้องนอน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="bathroom"
                                                                               name="bathroom"
                                                                               required="required"
                                                                               placeholder="จำนวนห้องนอน">
                                                                    </div>

                                                                    <div class="col-sm-5">
                                                                        <label for="bedroom"
                                                                               class="control-label">จำนวนห้องน้ำ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="bedroom"
                                                                               name="bedroom"
                                                                               required="required"
                                                                               placeholder="จำนวนห้องน้ำ">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="comment"
                                                                               class="control-label">คำอธิบาย</label>
                                                                        <textarea name="comment" id="comment"
                                                                                  class="form-control"
                                                                                  rows="5"></textarea>
                                                                    </div>
                                                                </div>

                                                                <!--div class="form-group row">
                                                                    <p id="myDIV"></p>
                                                                </div-->

                                                                <div class="form-group">
                                                                    <label for="status"
                                                                           class="control-label">Status</label>
                                                                    <select id="status" name="status"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option>Active</option>
                                                                        <option>Inactive</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
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

                </div>
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>


    <script src="js/modal/show_pgroup_modal.js"></script>
    <script src="js/modal/show_brand_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>

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

        $("#product_id").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let product_id = $('#product_id').val();
                let formData = {action: "SEARCH", product_id: product_id};
                $.ajax({
                    url: 'model/manage_product_process.php',
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
            let formData = {action: "GET_HOME", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_home_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'project_name'},
                    {data: 'home_model_name'},
                    {data: 'area'},
                    {data: 'floor'},
                    {data: 'bedroom'},
                    {data: 'bathroom'},
                    {data: 'status'},
                    {data: 'update'},
                    {data: 'image'},
                    {data: 'delete'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_home_process.php',
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
                $('#product_id').val("");
                $('#name_t').val("");
                $('#quantity').val("");
                $('#pgroup_id').val("");
                $('#pgroup_name').val("");
                $('#brand_id').val("");
                $('#brand_name').val("");
                $('#quantity').val("");
                $('#unit_id').val("");
                $('#unit_name').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_home_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let home_id = response[i].home_id;
                        let home_model_name = response[i].home_model_name;
                        let project_id = response[i].project_id;
                        let project_name = response[i].project_name;
                        let area = response[i].area;
                        let floor = response[i].floor;
                        let bedroom = response[i].bedroom;
                        let bathroom = response[i].bathroom;
                        let comment = response[i].comment;
                        let status = response[i].status;
                        let img = response[i].img;

                        //alert(img);

                        //let img_show = img.split(",");

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#home_id').val(home_id);
                        $('#home_model_name').val(home_model_name);
                        $('#project_id').val(project_id);
                        $('#project_name').val(project_name);
                        $('#area').val(area);
                        $('#floor').val(floor);
                        $('#bedroom').val(bedroom);
                        $('#bathroom').val(bathroom);
                        $('#comment').val(comment);
                        $('#status').val(status);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');

/*
                        let img_gallery = "<div class='card'><div class='card-body'><div class='card-columns'>";

                        for (let i = 0; i < img_show.length; i++) {
                            if (img_show[i] !== "") {
                                img_gallery = img_gallery + "<img src='gallery/" + img_show[i] + "' style=' width:100%'  onclick='Manage_image(this);'>&nbsp;";
                            }
                        }

                        img_gallery = img_gallery + "</div></div></div>";

                        //alert(img_gallery);

                        document.getElementById("myDIV").innerHTML = img_gallery;

 */

                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_home_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let home_id = response[i].home_id;
                        let home_model_name = response[i].home_model_name;
                        let project_id = response[i].project_id;
                        let project_name = response[i].project_name;
                        let area = response[i].area;
                        let floor = response[i].floor;
                        let bedroom = response[i].bedroom;
                        let bathroom = response[i].bathroom;

                        let status = response[i].status;
                        let img = response[i].img;

                        //alert(img);

                        let img_show = img.split(",");

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#home_id').val(home_id);
                        $('#home_model_name').val(home_model_name);
                        $('#project_id').val(project_id);
                        $('#project_name').val(project_name);
                        $('#area').val(area);
                        $('#floor').val(floor);
                        $('#bedroom').val(bedroom);
                        $('#bathroom').val(bathroom);
                        $('#status').val(status);
                        $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');


                        let img_gallery = "<div class='card'><div class='card-body'><div class='card-columns'>";

                        for (let i = 0; i < img_show.length; i++) {
                            if (img_show[i] !== "") {
                                img_gallery = img_gallery + "<img src='gallery/" + img_show[i] + "' style=' width:100%'  onclick='Manage_image(this);'>&nbsp;";
                            }
                        }

                        img_gallery = img_gallery + "</div></div></div>";

                        //alert(img_gallery);

                        document.getElementById("myDIV").innerHTML = img_gallery;

                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.image', function () {
            let id = $(this).attr("id");
            let main_menu = document.getElementById("main_menu").value;
            let sub_menu = document.getElementById("sub_menu").value;
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_home_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let img = response[i].img;
                        //let img_show = img.split(",");
                        //$('#id').val(id);
                        let url = "manage_image.php?title=จัดการรูปภาพ (Manage Image)"
                            + '&img=' + img
                            + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                            + '&id=' + id
                            + '&action=UPDATE';
                        OpenPopupCenter(url, "", "");
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>


    <script>
        function Manage_image(img_name) {
            let filename = img_name.src.split("/").pop().split(".")[0];
            alert("Ad = " + filename);

            //let filename1 = img_name.src.replace(/^.*[\\\/]/, '');
            //alert(filename1);

            /*
            let fullPath = img_name.src;
            let index = fullPath.lastIndexOf("/");
            let filename2 = fullPath;
            if(index !== -1) {
                filename2 = fullPath.substring(index+1,fullPath.length);
            }
            alert("filename2 = " + filename2);
            */
        }
    </script>


    </body>
    </html>

<?php } ?>