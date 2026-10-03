<?php
include('../config/connect_db.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

date_default_timezone_set('Asia/Bangkok');
//$filename = "user_manage-" . date('Y-m-d_H-i-s') . ".xlsx";
$filename = "user_manage" . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Create SQL query
$account_types = isset($_POST['account_types']) ? $_POST['account_types'] : [];

try {
    if (!empty($account_types)) {
        $in_clauses = [];
        foreach ($account_types as $index => $type) {
            $param_name = ":type_" . $index;
            $in_clauses[] = $param_name;
        }
        $select_user = "SELECT * FROM ims_user WHERE account_type IN (" . implode(', ', $in_clauses) . ") ORDER BY account_type, id";
        $query = $conn->prepare($select_user);
        foreach ($account_types as $index => $type) {
            $param_name = ":type_" . $index;
            $query->bindValue($param_name, $type, PDO::PARAM_STR);
        }
    } else {
        $select_user = "SELECT * FROM ims_user ORDER BY account_type, id";
        $query = $conn->prepare($select_user);
    }

    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if (empty($results)) {
        echo "<script>alert('ไม่มีข้อมูลในระบบตามเงื่อนไขที่เลือก'); window.history.back();</script>";
        exit;
    }

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set the header for the Excel file
    $headers = ['ลำดับ', 'User ID', 'First Name', 'Last Name', 'Account Type'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Fill the spreadsheet with data
    $line_no = 0;
    foreach ($results as $result) {
        $line_no++;
        $row = [
            $line_no,
            $result->user_id,
            $result->first_name,
            $result->last_name,
            $result->account_type
        ];
        $sheet->fromArray($row, NULL, 'A' . ($line_no + 1));
    }

    // Save Excel file to output
    $writer = new Xlsx($spreadsheet);
    ob_end_clean(); // Clear buffer to avoid header conflicts
    $writer->save('php://output');
    exit;
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
