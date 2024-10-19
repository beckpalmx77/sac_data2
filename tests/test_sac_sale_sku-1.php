<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Fetch with AJAX</title>
    <!-- เพิ่ม Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-4">
    <h2>ค้นหาข้อมูลการขาย</h2>

    <!-- ฟอร์มตัวเลือก -->
    <form id="filterForm" class="row g-3">
        <div class="col-md-3">
            <label for="year" class="form-label">เลือกปี</label>
            <select id="year" name="year" class="form-select">
                <option value="2024">2024</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="month" class="form-label">เลือกเดือน</label>
            <select id="month" name="month" class="form-select">
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="9">September</option>
                <!-- เพิ่มเดือนตามต้องการ -->
            </select>
        </div>
        <div class="col-md-3">
            <label for="category" class="form-label">หมวดหมู่สินค้า</label>
            <select id="category" name="category" class="form-select">
                <option value="ยางเล็ก">ยางเล็ก</option>
                <option value="TBR">TBR</option>
                <option value="TBB">TBB</option>
                <!-- เพิ่มหมวดหมู่สินค้า -->
            </select>
        </div>
        <div class="col-md-3">
            <label for="salesperson" class="form-label">ชื่อพนักงานขาย</label>
            <select id="salesperson" name="salesperson" class="form-select">
                <option value="จิรกร (เตี้ยม)">จิรกร (เตี้ยม)</option>
            </select>
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">ค้นหา</button>
        </div>
    </form>

    <!-- พื้นที่แสดงผล -->
    <div id="result" class="mt-4"></div>
</div>

<!-- JavaScript สำหรับจัดการ AJAX -->
<script>
    $(document).ready(function(){
        // เมื่อฟอร์มถูกส่ง
        $('#filterForm').on('submit', function(e){
            e.preventDefault(); // ป้องกันการรีเฟรชหน้า

            // ดึงค่าจากฟอร์ม
            let year = $('#year').val();
            let month = $('#month').val();
            let category = $('#category').val();
            let salesperson = $('#salesperson').val();

            // ส่งข้อมูลด้วย AJAX ไปที่ PHP
            $.ajax({
                url: 'fetch_sac_sale_data.php',
                type: 'POST',
                data: {
                    DI_YEAR: year,
                    DI_MONTH: month,
                    SKU_CAT: category,
                    SALE_NAME: salesperson
                },
                success: function(response){
                    // นำผลลัพธ์ที่ได้มาแสดงใน div ที่ชื่อว่า result
                    $('#result').html(response);
                }
            });
        });
    });
</script>
</body>
</html>
