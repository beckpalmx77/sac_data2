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
                                                    <th>ชื่อผู้บันทึก</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>เลขทะเบียนรถ</th>
                                                    <th>หมายเลขยาง/Bar Code</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ชื่อผู้บันทึก</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>เลขทะเบียนรถ</th>
                                                    <th>หมายเลขยาง/Bar Code</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!-- Modal Add/Edit/Delete -->
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

                                                            <input type="hidden" class="form-control"
                                                                   id="user_id" name="user_id"
                                                                   placeholder="">

                                                            <div class="form-group">
                                                                <label class="control-label">ชื่อผู้บันทึก</label>
                                                                <input type="text" class="form-control"
                                                                       id="display_name" name="display_name"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label">ชื่อลูกค้า</label>
                                                                <input type="text" class="form-control"
                                                                       id="customer_name" name="customer_name"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label">เลขทะเบียนรถ</label>
                                                                <input type="text" class="form-control"
                                                                       id="car_no" name="car_no"
                                                                       placeholder="เลขทะเบียนรถ">
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label">หมายเหตุ</label>
                                                                <textarea class="form-control" id="remark" name="remark"
                                                                          rows="3" placeholder="หมายเหตุ"></textarea>
                                                            </div>

                                                            <!-- Photo section (shown only on edit) -->
                                                            <div class="form-group" id="photo_section" style="display:none;">
                                                                <label class="control-label">รูปภาพ</label>
                                                                <div id="photo_gallery" class="d-flex flex-wrap gap-2 mt-2">
                                                                    <!-- images rendered by JS -->
                                                                </div>
                                                                <small class="text-muted" id="photo_path_raw"></small>
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
                                                                    class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                        <!-- /Modal -->

                                        <!-- Modal Image Preview (lightbox) -->
                                        <div class="modal fade" id="imagePreviewModal" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content bg-dark">
                                                    <div class="modal-header border-0">
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body text-center p-2">
                                                        <img id="previewImg" src="" class="img-fluid rounded" style="max-height:80vh;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /Modal Image Preview -->

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
        .photo-thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #dee2e6;
            cursor: pointer;
            transition: transform 0.15s;
            margin: 4px;
        }
        .photo-thumb:hover {
            transform: scale(1.07);
            border-color: #4e73df;
        }
        #photo_gallery {
            display: flex;
            flex-wrap: wrap;
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

    <!-- DataTable init -->
    <script>
        $(document).ready(function () {
            let formData = {action: "GET_RECEIVE", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100, 200, 500, 1000, 1500], [5, 10, 20, 50, 100, 200, 500, 1000, 1500]],
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
                'autoWidth': true,
                'searching': true,
                <?php if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                } ?>
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_stock_product_receive_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'display_name'},
                    {data: 'customer_name'},
                    {data: 'car_no'},
                    {data: 'remark'},
                    {data: 'checkin_time'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_stock_product_receive_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#photo_section').hide();
                        $('#photo_gallery').html('');
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })
            });
            <!-- *** FOR SUBMIT FORM *** -->
        });
    </script>

    <!-- Add button -->
    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#recordForm')[0].reset();
                $('#id').val("");
                $('#photo_section').hide();
                $('#photo_gallery').html('');
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <!-- Helper: render photo gallery -->
    <script>
        const IMG_BASE_URL = 'http://171.100.56.194:8999/img_product_receive/img/';

        function renderPhotos(photoPathValue) {
            let gallery = $('#photo_gallery');
            gallery.html('');
            if (!photoPathValue || photoPathValue.trim() === '') {
                $('#photo_section').hide();
                return;
            }
            let files = photoPathValue.split(',');
            let hasImage = false;
            files.forEach(function (filename) {
                filename = filename.trim();
                if (filename !== '') {
                    hasImage = true;
                    let imgUrl = IMG_BASE_URL + filename;
                    let img = $('<img>')
                        .addClass('photo-thumb')
                        .attr('src', imgUrl)
                        .attr('alt', filename)
                        .attr('title', filename)
                        .on('click', function () {
                            $('#previewImg').attr('src', imgUrl);
                            $('#imagePreviewModal').modal('show');
                        })
                        .on('error', function () {
                            $(this).attr('src', 'vendor/images/no-image.png').off('error');
                        });
                    gallery.append(img);
                }
            });
            if (hasImage) {
                $('#photo_section').show();
            } else {
                $('#photo_section').hide();
            }
        }
    </script>

    <!-- Update button -->
    <script>
        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_stock_product_receive_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let r = response[i];
                        $('#recordModal').modal('show');
                        $('#id').val(r.id);
                        $('#user_id').val(r.user_id);
                        $('#display_name').val(r.display_name);
                        $('#customer_name').val(r.customer_name);
                        $('#car_no').val(r.car_no);
                        $('#remark').val(r.remark);
                        renderPhotos(r.photo_path);
                        $('.modal-title').html("<i class='fa fa-edit'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    <!-- Delete button -->
    <script>
        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_stock_product_receive_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let r = response[i];
                        $('#recordModal').modal('show');
                        $('#id').val(r.id);
                        $('#user_id').val(r.user_id);
                        $('#display_name').val(r.display_name);
                        $('#customer_name').val(r.customer_name);
                        $('#car_no').val(r.car_no);
                        $('#remark').val(r.remark);
                        renderPhotos(r.photo_path);
                        $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    </body>
    </html>

<?php } ?>
