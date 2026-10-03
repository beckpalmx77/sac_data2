<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มความคิดเห็นลูกค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
<div class="container mt-4">
    <h2>ความคิดเห็นลูกค้า</h2>
    <form id="feedbackForm">
        <div class="mb-3">
            <label for="customer_name" class="form-label">ชื่อของคุณ:</label>
            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">อีเมล:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="comment" class="form-label">ความคิดเห็น:</label>
            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">ส่งความคิดเห็น</button>
    </form>
    <div id="feedback-message" class="mt-3"></div>
</div>

<script>
    $(document).ready(function() {
        $('#feedbackForm').submit(function(event) {
            event.preventDefault(); // ป้องกันไม่ให้ฟอร์มทำการส่งข้อมูลแบบปกติ

            $.ajax({
                url: 'save_feedback.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#feedback-message').html(response);
                    $('#feedbackForm')[0].reset(); // รีเซ็ตฟอร์มหลังจากส่งข้อมูลเสร็จ
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการส่งความคิดเห็น');
                }
            });
        });
    });
</script>
</body>
</html>

