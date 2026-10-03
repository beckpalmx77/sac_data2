<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลร้านค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h1 class="text-center mb-4">แบบฟอร์มข้อมูลร้านค้า</h1>
    <form method="POST" action="process_form.php">
        <!-- 1. รายละเอียดร้านค้า -->
        <h4>1. รายละเอียดร้านค้า</h4>
        <div class="mb-3">
            <label for="company_name" class="form-label">ชื่อบริษัท</label>
            <input type="text" class="form-control" id="company_name" name="company_name" required>
        </div>
        <div class="mb-3">
            <label for="owner_name" class="form-label">ชื่อเจ้าของกิจการ</label>
            <input type="text" class="form-control" id="owner_name" name="owner_name" required>
        </div>
        <div class="mb-3">
            <label for="contact_person" class="form-label">ชื่อบุคคลติดต่อ</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">ที่อยู่</label>
            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">สถานะ / ตัวแทนยี่ห้อ</label>
            <input type="text" class="form-control" id="status" name="status" required>
        </div>

        <!-- 2. สินค้าหลักในร้าน -->
        <h4>2. สินค้าหลักในร้าน</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                <tr>
                    <th>ประเภท</th>
                    <th>ยี่ห้อ</th>
                    <th>ปริมาณขายต่อเดือน (เฉลี่ย)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>TBR</td>
                    <td><input type="text" class="form-control" name="tbr_brand[]" placeholder="ยี่ห้อ"></td>
                    <td><input type="number" class="form-control" name="tbr_quantity[]" placeholder="จำนวน"></td>
                </tr>
                <tr>
                    <td>LTR</td>
                    <td><input type="text" class="form-control" name="ltr_brand[]" placeholder="ยี่ห้อ"></td>
                    <td><input type="number" class="form-control" name="ltr_quantity[]" placeholder="จำนวน"></td>
                </tr>
                <!-- สามารถเพิ่มแถวเพิ่มเติมได้ -->
                </tbody>
            </table>
        </div>

        <!-- 3. คาดการณ์การขาย -->
        <h4>3. คาดการณ์การขายในเดือน</h4>
        <div class="mb-3">
            <label for="forecast_month" class="form-label">เดือนที่คาดการณ์</label>
            <input type="month" class="form-control" id="forecast_month" name="forecast_month">
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                <tr>
                    <th>ประเภท</th>
                    <th>ยี่ห้อ</th>
                    <th>จำนวนเส้น</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>TBR</td>
                    <td><input type="text" class="form-control" name="forecast_tbr_brand[]" placeholder="ยี่ห้อ"></td>
                    <td><input type="number" class="form-control" name="forecast_tbr_quantity[]" placeholder="จำนวน"></td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- 4. ข้อมูลอื่นๆ -->
        <h4>4. ข้อมูลอื่นๆ</h4>
        <div class="mb-3">
            <label for="financial_status" class="form-label">สถานะการเงิน</label>
            <input type="text" class="form-control" id="financial_status" name="financial_status" placeholder="ดี / ไม่ดี">
        </div>

        <!-- 5. ข้อมูลเพิ่มเติม -->
        <h4>5. ข้อมูลเพิ่มเติม</h4>
        <div class="mb-3">
            <textarea class="form-control" id="additional_info" name="additional_info" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
    </form>
</div>
</body>
</html>

