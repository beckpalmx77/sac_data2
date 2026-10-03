<?php

ini_set('display_errors', 1);
error_reporting(~0);

$start_time = microtime(true);

include("../config/connect_db.php");

$year = date("Y");

// 1. Prepared Statements Outside Loops
$sql_check = "SELECT 1 FROM ims_report_product_sale_summary_2 WHERE SLMN_MAN = :SLMN_MAN AND DI_MONTH = :DI_MONTH AND DI_YEAR = :DI_YEAR LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

// Statements for Section 1 (P1+P2+P3 Total Amt)
$sql_upd_1 = "UPDATE ims_report_product_sale_summary_2 SET total_amt = :total_amt WHERE SLMN_MAN = :SLMN_MAN AND DI_MONTH = :DI_MONTH AND DI_YEAR = :DI_YEAR";
$stmt_upd_1 = $conn->prepare($sql_upd_1);

$sql_ins_1 = "INSERT INTO ims_report_product_sale_summary_2 (SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR, total_amt) VALUES (:SLMN_MAN, :DI_MONTH, :DI_MONTH_NAME, :DI_YEAR, :total_amt)";
$stmt_ins_1 = $conn->prepare($sql_ins_1);

// Statements for Section 2 (P1 Tires)
$sql_upd_2 = "UPDATE ims_report_product_sale_summary_2 SET DI_MONTH_NAME = :DI_MONTH_NAME, tires_total_qty = :tires_total_qty, tires_total_amt = :tires_total_amt WHERE SLMN_MAN = :SLMN_MAN AND DI_MONTH = :DI_MONTH AND DI_YEAR = :DI_YEAR";
$stmt_upd_2 = $conn->prepare($sql_upd_2);

$sql_ins_2 = "INSERT INTO ims_report_product_sale_summary_2 (SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR, tires_total_qty, tires_total_amt) VALUES (:SLMN_MAN, :DI_MONTH, :DI_MONTH_NAME, :DI_YEAR, :tires_total_qty, :tires_total_amt)";
$stmt_ins_2 = $conn->prepare($sql_ins_2);

// Statements for Section 3 (P2 Part)
$sql_upd_3 = "UPDATE ims_report_product_sale_summary_2 SET part_total_amt = :part_total_amt WHERE SLMN_MAN = :SLMN_MAN AND DI_MONTH = :DI_MONTH AND DI_YEAR = :DI_YEAR";
$stmt_upd_3 = $conn->prepare($sql_upd_3);

// Statements for Section 4 (P3 Service)
$sql_upd_4 = "UPDATE ims_report_product_sale_summary_2 SET svr_total_amt = :svr_total_amt WHERE SLMN_MAN = :SLMN_MAN AND DI_MONTH = :DI_MONTH AND DI_YEAR = :DI_YEAR";
$stmt_upd_4 = $conn->prepare($sql_upd_4);

// Pre-fetch existing record keys for current year
$sql_existing = "SELECT CONCAT(SLMN_MAN, '|', DI_MONTH, '|', DI_YEAR) FROM ims_report_product_sale_summary_2 WHERE DI_YEAR = :year";
$stmt_existing = $conn->prepare($sql_existing);
$stmt_existing->execute([':year' => $year]);
$existing_keys = array_flip($stmt_existing->fetchAll(PDO::FETCH_COLUMN));

$conn->beginTransaction();

