<?php

function LAST_ID_YEAR($conn, $table, $field, $doc_year)
{
    $query_str = "select " . $field . " from " . $table
        . " where doc_year = " . $doc_year
        . " order by " . $field . " desc limit 1 ";
    $row = $conn->query($query_str)->fetch();
    if (empty($row) || (!isset($row[0]) && !isset($row[$field]))) {
        $ret_value = 1;
    } else {
        $val = isset($row[0]) ? $row[0] : $row[$field];
        $ret_value = intval($val) + 1;
    }
    return $ret_value;
}

function LAST_ID($conn, $table, $field)
{
    $row = $conn->query("select " . $field . " from " . $table . " order by " . $field . " desc limit 1 ")->fetch();
    if (empty($row) || (!isset($row[0]) && !isset($row[$field]))) {
        $ret_value = 1;
    } else {
        $val = isset($row[0]) ? $row[0] : $row[$field];
        $ret_value = intval($val) + 1;
    }
    return $ret_value;
}

function LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond)
{
    $sql_cmd = "select count(" . $field . ") as record_number from " . $table . " " . $cond;
    $row = $conn->query($sql_cmd)->fetch();
    if (empty($row) || (!isset($row[0]) && !isset($row['record_number']))) {
        $ret_value = 1;
    } else {
        $val = isset($row[0]) ? $row[0] : $row['record_number'];
        $ret_value = intval($val) + 1;
    }
    return $ret_value;
}

function LAST_DOCUMENT_COND($conn, $table, $cond)
{
    $row = $conn->query("select count(*) as record_number from " . $table . " " . $cond)->fetch();
    if (empty($row) || (!isset($row[0]) && !isset($row['record_number']))) {
        $ret_value = 1;
    } else {
        $val = isset($row[0]) ? $row[0] : $row['record_number'];
        $ret_value = intval($val) + 1;
    }
    return $ret_value;
}

function LAST_ID_COND($conn, $table, $cond, $field)
{
    $row = $conn->query("select count(" . $field . ") as record_number from " . $table
        . " where sub_menu_id like '" . $cond . "%'")->fetch();
    if (empty($row) || (!isset($row[0]) && !isset($row['record_number']))) {
        $ret_value = 1;
    } else {
        $val = isset($row[0]) ? $row[0] : $row['record_number'];
        $ret_value = intval($val) + 1;
    }
    return $ret_value;
}

function LAST_RUNNO_COND($conn, $table, $doc_date)
{
    // 1. ตรวจสอบว่ามีอยู่แล้วหรือไม่
    $sql = "SELECT runno FROM m_runno WHERE table_name = :table AND doc_date = :doc_date";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':table' => $table,
        ':doc_date' => $doc_date
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // ถ้ายังไม่มี เริ่มที่ 1 และ insert เข้าไป
        $runno = 1;
        $insert_sql = "INSERT INTO m_runno (table_name, doc_date, runno) VALUES (:table, :doc_date, :runno)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->execute([
            ':table' => $table,
            ':doc_date' => $doc_date,
            ':runno' => $runno
        ]);
    } else {
        // ถ้ามีแล้ว เพิ่ม +1 และ update
        $runno = $row['runno'] + 1;
        $update_sql = "UPDATE m_runno SET runno = :runno WHERE table_name = :table AND doc_date = :doc_date";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute([
            ':runno' => $runno,
            ':table' => $table,
            ':doc_date' => $doc_date
        ]);
    }

    return $runno;
}
