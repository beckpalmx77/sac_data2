<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

// Helper function to build fast indexed date conditions
function buildDateConditions($doc_date_start, $doc_date_to, &$whereClauses, &$searchArray) {
    if (empty($doc_date_start) || empty($doc_date_to)) {
        return;
    }
    $doc_date_start = str_replace('/', '-', trim($doc_date_start));
    $doc_date_to = str_replace('/', '-', trim($doc_date_to));

    $d_start = DateTime::createFromFormat('d-m-Y', $doc_date_start);
    $d_to = DateTime::createFromFormat('d-m-Y', $doc_date_to);

    if (!$d_start || !$d_to) {
        return;
    }

    if ($d_start > $d_to) {
        $tmp = $d_start;
        $d_start = $d_to;
        $d_to = $tmp;
    }

    $diffDays = $d_start->diff($d_to)->days;

    if ($diffDays <= 365) {
        $placeholders = [];
        $i = 0;
        $curr = clone $d_start;
        while ($curr <= $d_to) {
            $p1 = "d_sp" . $i;
            $p2 = "d_no" . $i;
            $placeholders[] = ":$p1";
            $placeholders[] = ":$p2";
            $searchArray[$p1] = $curr->format("d/m/Y ");
            $searchArray[$p2] = $curr->format("d/m/Y");
            $curr->modify("+1 day");
            $i++;
        }
        $whereClauses[] = "DI_DATE IN (" . implode(",", $placeholders) . ")";
    } else {
        $whereClauses[] = "DI_YEAR BETWEEN :y_start AND :y_to";
        $searchArray["y_start"] = $d_start->format("Y");
        $searchArray["y_to"] = $d_to->format("Y");
        $whereClauses[] = "STR_TO_DATE(TRIM(DI_DATE), '%d/%m/%Y') BETWEEN :d_start AND :d_to";
        $searchArray["d_start"] = $d_start->format("Y-m-d");
        $searchArray["d_to"] = $d_to->format("Y-m-d");
    }
}

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();

    $sql_get = "SELECT * FROM ims_product_sale_sac WHERE id = :id";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "DI_KEY" => $result['DI_KEY'],
            "DI_REF" => $result['DI_REF'],
            "DI_DATE" => $result['DI_DATE'],
            "DI_MONTH" => $result['DI_MONTH'],
            "DI_MONTH_NAME" => $result['DI_MONTH_NAME'],
            "DI_YEAR" => $result['DI_YEAR'],
            "AR_CODE" => $result['AR_CODE'],
            "AR_NAME" => $result['AR_NAME'],
            "SLMN_SLT" => $result['SLMN_SLT'],
            "SLMN_CODE" => $result['SLMN_CODE'],
            "SLMN_NAME" => $result['SLMN_NAME'],
            "SKU_CODE" => $result['SKU_CODE'],
            "SKU_NAME" => $result['SKU_NAME'],
            "SKU_CAT" => $result['SKU_CAT'],
            "ICCAT_CODE" => $result['ICCAT_CODE'],
            "ICCAT_NAME" => $result['ICCAT_NAME'],
            "TRD_QTY" => number_format((float)$result['TRD_QTY'], 2, '.', ''),
            "TRD_U_PRC" => number_format((float)$result['TRD_U_PRC'], 2, '.', ''),
            "TRD_DSC_KEYINV" => $result['TRD_DSC_KEYINV'],
            "TRD_B_SELL" => number_format((float)$result['TRD_B_SELL'], 2, '.', ''),
            "TRD_B_VAT" => number_format((float)$result['TRD_B_VAT'], 2, '.', ''),
            "TRD_G_KEYIN" => number_format((float)$result['TRD_G_KEYIN'], 2, '.', ''),
            "WL_CODE" => $result['WL_CODE'],
            "BRANCH" => $result['BRANCH'],
            "DT_DOCCODE" => $result['DT_DOCCODE'],
            "TRD_SEQ" => $result['TRD_SEQ'],
            "BRN_CODE" => $result['BRN_CODE'],
            "BRN_NAME" => $result['BRN_NAME'],
            "DI_TIME_CHK" => $result['DI_TIME_CHK'],
            "PGROUP" => $result['PGROUP'],
            "TRD_Q_FREE" => $result['TRD_Q_FREE'],
            "DI_ACTIVE" => $result['DI_ACTIVE']
        );
    }

    echo json_encode($return_arr);
    exit();
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["DI_REF"] !== '' && $_POST["SKU_CODE"] !== '') {
        $DI_REF = $_POST["DI_REF"];
        $DI_DATE = $_POST["DI_DATE"];
        $AR_CODE = $_POST["AR_CODE"];
        $AR_NAME = $_POST["AR_NAME"];
        $SLMN_CODE = $_POST["SLMN_CODE"];
        $SLMN_NAME = $_POST["SLMN_NAME"];
        $SKU_CODE = $_POST["SKU_CODE"];
        $SKU_NAME = $_POST["SKU_NAME"];
        $SKU_CAT = $_POST["SKU_CAT"];
        $TRD_QTY = $_POST["TRD_QTY"];
        $TRD_U_PRC = $_POST["TRD_U_PRC"];
        $TRD_B_SELL = $_POST["TRD_B_SELL"];
        $BRANCH = $_POST["BRANCH"];
        $PGROUP = $_POST["PGROUP"];

        // Parse date for year, month, day if available
        $date_parts = explode('-', str_replace('/', '-', $DI_DATE));
        if (count($date_parts) === 3) {
            $DI_YEAR = $date_parts[2];
            $DI_MONTH = (int)$date_parts[1];
        } else {
            $DI_YEAR = date("Y");
            $DI_MONTH = (int)date("m");
        }

        $sql = "INSERT INTO ims_product_sale_sac (
                    DI_REF, DI_DATE, DI_MONTH, DI_YEAR, AR_CODE, AR_NAME, 
                    SLMN_CODE, SLMN_NAME, SKU_CODE, SKU_NAME, SKU_CAT, 
                    TRD_QTY, TRD_U_PRC, TRD_B_SELL, BRANCH, PGROUP
                ) VALUES (
                    :DI_REF, :DI_DATE, :DI_MONTH, :DI_YEAR, :AR_CODE, :AR_NAME, 
                    :SLMN_CODE, :SLMN_NAME, :SKU_CODE, :SKU_NAME, :SKU_CAT, 
                    :TRD_QTY, :TRD_U_PRC, :TRD_B_SELL, :BRANCH, :PGROUP
                )";
        $query = $conn->prepare($sql);
        $query->bindParam(':DI_REF', $DI_REF, PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $DI_DATE, PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $DI_MONTH, PDO::PARAM_INT);
        $query->bindParam(':DI_YEAR', $DI_YEAR, PDO::PARAM_STR);
        $query->bindParam(':AR_CODE', $AR_CODE, PDO::PARAM_STR);
        $query->bindParam(':AR_NAME', $AR_NAME, PDO::PARAM_STR);
        $query->bindParam(':SLMN_CODE', $SLMN_CODE, PDO::PARAM_STR);
        $query->bindParam(':SLMN_NAME', $SLMN_NAME, PDO::PARAM_STR);
        $query->bindParam(':SKU_CODE', $SKU_CODE, PDO::PARAM_STR);
        $query->bindParam(':SKU_NAME', $SKU_NAME, PDO::PARAM_STR);
        $query->bindParam(':SKU_CAT', $SKU_CAT, PDO::PARAM_STR);
        $query->bindParam(':TRD_QTY', $TRD_QTY, PDO::PARAM_STR);
        $query->bindParam(':TRD_U_PRC', $TRD_U_PRC, PDO::PARAM_STR);
        $query->bindParam(':TRD_B_SELL', $TRD_B_SELL, PDO::PARAM_STR);
        $query->bindParam(':BRANCH', $BRANCH, PDO::PARAM_STR);
        $query->bindParam(':PGROUP', $PGROUP, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();
        if ($lastInsertId) {
            unset($_SESSION['total_records_sale_sac']);
            echo $save_success;
        } else {
            echo $error;
        }
    }
    exit();
}

