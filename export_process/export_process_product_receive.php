<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $customer = $_POST['filter_customer'];
    $car_no = $_POST['filter_car_no'];

    // 1. ตั้งชื่อไฟล์ - ห้ามมีเครื่องหมาย : ในชื่อไฟล์
    $filename = "Stock_Report_" . date('Y-m-d_His') . ".xls";

    // 2. ตั้งค่า Header สำหรับ Excel
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    // สร้าง SQL
    $sql = "SELECT * FROM ims_stock_product_receive WHERE 1=1";

    if (!empty($date_start) && !empty($date_end)) {
        $sql .= " AND checkin_time BETWEEN :date_start AND :date_end";
    }
    if (!empty($customer)) {
        $sql .= " AND customer_name LIKE :customer";
    }
    if (!empty($car_no)) {
        $sql .= " AND car_no LIKE :car_no";
    }

    $sql .= " ORDER BY checkin_time DESC";
    $query = $conn->prepare($sql);

    if (!empty($date_start) && !empty($date_end)) {
        $ds = $date_start . " 00:00:00";
        $de = $date_end . " 23:59:59";
        $query->bindParam(':date_start', $ds);
        $query->bindParam(':date_end', $de);
    }
    if (!empty($customer)) {
        $customer_param = "%$customer%";
        $query->bindParam(':customer', $customer_param);
    }
    if (!empty($car_no)) {
        $car_no_param = "%$car_no%";
        $query->bindParam(':car_no', $car_no_param);
    }

    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // ส่ง BOM ป้องกันภาษาไทยเพี้ยน
    echo "\xEF\xBB\xBF";
    // ใช้ echo เพื่อป้องกัน PHPStorm แจ้ง Error เรื่อง XML Schema
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    ?>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            /* ส่วนของ Header ตาราง */
            .header-highlight {
                background-color: #157347; /* สีเขียวเข้ม */
                color: #FFFFFF;            /* ตัวอักษรสีขาว */
                font-weight: bold;
                text-align: center;
                height: 40px;
                border: 0.5pt solid #000000;
            }

            /* การจัดการฟอร์แมตข้อมูล */
            .text-mode {
                mso-number-format: "\@";
                white-space: normal;
                vertical-align: top;
                border: 0.5pt solid #000000;
            }

            td {
                border: 0.5pt solid #000000;
                padding: 5px;
            }
        </style>
    </head>
    <body>
    <table border="1">
        <thead>
        <tr>
            <th class="header-highlight" style="width: 150px;">วันที่บันทึก</th>
            <th class="header-highlight" style="width: 200px;">ชื่อผู้บันทึก</th>
            <th class="header-highlight" style="width: 250px;">ชื่อลูกค้า</th>
            <th class="header-highlight" style="width: 120px;">เลขทะเบียนรถ</th>
            <th class="header-highlight" style="width: 150px;">รหัสสินค้า</th>
            <th class="header-highlight" style="width: 300px;">หมายเลขยาง/Bar Code</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if(count($results) > 0) {
            foreach($results as $row) {
                // จัดการเรื่อง New Line เมื่อพบเครื่องหมาย ,
                $clean_remark = str_replace(", ", ",", $row->remark);
                $formatted_remark = str_replace(",", "<br>", $clean_remark);
                ?>
                <tr>
                    <td align="center" style="vertical-align: top;"><?php echo $row->checkin_time; ?></td>
                    <td style="vertical-align: top;"><?php echo $row->display_name; ?></td>
                    <td style="vertical-align: top;"><?php echo $row->customer_name; ?></td>
                    <td align="center" style="vertical-align: top;"><?php echo $row->car_no; ?></td>
                    <td class="text-mode"><?php echo $row->product_id; ?></td>
                    <td class="text-mode"><?php echo $formatted_remark; ?></td>
                </tr>
            <?php }
        } else {
            echo "<tr><td colspan='6' align='center'>ไม่พบข้อมูลตามเงื่อนไขที่ระบุ</td></tr>";
        }
        ?>
        </tbody>
    </table>
    </body>
    </html>
    <?php
    exit;
}
?>