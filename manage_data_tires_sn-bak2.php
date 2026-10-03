<?php
// ตรวจสอบและรวมไฟล์ Header, Session
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    // โค้ด PHP ในส่วนนี้ถูกลดเหลือเพียงการตรวจสอบสิทธิ์และ Layout
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


    <style>
        /* CSS เพิ่มเติมเพื่อให้ตาราง DataTables แสดงผลถูกต้อง */
        .table-responsive {
            overflow-x: auto;
        }

        .dataTables_wrapper .row {
            margin-bottom: 15px;
        }

        /* เพิ่มสไตล์สำหรับ Loading Spinner */
        #results-container {
            min-height: 50px;
        }

        .loading {
            text-align: center;
            padding: 20px;
        }
        /* แก้ไขปัญหา Autocomplete List อยู่ใต้ Modal */
        .ui-autocomplete {
            z-index: 1055 !important; /* ต้องสูงกว่า Modal z-index (1050) */
        }
    </style>

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
                        <h1 class="h4 mb-0 text-gray-800"><?php echo htmlspecialchars(urldecode($_GET['s'] ?? 'ค้นหาข้อมูลการขาย')) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo htmlspecialchars(urldecode($_GET['m'] ?? 'จัดการข้อมูล')) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo htmlspecialchars(urldecode($_GET['s'] ?? 'ค้นหา Serial')) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">กำหนดเงื่อนไขการค้นหา</h6>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="searchForm" method="POST">
                                                            <div class="row mb-3 align-items-end">

                                                                <div class="col-sm-2 mb-3">
                                                                    <label for="search_serial">หมายเลขยาง:</label>
                                                                    <input
                                                                            type="text"
                                                                            id="search_serial"
                                                                            name="search_serial"
                                                                            placeholder=""
                                                                            class="form-control"
                                                                    >
                                                                </div>

                                                                <div class="col-sm-2 mb-3">
                                                                    <label for="sku_code">รหัสสินค้า:</label>
                                                                    <input
                                                                            type="text"
                                                                            id="sku_code"
                                                                            name="sku_code"
                                                                            placeholder=""
                                                                            class="form-control"
                                                                    >
                                                                </div>

                                                                <div class="col-sm-3 mb-3">
                                                                    <label for="ar_name">ลูกค้า:</label>
                                                                    <input
                                                                            type="text"
                                                                            id="ar_name"
                                                                            name="ar_name"
                                                                            placeholder=""
                                                                            class="form-control"
                                                                    >
                                                                </div>

                                                                <div class="col-sm-1 mb-3">
                                                                    <label for="month">เดือน:</label>
                                                                    <select name="month" id="month"
                                                                            class="form-control">
                                                                        <option value="all">ทุกเดือน</option>
                                                                        <?php
                                                                        // สร้าง Dropdown เดือน
                                                                        $months = [1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'];
                                                                        foreach ($months as $num => $name) {
                                                                            echo "<option value=\"{$num}\">{$name}</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>

                                                                <div class="col-sm-1 mb-3">
                                                                    <label for="year">ปี:</label>
                                                                    <select name="year" id="year" class="form-control">
                                                                        <option value="all" selected>ทุกปี</option>
                                                                        <?php
                                                                        // สร้าง Dropdown ปี
                                                                        $current_year = date('Y');
                                                                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                                                                            echo "<option value=\"{$y}\">{$y}</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>

                                                                <div class="col-sm-3 mb-3">
                                                                    <button type="submit" class="btn btn-primary w-100"
                                                                            id="searchButton">
                                                                        <i class="fa fa-search"></i> ค้นหา
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>

                                                        <div id="results-container">
                                                            <p class="mt-3">กรุณาป้อน หมายเลขยาง หรือเงื่อนไขอื่น ๆ
                                                                เพื่อเริ่มค้นหา...</p>
                                                        </div>

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

            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">แก้ไขข้อมูลการขาย</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="editForm">
                            <div class="modal-body">
                                <input type="hidden" id="edit_id_hidden" name="id_main">

                                <div class="form-group">
                                    <label for="edit_di_ref">เลขที่เอกสาร (DI_REF):</label>
                                    <input type="text" class="form-control" id="edit_di_ref" name="di_ref" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="edit_sku_code">SKU:</label>
                                    <input type="text" class="form-control" id="edit_sku_code" name="edit_sku_code">
                                </div>
                                <div class="form-group">
                                    <label for="edit_sku_name">รายละเอียด:</label>
                                    <input type="text" class="form-control" id="edit_sku_name" name="edit_sku_name">
                                </div>
                                <div class="form-group">
                                    <label for="edit_serial_number">หมายเลขยาง (Serial Number):</label>
                                    <input type="text" class="form-control" id="edit_serial_number" name="serial_number">
                                </div>
                                <div class="form-group">
                                    <label for="edit_ar_name">ลูกค้า/ผู้ขาย (AR_NAME):</label>
                                    <input type="text" class="form-control" id="edit_ar_name" name="ar_name_modal"> </div>

                                <div id="edit_status_message" class="mt-3"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn btn-success" id="saveEditButton">บันทึกการแก้ไข</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function () {

            // ----------------------------------------------------------------------
            // *** Autocomplete Logic ***
            // ----------------------------------------------------------------------
            function setupAutocomplete(selector, field_name) {
                $(selector).autocomplete({
                    source: function(request, response) {
                        if (request.term.length < 3) {
                            return;
                        }

                        $.ajax({
                            url: 'model/fetch_data_tires_sn_autocomplete.php', // ไฟล์ PHP สำหรับดึงข้อมูล Hint
                            dataType: "json",
                            data: {
                                term: request.term,
                                field: field_name // ระบุฟิลด์ที่ต้องการค้นหา (AR_NAME, SKU_CODE, serial_tires_number)
                            },
                            success: function(data) {
                                response(data); // นำข้อมูลที่ได้มาแสดงเป็นรายการ Hint
                            },
                            error: function() {
                                // ซ่อน Autocomplete เมื่อเกิดข้อผิดพลาด
                                response([]);
                            }
                        });
                    },
                    minLength: 3,
                    delay: 300
                });
            }

            // กำหนด Autocomplete ให้กับช่องค้นหาหลัก
            
            //setupAutocomplete('#ar_name', 'AR_NAME');
            //setupAutocomplete('#sku_code', 'SKU_CODE');
            //setupAutocomplete('#search_serial', 'serial_tires_number');

            // ----------------------------------------------------------------------

            // Function สำหรับสร้างตาราง HTML จากข้อมูล JSON
            function buildTable(data) {
                let html = `
                    <hr>
                    <h5 class="mt-4">ผลลัพธ์การค้นหา | Serial: ${data.search_value || '-'}, Ref: ${data.di_ref || '-'}, AR: ${data.ar_name || '-'}</h5>
                `;

                if (data.results && data.results.length > 0) {
                    html += `
                        <div class="table-responsive">
                            <table id="resultsTable" class="display table table-bordered table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>เลขที่เอกสาร</th>
                                        <th>วันที่</th>
                                        <th>ลูกค้า</th>
                                        <th>รหัสสินค้า</th>
                                        <th>หมายเลขยางทั้งหมด</th>
                                        <th>Action</th>  </tr>
                                </thead>
                                <tbody>
                    `;
                    data.results.forEach(row => {
                        // *** ใช้ row.ID ใน data-id สำหรับการแก้ไข ***
                        const updateButton = `
                            <button
                                class="btn btn-warning btn-sm update-btn"
                                data-toggle="modal"
                                data-target="#editModal"
                                data-id="${row.ID || ''}">
                                <i class="fa fa-edit"></i> Update
                            </button>`;

                        html += `
                            <tr>
                                <td>${row.DI_REF || ''}</td>
                                <td>${row.DI_DATE || ''}</td>
                                <td>${row.AR_NAME || ''}</td>
                                <td>${row.SKU_CODE || ''}</td>
                                <td>${row.serial_tires_number || ''}</td>
                                <td>${updateButton}</td>
                            </tr>
                        `;
                    });
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    html += '<p class="mt-3">ไม่พบข้อมูลที่ตรงกับเงื่อนไขที่ค้นหา</p>';
                }

                return html;
            }

            // เมื่อ Submit Form ค้นหา
            $('#searchForm').on('submit', function (e) {
                e.preventDefault(); // ป้องกันการโหลดหน้าใหม่

                let formData = {
                    search_serial: $('#search_serial').val(),
                    sku_code: $('#sku_code').val(), // เพิ่ม SKU_CODE ในการส่งค่าค้นหา
                    di_ref: $('#di_ref').val(), // **DI_REF ไม่มีใน form แต่มีใน logic เก่าจึงเก็บไว้**
                    ar_name: $('#ar_name').val(),
                    month: $('#month').val(),
                    year: $('#year').val()
                };

                // ตรวจสอบว่ามีการป้อนเงื่อนไขค้นหาหลักอย่างน้อยหนึ่งตัวหรือไม่
                let isAnyFieldFilled = formData.search_serial !== '' || formData.sku_code !== '' || formData.ar_name !== '' || formData.month !== 'all' || formData.year !== 'all';

                if (!isAnyFieldFilled) {
                    alert('กรุณาป้อน Serial Number หรือเงื่อนไขอื่น ๆ อย่างน้อยหนึ่งอย่างเพื่อค้นหา');
                    return;
                }

                let resultsContainer = $('#results-container');
                let searchButton = $('#searchButton');

                // 1. แสดงสถานะ Loading
                resultsContainer.html('<div class="loading"><i class="fa fa-spinner fa-spin"></i> กำลังค้นหาข้อมูล...</div>');
                searchButton.prop('disabled', true);

                // 2. ลบ DataTables เดิมทิ้งไปก่อน
                if ($.fn.DataTable.isDataTable('#resultsTable')) {
                    $('#resultsTable').DataTable().destroy();
                }

                // 3. ส่ง AJAX Request
                $.ajax({
                    url: 'fetch_data.php',
                    type: 'POST',
                    data: formData, // ส่งค่าทั้งหมดไป
                    dataType: 'json',
                    success: function (response) {
                        resultsContainer.empty();

                        if (response.error) {
                            resultsContainer.html('<p class="text-danger font-weight-bold">**ข้อผิดพลาด:** ' + response.error + '</p>');
                        } else {
                            resultsContainer.html(buildTable(response));

                            // Initialize DataTables ใหม่
                            if (response.results && response.results.length > 0) {
                                $('#resultsTable').DataTable({
                                    "language": {
                                        "url": "https://cdn.datatables.net/plug-ins/2.0.8/i18n/th.json"
                                    },
                                    "pageLength": 10,
                                    "order": [[1, "desc"]]
                                });
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        resultsContainer.html('<p class="text-danger font-weight-bold">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + xhr.status + ' ' + error + '</p>');
                    },
                    complete: function () {
                        searchButton.prop('disabled', false);
                    }
                });
            });

            // ----------------------------------------------------------------------
            // *** Logic สำหรับ Modal และการแก้ไขข้อมูล ***
            // ----------------------------------------------------------------------

            // 1. ดึงข้อมูลมาแสดงใน Modal เมื่อคลิกปุ่ม Update
            $(document).on('click', '.update-btn', function () {
                const recordId = $(this).data('id');

                $('#editForm')[0].reset();
                $('#edit_status_message').empty();
                $('#saveEditButton').prop('disabled', true).text('กำลังโหลด...');

                $.ajax({
                    url: 'fetch_single_record.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { id: recordId },
                    success: function (response) {
                        if (response.error) {
                            $('#edit_status_message').html('<span class="text-danger">' + response.error + '</span>');
                        } else if (response.data) {
                            // กรอกข้อมูลลงในฟอร์ม Modal
                            $('#edit_id_hidden').val(response.data.ID);
                            $('#edit_di_ref').val(response.data.DI_REF);
                            $('#edit_sku_code').val(response.data.SKU_CODE); // เพิ่ม SKU_CODE
                            $('#edit_sku_name').val(response.data.SKU_NAME); // เพิ่ม SKU_NAME
                            $('#edit_serial_number').val(response.data.serial_tires_number);
                            $('#edit_ar_name').val(response.data.AR_NAME);

                            $('#saveEditButton').prop('disabled', false).text('บันทึกการแก้ไข');
                        } else {
                            $('#edit_status_message').html('<span class="text-danger">ไม่พบข้อมูล Record ที่ต้องการแก้ไข</span>');
                        }
                    },
                    error: function () {
                        $('#edit_status_message').html('<span class="text-danger">เกิดข้อผิดพลาดในการเชื่อมต่อเพื่อดึงข้อมูล</span>');
                        $('#saveEditButton').prop('disabled', false).text('บันทึกการแก้ไข');
                    }
                });
            });

            // 2. ส่งข้อมูลการแก้ไขด้วย AJAX เมื่อ Submit ฟอร์มใน Modal
            $('#editForm').on('submit', function (e) {
                e.preventDefault();

                const formData = $(this).serialize();
                const saveButton = $('#saveEditButton');

                saveButton.prop('disabled', true).text('กำลังบันทึก...');
                $('#edit_status_message').empty();

                $.ajax({
                    url: 'update_record_process.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('#edit_status_message').html('<span class="text-success font-weight-bold">✓ บันทึกข้อมูลสำเร็จ!</span>');
                            setTimeout(function(){
                                $('#editModal').modal('hide');
                                // รีเฟรชตารางโดยการเรียกค้นหาซ้ำ
                                $('#searchForm').submit();
                            }, 1500);
                        } else {
                            $('#edit_status_message').html('<span class="text-danger">✘ บันทึกไม่สำเร็จ: ' + (response.error || 'Unknown error') + '</span>');
                            saveButton.prop('disabled', false).text('บันทึกการแก้ไข');
                        }
                    },
                    error: function () {
                        $('#edit_status_message').html('<span class="text-danger">เกิดข้อผิดพลาดในการส่งข้อมูลไปยังเซิร์ฟเวอร์</span>');
                        saveButton.prop('disabled', false).text('บันทึกการแก้ไข');
                    }
                });
            });


        });
    </script>

    </body>
    </html>

<?php } ?>