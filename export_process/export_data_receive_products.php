<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Receive_Products-" . date('m-d-Y_His', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_info_receive_products.php');

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);

$String_Sql = $str_query_select . $str_query_from . $str_query_where . " AND DOCINFO.DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
    . $str_query_order;

$columns = [
    'DI_REF',
    'DI_DATE',
    'DI_CRE_BY',
    'AP_CODE',
    'AP_NAME',
    'APCAT_CODE',
    'APCAT_NAME',
    'APCD_NAME',
    'APD_DUE_DA',
    'APD_CHQ_DA',
    'TRH_SHIP_DATE',
    'SB_NAME',
    'DEPT_CODE',
    'DEPT_THAIDESC',
    'DEPT_ENGDESC',
    'PRJ_CODE',
    'PRJ_NAME',
    'TRD_SH_CODE',
    'TRD_SH_NAME',
    'TRD_LOT_NO',
    'TRD_SERIAL',
    'TRD_QTY',
    'TRD_SH_QTY',
    'TRD_Q_FREE',
    'TRD_SH_UPRC',
    'TRD_G_KEYIN',
    'TRD_DSC_KEYIN',
    'TRD_DSC_KEYINV',
    'TRD_TDSC_KEYINV',
    'TRD_U_PRC',
    'TRD_G_SELL',
    'TRD_G_VAT',
    'TRD_G_AMT',
    'TRD_B_SELL',
    'TRD_B_VAT',
    'TRD_B_AMT',
    'TRD_VAT_TY',
    'TRD_UTQNAME',
    'TRD_UTQQTY',
    'TRD_VAT_R',
    'TRD_REFER_REF',
    'VAT_RATE',
    'VAT_REF',
    'VAT_DATE',
    'APD_G_SV',
    'APD_G_SNV',
    'APD_G_VAT',
    'APD_B_SV',
    'APD_B_SNV',
    'APD_B_VAT',
    'APD_B_AMT',
    'APD_G_KEYIN',
    'TRH_N_QTY',
    'TRH_N_ITEMS',
    'APD_TDSC_KEYIN',
    'APD_TDSC_KEYINV',
    'WH_CODE',
    'WH_NAME',
    'WL_CODE',
    'WL_NAME',
    'TRD_SH_REMARK',
    'APD_BIL_DA',
    'BR_CODE',
    'DI_ACTIVE'
];

function format_date_dmy($date_str) {
    if (empty($date_str)) {
        return '';
    }
    $date_str = trim((string)$date_str);
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date_str)) {
        return $date_str;
    }
    if (preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})/', $date_str, $m)) {
        return $m[3] . '/' . $m[2] . '/' . $m[1];
    }
    $ts = strtotime($date_str);
    if ($ts !== false && $ts > 0) {
        return date('d/m/Y', $ts);
    }
    return $date_str;
}

$date_columns = ['DI_DATE', 'APD_DUE_DA', 'APD_CHQ_DA', 'VAT_DATE', 'TRH_SHIP_DATE', 'APD_BIL_DA'];

$data = implode(',', $columns) . "\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $line = [];
    foreach ($columns as $col) {
        $val = isset($row[$col]) ? $row[$col] : '';
        if (in_array($col, $date_columns)) {
            $val = format_date_dmy($val);
        }
        $val = trim((string)$val);
        $val = str_replace(["\r", "\n"], ' ', $val);
        $val = str_replace(',', '^', $val);
        $line[] = $val;
    }
    $data .= implode(',', $line) . "\n";
}

$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();
