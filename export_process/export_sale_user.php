<?php
include('../config/connect_db.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

date_default_timezone_set('Asia/Bangkok');
$filename = "sale_users_" . date('Y-m-d_H-i-s') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$filter_type = isset($_POST['account_type_filter']) ? $_POST['account_type_filter'] : 'all';

if ($filter_type === 'sale_user') {
    $select_user = "SELECT u.*, p.permission_detail 
                    FROM ims_user u
                    LEFT JOIN ims_permission p ON u.account_type = p.permission_id
                    WHERE u.account_type = 'sale_user' 
                      AND u.status = 'Active' 
                    ORDER BY u.id ASC";
} elseif ($filter_type === 'sale_sup') {
    $select_user = "SELECT u.*, p.permission_detail 
                    FROM ims_user u
                    LEFT JOIN ims_permission p ON u.account_type = p.permission_id
                    WHERE u.account_type = 'sale_sup' 
                      AND u.status = 'Active' 
                    ORDER BY u.id ASC";
} else {
    $select_user = "SELECT u.*, p.permission_detail 
                    FROM ims_user u
                    LEFT JOIN ims_permission p ON u.account_type = p.permission_id
                    WHERE (u.account_type = 'sale_user' OR u.account_type = 'sale_sup') 
                      AND u.status = 'Active' 
                    ORDER BY u.id ASC";
}

try {
    $query = $conn->prepare($select_user);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if (empty($results)) {
        echo "<script>alert('ไม่มีข้อมูลในระบบ'); window.history.back();</script>";
        exit;
    }

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set the header for the Excel file
    $headers = ['ลำดับ', 'User ID', 'First Name', 'Last Name', 'Account Type', 'Status'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Fill the spreadsheet with data
    $line_no = 0;
    foreach ($results as $result) {
        $line_no++;
        $acc_type_label = !empty($result->permission_detail) ? $result->permission_detail . " (" . $result->account_type . ")" : $result->account_type;
        $row = [
            $line_no,
            $result->user_id,
            $result->first_name,
            $result->last_name,
            $acc_type_label,
            $result->status
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
