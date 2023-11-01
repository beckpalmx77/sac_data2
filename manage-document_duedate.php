<?php

include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>

    <html lang="th">
    <body>

    <style>
        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'Prompt', sans-serif !important;
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
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
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
                                            <!--table>
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

                                            <br-->

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

                                            <div id="result"></div>

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
        $(document).ready(function () {
            let cnt_date = $('#cnt_date').val();
            let formData = {action: "GET_DATA_DUE_DATE", sub_action: "GET_MASTER" , cnt_date: cnt_date};
            let dataRecords = $('#TableRecordList').DataTable({
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
                'searching':true,
                'ajax': {
                    'url': 'model/manage_document_duedate_process.php',
                    'data': formData
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
        });
    </script>

    <!--script>
        $(document).ready(function(){
            let dataTable = $('#TableRecordList').DataTable({
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                //'searching': false, // Remove default Search Control
                'ajax': {
                    'url':'model/manage_document_duedate_process.php',
                    'data': function(data){
                        // Read values


                        let duedate = $('#searchByDueDate').val();
                        let name = $('#searchByName').val();
                        // Append to data
                        data.$searchByDueDate = duedate;
                        data.$searchByName = name;



                        //alert($('#searchByDueDate').val());

                        data.action = "GET_DATA_DUE_DATE";
                        data.sub_action = "GET_MASTER";


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
                ]
            });

        });

        $('#searchByName').keyup(function () {
            dataTable.draw();
        });

        $('#searchByDueDate').change(function () {
            dataTable.draw();
        });

    </script-->



    </body>
    </html>

<?php } ?>