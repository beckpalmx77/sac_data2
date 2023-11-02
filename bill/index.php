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
<body>

<style>
    body, h1, h2, h3, h4, h5, h6 {
        font-family: 'Prompt', sans-serif !important;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-12">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            </div>
            <div class="card-body">

                <div class="col-md-12 col-md-offset-2">

                    <div>
                        <!-- Custom Filter -->
                        <table>
                            <tr>
                                <td>
                                    <input type='text' id='searchByName' placeholder='ชื่อลูกค้า'>
                                </td>
                                <td>
                                    <input type='text' id='searchBySale' placeholder='ชื่อ Sale'>
                                </td>
                                <td> วันที่ครบกำหนดชำระ
                                    <select id='searchByDueDate'>
                                        <option value='7' selected>7</option>
                                        <?php for ($day=-31;$day<=60;$day++) {?>
                                        <option <?php echo "value='" . $day ."'"?>><?php echo $day ?></option>
                                        <?php } ?>
                                        <option value='32'>31++</option>
                                    </select>
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
                                <th>หมายเหตุ</th>
                                <th>การวางบิล</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    let duedate = $('#searchByDueDate').val();
                    let name = $('#searchByName').val();
                    let sale = $('#searchBySale').val();
                    let action = "GET_BILL_DATA";


                    // Append to data
                    data.searchByDueDate = duedate;
                    data.searchByName = name;
                    data.searchBySale = sale;
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
                {data: 'AR_REMARK'},
                {data: 'detail'}
            ]
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
                    let main_menu_id = response[i].main_menu_id;
                    let label = response[i].label;
                    let link = response[i].link;
                    let icon = response[i].icon;
                    let data_target = response[i].data_target;
                    let aria_controls = response[i].aria_controls;
                    let privilege = response[i].privilege;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#main_menu_id').val(main_menu_id);
                    $('#label').val(label);
                    $('#link').val(link);
                    $('#icon').val(icon);
                    $('#data_target').val(data_target);
                    $('#aria_controls').val(aria_controls);
                    $('#privilege').val(privilege);
                    $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
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

</body>

</html>