if ($_POST["action"] === 'UPDATE') {
    if ($_POST["id"] !== '' && $_POST["SKU_CODE"] !== '') {
        $id = $_POST["id"];
        $DI_REF = $_POST["DI_REF"];
        $DI_DATE = $_POST["DI_DATE"];
        $AR_CODE = $_POST["AR_CODE"];
        $AR_NAME = $_POST["AR_NAME"];
        $SLMN_CODE = $_POST["SLMN_CODE"];
        $SLMN_NAME = $_POST["SLMN_NAME"];
        $SKU_CODE = $_POST["SKU_CODE"];
        $SKU_NAME = $_POST["SKU_NAME"];
        $SKU_CAT = $_POST["SKU_CAT"];
        $TRD_QTY = $_POST["TRD_QTY"];
        $TRD_U_PRC = $_POST["TRD_U_PRC"];
        $TRD_B_SELL = $_POST["TRD_B_SELL"];
        $BRANCH = $_POST["BRANCH"];
        $PGROUP = $_POST["PGROUP"];

        $sql_update = "UPDATE ims_product_sale_sac SET 
                        DI_REF=:DI_REF, DI_DATE=:DI_DATE, AR_CODE=:AR_CODE, AR_NAME=:AR_NAME,
                        SLMN_CODE=:SLMN_CODE, SLMN_NAME=:SLMN_NAME, SKU_CODE=:SKU_CODE, SKU_NAME=:SKU_NAME,
                        SKU_CAT=:SKU_CAT, TRD_QTY=:TRD_QTY, TRD_U_PRC=:TRD_U_PRC, TRD_B_SELL=:TRD_B_SELL,
                        BRANCH=:BRANCH, PGROUP=:PGROUP
                       WHERE id = :id";
        $query = $conn->prepare($sql_update);
        $query->bindParam(':DI_REF', $DI_REF, PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $DI_DATE, PDO::PARAM_STR);
        $query->bindParam(':AR_CODE', $AR_CODE, PDO::PARAM_STR);
        $query->bindParam(':AR_NAME', $AR_NAME, PDO::PARAM_STR);
        $query->bindParam(':SLMN_CODE', $SLMN_CODE, PDO::PARAM_STR);
        $query->bindParam(':SLMN_NAME', $SLMN_NAME, PDO::PARAM_STR);
        $query->bindParam(':SKU_CODE', $SKU_CODE, PDO::PARAM_STR);
        $query->bindParam(':SKU_NAME', $SKU_NAME, PDO::PARAM_STR);
        $query->bindParam(':SKU_CAT', $SKU_CAT, PDO::PARAM_STR);
        $query->bindParam(':TRD_QTY', $TRD_QTY, PDO::PARAM_STR);
        $query->bindParam(':TRD_U_PRC', $TRD_U_PRC, PDO::PARAM_STR);
        $query->bindParam(':TRD_B_SELL', $TRD_B_SELL, PDO::PARAM_STR);
        $query->bindParam(':BRANCH', $BRANCH, PDO::PARAM_STR);
        $query->bindParam(':PGROUP', $PGROUP, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo $save_success;
    }
    exit();
}

if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT id FROM ims_product_sale_sac WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        try {
            $sql = "DELETE FROM ims_product_sale_sac WHERE id = :id";
            $query = $conn->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            unset($_SESSION['total_records_sale_sac']);
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
    exit();
}

if ($_POST["action"] === 'EXPLAIN_ANALYZE') {
    $time_start = microtime(true);
    $doc_date_start = isset($_POST['doc_date_start']) ? trim($_POST['doc_date_start']) : '';
    $doc_date_to = isset($_POST['doc_date_to']) ? trim($_POST['doc_date_to']) : '';
    $searchValue = isset($_POST['search_value']) ? trim($_POST['search_value']) : '';

    $searchArray = array();
    $whereClauses = array();

    buildDateConditions($doc_date_start, $doc_date_to, $whereClauses, $searchArray);

    if ($searchValue !== '') {
        $searchDateValue = str_replace('-', '/', $searchValue);
        $whereClauses[] = "(DI_DATE LIKE :s_date OR DI_REF LIKE :s_ref OR AR_CODE LIKE :s_ar_code 
                           OR AR_NAME LIKE :s_ar_name OR SKU_CODE LIKE :s_sku_code OR SKU_NAME LIKE :s_sku_name 
                           OR SLMN_NAME LIKE :s_slmn OR BRANCH LIKE :s_branch)";
        $searchArray['s_date'] = "%$searchDateValue%";
        $searchArray['s_ref'] = "%$searchValue%";
        $searchArray['s_ar_code'] = "%$searchValue%";
        $searchArray['s_ar_name'] = "%$searchValue%";
        $searchArray['s_sku_code'] = "%$searchValue%";
        $searchArray['s_sku_name'] = "%$searchValue%";
        $searchArray['s_slmn'] = "%$searchValue%";
        $searchArray['s_branch'] = "%$searchValue%";
    }

    $whereSQL = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

    $sql_explain = "EXPLAIN ANALYZE 
                    SELECT main.id, main.DI_KEY, main.DI_DATE, main.DI_TIME_CHK, main.DI_REF, 
                           main.AR_CODE, main.AR_NAME, main.SLMN_NAME, main.SKU_CODE, main.SKU_NAME, 
                           main.TRD_QTY, main.TRD_U_PRC, main.TRD_G_KEYIN, main.TRD_B_SELL, main.TRD_B_VAT, 
                           main.BRANCH 
                    FROM ims_product_sale_sac main 
                    JOIN (
                        SELECT id FROM ims_product_sale_sac " . $whereSQL . " 
                        ORDER BY id DESC 
                        LIMIT 0, 10
                    ) AS sub ON main.id = sub.id
                    ORDER BY main.id DESC";

    try {
        $stmt = $conn->prepare($sql_explain);
        foreach ($searchArray as $key => $search) {
            $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
        }
        $stmt->execute();
        $explain_lines = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $explain_text = implode("\n", $explain_lines);
        $time_end = microtime(true);

        echo json_encode([
            "status" => "success",
            "sql" => $sql_explain,
            "explain_result" => $explain_text,
            "exec_time_ms" => round(($time_end - $time_start) * 1000, 2)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
    exit();
}

if ($_POST["action"] === 'GET_DATA_SALE_SAC') {
    $time_start = microtime(true);

    ## Read value
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $row = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 10;
    if ($rowperpage < 1) $rowperpage = 10;

    $columnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : null;
    $columnName = isset($_POST['columns'][$columnIndex]['data']) ? $_POST['columns'][$columnIndex]['data'] : 'id';
    $columnSortOrder = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
    $searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

    $doc_date_start = isset($_POST['doc_date_start']) ? trim($_POST['doc_date_start']) : '';
    $doc_date_to = isset($_POST['doc_date_to']) ? trim($_POST['doc_date_to']) : '';

    $searchArray = array();
    $whereClauses = array();

    ## Filter by Date Range (Fast Indexed Date Matching)
    buildDateConditions($doc_date_start, $doc_date_to, $whereClauses, $searchArray);

    ## Search Keyword
    if ($searchValue !== '') {
        $searchDateValue = str_replace('-', '/', $searchValue);
        $whereClauses[] = "(DI_DATE LIKE :s_date OR DI_REF LIKE :s_ref OR AR_CODE LIKE :s_ar_code 
                           OR AR_NAME LIKE :s_ar_name OR SKU_CODE LIKE :s_sku_code OR SKU_NAME LIKE :s_sku_name 
                           OR SLMN_NAME LIKE :s_slmn OR BRANCH LIKE :s_branch)";
        $searchArray['s_date'] = "%$searchDateValue%";
        $searchArray['s_ref'] = "%$searchValue%";
        $searchArray['s_ar_code'] = "%$searchValue%";
        $searchArray['s_ar_name'] = "%$searchValue%";
        $searchArray['s_sku_code'] = "%$searchValue%";
        $searchArray['s_sku_name'] = "%$searchValue%";
        $searchArray['s_slmn'] = "%$searchValue%";
        $searchArray['s_branch'] = "%$searchValue%";
    }

    $whereSQL = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

    ## Total number of records without filtering (Cached in session for maximum speed)
    if (!isset($_SESSION['total_records_sale_sac']) || empty($_SESSION['total_records_sale_sac'])) {
        $stmt = $conn->prepare("SELECT COUNT(id) AS allcount FROM ims_product_sale_sac");
        $stmt->execute();
        $records = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['total_records_sale_sac'] = (int)($records['allcount'] ?? 0);
    }
    $totalRecords = $_SESSION['total_records_sale_sac'];

    ## Total number of records with filtering
    if (empty($whereClauses)) {
        $totalRecordwithFilter = $totalRecords;
    } else {
        $stmt = $conn->prepare("SELECT COUNT(id) AS allcount FROM ims_product_sale_sac" . $whereSQL);
        $stmt->execute($searchArray);
        $records = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalRecordwithFilter = (int)($records['allcount'] ?? 0);
    }

    ## Map column index for sorting
    $validColumns = array(
        "id", "DI_KEY", "DI_DATE", "DI_TIME_CHK", "DI_REF", "AR_CODE", "AR_NAME", "SLMN_NAME", 
        "SKU_CODE", "SKU_NAME", "TRD_QTY", "TRD_U_PRC", "TRD_G_KEYIN", "TRD_B_SELL", "TRD_B_VAT", "BRANCH"
    );
    $numericColumns = array("TRD_QTY", "TRD_U_PRC", "TRD_G_KEYIN", "TRD_B_SELL", "TRD_B_VAT");

    if ($columnIndex === null || !in_array($columnName, $validColumns)) {
        $columnName = "id";
    }

    if ($columnName === 'id' || $columnName === 'DI_DATE') {
        // Optimal index usage: id is chronological and unique
        $orderSub = "id " . $columnSortOrder;
        $orderMain = "main.id " . $columnSortOrder;
    } elseif (in_array($columnName, $numericColumns)) {
        $orderSub = "CAST(" . $columnName . " AS DECIMAL(12,2)) " . $columnSortOrder . ", id " . $columnSortOrder;
        $orderMain = "CAST(main." . $columnName . " AS DECIMAL(12,2)) " . $columnSortOrder . ", main.id " . $columnSortOrder;
    } else {
        $orderSub = "`" . $columnName . "` " . $columnSortOrder . ", id " . $columnSortOrder;
        $orderMain = "main.`" . $columnName . "` " . $columnSortOrder . ", main.id " . $columnSortOrder;
    }

    ## Fetch records (Optimized with selective columns and Deferred Join)
    $sql_fetch = "SELECT main.id, main.DI_KEY, main.DI_DATE, main.DI_TIME_CHK, main.DI_REF, 
                         main.AR_CODE, main.AR_NAME, main.SLMN_NAME, main.SKU_CODE, main.SKU_NAME, 
                         main.TRD_QTY, main.TRD_U_PRC, main.TRD_G_KEYIN, main.TRD_B_SELL, main.TRD_B_VAT, 
                         main.BRANCH 
                  FROM ims_product_sale_sac main 
                  JOIN (
                      SELECT id FROM ims_product_sale_sac " . $whereSQL . " 
                      ORDER BY " . $orderSub . " 
                      LIMIT :offset, :limit
                  ) AS sub ON main.id = sub.id
                  ORDER BY " . $orderMain;

    $stmt = $conn->prepare($sql_fetch);

    // Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array();

    foreach ($empRecords as $r) {
        $data[] = array(
            "id" => $r['id'],
            "DI_KEY" => $r['DI_KEY'],
            "DI_DATE" => $r['DI_DATE'],
            "DI_TIME_CHK" => $r['DI_TIME_CHK'],
            "DI_REF" => $r['DI_REF'],
            "AR_CODE" => $r['AR_CODE'],
            "AR_NAME" => $r['AR_NAME'],
            "SLMN_NAME" => $r['SLMN_NAME'],
            "SKU_CODE" => $r['SKU_CODE'],
            "SKU_NAME" => $r['SKU_NAME'],
            "TRD_QTY" => number_format((float)$r['TRD_QTY'], 2),
            "TRD_U_PRC" => number_format((float)$r['TRD_U_PRC'], 2),
            "TRD_G_KEYIN" => number_format((float)$r['TRD_G_KEYIN'], 2),
            "TRD_B_SELL" => number_format((float)$r['TRD_B_SELL'], 2),
            "TRD_B_VAT" => number_format((float)$r['TRD_B_VAT'], 2),
            "BRANCH" => $r['BRANCH'],
            "update" => "<button type='button' name='info' id='" . $r['id'] . "' class='btn btn-info btn-xs info' data-toggle='tooltip' title='Info'>Info <i class='fa fa-info-circle'></i></button>",
            "delete" => "<button type='button' name='delete' id='" . $r['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
        );
    }

    $time_end = microtime(true);
    $exec_time_ms = round(($time_end - $time_start) * 1000, 2);

    ## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "exec_time_ms" => $exec_time_ms,
        "aaData" => $data
    );

    echo json_encode($response);
    exit();
}
