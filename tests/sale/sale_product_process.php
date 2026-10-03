<?php
session_start();
error_reporting(0);

include('../../config/connect_db.php');
include('../../config/lang.php');
include('../../util/record_util.php');

// เพิ่มรายการขาย
if ($_POST["action"] === 'ADD') {
    if ($_POST["doc_no"] !== '' && $_POST["doc_date"] !== '' && $_POST["customer"] !== '') {
        $doc_no = $_POST["doc_no"];
        $doc_date = $_POST["doc_date"];
        $customer = $_POST["customer"];
        $status = $_POST["status"];

        try {
            $sql = "INSERT INTO sales_master(doc_no, doc_date, customer, status) 
                    VALUES(:doc_no, :doc_date, :customer, :status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':customer', $customer, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            // Save sales details
            if ($lastInsertId) {
                $productCodes = $_POST["product_code"];
                $prices = $_POST["price"];
                $quantities = $_POST["quantity"];
                $totals = $_POST["total"];

                $stmtDetail = $conn->prepare("INSERT INTO sales_detail(sales_id, product_code, price, quantity, total) 
                                             VALUES(:sales_id, :product_code, :price, :quantity, :total)");

                for ($i = 0; $i < count($productCodes); $i++) {
                    $stmtDetail->execute([
                        ':sales_id' => $lastInsertId,
                        ':product_code' => $productCodes[$i],
                        ':price' => $prices[$i],
                        ':quantity' => $quantities[$i],
                        ':total' => $totals[$i]
                    ]);
                }
                echo "บันทึกข้อมูลเรียบร้อย";
            }
        } catch (Exception $e) {
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}

// อัปเดตรายการขาย
if ($_POST["action"] === 'UPDATE') {
    if ($_POST["doc_no"] !== '' && $_POST["doc_date"] !== '' && $_POST["customer"] !== '') {
        $salesId = $_POST["sales_id"];
        $doc_no = $_POST["doc_no"];
        $doc_date = $_POST["doc_date"];
        $customer = $_POST["customer"];
        $status = $_POST["status"];

        try {
            // Update master record
            $sql_update = "UPDATE sales_master SET doc_no=:doc_no, doc_date=:doc_date, customer=:customer, status=:status
                           WHERE id = :sales_id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':customer', $customer, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':sales_id', $salesId, PDO::PARAM_INT);
            $query->execute();

            // Delete old details and save new details
            $stmt = $conn->prepare("DELETE FROM sales_detail WHERE sales_id = :sales_id");
            $stmt->bindParam(':sales_id', $salesId, PDO::PARAM_INT);
            $stmt->execute();

            // Insert new details
            $productCodes = $_POST["product_code"];
            $prices = $_POST["price"];
            $quantities = $_POST["quantity"];
            $totals = $_POST["total"];

            $stmtDetail = $conn->prepare("INSERT INTO sales_detail(sales_id, product_code, price, quantity, total) 
                                         VALUES(:sales_id, :product_code, :price, :quantity, :total)");

            for ($i = 0; $i < count($productCodes); $i++) {
                $stmtDetail->execute([
                    ':sales_id' => $salesId,
                    ':product_code' => $productCodes[$i],
                    ':price' => $prices[$i],
                    ':quantity' => $quantities[$i],
                    ':total' => $totals[$i]
                ]);
            }

            echo "อัปเดตข้อมูลเรียบร้อย";
        } catch (Exception $e) {
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}

// ลบรายการขาย
if ($_POST["action"] === 'DELETE') {
    $salesId = $_POST["sales_id"];

    try {
        $stmt = $conn->prepare("DELETE FROM sales_detail WHERE sales_id = :sales_id");
        $stmt->bindParam(':sales_id', $salesId, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM sales_master WHERE id = :sales_id");
        $stmt->bindParam(':sales_id', $salesId, PDO::PARAM_INT);
        $stmt->execute();

        echo "ลบข้อมูลเรียบร้อย";
    } catch (Exception $e) {
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ดึงข้อมูลรายการขาย
if ($_POST["action"] === 'GET_DATA') {
    $salesId = $_POST["sales_id"];
    $return_arr = array();

    $sql_get = "SELECT * FROM sales_master WHERE id = " . $salesId;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "customer" => $result['customer'],
            "status" => $result['status']
        );
    }

    echo json_encode($return_arr);
}