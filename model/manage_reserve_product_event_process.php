<?php
include('../config/connect_db.php');
include('../util/record_util.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

$inserted = 0;
$updated = 0;
$line_no = 0;

$DOC_DATE = $data[0]['DOC_DATE'] ?? '';
$table = 'ims_product_reserve_event';
$RUNNO = LAST_RUNNO_COND($conn, $table, $DOC_DATE);
$DOC_ID = "RESERVE-" . $DOC_DATE . "-" . sprintf('%04s', $RUNNO);

$CUSTOMER_CODE = $data[0]['CUSTOMER_CODE'] ?? '';
$CUSTOMER_NAME = $data[0]['CUSTOMER_NAME'] ?? '';
$PHONE = $data[0]['PHONE'] ?? '';
$PROVINCE = $data[0]['PROVINCE'] ?? '';
$SALE_NAME = $data[0]['SALE_NAME'] ?? '';
$TAKE_NAME = $data[0]['TAKE_NAME'] ?? '';

$sql_header = "INSERT INTO ims_product_reserve_event_header (
    DOC_ID, DOC_DATE, CUSTOMER_CODE , CUSTOMER_NAME, PHONE, PROVINCE, SALE_NAME, TAKE_NAME 
) VALUES (
    :DOC_ID, :DOC_DATE, :CUSTOMER_CODE ,:CUSTOMER_NAME, :PHONE, :PROVINCE, :SALE_NAME, :TAKE_NAME 
)";

try {
    $stmt_header = $conn->prepare($sql_header);
    $stmt_header->execute([
        ':DOC_ID' => $DOC_ID,
        ':DOC_DATE' => $DOC_DATE,
        ':CUSTOMER_CODE' => $CUSTOMER_CODE,
        ':CUSTOMER_NAME' => $CUSTOMER_NAME,
        ':PHONE' => $PHONE,
        ':PROVINCE' => $PROVINCE,
        ':SALE_NAME' => $SALE_NAME,
        ':TAKE_NAME' => $TAKE_NAME
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'HEADER ERROR: ' . $e->getMessage()]);
    exit;
}

// LOOP รายการ SKU
foreach ($data as $row) {
    $line_no++;

    $params = [
        ':DOC_ID' => $DOC_ID,
        ':DOC_DATE' => $DOC_DATE,
        ':CUSTOMER_CODE' => $row['CUSTOMER_CODE'] ?? '',
        ':CUSTOMER_NAME' => $row['CUSTOMER_NAME'] ?? '',
        ':PHONE' => $row['PHONE'] ?? '',
        ':PROVINCE' => $row['PROVINCE'] ?? '',
        ':SALE_NAME' => $row['SALE_NAME'] ?? '',
        ':TAKE_NAME' => $row['TAKE_NAME'] ?? '',
        ':SKU_CODE' => $row['SKU_CODE'] ?? '-',
        ':SKU_NAME' => $row['SKU_NAME'] ?? '-',
        ':SKU_CAT_CODE' => $row['SKU_CAT_CODE'] ?? '',
        ':SKU_CAT_NAME' => $row['SKU_CAT_NAME'] ?? '-',
        ':BRAND' => $row['BRAND'] ?? '-',
        ':price_1' => $row['price_1'] ?? 0,
        ':price_2' => $row['price_2'] ?? 0,
        ':price_3' => $row['price_3'] ?? 0,
        ':quantity_1' => $row['quantity_1'] ?? 0,
        ':quantity_2' => $row['quantity_2'] ?? 0,
        ':quantity_3' => $row['quantity_3'] ?? 0,
        ':total_quantity' => $row['total_quantity'] ?? 0,
        ':total_point' => $row['total_point'] ?? 0,
        ':total_gift_voucher' => $row['total_gift_voucher'] ?? 0,
        ':remark' => $row['remark'] ?? '-',
        ':status' => $row['status'] ?? 'Y',
        ':line_no' => $line_no
    ];

    $sql = "INSERT INTO ims_product_reserve_event (
                DOC_ID, line_no, DOC_DATE, CUSTOMER_CODE , CUSTOMER_NAME, PHONE, PROVINCE, SALE_NAME, TAKE_NAME,
                SKU_CODE, SKU_NAME, SKU_CAT_CODE, SKU_CAT_NAME, BRAND,
                price_1, price_2, price_3, 
                quantity_1, quantity_2, quantity_3, total_quantity,
                total_point, total_gift_voucher, remark, status
            ) VALUES (
                :DOC_ID, :line_no, :DOC_DATE, :CUSTOMER_CODE ,:CUSTOMER_NAME, :PHONE, :PROVINCE, :SALE_NAME, :TAKE_NAME,
                :SKU_CODE, :SKU_NAME, :SKU_CAT_CODE, :SKU_CAT_NAME, :BRAND,
                :price_1, :price_2, :price_3,
                :quantity_1, :quantity_2, :quantity_3, :total_quantity,
                :total_point, :total_gift_voucher, :remark, :status
            )
            ON DUPLICATE KEY UPDATE
                CUSTOMER_CODE = VALUES(CUSTOMER_CODE),
                CUSTOMER_NAME = VALUES(CUSTOMER_NAME),
                PROVINCE = VALUES(PROVINCE),
                SALE_NAME = VALUES(SALE_NAME),
                TAKE_NAME = VALUES(TAKE_NAME),
                SKU_NAME = VALUES(SKU_NAME),
                SKU_CAT_CODE = VALUES(SKU_CAT_CODE),
                SKU_CAT_NAME = VALUES(SKU_CAT_NAME),
                BRAND = VALUES(BRAND),
                price_1 = VALUES(price_1),
                price_2 = VALUES(price_2),
                price_3 = VALUES(price_3),
                quantity_1 = VALUES(quantity_1),
                quantity_2 = VALUES(quantity_2),
                quantity_3 = VALUES(quantity_3),
                total_quantity = VALUES(total_quantity),
                total_point = VALUES(total_point),
                total_gift_voucher = VALUES(total_gift_voucher),
                remark = VALUES(remark),
                status = VALUES(status),
                update_date = CURRENT_TIMESTAMP";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $updated++;
        } else {
            $inserted++;
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

$sql = "
    UPDATE ims_product_reserve_event e
    JOIN ims_product_reserve_target_point t ON e.SKU_CODE = t.SKU_CODE
    SET 
        e.point = t.point,
        e.gift_voucher = t.gift_voucher,
        e.gift_voucher1 = t.gift_voucher1,
        e.gift_voucher2 = t.gift_voucher2,        
        e.price_2 = t.price_2,
        e.price_1 = t.price_1,
        e.price_2 = t.price_2,
        e.price_3 = t.price_3,
        e.sale_price_1 = t.sale_price_1,
        e.sale_price_2 = t.sale_price_2,
        e.sale_price_3 = t.sale_price_3
    WHERE e.DOC_ID = :doc_id
";

// เตรียม statement
$stmt = $conn->prepare($sql);

// bind ค่า parameter ป้องกัน SQL Injection
$stmt->bindParam(':doc_id', $DOC_ID, PDO::PARAM_STR); // หรือ PARAM_INT ถ้าเป็นตัวเลขล้วน

// execute
$stmt->execute();


echo json_encode([
    'status' => 'success',
    'inserted' => $inserted,
    'updated' => $updated
]);