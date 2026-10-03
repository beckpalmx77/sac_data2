<?php
session_start();
error_reporting(0);

require_once __DIR__ . '/../config/connect_db.php';
require_once __DIR__ . '/../config/lang.php';
require_once __DIR__ . '/../util/record_util.php';

$conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

if ($_POST["action"] === 'GET_DATA_SALE_ALL') {

    ## Read DataTable Parameters
    $draw            = intval($_POST['draw'] ?? 1);
    $row             = intval($_POST['start'] ?? 0);
    $rowperpage      = intval($_POST['length'] ?? 10);
    if ($rowperpage <= 0) $rowperpage = 10;

    $columnIndex     = intval($_POST['order'][0]['column'] ?? 0);
    $columnName      = $_POST['columns'][$columnIndex]['data'] ?? 'id';
    $columnSortOrder = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
    $searchValue     = trim($_POST['search']['value'] ?? '');

    // Whitelist columns for sorting
    $allowedColumns = [
        'DI_REF'           => 'DI_REF',
        'DI_DATE'          => 'DI_DATE',
        'SKU_CODE'         => 'SKU_CODE',
        'AR_NAME'          => 'AR_NAME',
        'TRD_QTY'          => 'TRD_QTY',
        'TRD_Q_FREE'       => 'TRD_Q_FREE',
        'TRD_AMOUNT_PRICE' => 'TRD_AMOUNT_PRICE',
        'SALE_NAME'        => 'SALE_NAME',
        'TAKE_NAME'        => 'TAKE_NAME'
    ];

    $orderByCol = $allowedColumns[$columnName] ?? 'id';
    $orderClause = " ORDER BY `{$orderByCol}` {$columnSortOrder} ";
    if ($orderByCol !== 'id') {
        $orderClause .= ", id DESC ";
    }

    // Selected columns needed by DataTable
    $selectColumns = "id, DI_REF, DI_DATE, AR_CODE, AR_NAME, TRD_AMOUNT_PRICE, BRAND, SKU_CODE, SKU_NAME, TRD_QTY, TRD_Q_FREE, SALE_NAME, TAKE_NAME";

    if ($searchValue === '') {
        ## 1. กรณีไม่มีการค้นหา (Normal Pagination) - ทำงานใน 1-3 ms

        // ดึงจำนวนแถวทั้งหมดอย่างรวดเร็วจาก information_schema (หรือ Session Cache)
        if (!isset($_SESSION['sac_total_sale_all_count']) || (time() - ($_SESSION['sac_total_sale_all_time'] ?? 0)) > 600) {
            $stmtCount = $conn->query("SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ims_data_sale_sac_all'");
            $totalCount = (int)$stmtCount->fetchColumn();
            $stmtCount->closeCursor();
            if ($totalCount <= 0) {
                $stmtExact = $conn->query("SELECT COUNT(*) FROM ims_data_sale_sac_all");
                $totalCount = (int)$stmtExact->fetchColumn();
                $stmtExact->closeCursor();
            }
            $_SESSION['sac_total_sale_all_count'] = $totalCount;
            $_SESSION['sac_total_sale_all_time']  = time();
        }

        $totalRecords = $_SESSION['sac_total_sale_all_count'];
        $totalRecordwithFilter = $totalRecords;

        // ดึงข้อมูลเฉพาะแถวที่แสดงผลตาม Limit/Offset
        $sql_get_data = "SELECT {$selectColumns} FROM ims_data_sale_sac_all {$orderClause} LIMIT :limit, :offset";
        $stmt = $conn->prepare($sql_get_data);
        $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
        $stmt->execute();
        $dataRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

    } else {
        ## 2. กรณีมีการค้นหา (High-Performance Smart Search) - รวดเร็วระดับเสี้ยววินาที
        $trimmed     = trim($searchValue);
        $prefixQ     = "{$trimmed}%";
        $containsQ   = "%{$trimmed}%";
        $fetchLimit  = $rowperpage + 1; // ดึงเกินมา 1 แถวเพื่อเช็ค Next Page โดยไม่ต้องรัน COUNT() ทั้ง 5 แสนแถว

        $totalRecords = $_SESSION['sac_total_sale_all_count'] ?? 500342;

        // 2.1 ลองค้นหาแบบ Prefix ก่อน (Index Range Scan บน DI_REF, AR_CODE, SKU_CODE, AR_NAME ใช้เวลาเพียง 5-30 ms)
        $sqlPrefix = "SELECT {$selectColumns} FROM ims_data_sale_sac_all 
                      WHERE (DI_REF LIKE :pq OR AR_CODE LIKE :pq OR SKU_CODE LIKE :pq OR AR_NAME LIKE :pq) 
                      ORDER BY id DESC LIMIT :limit, :offset";
        $stmt = $conn->prepare($sqlPrefix);
        $stmt->bindValue(':pq', $prefixQ, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$fetchLimit, PDO::PARAM_INT);
        $stmt->execute();
        $dataRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        // 2.2 ถ้า Prefix ไม่พบข้อมูล จึงค่อยค้นหาแบบ Substring Search (Single Query ไม่ต้องรัน Count ซ้ำซ้อน)
        if (count($dataRecords) === 0) {
            $sqlFull = "SELECT {$selectColumns} FROM ims_data_sale_sac_all 
                        WHERE (AR_NAME LIKE :cq OR DI_REF LIKE :cq OR SKU_CODE LIKE :cq OR SALE_NAME LIKE :cq OR TAKE_NAME LIKE :cq OR AR_CODE LIKE :cq) 
                        ORDER BY id DESC LIMIT :limit, :offset";
            $stmt = $conn->prepare($sqlFull);
            $stmt->bindValue(':cq', $containsQ, PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$fetchLimit, PDO::PARAM_INT);
            $stmt->execute();
            $dataRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        }

        // 2.3 ตรวจสอบสถานะหน้าถัดไป (Peek Strategy) โดยไม่ต้อง Scan 500k แถวเพื่อ COUNT
        $hasNext = count($dataRecords) > $rowperpage;
        if ($hasNext) {
            array_pop($dataRecords);
            $totalRecordwithFilter = $row + $rowperpage + 10;
        } else {
            $totalRecordwithFilter = $row + count($dataRecords);
        }
    }

    ## จัดรูปแบบข้อมูลส่งกลับ DataTables
    $data = [];
    $isMaster = ($_POST['sub_action'] === "GET_MASTER");

    foreach ($dataRecords as $r) {
        if ($isMaster) {
            $data[] = [
                "DI_REF"           => $r['DI_REF'],
                "DI_DATE"          => $r['DI_DATE'],
                "AR_NAME"          => $r['AR_NAME'],
                "TRD_AMOUNT_PRICE" => $r['TRD_AMOUNT_PRICE'],
                "BRAND"            => $r['BRAND'],
                "SKU_CODE"         => $r['SKU_CODE'],
                "SKU_NAME"         => $r['SKU_NAME'],
                "TRD_QTY"          => $r['TRD_QTY'],
                "TRD_Q_FREE"       => $r['TRD_Q_FREE'],
                "SALE_NAME"        => $r['SALE_NAME'],
                "TAKE_NAME"        => $r['TAKE_NAME']
            ];
        } else {
            $data[] = [
                "DI_REF"  => $r['DI_REF'],
                "DI_DATE" => $r['DI_DATE'],
                "AR_NAME" => $r['AR_NAME'],
                "select"  => "<button type='button' name='select' id='" . htmlspecialchars($r['AR_CODE']) . "@" . htmlspecialchars($r['AR_NAME']) . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i></button>"
            ];
        }
    }

    ## Response Return Value
    $response = [
        "draw"            => $draw,
        "iTotalRecords"   => (int)$totalRecords,
        "iTotalDisplayRecords" => (int)$totalRecordwithFilter,
        "aaData"          => $data
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}
