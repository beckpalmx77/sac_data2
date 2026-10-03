<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/Header.php');

// 1. Connect to databases
include('config/connect_sqlserver.php'); // $conn
include('config/connect_db.php');        // $conn (MySQL for Presets)

// Check Login
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
}

// ==========================================================================================
// *** 2. Define Screen ID ***
$current_screen_id = "wh_sac_province"; // เปลี่ยน ID เพื่อแยก Preset
// ==========================================================================================

// 3. Fetch Master Data (Province) from SQL Server
// ดึงรายชื่อจังหวัดที่มีอยู่ในระบบ เพื่อมาทำตัวเลือก
$sql_province = "SELECT DISTINCT TRD_PROVINCE 
                 FROM ims_data_sale_sac_all 
                 WHERE AR_CODE LIKE 'SAC%' AND TRD_PROVINCE IS NOT NULL AND TRD_PROVINCE != ''
                 ORDER BY TRD_PROVINCE ASC";

$stmt1 = $conn->prepare($sql_province);
$stmt1->execute();
$provinces = $stmt1->fetchAll(PDO::FETCH_ASSOC);

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
    <title>SAC Customer by Province Report</title>
    <style>
        label { font-size: 1.1rem; cursor: pointer; color: #555; }
        label:hover { color: #000; }
        input[type="checkbox"] { width: 18px; height: 18px; margin-right: 8px; vertical-align: text-bottom; }
        input.select-all { transform: scale(1.3); margin-right: 10px; cursor: pointer; }
        fieldset legend { font-size: 1.2rem; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; }
        .preset-section { background-color: #e3e6f0; border: 1px solid #d1d3e2; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .section-title { font-weight: 800; color: #4e73df; margin-bottom: 10px; }
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
                    <h1 class="h3 mb-0 text-gray-800">รายงานลูกค้า SAC แยกตามจังหวัด</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">SAC Customer Report</li>
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
                                        <div class="col-md-4 mb-2">
                                            <div class="d-flex justify-content-end flex-wrap">
                                                <button type="button" class="btn btn-secondary mr-1 mb-1" id="btn_clear_all"><i class="fas fa-eraser"></i> ล้างค่า</button>

                                                <button type="submit" form="exportForm" class="btn btn-danger mr-1 mb-1"
                                                        formaction="export_process/wh_print_sac_customer_pdf.php"
                                                        formtarget="_blank">
                                                    <i class="fa fa-file-pdf"></i> Export PDF
                                                </button>

                                                <button type="submit" form="exportForm" class="btn btn-success mb-1"
                                                        formaction="export_process/wh_export_sac_customer_csv.php">
                                                    <i class="fa fa-file-excel"></i> Export Excel/CSV
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form method="post" id="exportForm">
                                    <fieldset class="mb-4">
                                        <legend class="text-primary">
                                            <input type="checkbox" class="select-all" id="check_all_province" checked>
                                            เลือกจังหวัด (Province)
                                        </legend>
                                        <div class="row" style="max-height: 400px; overflow-y: auto;">
                                            <?php foreach ($provinces as $row): ?>
                                                <div class="col-md-3 col-sm-4">
                                                    <div class="custom-control custom-checkbox">
                                                        <label>
                                                            <input type="checkbox" class="chk-province" name="provinces[]" value="<?= htmlspecialchars($row['TRD_PROVINCE']) ?>" checked>
                                                            <?= htmlspecialchars($row['TRD_PROVINCE']) ?>
                                                        </label>
                                                    </div>
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
        <?php include('includes/Modal-Logout.php'); include('includes/Footer.php'); ?>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const URL_PROCESS = 'model/preset_process.php';

        // Select All Function
        function bindSelectAll(masterID, childClass) {
            $('#' + masterID).change(function() {
                $('.' + childClass).prop('checked', $(this).prop('checked'));
            });
            $('.' + childClass).change(function() {
                let allChecked = $('.' + childClass + ':checked').length === $('.' + childClass).length;
                $('#' + masterID).prop('checked', allChecked);
            });
        }
        bindSelectAll('check_all_province', 'chk-province');

        // Preset Functions (Copy จากไฟล์เดิมได้เลย แต่เปลี่ยนตัวแปร input name ให้ตรง)
        function loadPreset(preset_id) {
            let preset_name_text = $('#select_preset option:selected').text();
            if(preset_id == "") {
                $('input[type="checkbox"]').prop('checked', true);
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
                    $('.chk-province').first().trigger('change');
                },
                error: function() { alert("ไม่สามารถดึงข้อมูลได้"); }
            });
        }

        $('#btn_save_preset').click(function() {
            let preset_name = $('#new_preset_name').val();
            let screen_id = $('#screen_id_val').val();
            let preset_id = $('#current_preset_id').val();

            if(preset_name.trim() == "") { alert("กรุณาระบุชื่อเงื่อนไข"); return; }

            let formData = $('#exportForm').serializeArray();
            formData.push({name: 'action', value: 'save'});
            formData.push({name: 'preset_name', value: preset_name});
            formData.push({name: 'screen_id', value: screen_id});
            formData.push({name: 'preset_id', value: preset_id});

            if(confirm("ยืนยันการบันทึก?")) {
                $.ajax({
                    url: URL_PROCESS, type: 'POST', data: formData, dataType: 'json',
                    success: function(response) {
                        if(response.status == 'success') { alert(response.message); location.reload(); }
                        else { alert("Error: " + response.message); }
                    }
                });
            }
        });

        $('#select_preset').change(function() { loadPreset($(this).val()); });

        $('#btn_delete_preset').click(function() {
            let preset_id = $('#select_preset').val();
            if(preset_id && confirm("ต้องการลบเงื่อนไขนี้?")) {
                $.ajax({
                    url: URL_PROCESS, type: 'POST',
                    data: { action: 'delete', preset_id: preset_id }, dataType: 'json',
                    success: function(response) { if(response.status == 'success'){ location.reload(); } }
                });
            }
        });

        $('#btn_clear_all').click(function() {
            $('input[type="checkbox"]').prop('checked', false);
            $('#new_preset_name').val('');
            $('#current_preset_id').val('');
            $('#select_preset').val('');
        });
    });
</script>
</body>
</html>