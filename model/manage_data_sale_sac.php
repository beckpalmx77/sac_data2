<?php
include('../config/connect_db.php');  // เชื่อมต่อกับฐานข้อมูล

// --- ส่วนจัดการลบข้อมูลตามวันที่ (แก้ไขปรับปรุง) ---
if (isset($_POST['action']) && $_POST['action'] === 'DELETE_BY_DATE') {

    if (isset($_POST['DI_DATE']) && !empty($_POST['DI_DATE'])) {
        $di_date = $_POST['DI_DATE'];

        $format = $_POST['format'] ?? 'text';
        try {
            // คำสั่ง SQL
            $sql = "DELETE FROM ims_data_sale_sac_all WHERE DI_DATE = :di_date";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':di_date', $di_date, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // เช็คจำนวนรายการที่ถูกลบจริง
                $count = $stmt->rowCount();
                if ($format === 'json') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'count' => $count,
                        'di_date' => $di_date,
                        'message' => $count > 0 ? "ลบข้อมูลเรียบร้อยแล้ว จำนวน " . $count . " รายการ" : "ไม่พบข้อมูลสำหรับวันที่ " . $di_date . " (ไม่มีข้อมูลถูกลบ)"
                    ]);
                } else {
                    if ($count > 0) {
                        echo "ลบข้อมูลเรียบร้อยแล้ว จำนวน " . $count . " รายการ";
                    } else {
                        echo "ไม่พบข้อมูลสำหรับวันที่ " . $di_date . " (ไม่มีข้อมูลถูกลบ)";
                    }
                }
            } else {
                if ($format === 'json') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ไม่สามารถประมวลผลคำสั่ง SQL ได้']);
                } else {
                    echo "เกิดข้อผิดพลาด: ไม่สามารถประมวลผลคำสั่ง SQL ได้";
                }
            }
        } catch (PDOException $e) {
            if ($format === 'json') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
            } else {
                echo "Error: " . $e->getMessage();
            }
        }
    } else {
        if ($format === 'json') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ไม่ได้ระบุวันที่']);
        } else {
            echo "Error: ไม่ได้ระบุวันที่";
        }
    }
    exit; // จบการทำงานทันทีเพื่อไม่ให้รันโค้ดส่วนอื่นต่อ
}

// --- ส่วนอัปเดตประเภทร้านค้า (คงเดิม) ---
if (isset($_POST['action']) && $_POST['action'] === 'UPDATE_SHOP_TYPE') {

    $sql_find = "SELECT * FROM ims_ar_shop WHERE 1 " ;
    $query = $conn->prepare($sql_find);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() >= 1) {
        foreach ($results as $result) {

            if ($result->status==='Y') {
                $shop_type = "SHOP";
            } else {
                $shop_type = "ร้านทั่วไป";
            }

            // หมายเหตุ: Query ด้านล่างนี้ในไฟล์เดิมดูเหมือนยังไม่สมบูรณ์ (UPDATE ... SET ... WHERE ...)
            // แต่ผมคงไว้ตามต้นฉบับของคุณเพื่อความปลอดภัยครับ
            $sql = "UPDATE ims_data_sale_sac_all SET SHOP_TYPE = :shop_type WHERE ... "; // ควรตรวจสอบเงื่อนไข WHERE อีกครั้ง
            // $stmt = $conn->prepare($sql);
            // $stmt->bindParam(':shop_type', $shop_type, PDO::PARAM_STR);
            // $stmt->execute();
        }
    }
}