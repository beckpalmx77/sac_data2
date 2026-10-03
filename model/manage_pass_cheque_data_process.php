<?php
session_start();
error_reporting(0);

include("../config/connect_sqlserver.php");
include('../config/lang.php');

function convertDateForSql($dateStr) {
    $dateStr = trim($dateStr);
    if (empty($dateStr)) {
        return '';
    }
    if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $dateStr, $matches)) {
        return sprintf('%04d/%02d/%02d', $matches[3], $matches[2], $matches[1]);
    }
    if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $dateStr, $matches)) {
        return sprintf('%04d/%02d/%02d', $matches[1], $matches[2], $matches[3]);
    }
    $ts = strtotime($dateStr);
    return $ts !== false ? date('Y/m/d', $ts) : $dateStr;
}

function formatDisplayDate($dateVal) {
    if (empty($dateVal)) return '';
    if ($dateVal instanceof DateTime) {
        return $dateVal->format('d-m-Y');
    }
    $ts = strtotime($dateVal);
    return $ts !== false ? date('d-m-Y', $ts) : trim((string)$dateVal);
}

// -------------------------------------------------------------
// 1. ดึงข้อมูลเดี่ยวสำหรับแสดงใน Modal (Action: GET_DATA)
// -------------------------------------------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'GET_DATA') {
    $id = isset($_POST["id"]) ? trim($_POST["id"]) : '';
    $return_arr = array();

    if (!empty($id)) {
        $sql_get = "
        SELECT 
          DI_DATE, 
          DI_REF, 
          BNKAC_CODE,
          BNKAC_NAME,
          BANK_INITIAL,
          BANK_T_NAME, 
          CQIN_CHEQUE_NO,
          CQIN_CHEQUE_DD,
          CQIN_OWNER, 
          CQIN_AMT, 
          CQIN_FEE_A,
          DI_ACTIVE,
          BR_CODE,
          DEPT_CODE,
          PRJ_CODE,
          CQIN_KEY
        FROM 
          BRANCH WITH (NOLOCK),
          DEPTTAB WITH (NOLOCK),
          PRJTAB WITH (NOLOCK),
          CHEQUEIN WITH (NOLOCK), 
          BANKFILE WITH (NOLOCK), 
          BANKACCOUNT WITH (NOLOCK), 
          DOCTYPE WITH (NOLOCK), 
          ARFILE WITH (NOLOCK),
          DOCINFO WITH (NOLOCK)
        LEFT OUTER JOIN TRANPAYH WITH (NOLOCK)
            ON TPH_DI=DI_KEY 
        WHERE (CQIN_DI=DI_KEY)
          AND (CQIN_BANK=BANK_KEY)
          AND (CQIN_BNKAC=BNKAC_KEY)
          AND (CQIN_AR=AR_KEY)
          AND (DI_DT=DT_KEY)
          AND (DT_PROPERTIES=612) 
          AND (TPH_DEPT=DEPT_KEY) 
          AND (TPH_PRJ=PRJ_KEY) 
          AND (TPH_BR=BR_KEY)
          AND (CQIN_KEY = :id)
        ";

        try {
            $stmt = $conn_sqlsvr->prepare($sql_get);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $result) {
                $amt = (float)$result['CQIN_AMT'];
                $fee = (float)$result['CQIN_FEE_A'];
                $net = $amt + $fee;
                $return_arr[] = array(
                    "id" => $result['CQIN_KEY'],
                    "DI_DATE" => formatDisplayDate($result['DI_DATE']),
                    "DI_REF" => trim((string)$result['DI_REF']),
                    "BNKAC_CODE" => trim((string)$result['BNKAC_CODE']),
                    "BNKAC_NAME" => trim((string)$result['BNKAC_NAME']),
                    "BANK_INITIAL" => trim((string)$result['BANK_INITIAL']),
                    "BANK_T_NAME" => trim((string)$result['BANK_T_NAME']),
                    "CQIN_CHEQUE_NO" => trim((string)$result['CQIN_CHEQUE_NO']),
                    "CQIN_CHEQUE_DD" => formatDisplayDate($result['CQIN_CHEQUE_DD']),
                    "CQIN_OWNER" => trim((string)$result['CQIN_OWNER']),
                    "CQIN_AMT" => number_format($amt, 2),
                    "CQIN_FEE_A" => number_format($fee, 2),
                    "CQIN_NET" => number_format($net, 2),
                    "DI_ACTIVE" => trim((string)$result['DI_ACTIVE']),
                    "BR_CODE" => trim((string)$result['BR_CODE']),
                    "DEPT_CODE" => trim((string)$result['DEPT_CODE']),
                    "PRJ_CODE" => trim((string)$result['PRJ_CODE'])
                );
            }
        } catch (PDOException $e) {
            // handle error
        }
    }

    echo json_encode($return_arr);
    exit();
}

