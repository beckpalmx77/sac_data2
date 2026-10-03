<?php
include("config/connect_db.php");

// ตรวจสอบว่าแบบฟอร์มถูกส่งมาแล้วหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากแบบฟอร์ม
    $contact_date = $_POST['contact_date'];
    $contact_method = $_POST['contact_method'];
    $store_name = $_POST['store_name'];
    $is_new_customer = $_POST['is_new_customer'];
    $province = $_POST['province'];
    $purpose = $_POST['purpose'];
    $conversation_details = $_POST['conversation_details'];
    $uploaded_images = $_FILES['uploaded_image'];

    // สร้างอาร์เรย์เพื่อเก็บชื่อไฟล์
    $file_names = [];

    // ลูปผ่านไฟล์ที่อัปโหลด
    for ($i = 0; $i < count($uploaded_images['name']); $i++) {
        $tmp_name = $uploaded_images['tmp_name'][$i];
        $file_name = basename($uploaded_images['name'][$i]);
        $upload_dir = 'uploads/'; // กำหนดโฟลเดอร์สำหรับอัปโหลด

        // สร้างชื่อไฟล์ใหม่เพื่อหลีกเลี่ยงการซ้ำ
        $new_file_name = uniqid() . '-' . $file_name;

        // ย้ายไฟล์จาก temporary ไปยังโฟลเดอร์ที่กำหนด
        if (move_uploaded_file($tmp_name, $upload_dir . $new_file_name)) {
            // เก็บชื่อไฟล์ลงในอาร์เรย์
            $file_names[] = $new_file_name;
        }
    }

    // เชื่อมต่อฐานข้อมูลและเก็บข้อมูล
    $sql = "INSERT INTO your_table_name (contact_date, contact_method, store_name, is_new_customer, province, purpose, conversation_details, uploaded_images)
            VALUES (:contact_date, :contact_method, :store_name, :is_new_customer, :province, :purpose, :conversation_details, :uploaded_images)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':contact_date', $contact_date);
    $stmt->bindParam(':contact_method', $contact_method);
    $stmt->bindParam(':store_name', $store_name);
    $stmt->bindParam(':is_new_customer', $is_new_customer);
    $stmt->bindParam(':province', $province);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':conversation_details', $conversation_details);

    // แปลงอาร์เรย์ชื่อไฟล์เป็นสตริง
    $uploaded_images_str = implode(',', $file_names);
    $stmt->bindParam(':uploaded_images', $uploaded_images_str);

    // ประมวลผลคำสั่ง SQL
    if ($stmt->execute()) {
        echo "ข้อมูลถูกส่งเรียบร้อยแล้ว!";
    } else {
        echo "เกิดข้อผิดพลาดในการส่งข้อมูล: " . $stmt->errorInfo()[2];
    }
}

// ดึงชื่อไฟล์รูปภาพจากฐานข้อมูล
$sql_images = "SELECT IMAGESS FROM ims_crm_images ORDER BY id DESC"; // เปลี่ยนชื่อเป็นชื่อจริงของตาราง
$stmt_images = $conn->prepare($sql_images);
$stmt_images->execute();
$images = $stmt_images->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มการติดต่อลูกค้า</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.2.0/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>ฟอร์มการติดต่อลูกค้า</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="contact_date" class="form-label">วันที่</label>
            <input type="date" class="form-control" id="contact_date" name="contact_date" required>
        </div>

        <div class="mb-3">
            <label for="contact_method" class="form-label">วิธีการติดต่อลูกค้า</label>
            <select class="form-select" id="contact_method" name="contact_method" required>
                <option value="โทร">โทร</option>
                <option value="ไลน์">ไลน์</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="store_name" class="form-label">ชื่อร้านค้า</label>
            <input type="text" class="form-control" id="store_name" name="store_name" required>
        </div>

        <div class="mb-3">
            <label for="is_new_customer" class="form-label">ประเภทลูกค้า</label>
            <select class="form-select" id="is_new_customer" name="is_new_customer" required>
                <option value="existing">ใช้ดาต้าลูกค้าเดิม</option>
                <option value="new">ลูกค้าใหม่</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="province" class="form-label">จังหวัด</label>
            <input type="text" class="form-control" id="province" name="province" required>
        </div>

        <div class="mb-3">
            <label for="purpose" class="form-label">วัตถุประสงค์</label>
            <select class="form-select" id="purpose" name="purpose" required>
                <option value="แนะนำสินค้า">แนะนำสินค้า</option>
                <option value="รับคำสั่งซื้อสินค้า">รับคำสั่งซื้อสินค้า</option>
                <option value="เสนอโปรโมชั่นใหม่">เสนอโปรโมชั่นใหม่</option>
                <option value="ตามยอดค้างชำระ">ตามยอดค้างชำระ</option>
                <option value="ปิดยอดขาย">ปิดยอดขาย</option>
                <option value="เรื่องยางเคลม">เรื่องยางเคลม</option>
                <option value="ปัญหาที่ลูกค้าแจ้ง">ปัญหาที่ลูกค้าแจ้ง</option>
                <option value="อื่นๆ">อื่นๆ โปรดระบุ.....</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="conversation_details" class="form-label">รายละเอียดการสนทนา</label>
            <textarea class="form-control" id="conversation_details" name="conversation_details" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label for="uploaded_image" class="form-label">เพิ่มรูป</label>
            <input type="file" class="form-control" id="uploaded_image" name="uploaded_image[]" accept="image/*" multiple>
        </div>

        <button type="submit" class="btn btn-primary">ส่งข้อมูล</button>
    </form>

    <h3 class="mt-5">ภาพที่อัปโหลด</h3>
    <div class="row">
        <?php foreach ($images as $image): ?>
            <div class="col-4">
                <img src="uploads/<?php echo htmlspecialchars($image); ?>" alt="Uploaded Image" class="img-fluid mb-3">
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.2.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>