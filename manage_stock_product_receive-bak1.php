<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <style>
            .icon-input-btn { display: inline-block; position: relative; }
            .icon-input-btn input[type="submit"] { padding-left: 2em; }
            .icon-input-btn .fa { display: inline-block; position: absolute; left: 0.65em; top: 30%; }

            /* สไตล์รูป Thumbnail */
            .photo-thumb {
                width: 100px;
                height: 100px;
                object-fit: cover;
                border-radius: 6px;
                border: 2px solid #dee2e6;
                cursor: pointer;
                transition: transform 0.15s, border-color 0.15s;
                margin: 4px;
            }
            .photo-thumb:hover {
                transform: scale(1.07);
                border-color: #4e73df;
            }
            #item_count { font-size: 0.85rem; padding: 5px 10px; }

            /* สไตล์ Modal รูปใหญ่ */
            #imagePreviewModal .modal-content {
                background-color: transparent;
                border: none;
            }
            #previewImg {
                max-width: 100%;
                max-height: 80vh;
                border: 4px solid white;
                border-radius: 4px;
                box-shadow: 0 5px 15px rgba(0,0,0,.5);
            }
            .close-preview {
                color: white;
                font-size: 2rem;
                text-shadow: 0 0 10px black;
                opacity: 1;
            }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page']?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="col-md-12">
                                            <label class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnExportExcel' id='btnExportExcel' class='btn btn-success btn-xs'>
                                                Export Excel <i class="fa fa-file-excel"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 mt-3">
                                            <table id='TableRecordList' class='display dataTable' style="width:100%">
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
                                            </table>
                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <input type="hidden" id="user_id" name="user_id">
                                                            <div class="form-group">
                                                                <label class="control-label">ชื่อผู้บันทึก</label>
                                                                <input type="text" class="form-control" id="display_name" name="display_name">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">ชื่อลูกค้า</label>
                                                                <input type="text" class="form-control" id="customer_name" name="customer_name">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">เลขทะเบียนรถ</label>
                                                                <input type="text" class="form-control" id="car_no" name="car_no">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">
                                                                    <b>หมายเลขยาง/Bar Code</b>
                                                                    <span id="item_count" class="badge badge-primary ml-2">จำนวนทั้งสิ้น 0 รายการ</span>
                                                                </label>
                                                                <textarea class="form-control" id="remark" name="remark" rows="6" placeholder="รายการแต่ละบรรทัด..."></textarea>
                                                            </div>
                                                            <div class="form-group" id="photo_section" style="display:none;">
                                                                <label class="control-label">รูปภาพ (คลิกเพื่อดูรูปใหญ่)</label>
                                                                <div id="photo_gallery" class="d-flex flex-wrap gap-2 mt-2"></div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                                <input type="submit" name="save" id="save" class="btn btn-primary" value="Save"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close <i class="fa fa-times"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content text-center">
                                                    <div class="modal-header border-0">
                                                        <button type="button" class="close close-preview" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body p-0">
                                                        <img id="previewImg" src="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include('includes/Footer.php'); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>

    <script>
        const IMG_BASE_URL = 'http://171.100.56.194:8999/img_product_receive/img/';

        // ฟังก์ชันสำหรับอัปเดตตัวเลขจำนวนรายการ
        function updateItemCount() {
            let remarkValue = $('#remark').val();
            if (!remarkValue) {
                $('#item_count').text('จำนวนทั้งสิ้น 0 รายการ');
                return;
            }
            let lines = remarkValue.split('\n').filter(line => line.trim() !== '');
            $('#item_count').text('จำนวนทั้งสิ้น ' + lines.length + ' รายการ');
        }

        // ฟังก์ชัน Render รูปภาพลงใน Gallery
        function renderPhotos(photoPathValue) {
            let gallery = $('#photo_gallery');
            gallery.html('');

            if (!photoPathValue || photoPathValue.trim() === '') {
                $('#photo_section').hide();
                return;
            }

            let files = photoPathValue.split(',');
            files.forEach(function (filename) {
                if (filename.trim() !== '') {
                    let imgUrl = IMG_BASE_URL + filename.trim();
                    let img = $('<img>')
                        .addClass('photo-thumb')
                        .attr('src', imgUrl)
                        .attr('title', 'คลิกเพื่อขยาย')
                        .on('click', function () {
                            $('#previewImg').attr('src', imgUrl);
                            $('#imagePreviewModal').modal('show');
                        });
                    gallery.append(img);
                }
            });
            $('#photo_section').show();
        }

        $(document).ready(function () {
            // อัปเดตจำนวนรายการเมื่อมีการพิมพ์
            $('#remark').on('input', updateItemCount);

            // DataTable Init
            let formData = {action: "GET_RECEIVE", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'processing': true,
                'serverSide': true,
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
                    {data: 'info'},
                    {data: 'delete'}
                ]
            });

            // ปุ่มดูข้อมูล (Info)
            $("#TableRecordList").on('click', '.info', function () {
                let id = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: 'model/manage_stock_product_receive_process.php',
                    dataType: "json",
                    data: {action: "GET_DATA", id: id},
                    success: function (response) {
                        let r = response[0];
                        $('#recordModal').modal('show');
                        $('#id').val(r.id);
                        $('#display_name').val(r.display_name);
                        $('#customer_name').val(r.customer_name);
                        $('#car_no').val(r.car_no);
                        let formattedRemark = r.remark ? r.remark.replaceAll(',', '\n') : '';
                        $('#remark').val(formattedRemark);
                        updateItemCount();
                        renderPhotos(r.photo_path);
                        $('.modal-title').html("<i class='fa fa-edit'></i> Info Record");
                        $('#action').val('INFO');
                        $('#save').val('Save Changes');
                    }
                });
            });

            // Submit Form
            $("#recordForm").on('submit', function (event) {
                event.preventDefault();
                let currentRemark = $('#remark').val();
                let remarkForDB = currentRemark.split('\n').filter(n => n.trim() !== '').join(',');

                let postData = $(this).serializeArray();
                postData.forEach(item => { if(item.name === "remark") item.value = remarkForDB; });

                $.ajax({
                    url: 'model/manage_stock_product_receive_process.php',
                    method: "POST",
                    data: postData,
                    success: function () {
                        $('#recordModal').modal('hide');
                        dataRecords.ajax.reload();
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>