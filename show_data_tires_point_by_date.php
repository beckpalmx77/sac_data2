<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    include 'config/connect_db.php';

    isset( $_POST['doc_date_start'] ) ? $doc_date_start = $_POST['doc_date_start'] : $doc_date_start = "";
    isset( $_POST['doc_date_to'] ) ? $doc_date_to = $_POST['doc_date_to'] : $doc_date_to = "";

    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>สรุปคะแนนลูกค้า</title>
        <link rel="icon" href="img/favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

        <link href="css/spinner_over.css" rel="stylesheet"/>


        <style>

            .container-fluid {
                max-width: 100%;
            }


            /* กำหนดให้คอลัมน์ที่ 1 และ 2 อยู่คงที่ */
            .table-responsive {
                position: relative;
            }

            th:nth-child(1), td:nth-child(1) {
                position: sticky;
                left: 0;
                background-color: white; /* กำหนดสีพื้นหลังเพื่อไม่ให้ทับกับแถวอื่น */
                z-index: 999; /* กำหนดความสำคัญให้สูง */
            }

            /*  ปรับให้เลื่อนไปทางขวาตามความกว้างของคอลัมน์แรก
                    th:nth-child(2), td:nth-child(2) {
                        position: sticky;
                        left: 250px;
                        background-color: white;
                        z-index: 998;
                    }
            */
            /* สำหรับการจัดแนวขวา */
            .text-right {
                text-align: right;
            }

            /* ป้องกันไม่ให้ข้อมูลตกบรรทัด */
            th, td {
                white-space: nowrap;
            }

            /* ปรับขนาดฟอนต์ */
            th, td {
                font-size: 14px;
                padding: 4px 8px;
            }
        </style>

    </head>
    <body>
    <input type="hidden" name="doc_date_start" id="doc_date_start" value="<?php echo $doc_date_start; ?>">
    <input type="hidden" name="doc_date_to" id="doc_date_to" value="<?php echo $doc_date_to; ?>">

    <div id="spinner" class="text-center my-3" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div class="container-fluid mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">สรุปคะแนนลูกค้า ตาม Size ยาง วันที่  : <?php echo $doc_date_start . " ถึง " . $doc_date_to?></h3>
            <button class="btn btn-secondary" onclick="window.close();">ปิดหน้าจอ</button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" id="salesTable">
                <thead>
                <tr>
                    <th>วันที่</th>
                    <th>เลขที่เอกสาร</th>
                    <th>รหัสลูกค้า</th>
                    <th>ชื่อลูกค้า</th>
                    <th>รหัสสินค้า</th>
                    <th>รายละเอียดสินค้า</th>
                    <th>ประเภทร้าน</th>
                    <th>ขอบ</th>
                    <th class="text-right">จำนวน</th>
                    <th class="text-right">คะแนนร้านทั่วไป</th>
                    <th class="text-right">คะแนนรวมร้านทั่วไป</th>
                    <th class="text-right">คะแนน Shop</th>
                    <th class="text-right">คะแนนรวม Shop</th>
                    <th class="text-right">คะแนนรวมทั้งหมด</th>
                </tr>
                </thead>
                <tbody>
                <!-- ข้อมูลจะถูกเพิ่มที่นี่โดย AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <script>

        $(document).ready(function () {
            function loadSalesData() {
                // แสดง spinner ก่อนเริ่มการโหลด
                $('#spinner').show();

                let doc_date_start = $('#doc_date_start').val();
                let doc_date_to = $('#doc_date_to').val();
                let title = 'สรุปคะแนนลูกค้า';

                $.ajax({
                    url: 'model/fetch_data_tires_point_by_date.php',
                    type: 'POST',
                    data: {
                        doc_date_start: doc_date_start,
                        doc_date_to: doc_date_to,
                        //shop_type:shop_type,
                    },
                    dataType: 'json',
                    success: function (data) {
                        let tbody = '';
                        $.each(data, function (index, item) {
                            tbody += '<tr>';
                            tbody += '<td>' + item.DI_DATE + '</td>';
                            tbody += '<td>' + item.DI_REF + '</td>';
                            tbody += '<td>' + item.AR_CODE + '</td>';
                            tbody += '<td>' + item.AR_NAME + '</td>';
                            tbody += '<td>' + item.SKU_CODE + '</td>';
                            tbody += '<td>' + item.SKU_NAME + '</td>';
                            tbody += '<td>' + item.SHOP_TYPE + '</td>';
                            tbody += '<td>' + item.TIRES_EDGE + '</td>';
                            tbody += '<td class="text-right">' + formatNumber(item.TRD_QTY) + '</td>';
                            tbody += '<td class="text-right">' + formatNumber(item.TRD_U_POINT) + '</td>';
                            tbody += '<td class="text-right">' + formatNumber(item.TRD_U_POINT_TOTAL) + '</td>';
                            tbody += '<td class="text-right">' + formatNumber(item.TRD_S_POINT) + '</td>';
                            tbody += '<td class="text-right">' + formatNumber(item.TRD_S_POINT_TOTAL) + '</td>';
                            tbody += '<td class="text-right">' + formatNumber(item.TOTAL_POINTS) + '</td>';
                            tbody += '</tr>';
                        });
                        $('#salesTable tbody').html(tbody);

                        // ทำลาย DataTables ก่อนถ้ามีการใช้งานอยู่แล้ว
                        if ($.fn.DataTable.isDataTable('#salesTable')) {
                            $('#salesTable').DataTable().destroy();
                        }

                        // เรียกใช้งาน DataTables
                        $('#salesTable').DataTable({
                            "ordering": false, // ปิดการเรียงลำดับข้อมูลใน DataTables
                            "lengthMenu": [[12, 30, 50, 100], [12, 30, 50, 100]],
                            "pageLength": 12,
                            dom: 'Bfrtip',
                            buttons: [
                                {
                                    extend: 'excelHtml5',
                                    title: title,
                                    text: 'Export Excel'
                                }
                            ]
                        });

                        // ซ่อน spinner เมื่อโหลดข้อมูลเสร็จ
                        $('#spinner').hide();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error loading sales data: ', textStatus, errorThrown);
                        // ซ่อน spinner ในกรณีเกิดข้อผิดพลาด
                        $('#spinner').hide();
                    }
                });
            }

            // ดักฟังการเปลี่ยนแปลง select option แล้ว refresh ข้อมูล
            //$('#filterShop_type, #filterMonth').on('change', function () {
            /*
                        $('#filterShop_type').on('change', function () {
                            loadSalesData(); // โหลดข้อมูลใหม่ตามที่เลือก
                        });

             */

            function formatNumber(num) {
                return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            loadSalesData();
        });


    </script>

    </body>
    </html>

<?php } ?>