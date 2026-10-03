<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Contact Entry</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>บันทึกข้อมูลการติดต่อ</h2>
    <form id="contactForm" enctype="multipart/form-data">
        <div class="form-group">
            <label>วันที่</label>
            <input type="date" class="form-control" name="contact_date" required>
        </div>
        <div class="form-group">
            <label>ชื่อลูกค้า</label>
            <input type="text" class="form-control" name="customer_name" required>
        </div>
        <div class="form-group">
            <label>จังหวัด</label>
            <input type="text" class="form-control" name="province">
        </div>
        <div class="form-group">
            <label>วัตถุประสงค์</label>
            <select class="form-control" name="purpose">
                <option>ใช้ดาต้าลูกค้าเดิม</option>
                <option>แนะนำตัว (ลูกค้าใหม่)</option>
                <option>เข้าพบลูกค้าประจำเดือน</option>
                <option>เสนอโปรโมชั่นใหม่</option>
                <option>อื่น ๆ</option>
            </select>
        </div>
        <div class="form-group">
            <label>รายละเอียดการสนทนา</label>
            <textarea class="form-control" name="conversation_details" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>เพิ่มรูปภาพ</label>
            <input type="file" class="form-control" name="images[]" multiple>
        </div>
        <button type="submit" class="btn btn-primary">บันทึก</button>
    </form>
    <div id="responseMessage" class="mt-3"></div>
</div>

<script>
    $(document).ready(function(){
        $('#contactForm').on('submit', function(e){
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: 'save_contact.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response){
                    $('#responseMessage').html(response);
                    $('#contactForm')[0].reset();
                }
            });
        });
    });
</script>
</body>
</html>
