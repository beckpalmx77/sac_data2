<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $customer = $_POST['filter_customer'];
    $car_no = $_POST['filter_car_no'];

    $filename = "Stock_Tires_Number_" . date('Y-m-d_His') . ".xls";

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

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

    echo "\xEF\xBB\xBF";
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    ?>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            .header-highlight {
                background-color: #157347;
                color: #FFFFFF;
                font-weight: bold;
                text-align: center;
                height: 40px;
                border: 0.5pt solid #000000;
            }

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
            <th class="header-highlight">ลำดับ</th>
            <th class="header-highlight">ชื่อลูกค้า</th>
            <th class="header-highlight">วันที่ส่ง</th>
            <th class="header-highlight">ยี่ห้อยาง</th>
            <th class="header-highlight">ขนาดยาง</th>
            <th class="header-highlight">รุ่นยาง</th>
            <th class="header-highlight">เบอร์ยาง</th>
            <th class="header-highlight">ปีที่ผลิต</th>
            <th class="header-highlight">รหัสสินค้า</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if(count($results) > 0) {
            $row_num = 1;
            foreach($results as $row) {
                $clean_remark = str_replace(", ", ",", $row->remark);
                $tire_numbers = array_filter(array_map('trim', explode(",", $clean_remark)));
                
                $checkin_date = "";
                if (!empty($row->checkin_time)) {
                    $datetime_parts = explode(" ", $row->checkin_time);
                    $checkin_date = $datetime_parts[0] ?? "";
                }
                
                if (count($tire_numbers) > 0) {
                    foreach ($tire_numbers as $tire) {
                        ?>
                        <tr>
                            <td align="center"><?php echo $row_num; ?></td>
                            <td><?php echo $row->customer_name; ?></td>
                            <td align="center"><?php echo $checkin_date; ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-mode"><?php echo $tire; ?></td>
                            <td></td>
                            <td class="text-mode"><?php echo $row->product_id; ?></td>
                        </tr>
                        <?php
                        $row_num++;
                    }
                } else {
                    ?>
                    <tr>
                        <td align="center"><?php echo $row_num; ?></td>
                        <td><?php echo $row->customer_name; ?></td>
                        <td align="center"><?php echo $checkin_date; ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-mode"></td>
                        <td></td>
                        <td class="text-mode"><?php echo $row->product_id; ?></td>
                    </tr>
                    <?php
                    $row_num++;
                }
            }
        } else {
            echo "<tr><td colspan='9' align='center'>ไม่พบข้อมูลตามเงื่อนไขที่ระบุ</td></tr>";
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
