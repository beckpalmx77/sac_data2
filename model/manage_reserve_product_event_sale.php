<?php

include '../config/connect_db.php';

if ($_POST["action"] === 'UPDATE_QUANTITY') {

    if ($_POST["id"] != '') {

        $id = $_POST["id"];
        $quantity_1 = $_POST["quantity_1"];
        $quantity_2 = $_POST["quantity_2"];
        $quantity_3 = $_POST["quantity_3"];
        $total_quantity = $quantity_1 + $quantity_2 + $quantity_3;

        $sql_find = "SELECT * FROM ims_product_reserve_event WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_product_reserve_event SET quantity_1=:quantity_1,quantity_2=:quantity_2,quantity_3=:quantity_3,total_quantity=:total_quantity            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':quantity_1', $quantity_1, PDO::PARAM_STR);
            $query->bindParam(':quantity_2', $quantity_2, PDO::PARAM_STR);
            $query->bindParam(':quantity_3', $quantity_3, PDO::PARAM_STR);
            $query->bindParam(':total_quantity', $total_quantity, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE_DETAIL') {
    if (!empty($_POST["id"])) {
        $id = $_POST["id"];

        try {
            // เริ่ม Transaction
            $conn->beginTransaction();

            // 1. ดึง doc_id จาก record ที่จะลบ
            $stmt = $conn->prepare("SELECT doc_id FROM ims_product_reserve_event WHERE id = ?");
            $stmt->execute([$id]);
            $doc_id = $stmt->fetchColumn();

            if ($doc_id) {
                // 2. ลบ record
                $stmt_del = $conn->prepare("DELETE FROM ims_product_reserve_event WHERE id = ?");
                $stmt_del->execute([$id]);

                // 3. ดึงรายการที่เหลือใน doc_id เดียวกัน มาเรียงลำดับใหม่
                $stmt_rows = $conn->prepare("SELECT id FROM ims_product_reserve_event WHERE doc_id = ? ORDER BY id ASC");
                $stmt_rows->execute([$doc_id]);
                $rows = $stmt_rows->fetchAll(PDO::FETCH_ASSOC);

                // 4. Loop เพื่ออัปเดต line_no ใหม่
                $line_no = 1;
                $stmt_update = $conn->prepare("UPDATE ims_product_reserve_event SET line_no = ? WHERE id = ?");
                foreach ($rows as $row) {
                    $stmt_update->execute([$line_no, $row['id']]);
                    $line_no++;
                }

                $conn->commit();
                echo "ลบและจัดเรียง line_no ใหม่เรียบร้อยแล้ว";
            } else {
                $conn->rollBack();
                http_response_code(400);
                echo "ไม่พบรายการที่ต้องการลบ";
            }

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}

