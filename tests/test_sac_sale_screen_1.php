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
        <tr id="headerRow">
            <th>ผลรวมทั้งหมด</th>
            <!-- คอลัมน์วันที่จะถูกเพิ่มที่นี่ -->
        </tr>
        </thead>
        <tbody>
        <tr>
            <td id="totalAmount"></td>
            <td id="salesData"></td>
        </tr>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#filterForm').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการรีเฟรชหน้า

            const month = parseInt($('#month').val());
            const year = parseInt($('#year').val());
            const saleName = $('#saleName').val();

            // สร้างคอลัมน์วันที่อัตโนมัติ
            $('#headerRow').find('th:gt(0)').remove(); // ล้างคอลัมน์วันที่เก่า
            const daysInMonth = new Date(year, month, 0).getDate(); // หาจำนวนวันในเดือนนั้น
            for (let day = 1; day <= daysInMonth; day++) {
                $('#headerRow').append(`<th>${day}</th>`); // เพิ่มวันที่ใน header
            }

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
                    $('#salesData').empty(); // ล้างข้อมูลยอดขายเก่า
                    let totalAmount = 0; // ตัวแปรเก็บผลรวม
                    let salesByDay = new Array(daysInMonth).fill(0); // สร้างอาร์เรย์สำหรับเก็บยอดขายตามวัน

                    // จัดเก็บยอดขายตามวัน
                    $.each(response, function(index, item) {
                        const dayIndex = item.DI_DAY - 1; // วันที่ในฐานข้อมูล
                        salesByDay[dayIndex] = parseFloat(item.TRD_AMOUNT_PRICE); // บันทึกยอดขาย
                    });

                    // แสดงยอดขายในตาราง
                    salesByDay.forEach(sale => {
                        $('#salesData').append(`<td>${sale.toFixed(2)}</td>`); // เพิ่มยอดขายในข้อมูล
                        totalAmount += sale; // คำนวณผลรวม
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
