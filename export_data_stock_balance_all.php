<?php
include('includes/Header.php');

// 1. Connect to both databases
include('config/connect_sqlserver.php'); // $conn_sqlsvr (for Master Data)
include('config/connect_db.php');        // $conn (MySQL for Presets)

// Check Login
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
}

// ==========================================================================================
// *** 2. Define Screen ID ***
$current_screen_id = "stock_balance";
// ==========================================================================================

// 3. Fetch Master Data from SQL Server
$stmt1 = $conn_sqlsvr->prepare("SELECT DISTINCT ICCAT_CODE, ICCAT_NAME FROM ICCAT ORDER BY ICCAT_CODE ASC");
$stmt1->execute();
$iccats = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $conn_sqlsvr->prepare("SELECT DISTINCT BRN_CODE, BRN_NAME FROM BRAND ORDER BY BRN_CODE ASC");
$stmt2->execute();
$brands = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$stmt3 = $conn_sqlsvr->prepare("SELECT DISTINCT WH_CODE, WH_NAME FROM WAREHOUSE ORDER BY WH_CODE ASC");
$stmt3->execute();
$warehouses = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$stmt4 = $conn_sqlsvr->prepare("SELECT DISTINCT WL_CODE, WL_NAME FROM WARELOCATION ORDER BY WL_CODE ASC");
$stmt4->execute();
$warelocations = $stmt4->fetchAll(PDO::FETCH_ASSOC);

// 4. Fetch Presets from MySQL
$user_id = $_SESSION['alogin'];
$sql_preset = "SELECT preset_id, preset_name 
               FROM USER_EXPORT_PRESETS 
               WHERE user_id = :user_id AND screen_id = :screen_id 
               ORDER BY preset_id DESC";
