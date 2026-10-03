<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit();
}

$and_sale = "";
if ($_SESSION['account_type'] === "sale_user") {
    $and_sale = " AND SALE_NAME = '" . addslashes($_SESSION['first_name']) . "'";
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ระบบจองยาง</title>

    <!-- Bootstrap, DataTables, Select2 -->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css">
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            padding: 6px 12px;
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
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <section class="container-fluid">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label for="customerCode" class="form-label">ชื่อร้านค้า</label>
                                            <input type="hidden" name="customerCode" id="customerCode" readonly>
                                            <input type="hidden" name="customerNameText" id="customerNameText" readonly>

                                            <select id="customerName" name="customerName" class="form-control"
                                                    style="width:100%;">
                                                <option value="" selected disabled>-- ชื่อร้านค้า --</option>
                                                <?php
                                                $sql1 = "SELECT DISTINCT(AR_CODE), AR_NAME , SALE_NAME FROM ims_data_sale_sac_all WHERE 1=1 " . $and_sale . " ORDER BY AR_CODE ASC";
                                                $query1 = $conn->prepare($sql1);
                                                $query1->execute();
                                                $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                                if ($query1->rowCount() > 0) {
                                                    foreach ($results1 as $result1) {
                                                        echo '<option value="' . htmlentities($result1->AR_CODE) . '" data-name="' . htmlentities($result1->AR_NAME) . '">' . htmlentities($result1->AR_NAME) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button" name="btnAdd" id="btnAdd"
                                                    class="btn btn-primary btn-sm w-100">
                                                เพิ่มการจองยางตามลูกค้า <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button" name="btnCopy" id="btnCopy"
                                                    class="btn btn-outline-primary btn-sm w-100">
                                                คัดลอกลิงก์ร้านค้า <i class="fa fa-link"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button" name="btnSummary" id="btnSummary"
                                                    class="btn btn-info btn-sm w-100">
                                                สรุปยอด จอง <i class="fa fa-info-circle"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button" name="btnSummary2" id="btnSummary2"
                                                    class="btn btn-success btn-sm w-100">
                                                สรุปยอด จอง - ขาย <i class="fa fa-info-circle"></i>
                                            </button>
                                        </div>

                                    </div>

                                    <br>

                                    <div class="col-md-12">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>เลขที่เอกสาร</th>
                                                <th>วันที่</th>
                                                <th>ชื่อร้านค้า</th>
                                                <th>จังหวัด</th>
                                                <th>ชื่อเซลล์</th>
                                                <th>Action</th>
                                                <th>Action</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                        </table>
                                        <div id="result"></div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
<?php
include('includes/Footer.php');
?>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/Modal-Logout.php');
?>
<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<!-- JS -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/v11/bootbox.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>


<script>
    // รีโหลด DataTable เมื่อผู้ใช้กลับมาที่แท็บนี้
    document.addEventListener("visibilitychange", function () {
        if (document.visibilityState === "visible") {
            $('#TableRecordList').DataTable().ajax.reload(null, false); // false = ไม่ reset pagination
        }
    });
</script>

<script>
    $(document).ready(function () {
        // Init Select2
        $('#customerName').select2({
            placeholder: "-- ชื่อร้านค้า --",
            allowClear: true
        });

        let formData = {action: "GET_DOC_ID", sub_action: "GET_MASTER"};
        let dataRecords = $('#TableRecordList').DataTable({
            'lengthMenu': [[5, 10, 20, 50, 100, 500, 1000], [5, 10, 20, 50, 100, 500, 1000]],
            'language': {
                search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: {previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป'}
            },
            'processing': true,
            'serverSide': true,
            'autoWidth': true,
            'searching': true,
            <?php if ($_SESSION['deviceType'] !== 'computer') echo "'scrollX': true,"; ?>
            'serverMethod': 'post',
            'ajax': {
                'url': 'model/manage_reserve_product_event_header_process.php',
                'data': formData
            },
            'columns': [
                {data: 'DOC_ID'},
                {data: 'DOC_DATE'},
                {data: 'CUSTOMER_NAME'},
                {data: 'PROVINCE'},
                {data: 'SALE_NAME'},
                {data: 'detail'},
                {data: 'update'},
                {data: 'delete'},
            ]
        });

        $("#btnAdd").click(function () {
            const selectedValue = $("#customerName").val();
            const selectedText = $("#customerName option:selected").text();

            if (!selectedValue) {
                alertify.error("กรุณาเลือกร้านค้าก่อน");
                $("#customerName").focus();
                return;
            }

            $("#customerNameText").val(selectedText);
            $("#customerCode").val(selectedValue);

            const url = "manage_reserve_product_event.php?customerCode=" + encodeURIComponent(selectedValue) +
                "&customerName=" + encodeURIComponent(selectedText);
            window.open(url, "_blank");
        });

        $("#TableRecordList").on('click', '.detail', function () {
            let url = "manage_reserve_product_event_show.php?" + $.param({
                doc_id: $(this).attr("id"),
                DOC_DATE: $(this).data("doc_date"),
                CUSTOMER_CODE: $(this).data("customer_code"),
                CUSTOMER_NAME: $(this).data("customer_name"),
                PHONE: $(this).data("phone"),
                SALE_NAME: $(this).data("sale_name"),
                PROVINCE: $(this).data("province")
            });
            window.open(url, "_blank");
        });

        $("#TableRecordList").on('click', '.update', function () {
            let url = "manage_reserve_product_event_sale.php?" + $.param({
                doc_id: $(this).attr("id"),
                DOC_DATE: $(this).data("doc_date"),
                CUSTOMER_CODE: $(this).data("customer_code"),
                CUSTOMER_NAME: $(this).data("customer_name"),
                PHONE: $(this).data("phone"),
                SALE_NAME: $(this).data("sale_name"),
                PROVINCE: $(this).data("province")
            });
            window.open(url, "_blank");
        });

        $("#TableRecordList").on('click', '.delete', function () {
            if (!confirm("ยืนยันการลบรายการนี้?")) return;

            let id = $(this).attr("id");

            //alert(id);

            $.ajax({
                url: "model/manage_reserve_product_event_header_process.php",
                type: "POST",
                data: {action: "DELETE" , id: id },
                success: function (response) {
                    alert("ลบเรียบร้อยแล้ว");
                    // รีเฟรช DataTable
                    $("#TableRecordList").DataTable().ajax.reload();
                },
                error: function () {
                    alert("เกิดข้อผิดพลาดในการลบข้อมูล");
                }
            });
        });

    });
</script>

<script>
    document.getElementById("btnSummary").addEventListener("click", function () {
        window.open("manage_reserve_product_event_summary", "_blank");
    });
</script>

<script>
    document.getElementById("btnSummary2").addEventListener("click", function () {
        window.open("manage_reserve_product_event_data", "_blank");
    });
</script>

<script>

    $("#btnCopy").click(function () {
        console.log("btnCopy clicked");

        const selectedValue = $("#customerName").val();
        const selectedText = $("#customerName option:selected").text();

        if (!selectedValue) {
            alertify.error("กรุณาเลือกร้านค้าก่อน");
            $("#customerName").focus();
            return;
        }

        const urlPrefix = "https://syycp.com/sac_reserve/";
        const url = urlPrefix + "manage_reserve_product_event.php?customerCode=" + encodeURIComponent(selectedValue) +
            "&customerName=" + encodeURIComponent(selectedText);

        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () {
                alertify.success("คัดลอกลิงก์เรียบร้อยแล้ว:\n" + url);
                console.log("Copied: " + url);
            }).catch(function () {
                alertify.error("เกิดข้อผิดพลาดในการคัดลอกลิงก์");
            });
        } else {
            // fallback วิธีเก่าใช้ textarea + execCommand
            const $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(url).select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    alertify.success("คัดลอกลิงก์เรียบร้อยแล้ว:\n" + url);
                    console.log("Copied (fallback): " + url);
                } else {
                    alertify.error("ไม่สามารถคัดลอกลิงก์ได้");
                }
            } catch (err) {
                alertify.error("เกิดข้อผิดพลาด: " + err);
            }

            $temp.remove();
        }
    });


</script>

</body>
</html>
