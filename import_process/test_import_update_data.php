<?php
// Database connection settings
$host = '127.0.0.1';
$dbname = 'your_database';
$user = 'your_username';
$pass = 'your_password';
$port = 3307;

try {
    // Establish the connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Data to insert or update
$data = [
    'DI_DAY' => '2024-09-25',
    'DI_MONTH' => '09',
    'DI_MONTH_NAME' => 'September',
    'DI_YEAR' => '2024',
    'AR_CODE' => 'AR123',
    'SKU_CODE' => 'SKU456',
    'SKU_NAME' => 'Product Name',
    'DETAIL' => 'Product Details',
    'BRAND' => 'Brand Name',
    'AR_NAME' => 'Area Name',
    'SALE_NAME' => 'Salesperson',
    'TAKE_NAME' => 'Take Name',
    'TRD_QTY' => '10',
    'TRD_PRC' => '1500',
    'TRD_DISCOUNT' => '100',
    'TRD_TOTAL_PRICE' => '1400',
    'TRD_VAT' => '7',
    'TRD_AMOUNT_PRICE' => '1498',
    'TRD_PER_POINT' => '10',
    'TRD_TOTALPOINT' => '100',
    'WL_CODE' => 'WL001',
    'TRD_Q_FREE' => '2',
    'TRD_AMPHUR' => 'Amphur',
    'TRD_PROVINCE' => 'Province',
    'TRD_MARK' => 'Mark',
    'TRD_U_POINT' => 'UPoint',
    'TRD_R_POINT' => 'RPoint',
    'TRD_S_POINT' => 'SPoint',
    'TRD_T_POINT' => 'TPoint',
    'TRD_COMPARE' => 'Compare',
    'TRD_SHOP' => 'Shop',
    'TRD_BY_MOBAPP' => 'Yes',
    'TRD_YEAR_OLD' => '5',
    'SKU_CAT' => 'Category'
];

// Insert or Update query
$sql = "
    INSERT INTO your_table_name (DI_DAY, DI_MONTH, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, SKU_NAME, DETAIL, BRAND, AR_NAME, SALE_NAME, TAKE_NAME, TRD_QTY, TRD_PRC, TRD_DISCOUNT, TRD_TOTAL_PRICE, TRD_VAT, TRD_AMOUNT_PRICE, TRD_PER_POINT, TRD_TOTALPOINT, WL_CODE, TRD_Q_FREE, TRD_AMPHUR, TRD_PROVINCE, TRD_MARK, TRD_U_POINT, TRD_R_POINT, TRD_S_POINT, TRD_T_POINT, TRD_COMPARE, TRD_SHOP, TRD_BY_MOBAPP, TRD_YEAR_OLD, SKU_CAT)
    VALUES (:DI_DAY, :DI_MONTH, :DI_MONTH_NAME, :DI_YEAR, :AR_CODE, :SKU_CODE, :SKU_NAME, :DETAIL, :BRAND, :AR_NAME, :SALE_NAME, :TAKE_NAME, :TRD_QTY, :TRD_PRC, :TRD_DISCOUNT, :TRD_TOTAL_PRICE, :TRD_VAT, :TRD_AMOUNT_PRICE, :TRD_PER_POINT, :TRD_TOTALPOINT, :WL_CODE, :TRD_Q_FREE, :TRD_AMPHUR, :TRD_PROVINCE, :TRD_MARK, :TRD_U_POINT, :TRD_R_POINT, :TRD_S_POINT, :TRD_T_POINT, :TRD_COMPARE, :TRD_SHOP, :TRD_BY_MOBAPP, :TRD_YEAR_OLD, :SKU_CAT)
    ON DUPLICATE KEY UPDATE
    DI_MONTH = VALUES(DI_MONTH),
    DI_MONTH_NAME = VALUES(DI_MONTH_NAME),
    DI_YEAR = VALUES(DI_YEAR),
    AR_CODE = VALUES(AR_CODE),
    SKU_NAME = VALUES(SKU_NAME),
    DETAIL = VALUES(DETAIL),
    BRAND = VALUES(BRAND),
    AR_NAME = VALUES(AR_NAME),
    SALE_NAME = VALUES(SALE_NAME),
    TAKE_NAME = VALUES(TAKE_NAME),
    TRD_QTY = VALUES(TRD_QTY),
    TRD_PRC = VALUES(TRD_PRC),
    TRD_DISCOUNT = VALUES(TRD_DISCOUNT),
    TRD_TOTAL_PRICE = VALUES(TRD_TOTAL_PRICE),
    TRD_VAT = VALUES(TRD_VAT),
    TRD_AMOUNT_PRICE = VALUES(TRD_AMOUNT_PRICE),
    TRD_PER_POINT = VALUES(TRD_PER_POINT),
    TRD_TOTALPOINT = VALUES(TRD_TOTALPOINT),
    WL_CODE = VALUES(WL_CODE),
    TRD_Q_FREE = VALUES(TRD_Q_FREE),
    TRD_AMPHUR = VALUES(TRD_AMPHUR),
    TRD_PROVINCE = VALUES(TRD_PROVINCE),
    TRD_MARK = VALUES(TRD_MARK),
    TRD_U_POINT = VALUES(TRD_U_POINT),
    TRD_R_POINT = VALUES(TRD_R_POINT),
    TRD_S_POINT = VALUES(TRD_S_POINT),
    TRD_T_POINT = VALUES(TRD_T_POINT),
    TRD_COMPARE = VALUES(TRD_COMPARE),
    TRD_SHOP = VALUES(TRD_SHOP),
    TRD_BY_MOBAPP = VALUES(TRD_BY_MOBAPP),
    TRD_YEAR_OLD = VALUES(TRD_YEAR_OLD),
    SKU_CAT = VALUES(SKU_CAT)
";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);

try {
    // Bind the parameters and execute
    $stmt->execute($data);
    echo "Data inserted/updated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