$stmt_preset = $conn->prepare($sql_preset);
$stmt_preset->execute([':user_id' => $user_id, ':screen_id' => $current_screen_id]);
$presets = $stmt_preset->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Stock Balance Report</title>
    <style>
        label { font-size: 1.1rem; cursor: pointer; color: #555; }
        label:hover { color: #000; }

        /* Checkbox ทั่วไป */
        input[type="checkbox"] { width: 18px; height: 18px; margin-right: 8px; vertical-align: text-bottom; }

        /* Checkbox สำหรับ Select All */
        input.select-all { transform: scale(1.3); margin-right: 10px; cursor: pointer; }

        fieldset legend {
            font-size: 1.2rem;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .btn { font-size: 1rem; }

        .preset-section {
            background-color: #e3e6f0;
            border: 1px solid #d1d3e2;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: 800;
            color: #4e73df;
            margin-bottom: 10px;
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
                    <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active"
                            aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <input type="hidden" id="screen_id_val" value="<?php echo $current_screen_id; ?>">
                <input type="hidden" id="current_preset_id" value="">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-body">

                                <div class="preset-section">
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <span class="section-title"><i class="fas fa-save"></i> บันทึก/เรียกใช้ เงื่อนไขการค้นหา</span>
                                        </div>
                                    </div>

                                    <div class="row align-items-end">

                                        <div class="col-md-4 mb-2">
                                            <label class="font-weight-bold text-gray-800">เลือกเงื่อนไขเดิม:</label>
                                            <div class="input-group">
                                                <select class="form-control" id="select_preset">
                                                    <option value="">-- กรุณาเลือกรายการที่บันทึกไว้ --</option>
                                                    <?php foreach ($presets as $p): ?>
                                                        <option value="<?= $p['preset_id'] ?>"><?= htmlspecialchars($p['preset_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="input-group-append">
                                                    <button class="btn btn-danger" type="button" id="btn_delete_preset" title="ลบข้อมูล"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label class="font-weight-bold text-gray-800">บันทึกเงื่อนไขปัจจุบัน:</label>
                                            <small class="text-muted" id="edit_mode_text" style="display:none;">* กำลังแก้ไข</small>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="new_preset_name" placeholder="ตั้งชื่อเงื่อนไข">
                                                <div class="input-group-append">
                                                    <button class="btn btn-success" type="button" id="btn_save_preset"><i class="fas fa-save"></i> Save</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-2 text-right">
                                            <button type="button" class="btn btn-secondary mr-1" id="btn_clear_all">
                                                <i class="fas fa-eraser"></i> ล้างค่า
                                            </button>

                                            <button type="submit" form="exportForm" class="btn btn-info mr-1"
                                                    formaction="export_process/print_stock_all_pdf"
                                                    formtarget="_blank">
                                                <i class="fa fa-file-pdf"></i> Print PDF
                                            </button>

                                            <button type="submit" form="exportForm" class="btn btn-success">
                                                <i class="fa fa-file-excel"></i> Export Excel
                                            </button>
                                        </div>

                                    </div>
                                </div>

                                <form method="post" id="exportForm" action="export_process/export_data_stock_balance_all_process.php">

                                    <fieldset class="mb-4">
                                        <legend class="text-info">
                                            <input type="checkbox" class="select-all" id="check_all_icc">
                                            หมวดหมู่สินค้า (ICCAT)
                                        </legend>
                                        <div class="row" style="max-height: 200px; overflow-y: auto;">
                                            <?php foreach ($iccats as $row): ?>
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="custom-control custom-checkbox">
                                                        <label>
                                                            <input type="checkbox" class="chk-icc" name="icc_codes[]" value="<?= htmlspecialchars($row['ICCAT_CODE']) ?>">
                                                            <small class="font-weight-bold text-primary"><?= htmlspecialchars($row['ICCAT_CODE']) ?></small>
                                                            - <?= htmlspecialchars($row['ICCAT_NAME']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                    <fieldset class="mb-4">
                                        <legend class="text-success">
                                            <input type="checkbox" class="select-all" id="check_all_brand">
                                            ยี่ห้อสินค้า (BRAND)
                                        </legend>
                                        <div class="row" style="max-height: 200px; overflow-y: auto;">
                                            <?php foreach ($brands as $row): ?>
                                                <div class="col-md-4 col-sm-6">
                                                    <label>
                                                        <input type="checkbox" class="chk-brand" name="brn_codes[]" value="<?= htmlspecialchars($row['BRN_CODE']) ?>">
                                                        <small class="font-weight-bold text-success"><?= htmlspecialchars($row['BRN_CODE']) ?></small>
                                                        - <?= htmlspecialchars($row['BRN_NAME']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                    <fieldset class="mb-4">
                                        <legend class="text-primary">
                                            <input type="checkbox" class="select-all" id="check_all_wh">
                                            คลังสินค้า (WAREHOUSE)
                                        </legend>
                                        <div class="row">
                                            <?php foreach ($warehouses as $row): ?>
                                                <div class="col-md-4 col-sm-6">
                                                    <label>
                                                        <input type="checkbox" class="chk-wh" name="wh_codes[]" value="<?= htmlspecialchars($row['WH_CODE']) ?>">
                                                        <small class="font-weight-bold text-info"><?= htmlspecialchars($row['WH_CODE']) ?></small>
                                                        - <?= htmlspecialchars($row['WH_NAME']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                    <fieldset class="mb-4">
                                        <legend class="text-danger">
                                            <input type="checkbox" class="select-all" id="check_all_wl">
                                            ตำแหน่งจัดเก็บ (LOCATION)
                                        </legend>
                                        <div class="row" style="max-height: 200px; overflow-y: auto;">
                                            <?php foreach ($warelocations as $row): ?>
                                                <div class="col-md-4 col-sm-6">
                                                    <label>
                                                        <input type="checkbox" class="chk-wl" name="wl_codes[]" value="<?= htmlspecialchars($row['WL_CODE']) ?>">
                                                        <small class="font-weight-bold text-danger"><?= htmlspecialchars($row['WL_CODE']) ?></small>
                                                        - <?= htmlspecialchars($row['WL_NAME']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </fieldset>

                                </form>
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

<script>
    $(document).ready(function() {
        const URL_PROCESS = 'model/preset_process.php';

        // =======================================================
        // *** ส่วนจัดการ SELECT ALL ***
        // =======================================================
        function bindSelectAll(masterID, childClass) {
            $('#' + masterID).change(function() {
                let isChecked = $(this).prop('checked');
                $('.' + childClass).prop('checked', isChecked);
            });

            $('.' + childClass).change(function() {
                let allChecked = $('.' + childClass + ':checked').length === $('.' + childClass).length;
                $('#' + masterID).prop('checked', allChecked);
            });
        }
        bindSelectAll('check_all_icc', 'chk-icc');
        bindSelectAll('check_all_brand', 'chk-brand');
        bindSelectAll('check_all_wh', 'chk-wh');
        bindSelectAll('check_all_wl', 'chk-wl');


        // =======================================================
        // *** ส่วนจัดการ PRESET ***
        // =======================================================
        function loadPreset(preset_id) {
            let preset_name_text = $('#select_preset option:selected').text();

            if(preset_id == "") {
                $('input[type="checkbox"]').prop('checked', false);
                $('#new_preset_name').val('');
                $('#current_preset_id').val('');
                $('#edit_mode_text').hide();
                return;
            }

            $.ajax({
                url: URL_PROCESS,
                type: 'POST',
                data: { action: 'load', preset_id: preset_id },
                dataType: 'json',
                success: function(data) {
                    $('#new_preset_name').val(preset_name_text);
                    $('#current_preset_id').val(preset_id);
                    $('#edit_mode_text').show();
                    $('input[type="checkbox"]').prop('checked', false);

                    $.each(data, function(inputName, valuesArray) {
                        if(Array.isArray(valuesArray)) {
                            $.each(valuesArray, function(index, val) {
                                $('input[name="' + inputName + '[]"][value="' + val + '"]').prop('checked', true);
                            });
                        }
                    });

                    // Update Select All Checkboxes
                    $('.chk-icc').first().trigger('change');
                    $('.chk-brand').first().trigger('change');
                    $('.chk-wh').first().trigger('change');
                    $('.chk-wl').first().trigger('change');
                },
                error: function() {
                    alert("ไม่สามารถดึงข้อมูลได้");
                }
            });
        }

        // 1. SAVE / UPDATE
        $('#btn_save_preset').click(function() {
            let preset_name = $('#new_preset_name').val();
            let screen_id = $('#screen_id_val').val();
            let preset_id = $('#current_preset_id').val();

            if(preset_name.trim() == "") {
                alert("กรุณาระบุชื่อเงื่อนไข");
                $('#new_preset_name').focus();
                return;
            }

            // Check only regular items (not select-all checkboxes)
            if ($('input[type=checkbox]:checked').not('.select-all').length == 0) {
                if(!confirm("คุณยังไม่ได้เลือกเงื่อนไขใดๆ ต้องการบันทึกเป็นค่าว่างหรือไม่?")) {
                    return;
                }
            }

            let formData = $('#exportForm').serializeArray();
            formData.push({name: 'action', value: 'save'});
            formData.push({name: 'preset_name', value: preset_name});
            formData.push({name: 'screen_id', value: screen_id});
            formData.push({name: 'preset_id', value: preset_id});

            let msg = preset_id ? "ยืนยันการแก้ไขเงื่อนไขเดิม?" : "ยืนยันการบันทึกเงื่อนไขใหม่?";

            if(confirm(msg)) {
                $.ajax({
                    url: URL_PROCESS,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if(response.status == 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        alert("Connection Error");
                    }
                });
            }
        });

        // 2. LOAD
        $('#select_preset').change(function() {
            let preset_id = $(this).val();
            loadPreset(preset_id);
        });

        // 3. DELETE
        $('#btn_delete_preset').click(function() {
            let preset_id = $('#select_preset').val();
            if(preset_id == "") {
                alert("กรุณาเลือกรายการที่ต้องการลบ");
                return;
            }

            let preset_text = $('#select_preset option:selected').text();
            if(confirm("คุณต้องการลบเงื่อนไข: '" + preset_text + "' ใช่หรือไม่?")) {
                $.ajax({
                    url: URL_PROCESS,
                    type: 'POST',
                    data: { action: 'delete', preset_id: preset_id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status == 'success'){
                            alert("ลบข้อมูลเรียบร้อย");
                            location.reload();
                        }
                    },
                    error: function() {
                        alert("Error deleting data");
                    }
                });
            }
        });

        // 4. CLEAR
        $('#btn_clear_all').click(function() {
            $('input[type="checkbox"]').prop('checked', false);
            $('#new_preset_name').val('');
            $('#current_preset_id').val('');
            $('#edit_mode_text').hide();
            $('#select_preset').val('');
        });

    });
</script>

</body>
</html>