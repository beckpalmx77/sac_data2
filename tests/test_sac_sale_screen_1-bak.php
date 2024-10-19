<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แสดงข้อมูลขายตามวัน</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>ข้อมูลการขายประจำวัน</h2>
    <form id="filterForm">
        <div class="row mb-3">
            <div class="col">
                <label for="month" class="form-label">เลือกเดือน</label>
                <select id="month" name="month" class="form-select">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="9">September</option>
                </select>
            </div>
            <div class="col">
                <label for="year" class="form-label">เลือกปี</label>
                <select id="year" name="year" class="form-select">
                    <option value="2024">2024</option>
                </select>
            </div>
            <div class="col">
                <select class="form-select" id="saleName">
                    <option value="จิรกร (เตี้ยม)">จิรกร (เตี้ยม)</option>
                    <!-- เพิ่มตัวเลือกอื่น ๆ ตามที่ต้องการ -->
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>วัน</th>
            <th>ยอดขาย (ราคา)</th>
        </tr>
        </thead>
        <tbody id="resultTableBody">
        <!-- ข้อมูลจะถูกแทรกที่นี่ -->
        </tbody>
        <tfoot>
        <tr>
            <th>ผลรวมทั้งหมด</th>
            <th id="totalAmount"></th>
        </tr>
        </tfoot>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#filterForm').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการรีเฟรชหน้า

            const month = $('#month').val();
            const year = $('#year').val();
            const saleName = $('#saleName').val();

            $.ajax({
                type: 'POST',
                url: 'get_data_test1.php', // เปลี่ยนเป็น path ของไฟล์ PHP ของคุณ
                data: {
                    month: month,
                    year: year,
                    SALE_NAME: saleName
                },
                dataType: 'json',
                success: function(response) {
                    $('#resultTableBody').empty(); // ล้างข้อมูลเดิม
                    let totalAmount = 0; // ตัวแปรเก็บผลรวม

                    // แสดงข้อมูลใหม่
                    $.each(response, function(index, item) {
                        $('#resultTableBody').append(`
                                <tr>
                                    <td>${item.DI_DAY}</td>
                                    <td>${item.TRD_AMOUNT_PRICE}</td>
                                </tr>
                            `);
                        totalAmount += parseFloat(item.TRD_AMOUNT_PRICE); // คำนวณผลรวม
                    });

                    // แสดงผลรวมทั้งหมด
                    $('#totalAmount').text(totalAmount.toFixed(2)); // แสดงผลรวมใน format 2 ตำแหน่งหลังจุด
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });
</script>
</body>
</html>