try {
    for ($month = 1; $month <= 12; $month++) {
        // Section 1: Total Amount (P1 + P2 + P3)
        $sql_get_1 = "SELECT SLMN_NAME AS SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                      SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
                      SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                      FROM ims_product_sale_sac 
                      WHERE DI_MONTH = '$month' AND DI_YEAR = '$year'
                      AND (PGROUP = 'P1' OR PGROUP = 'P2' OR PGROUP = 'P3')    
                      GROUP BY SLMN_NAME, DI_MONTH, DI_MONTH_NAME, DI_YEAR 
                      ORDER BY DI_MONTH, TRD_G_KEYIN DESC";
        $results1 = $conn->query($sql_get_1)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results1 as $result) {
            if (empty($result["SLMN_MAN"])) continue;
            $key = $result["SLMN_MAN"] . '|' . $result["DI_MONTH"] . '|' . $result["DI_YEAR"];
            if (isset($existing_keys[$key])) {
                $stmt_upd_1->execute([
                    ':total_amt' => $result["TRD_G_KEYIN"],
                    ':SLMN_MAN'  => $result["SLMN_MAN"],
                    ':DI_MONTH'  => $result["DI_MONTH"],
                    ':DI_YEAR'   => $result["DI_YEAR"]
                ]);
            } else {
                $stmt_ins_1->execute([
                    ':total_amt'     => $result["TRD_G_KEYIN"],
                    ':SLMN_MAN'      => $result["SLMN_MAN"],
                    ':DI_MONTH'      => $result["DI_MONTH"],
                    ':DI_MONTH_NAME' => $result["DI_MONTH_NAME"],
                    ':DI_YEAR'       => $result["DI_YEAR"]
                ]);
                $existing_keys[$key] = true;
            }
        }

        // Section 2: P1 (Tires)
        $sql_get_2 = "SELECT SLMN_NAME AS SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                      SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
                      SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                      FROM ims_product_sale_sac 
                      WHERE DI_MONTH = '$month' AND DI_YEAR = '$year' AND PGROUP = 'P1'   
                      GROUP BY SLMN_NAME, DI_MONTH, DI_MONTH_NAME, DI_YEAR 
                      ORDER BY DI_MONTH, TRD_G_KEYIN DESC";
        $results2 = $conn->query($sql_get_2)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results2 as $result) {
            if (empty($result["SLMN_MAN"])) continue;
            $key = $result["SLMN_MAN"] . '|' . $result["DI_MONTH"] . '|' . $result["DI_YEAR"];
            if (isset($existing_keys[$key])) {
                $stmt_upd_2->execute([
                    ':DI_MONTH_NAME'  => $result["DI_MONTH_NAME"],
                    ':tires_total_qty'=> $result["TRD_QTY"],
                    ':tires_total_amt'=> $result["TRD_G_KEYIN"],
                    ':SLMN_MAN'       => $result["SLMN_MAN"],
                    ':DI_MONTH'       => $result["DI_MONTH"],
                    ':DI_YEAR'        => $result["DI_YEAR"]
                ]);
            } else {
                $stmt_ins_2->execute([
                    ':SLMN_MAN'       => $result["SLMN_MAN"],
                    ':DI_MONTH'       => $result["DI_MONTH"],
                    ':DI_MONTH_NAME'  => $result["DI_MONTH_NAME"],
                    ':DI_YEAR'        => $result["DI_YEAR"],
                    ':tires_total_qty'=> $result["TRD_QTY"],
                    ':tires_total_amt'=> $result["TRD_G_KEYIN"]
                ]);
                $existing_keys[$key] = true;
            }
        }

        // Section 3: P2 (Part)
        $sql_get_3 = "SELECT SLMN_NAME AS SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                      SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                      FROM ims_product_sale_sac 
                      WHERE DI_MONTH = '$month' AND DI_YEAR = '$year' AND PGROUP = 'P2'   
                      GROUP BY SLMN_NAME, DI_MONTH, DI_MONTH_NAME, DI_YEAR 
                      ORDER BY DI_MONTH, TRD_G_KEYIN DESC";
        $results3 = $conn->query($sql_get_3)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results3 as $result) {
            if (empty($result["SLMN_MAN"])) continue;
            $key = $result["SLMN_MAN"] . '|' . $result["DI_MONTH"] . '|' . $result["DI_YEAR"];
            if (isset($existing_keys[$key])) {
                $stmt_upd_3->execute([
                    ':part_total_amt' => $result["TRD_G_KEYIN"],
                    ':SLMN_MAN'       => $result["SLMN_MAN"],
                    ':DI_MONTH'       => $result["DI_MONTH"],
                    ':DI_YEAR'        => $result["DI_YEAR"]
                ]);
            }
        }

        // Section 4: P3 (Service)
        $sql_get_4 = "SELECT SLMN_NAME AS SLMN_MAN, DI_MONTH, DI_MONTH_NAME, DI_YEAR,
                      SUM(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                      FROM ims_product_sale_sac 
                      WHERE DI_MONTH = '$month' AND DI_YEAR = '$year' AND PGROUP = 'P3'   
                      GROUP BY SLMN_NAME, DI_MONTH, DI_MONTH_NAME, DI_YEAR 
                      ORDER BY DI_MONTH, TRD_G_KEYIN DESC";
        $results4 = $conn->query($sql_get_4)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results4 as $result) {
            if (empty($result["SLMN_MAN"])) continue;
            $key = $result["SLMN_MAN"] . '|' . $result["DI_MONTH"] . '|' . $result["DI_YEAR"];
            if (isset($existing_keys[$key])) {
                $stmt_upd_4->execute([
                    ':svr_total_amt' => $result["TRD_G_KEYIN"],
                    ':SLMN_MAN'      => $result["SLMN_MAN"],
                    ':DI_MONTH'      => $result["DI_MONTH"],
                    ':DI_YEAR'       => $result["DI_YEAR"]
                ]);
            }
        }
    }

    $conn->commit();
    echo "Summary process completed successfully.\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;

echo "Execution time: " . (microtime(true) - $start_time) . " seconds\n";
