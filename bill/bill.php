<?php
include('../includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
header("Location: index.php");
} else {


?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="img/logo/Logo-01.png" rel="icon">
    <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <!-- Datatable CSS -->
    <!--link href='DataTables/datatables.min.css' rel='stylesheet' type='text/css'-->

    <!-- jQuery Library -->
    <!--script src="jquery-3.3.1.min.js"></script-->

    <!-- Datatable JS -->
    <!--script src="DataTables/datatables.min.js"></script-->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>

</head>
<body id="page-top">

<style>
    body, h1, h2, h3, h4, h5, h6 {
        font-family: 'Prompt', sans-serif !important;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-12">
            <div class="card-header bg-primary text-white">
                <i class="fa fa-tags" aria-hidden="true"></i> เอกสารบิลที่ครบกำหนดการชำระ
            </div>
            <div class="card-body">
                <div class="col-md-12 col-md-offset-2">
                    <div>
                        <table>
                            <tr>
                                <td>
                                    เงื่อนไขในการค้นหา
                                </td>
                                <td>
                                    <input type='text' id='searchByBillDoc'  name='searchByBillDoc' placeholder='เลขที่เอกสาร'>
                                </td>
                                <td>
                                    <input type='text' id='searchByName' name='searchByName' placeholder='ชื่อลูกค้า'>
                                </td>
                                <td>
                                    <input type='text' id='searchBySale' name='searchBySale' placeholder='ชื่อ Sale'>
                                </td>
                                <td> วันที่ครบกำหนดชำระ
                                    <select id='searchByDueDate' name='searchByDueDate' >
                                        <option value='-' selected>-</option>
                                        <?php for ($day=0;$day<=90;$day++) {?>
                                        <option <?php echo "value='" . $day ."'"?>><?php echo $day ?></option>
                                        <?php } ?>
                                        <option value='91'>91++</option>
                                    </select>
                                </td>
                                <td>
                                    วันที่ต้องวางบิล
                                    <input type="text"
                                           id="searchByBillNoteDate"
                                           name="searchByBillNoteDate"
                                           value=""
                                           readonly="true"
                                           placeholder="วันที่ต้องวางบิล">
                                </td>
                                <td>
                                </td>
                                <td>
                                    <a href="bill" class="btn btn-success">Clear</a>
                                </td>
                                <td>
                                    <a href="login" class="btn btn-danger">Logout</a>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <table id='TableRecordList' class='display dataTable'>
                            <thead>
                            <tr>
                                <th>เลขที่เอกสาร</th>
                                <th>วันที่เอกสาร</th>
                                <th>วันที่ Due</th>
                                <th>ชื่อลูกค้า</th>
                                <th>จำนวนเงิน</th>
                                <th>Sale</th>
                                <th>วันที่ต้องวางบิล</th>
                                <th>หมายเหตุ</th>
                                <th>การวางบิล</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>เลขที่เอกสาร</th>
                                <th>วันที่เอกสาร</th>
                                <th>วันที่ Due</th>
                                <th>ชื่อลูกค้า</th>
                                <th>จำนวนเงิน</th>
                                <th>Sale</th>
                                <th>วันที่ต้องวางบิล</th>
                                <th>หมายเหตุ</th>
                                <th>การวางบิล</th>
                            </tr>
                            </tfoot>
                        </table>

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
                                                    <div class="col-sm-6">
                                                        <label for="DI_REF" class="control-label">เลขที่เอกสาร</label>
                                                        <input type="text" class="form-control"
                                                               id="DI_REF" name="DI_REF"
                                                               readonly="true"
                                                               placeholder="เลขที่เอกสาร">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label for="DI_DATE"
                                                               class="control-label">วันที่เอกสาร</label>
                                                        <input type="text" class="form-control"
                                                               id="DI_DATE"
                                                               name="DI_DATE"
                                                               readonly="true"
                                                               placeholder="วันที่เอกสาร">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="col-sm-6">
                                                        <label for="AR_NAME" class="control-label">ชื่อลูกค้า</label>
                                                        <input type="AR_NAME" class="form-control"
                                                               id="AR_NAME" name="AR_NAME"
                                                               readonly="true"
                                                               placeholder="ชื่อลูกค้า">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label for="ARD_DUE_DA"
                                                               class="control-label">วันที่ครบกำหนดชำระ</label>
                                                        <input type="text" class="form-control"
                                                               id="ARD_DUE_DA"
                                                               name="ARD_DUE_DA"
                                                               readonly="true"
                                                               placeholder="วันที่ครบกำหนดชำระ">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="col-sm-6">
                                                        <label for="DI_AMOUNT" class="control-label">จำนวนเงิน</label>
                                                        <input type="text" class="form-control"
                                                               id="DI_AMOUNT" name="DI_AMOUNT"
                                                               readonly="true"
                                                               placeholder="DI_AMOUNT">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="col-sm-6">
                                                        <label for="BILL_DI_REF" class="control-label">เลขที่เอกสารการวางบิล</label>
                                                        <input type="text" class="form-control"
                                                               id="BILL_DI_REF" name="BILL_DI_REF"
                                                               readonly="true"
                                                               placeholder="เลขที่เอกสารการวางบิล">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label for="BILL_DI_DATE"
                                                               class="control-label">วันที่เอกสารการวางบิล</label>
                                                        <input type="text" class="form-control"
                                                               id="BILL_DI_DATE"
                                                               name="BILL_DI_DATE"
                                                               readonly="true"
                                                               placeholder="วันที่เอกสารการวางบิล">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="col-sm-6">
                                                        <label for="BILL_NOTE_DATE"
                                                               class="control-label"><b>* วันที่ต้องวางบิล</b></label>
                                                        <i class="fa fa-calendar"
                                                           aria-hidden="true"></i>
                                                        <input type="text" class="form-control"
                                                               id="BILL_NOTE_DATE"
                                                               name="BILL_NOTE_DATE"
                                                               value=""
                                                               readonly="true"
                                                               placeholder="วันที่ต้องวางบิล">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </form>

                                    <div class="modal-footer">
                                        <input type="hidden" name="id" id="id"/>
                                        <input type="hidden" name="action" id="action" value=""/>
                                        <button type="button" class="btn btn-primary" id="btnSubmit">Save <i class="fa fa-check"></i></button>
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close <i class="fa fa-window-close"></i></button>
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
include('../includes/Modal-Logout.php');
include('../includes/Footer.php');
?>

<script src="js/myadmin.min.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
<script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
<link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.0/jquery.mask.min.js"></script>

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

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>


<!-- Script -->
<script>
    $(document).ready(function () {
        let dataTable = $('#TableRecordList').DataTable({
            'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
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
            'searching': false, // Remove default Search Control
            'ajax': {
                'url': 'bill_ajaxprocess.php',
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
                'data': function (data) {
                    // Read values
                    let bill_doc = $('#searchByBillDoc').val();
                    let due_date = $('#searchByDueDate').val();
                    let name = $('#searchByName').val();
                    let sale = $('#searchBySale').val();
                    let bill_note_date = $('#searchByBillNoteDate').val();
                    let action = "GET_BILL_DATA";

                    // Append to data
                    data.searchByBillDoc = bill_doc;
                    data.searchByDueDate = due_date;
                    data.searchByName = name;
                    data.searchBySale = sale;
                    data.searchByBillNoteDate = bill_note_date;
                    data.action = action;
                }
            },
            'columns': [
                {data: 'DI_REF'},
                {data: 'DI_DATE'},
                {data: 'ARD_DUE_DA'},
                {data: 'AR_NAME'},
                {data: 'DI_AMOUNT'},
                {data: 'SLMN_NAME'},
                {data: 'BILL_NOTE_DATE'},
                {data: 'AR_REMARK'},
                {data: 'detail'}
            ]
        });

        $('#searchByBillDoc').keyup(function () {
            dataTable.draw();
        });

        $('#searchByName').keyup(function () {
            dataTable.draw();
        });

        $('#searchBySale').keyup(function () {
            dataTable.draw();
        });

        $('#searchByDueDate').change(function () {
            dataTable.draw();
        });

        $('#searchByBillNoteDate').change(function () {
            dataTable.draw();
        });

    });
</script>

<script>

    $("#TableRecordList").on('click', '.detail', function () {
        let id = $(this).attr("id");
        //alert(id);
        let formData = {action: "GET_DATA", id: id};
        $.ajax({
            type: "POST",
            url: 'bill_ajaxprocess.php',
            dataType: "json",
            data: formData,
            success: function (response) {
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let id = response[i].id;
                    let DI_REF = response[i].DI_REF;
                    let DI_DATE = response[i].DI_DATE;
                    let AR_NAME = response[i].AR_NAME;
                    let ARD_DUE_DA = response[i].ARD_DUE_DA;
                    let DI_AMOUNT = response[i].DI_AMOUNT;
                    let BILL_DI_REF = response[i].BILL_DI_REF;
                    let BILL_DI_DATE = response[i].BILL_DI_DATE;
                    let BILL_NOTE_DATE = response[i].BILL_NOTE_DATE;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#DI_REF').val(DI_REF);
                    $('#DI_DATE').val(DI_DATE);
                    $('#AR_NAME').val(AR_NAME);
                    $('#ARD_DUE_DA').val(ARD_DUE_DA);
                    $('#DI_AMOUNT').val(DI_AMOUNT);
                    $('#BILL_DI_REF').val(BILL_DI_REF);
                    $('#BILL_DI_DATE').val(BILL_DI_DATE);
                    $('#BILL_NOTE_DATE').val(BILL_NOTE_DATE);
                    $('.modal-title').html("<i class='fa fa-plus'></i> Detail Record");
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

<script>

    $(".btn").click(function(){
        $("#recordModal").modal('hide');
    });

</script>

<script>

    $(document).on('click','#btnSubmit',function(){

        let id = $('#id').val();
        let BILL_NOTE_DATE = $('#BILL_NOTE_DATE').val();
        let formData = {action: "UPDATE", id: id ,bill_note_date: BILL_NOTE_DATE};
/*
        alert(id);
        alert(BILL_NOTE_DATE);
*/

            $.ajax({
                url: 'bill_ajaxprocess.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    if (data==='1') {
                    alertify.success("Complete : บันทึกข้อมูลเรียบร้อยแล้ว");
                    //$('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');
                    $('#TableRecordList').DataTable().ajax.reload();
                 } else {
                        alertify.error("กรุณาป้อนข้อมูลให้ครบถ้วน");
                    }
                }
            })
});

</script>

<script>
    $(document).ready(function () {
        $('#BILL_NOTE_DATE').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            language: "th",
            autoclose: true
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#searchByBillNoteDate').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            language: "th",
            autoclose: true
        });
    });
</script>


</body>

</html>

<?php } ?>