// -------------------------------------------------------------
// 2. ดึงข้อมูลตาราง DataTables Server-Side (Action: GET_PASS_CHEQUE_DATA)
// -------------------------------------------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'GET_PASS_CHEQUE_DATA') {
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $row = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $ifAll = ($rowperpage == -1);
    if ($ifAll) {
        $rowperpage = 10000;
    }

    $columnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $columnName = isset($_POST['columns'][$columnIndex]['data']) ? $_POST['columns'][$columnIndex]['data'] : 'CQIN_CHEQUE_DD';
    $columnSortOrder = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
    $searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

    $doc_date_start = isset($_POST['doc_date_start']) ? trim($_POST['doc_date_start']) : '';
    $doc_date_to = isset($_POST['doc_date_to']) ? trim($_POST['doc_date_to']) : '';

    $whereClauses = array();
    $params = array();

    // กรองตามช่วงวันที่
    if (!empty($doc_date_start) && !empty($doc_date_to)) {
        $s_date = convertDateForSql($doc_date_start);
        $e_date = convertDateForSql($doc_date_to);
        $whereClauses[] = "(DI_DATE BETWEEN :start_date AND :end_date)";
        $params[':start_date'] = $s_date;
        $params[':end_date'] = $e_date;
    }

    // กรองตามคำค้นหา
    if ($searchValue !== '') {
        $whereClauses[] = "(
            DI_REF LIKE :s_val OR
            BANK_INITIAL LIKE :s_val OR
            BANK_T_NAME LIKE :s_val OR
            BNKAC_NAME LIKE :s_val OR
            CQIN_CHEQUE_NO LIKE :s_val OR
            CQIN_OWNER LIKE :s_val OR
            BR_CODE LIKE :s_val OR
            DEPT_CODE LIKE :s_val
        )";
        $params[':s_val'] = "%" . $searchValue . "%";
    }

    $extraWhere = !empty($whereClauses) ? " AND " . implode(" AND ", $whereClauses) : "";

    $baseFromWhere = "
    FROM 
      BRANCH WITH (NOLOCK),
      DEPTTAB WITH (NOLOCK),
      PRJTAB WITH (NOLOCK),
      CHEQUEIN WITH (NOLOCK), 
      BANKFILE WITH (NOLOCK), 
      BANKACCOUNT WITH (NOLOCK), 
      DOCTYPE WITH (NOLOCK), 
      ARFILE WITH (NOLOCK),
      DOCINFO WITH (NOLOCK)
    LEFT OUTER JOIN TRANPAYH WITH (NOLOCK)
        ON TPH_DI=DI_KEY 
    WHERE (CQIN_DI=DI_KEY)
      AND (CQIN_BANK=BANK_KEY)
      AND (CQIN_BNKAC=BNKAC_KEY)
      AND (CQIN_AR=AR_KEY)
      AND (DI_DT=DT_KEY)
      AND (DT_PROPERTIES=612) 
      AND (TPH_DEPT=DEPT_KEY) 
      AND (TPH_PRJ=PRJ_KEY) 
      AND (TPH_BR=BR_KEY)
    ";

    // 1) นับจำนวนแถวทั้งหมด (แบบไม่มีฟิลเตอร์คำค้น)
    $totalRecords = 0;
    try {
        $stmtTotal = $conn_sqlsvr->prepare("SELECT COUNT(CQIN_KEY) AS allcount " . $baseFromWhere);
        $stmtTotal->execute();
        $resTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
        $totalRecords = intval($resTotal['allcount'] ?? 0);
    } catch (PDOException $e) {
        $totalRecords = 0;
    }

    // 2) นับจำนวนแถวที่มีฟิลเตอร์
    $totalRecordwithFilter = $totalRecords;
    if (!empty($extraWhere)) {
        try {
            $stmtFilter = $conn_sqlsvr->prepare("SELECT COUNT(CQIN_KEY) AS allcount " . $baseFromWhere . $extraWhere);
            foreach ($params as $k => $v) {
                $stmtFilter->bindValue($k, $v);
            }
            $stmtFilter->execute();
            $resFilter = $stmtFilter->fetch(PDO::FETCH_ASSOC);
            $totalRecordwithFilter = intval($resFilter['allcount'] ?? 0);
        } catch (PDOException $e) {
            $totalRecordwithFilter = 0;
        }
    }

    // 3) แมปคอลัมน์การเรียงลำดับ (Order By)
    $validColumns = array(
        'CQIN_CHEQUE_DD' => 'CQIN_CHEQUE_DD',
        'DI_REF' => 'DI_REF',
        'BANK_INITIAL' => 'BANK_INITIAL',
        'BNKAC_NAME' => 'BNKAC_NAME',
        'CQIN_CHEQUE_NO' => 'CQIN_CHEQUE_NO',
        'CQIN_OWNER' => 'CQIN_OWNER',
        'CQIN_AMT' => 'CQIN_AMT',
        'CQIN_FEE_A' => 'CQIN_FEE_A'
    );

    $orderColumn = isset($validColumns[$columnName]) ? $validColumns[$columnName] : 'CQIN_CHEQUE_DD';

    // 4) ดึงข้อมูลพร้อมการแบ่งหน้า (Pagination)
    $sqlData = "
    SELECT 
      DI_DATE, 
      DI_REF, 
      BNKAC_CODE,
      BNKAC_NAME,
      BANK_INITIAL,
      BANK_T_NAME, 
      CQIN_CHEQUE_NO,
      CQIN_CHEQUE_DD,
      CQIN_OWNER, 
      CQIN_AMT, 
      CQIN_FEE_A,
      DI_ACTIVE,
      BR_CODE,
      DEPT_CODE,
      PRJ_CODE,
      CQIN_KEY
    " . $baseFromWhere . $extraWhere . "
    ORDER BY " . $orderColumn . " " . $columnSortOrder . ", CQIN_KEY DESC
    OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY
    ";

    $data = array();
    try {
        $stmtData = $conn_sqlsvr->prepare($sqlData);
        foreach ($params as $k => $v) {
            $stmtData->bindValue($k, $v);
        }
        $stmtData->bindValue(':offset', $row, PDO::PARAM_INT);
        $stmtData->bindValue(':limit', $rowperpage, PDO::PARAM_INT);
        $stmtData->execute();
        $rows = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            $amt = (float)$r['CQIN_AMT'];
            $fee = (float)$r['CQIN_FEE_A'];
            $net = $amt + $fee;

            $data[] = array(
                "CQIN_CHEQUE_DD" => formatDisplayDate($r['CQIN_CHEQUE_DD']),
                "DI_REF" => trim((string)$r['DI_REF']),
                "BANK_INITIAL" => trim((string)$r['BANK_INITIAL']),
                "BNKAC_NAME" => trim((string)$r['BNKAC_NAME']),
                "CQIN_CHEQUE_NO" => trim((string)$r['CQIN_CHEQUE_NO']),
                "CQIN_OWNER" => trim((string)$r['CQIN_OWNER']),
                "CQIN_AMT" => number_format($amt, 2),
                "CQIN_FEE_A" => number_format($fee, 2),
                "CQIN_NET" => number_format($net, 2),
                "update" => "<button type='button' name='info' id='" . $r['CQIN_KEY'] . "' class='btn btn-info btn-xs info' data-toggle='tooltip' title='ดูรายละเอียด'><i class='fa fa-info-circle'></i> Info</button>"
            );
        }
    } catch (PDOException $e) {
        // error handling
    }

    $response = array(
        "draw" => $draw,
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    exit();
}
