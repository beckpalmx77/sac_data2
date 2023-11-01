<!doctype html>
<html>
<head>
    <title>How to add Custom Filter in DataTable - AJAX and PHP</title>
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
                                <td> วันที่ครบกำหนดชำระ
                                    <select id='searchByDueDate'>
                                        <option value='7' selected>7</option>
                                        <?php for ($day=1;$day<=31;$day++) {?>
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

                    // Append to data
                    data.searchByDueDate = duedate;
                    data.searchByName = name;
                }
            },
            'columns': [
                {data: 'DI_REF'},
                {data: 'DI_DATE'},
                {data: 'ARD_DUE_DA'},
                {data: 'AR_NAME'},
                {data: 'DI_AMOUNT'},
                {data: 'SLMN_NAME'},
                {data: 'AR_REMARK'}
            ]
        });

        $('#searchByName').keyup(function () {
            dataTable.draw();
        });

        $('#searchByDueDate').change(function () {
            dataTable.draw();
        });
    });
</script>
</body>

</html>
