<?php
// การเชื่อมต่อกับ MS SQL Server
$mssql_host = '192.168.88.40';
$mssql_dbname = 'SAC';
$mssql_user = 'SYY';
$mssql_password = '39122222';

try {
    $pdo_mssql = new PDO("sqlsrv:Server=$mssql_host;Database=$mssql_dbname", $mssql_user, $mssql_password);
    $pdo_mssql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("MSSQL connection failed: " . $e->getMessage());
}

// การเชื่อมต่อกับ PostgreSQL
$pgsql_host = 'sac.cckwqocv7kfy.ap-southeast-1.rds.amazonaws.com';
$pgsql_dbport = '5432';
$pgsql_dbname = 'sac';
$pgsql_user = 'sac';
$pgsql_password = 'l;ovvF9h8kiN';

try {
    $pdo_pgsql = new PDO("pgsql:host=$pgsql_host;dbname=$pgsql_dbname", $pgsql_user, $pgsql_password);
    $pdo_pgsql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("PostgreSQL connection failed: " . $e->getMessage());
}


$sku_where_ext = " AND ICCAT_CODE IN ('1SAC14', '2SAC01', 'SAC05', '4SAC01', '3SAC01', '5SAC02', '1SAC06', '5SAC01',
        '8SAC11', '8BTCA01-001', '8BTCA01-002', '1SAC05', '6SAC08', '1SAC01', '1SAC02',
        '1SAC03', '1SAC04', '1SAC08', '1SAC07', '1SAC09', '1SAC10', '1SAC11', '1SAC12',
        '1SAC13', '2SAC09', '2SAC04', '2SAC13', '2SAC14', '2SAC12', '2SAC02', '2SAC03',
        '2SAC10', '2SAC15', '2SAC06', '2SAC05', '2SAC07', '2SAC08', '2SAC11', '3SAC02',
        '3SAC06', '3SAC03', '3SAC04', '4SAC02', '4SAC03', '4SAC04', '4SAC06', '3SAC05',
        '4SAC05', '2SAC16', '2SAC17', '2SAC19')";

$sql_cmd = "SELECT ICCAT_CODE,SKU_CODE,SKU_NAME,WH_CODE,WH_NAME,WL_CODE,WL_NAME,sum(CAST(QTY AS DECIMAL(10,2))) as  QTY FROM v_stock_movement "
    . " WHERE WH_CODE = 'SAC' " . $sku_where_ext
    . " GROUP BY ICCAT_CODE,SKU_CODE,SKU_NAME,WH_CODE,WH_NAME,WL_CODE,WL_NAME "
    . " HAVING sum(CAST(QTY AS DECIMAL(10,2))) > 0";

// ดึงข้อมูลจาก MS SQL Server พร้อมราคา
$sql_mssql = "SELECT SKU_CODE, product_name, stock_quantity, price FROM mssql_stock WHERE stock_quantity > 0";
$stmt_mssql = $pdo_mssql->prepare($sql_mssql);
$stmt_mssql->execute();
$mssql_data = $stmt_mssql->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลจาก PostgreSQL พร้อมราคา
$sql_pgsql = "SELECT SKU_CODE, SKU_NAME, QUANTITY, PRICE FROM SC_SKUMASTER WHERE QUANTITY > 0";
$stmt_pgsql = $pdo_pgsql->prepare($sql_pgsql);
$stmt_pgsql->execute();
$pgsql_data = $stmt_pgsql->fetchAll(PDO::FETCH_ASSOC);

// สร้าง associative array โดยใช้ SKU_CODE เป็น key
$compare_data = [];

// ใส่ข้อมูลจาก MS SQL Server
foreach ($mssql_data as $row) {
    $compare_data[$row['SKU_CODE']]['mssql'] = $row;
}

// ใส่ข้อมูลจาก PostgreSQL
foreach ($pgsql_data as $row) {
    $compare_data[$row['SKU_CODE']]['pgsql'] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare DataTables</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function () {
            $('#compareTable').DataTable();
        });
    </script>
</head>
<body>
<div class="container">
    <h2>Comparison of Products (MSSQL vs PostgreSQL)</h2>
    <table id="compareTable" class="display">
        <thead>
        <tr>
            <th>SKU Code</th>
            <th>MSSQL Product Name</th>
            <th>MSSQL Quantity</th>
            <th>MSSQL Price</th>
            <th>PostgreSQL Product Name</th>
            <th>PostgreSQL Quantity</th>
            <th>PostgreSQL Price</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($compare_data as $sku_code => $data) {
            echo "<tr>";
            echo "<td>{$sku_code}</td>";

            // ข้อมูลจาก MSSQL
            if (isset($data['mssql'])) {
                echo "<td>{$data['mssql']['product_name']}</td>";
                echo "<td>{$data['mssql']['stock_quantity']}</td>";
                echo "<td>{$data['mssql']['price']}</td>";
            } else {
                echo "<td>ไม่มีข้อมูล</td>";
                echo "<td>ไม่มีข้อมูล</td>";
                echo "<td>ไม่มีข้อมูล</td>";
            }

            // ข้อมูลจาก PostgreSQL
            if (isset($data['pgsql'])) {
                echo "<td>{$data['pgsql']['SKU_NAME']}</td>";
                echo "<td>{$data['pgsql']['QUANTITY']}</td>";
                echo "<td>{$data['pgsql']['PRICE']}</td>";
            } else {
                echo "<td>ไม่มีข้อมูล</td>";
                echo "<td>ไม่มีข้อมูล</td>";
                echo "<td>ไม่มีข้อมูล</td>";
            }

            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
