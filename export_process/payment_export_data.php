<?php
/**
 * BPlus AR Payment Data Export to XLSX (Standalone - No Composer/Vendor Required)
 * สร้างไฟล์ .xlsx ตรงตามโครงสร้าง Business Plus (BplusData) 31 คอลัมน์ 100%
 */

// ป้องกัน Memory Limit และ Timeout สำหรับข้อมูลขนาดใหญ่
ini_set('memory_limit', '512M');
set_time_limit(180);

include(__DIR__ . '/../config/connect_sqlserver.php');

// 1. รับค่าเงื่อนไขวันที่
$start_date = !empty($_REQUEST['doc_date_start']) ? $_REQUEST['doc_date_start'] : (!empty($_REQUEST['start_date']) ? $_REQUEST['start_date'] : (!empty($_REQUEST['date_from']) ? $_REQUEST['date_from'] : date('Y-m-01')));
$end_date   = !empty($_REQUEST['doc_date_to']) ? $_REQUEST['doc_date_to'] : (!empty($_REQUEST['end_date']) ? $_REQUEST['end_date'] : (!empty($_REQUEST['date_to']) ? $_REQUEST['date_to'] : date('Y-m-d')));

function toBplusDate($phpDate) {
    if (strpos($phpDate, '-') !== false && strlen($phpDate) == 10) {
        $parts = explode('-', $phpDate);
        if (strlen($parts[0]) == 2 && strlen($parts[2]) == 4) {
            // dd-mm-yyyy -> m/d/Y
            return $parts[1] . '/' . $parts[0] . '/' . $parts[2];
        }
    }
    return (new DateTime($phpDate))->format('m/d/Y');
}

function allocKey($k, $p, $t) {
    return "{$k}_{$p}_{$t}";
}

function emptyRow($k, $p, $src, $ar, $t) {
    return compact('k', 'p', 'src', 'ar', 't') + [
        'A_AMT' => 0.0, 'CASH_DSC' => 0.0, 'WH_TAX' => 0.0, 'CASH' => 0.0, 'CASHAC' => 0,
        'TRANSF' => 0.0, 'BNKAC' => 0, 'OTHER' => 0.0, 'PMT' => 0, 'DN' => 0.0, 'CN' => 0.0,
        'SHRT' => 0.0, 'FEE' => 0.0, 'INT' => 0.0, 'LOSTXCHG' => 0.0, 'OVER' => 0.0,
    ];
}

// 2. เชื่อมต่อฐานข้อมูล MSSQL ผ่าน PDO
if (!isset($conn_sqlsvr) || !$conn_sqlsvr) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: ไม่พบการเชื่อมต่อ MSSQL");
}
$pdo = $conn_sqlsvr;
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$bFrom = toBplusDate($start_date);
$bTo   = toBplusDate($end_date);

// -------------------------------------------------------------
// 3. ดึงข้อมูลจากฐานข้อมูลตามขั้นตอน BPlus SQL Trace
// -------------------------------------------------------------

// 3.1 ดึงรายการรับชำระหนี้ตามใบเสร็จ (TRANPAYA - DT_PROPERTIES = 404)
$stmt = $pdo->prepare("
    SELECT DI_KEY, TPA_REFER_DI, AR_KEY, TPA_B4_A_AMT, TPA_DISC_A
    FROM TRANPAYA WITH (NOLOCK)
    JOIN TRANPAYH WITH (NOLOCK) ON TPH_KEY = TPA_TPH
    JOIN DOCINFO WITH (NOLOCK) ON TPH_DI = DI_KEY
    JOIN DOCTYPE WITH (NOLOCK) ON DI_DT = DT_KEY AND DT_PROPERTIES = 404
    JOIN ARFILE WITH (NOLOCK) ON TPH_AR = AR_KEY
    JOIN ARCAT WITH (NOLOCK) ON AR_ARCAT = ARCAT_KEY
    WHERE DI_ACTIVE = 0 AND DI_DATE >= ? AND DI_DATE <= ?
");
$stmt->execute([$bFrom, $bTo]);
$tpaRows = $stmt->fetchAll();

// 3.2 ดึงรายการขายสด/รับชำระตรง (Direct Cash Sales/Receipts)
$stmt = $pdo->prepare("
    SELECT DI_KEY, ARD_AR, ARD_A_AMT
    FROM ARDETAIL WITH (NOLOCK)
    JOIN (
        SELECT DISTINCT DI_KEY FROM DOCINFO WITH (NOLOCK)
        JOIN DOCTYPE WITH (NOLOCK) ON DI_DT = DT_KEY
        AND (DT_PROPERTIES IN (402, 403, 302, 337, 408, 409, 803))
        JOIN ARPAYMENT WITH (NOLOCK) ON ARP_DI = DI_KEY
        JOIN ARDETAIL WITH (NOLOCK) ON ARD_DI = DI_KEY
        JOIN ARFILE WITH (NOLOCK) ON ARD_AR = AR_KEY
        JOIN ARCAT WITH (NOLOCK) ON AR_ARCAT = ARCAT_KEY
        WHERE DI_ACTIVE = 0 AND DI_DATE >= ? AND DI_DATE <= ?
    ) SELECTDI ON ARD_DI = DI_KEY
");
$stmt->execute([$bFrom, $bTo]);
$ardRows = $stmt->fetchAll();

// 3.3 ดึงรายการตัดชำระเงินประเภทต่างๆ (ARPAYMENT)
$stmt = $pdo->prepare("
    SELECT ARP.ARP_KEY, ARP.ARP_DI, ARP.ARP_ARD, ARP.ARP_BAHT, ARP.ARP_PMT,
           ARP.ARP_BNKAC, ARP.ARP_CQIN, ARD.ARD_DI, ARD.ARD_AR
    FROM ARPAYMENT ARP WITH (NOLOCK)
    JOIN ARDETAIL ARD WITH (NOLOCK) ON ARP.ARP_ARD = ARD.ARD_KEY
    JOIN DOCINFO DI WITH (NOLOCK) ON ARP.ARP_DI = DI.DI_KEY
    JOIN DOCTYPE DT WITH (NOLOCK) ON DI.DI_DT = DT.DT_KEY
    WHERE DI.DI_ACTIVE = 0 AND DI.DI_DATE >= ? AND DI.DI_DATE <= ?
    AND (DT.DT_PROPERTIES IN (404, 402, 403, 302, 337, 408, 409, 803))
");
$stmt->execute([$bFrom, $bTo]);
$arpRows = $stmt->fetchAll();

// 3.4 ดึงข้อมูล TRANPAYD สำหรับสมุดเงินสดและเงินโอน
$stmt = $pdo->prepare("
    SELECT TPD_TPH, TPD_PMT, TPD_BAHT, TPD_CASHAC, TPD_BNKAC, TPH_DI, TPH_AR
    FROM TRANPAYD WITH (NOLOCK)
    JOIN TRANPAYH WITH (NOLOCK) ON TPD_TPH = TPH_KEY
    JOIN DOCINFO WITH (NOLOCK) ON TPH_DI = DI_KEY
    JOIN DOCTYPE WITH (NOLOCK) ON DI_DT = DT_KEY
    WHERE DI_ACTIVE = 0 AND DI_DATE >= ? AND DI_DATE <= ?
");
$stmt->execute([$bFrom, $bTo]);
$tpdRows = $stmt->fetchAll();

$tpdCashByDi = [];
foreach ($tpdRows as $t) {
    if ((int)$t['TPD_PMT'] === 51) {
        $tpdCashByDi[(int)$t['TPH_DI']] = (int)$t['TPD_CASHAC'];
    }
}

// 3.5 ดึง TRANPAYH สำหรับฐานคำนวณ Overpayment (DT_PROPERTIES = 404)
$stmt = $pdo->prepare("
    SELECT DISTINCT TPH_DI, TPH_AR
    FROM TRANPAYH WITH (NOLOCK)
    JOIN DOCINFO WITH (NOLOCK) ON TPH_DI = DI_KEY
    JOIN DOCTYPE WITH (NOLOCK) ON DI_DT = DT_KEY AND DT_PROPERTIES = 404
    JOIN ARFILE WITH (NOLOCK) ON TPH_AR = AR_KEY
    JOIN ARCAT WITH (NOLOCK) ON AR_ARCAT = ARCAT_KEY
    WHERE DI_ACTIVE = 0 AND DI_DATE >= ? AND DI_DATE <= ?
");
$stmt->execute([$bFrom, $bTo]);
$tpaBase = $stmt->fetchAll();

// -------------------------------------------------------------
// 4. ประกอบข้อมูลลง Array (จำลอง T1E95CA2A7841)
// -------------------------------------------------------------
$alloc = [];

// 4.1 Base Debt จาก TRANPAYA
foreach ($tpaRows as $r) {
    $key = allocKey((int)$r['DI_KEY'], (int)$r['TPA_REFER_DI'], 1);
    if (!isset($alloc[$key])) {
        $alloc[$key] = emptyRow((int)$r['DI_KEY'], (int)$r['TPA_REFER_DI'], 1, (int)$r['AR_KEY'], 1);
    }
    $alloc[$key]['A_AMT'] += (float)$r['TPA_B4_A_AMT'];
    $alloc[$key]['CASH_DSC'] += (float)$r['TPA_DISC_A'];
}

// 4.2 Base Debt จาก ARDETAIL
foreach ($ardRows as $r) {
    $key = allocKey((int)$r['DI_KEY'], (int)$r['DI_KEY'], 1);
    if (!isset($alloc[$key])) {
        $alloc[$key] = emptyRow((int)$r['DI_KEY'], (int)$r['DI_KEY'], 1, (int)$r['ARD_AR'], 1);
    }
    $alloc[$key]['A_AMT'] += (float)$r['ARD_A_AMT'];
}

// 4.3 ประเภทการชำระเงิน (ARPAYMENT)
foreach ($arpRows as $r) {
    $arpDi = (int)$r['ARP_DI'];
    $ardDi = (int)$r['ARD_DI'];
    $ardAr = (int)$r['ARD_AR'];
    $baht  = (float)$r['ARP_BAHT'];
    $pmt   = (int)$r['ARP_PMT'];

    $baseKey = allocKey($arpDi, $ardDi, 1);
    if (!isset($alloc[$baseKey])) {
        continue;
    }

    $type = 0;
    $field = '';
    switch ($pmt) {
        case 53: $type = 3;  $field = 'WH_TAX'; break;
        case 51:
            if (isset($tpdCashByDi[$arpDi])) {
                $type = 4; $field = 'CASH';
            }
            break;
        case 63: $type = 5;  $field = 'TRANSF'; break;
        case 54: $type = 10; $field = 'DN'; break;
        case 55: $type = 11; $field = 'CN'; break;
        case 57: $type = 12; $field = 'SHRT'; break;
        case 58: $type = 13; $field = 'FEE'; break;
        case 59: $type = 14; $field = 'INT'; break;
        case 65: $type = 15; $field = 'LOSTXCHG'; break;
        default:
            if ($pmt > 100) {
                $type = 9; $field = 'OTHER';
            }
            break;
    }

    if ($type === 0) {
        continue;
    }

    $key = allocKey($arpDi, $ardDi, $type);
    if (!isset($alloc[$key])) {
        $alloc[$key] = emptyRow($arpDi, $ardDi, 1, $ardAr, $type);
    }
    $alloc[$key][$field] += $baht;

    if ($pmt === 51) {
        $alloc[$key]['CASHAC'] = $tpdCashByDi[$arpDi] ?? 0;
    } elseif ($pmt === 63) {
        $alloc[$key]['BNKAC'] = (int)$r['ARP_BNKAC'];
    } elseif ($pmt > 100) {
        $alloc[$key]['PMT'] = $pmt;
    }
}

// -------------------------------------------------------------
// 5. คำนวณยอดรับเกิน Overpayment (จำลอง T513ADAD51065)
// -------------------------------------------------------------
$overBase = [];
foreach ($tpaBase as $r) {
    $di = (int)$r['TPH_DI'];
    $ar = (int)$r['TPH_AR'];
    $overBase["{$di}_1"] = ['DI' => $di, 'AR' => $ar, 'TPD' => 0.0, 'ARP' => 0.0];
}

foreach ($tpdRows as $r) {
    $di = (int)$r['TPH_DI'];
    $ar = (int)$r['TPH_AR'];
    $k = "{$di}_2";
    if (!isset($overBase[$k])) {
        $overBase[$k] = ['DI' => $di, 'AR' => $ar, 'TPD' => 0.0, 'ARP' => 0.0];
    }
    $overBase[$k]['TPD'] += (float)$r['TPD_BAHT'];
}

foreach ($arpRows as $r) {
    $pmt = (int)$r['ARP_PMT'];
    if ($pmt == 52 || $pmt == 53) continue;
    $di = (int)$r['ARP_DI'];
    $k = "{$di}_3";
    if (!isset($overBase[$k])) {
        $ar = isset($overBase["{$di}_1"]) ? $overBase["{$di}_1"]['AR'] : 0;
        $overBase[$k] = ['DI' => $di, 'AR' => $ar, 'TPD' => 0.0, 'ARP' => 0.0];
    }
    $overBase[$k]['ARP'] += (float)$r['ARP_BAHT'];
}

$overGrouped = [];
foreach ($overBase as $key => $o) {
    $di = (int)$o['DI'];
    if (!isset($overBase["{$di}_1"])) continue;
    if (!isset($overGrouped[$di])) {
        $overGrouped[$di] = ['AR' => (int)$o['AR'], 'TPD' => 0.0, 'ARP' => 0.0];
    }
    if (substr($key, -2) === '_2') $overGrouped[$di]['TPD'] = $o['TPD'];
    if (substr($key, -2) === '_3') $overGrouped[$di]['ARP'] = $o['ARP'];
}

// สร้าง Header/Overpayment Row (TARP_SOURCE = 0)
foreach ($overGrouped as $di => $og) {
    $amt = $og['TPD'] - $og['ARP'];
    $key = allocKey($di, $di, 20);
    if (!isset($alloc[$key])) {
        $alloc[$key] = emptyRow($di, $di, 0, $og['AR'], 20);
    }
    $alloc[$key]['OVER'] += $amt;
}

foreach ($tpdRows as $r) {
    if ((int)$r['TPD_PMT'] !== 51) continue;
    $di = (int)$r['TPH_DI'];
    $ar = (int)$r['TPH_AR'];
    if (!isset($overGrouped[$di])) continue;
    $key = allocKey($di, $di, 21);
    if (!isset($alloc[$key])) {
        $alloc[$key] = emptyRow($di, $di, 0, $ar, 21);
    }
    $alloc[$key]['CASH'] += (float)$r['TPD_BAHT'];
    $alloc[$key]['CASHAC'] = (int)$r['TPD_CASHAC'];
}

foreach ($tpdRows as $r) {
    if ((int)$r['TPD_PMT'] !== 63) continue;
    $di = (int)$r['TPH_DI'];
    $ar = (int)$r['TPH_AR'];
    if (!isset($overGrouped[$di])) continue;
    $key = allocKey($di, $di, 22);
    if (!isset($alloc[$key])) {
        $alloc[$key] = emptyRow($di, $di, 0, $ar, 22);
    }
    $alloc[$key]['TRANSF'] += (float)$r['TPD_BAHT'];
    $alloc[$key]['BNKAC'] = (int)$r['TPD_BNKAC'];
}

foreach ($tpdRows as $r) {
    if ((int)$r['TPD_PMT'] <= 100) continue;
    $di = (int)$r['TPH_DI'];
    $ar = (int)$r['TPH_AR'];
    if (!isset($overGrouped[$di])) continue;
    $key = allocKey($di, $di, 23);
    if (!isset($alloc[$key])) {
        $alloc[$key] = emptyRow($di, $di, 0, $ar, 23);
    }
    $alloc[$key]['OTHER'] += (float)$r['TPD_BAHT'];
    $alloc[$key]['PMT'] = (int)$r['TPD_PMT'];
}

// -------------------------------------------------------------
// 6. จัดกลุ่มข้อมูล Group By และแตกรายการเช็ค (จำลอง T8BEA65822664)
// -------------------------------------------------------------
$aggGroups = [];
$neededDIs = [];

foreach ($alloc as $a) {
    $neededDIs[(int)$a['k']] = true;
    $neededDIs[(int)$a['p']] = true;
    $gk = "{$a['k']}_{$a['p']}_{$a['src']}_{$a['ar']}";
    if (!isset($aggGroups[$gk])) {
        $aggGroups[$gk] = [
            'K' => (int)$a['k'], 'P' => (int)$a['p'], 'SRC' => (int)$a['src'], 'AR' => (int)$a['ar'],
            'A_AMT' => 0.0, 'CASH_DSC' => 0.0, 'WH_TAX' => 0.0, 'CASH' => 0.0, 'CASHAC' => 0,
            'TRANSF' => 0.0, 'BNKAC' => 0, 'OTHER' => 0.0, 'PMT' => 0, 'DN' => 0.0, 'CN' => 0.0,
            'SHRT' => 0.0, 'FEE' => 0.0, 'INT' => 0.0, 'LOSTXCHG' => 0.0, 'OVER' => 0.0,
        ];
    }
    $g = &$aggGroups[$gk];
    $g['A_AMT'] += $a['A_AMT']; $g['CASH_DSC'] += $a['CASH_DSC']; $g['WH_TAX'] += $a['WH_TAX'];
    $g['CASH'] += $a['CASH']; $g['TRANSF'] += $a['TRANSF']; $g['OTHER'] += $a['OTHER'];
    $g['DN'] += $a['DN']; $g['CN'] += $a['CN']; $g['SHRT'] += $a['SHRT'];
    $g['FEE'] += $a['FEE']; $g['INT'] += $a['INT']; $g['LOSTXCHG'] += $a['LOSTXCHG'];
    $g['OVER'] += $a['OVER'];
    if ($a['CASHAC']) $g['CASHAC'] = (int)$a['CASHAC'];
    if ($a['BNKAC']) $g['BNKAC'] = (int)$a['BNKAC'];
    if ($a['PMT']) $g['PMT'] = (int)$a['PMT'];
    unset($g);
}

// 6.1 โหลด Master / Reference Tables
$arRef = []; $dtRef = []; $pmtRef = []; $cashRef = []; $bnkRef = []; $bankRef = []; $docRef = [];

$r = $pdo->query("SELECT AR_KEY, AR_CODE, AR_NAME, ARCAT_CODE, ARCAT_NAME FROM ARFILE WITH (NOLOCK) JOIN ARCAT WITH (NOLOCK) ON AR_ARCAT=ARCAT_KEY JOIN DEPTTAB WITH (NOLOCK) ON AR_DEPT=DEPT_KEY WHERE AR_KEY>=0");
while ($x = $r->fetch()) $arRef[(int)$x['AR_KEY']] = $x;

$r = $pdo->query("SELECT DT_KEY, DT_DOCCODE, DT_THAIDESC FROM DOCTYPE WITH (NOLOCK)");
while ($x = $r->fetch()) $dtRef[(int)$x['DT_KEY']] = $x;

$r = $pdo->query("SELECT PMT_KEY, PMT_NAME FROM PAYMENTTYPE WITH (NOLOCK)");
while ($x = $r->fetch()) $pmtRef[(int)$x['PMT_KEY']] = $x;

$r = $pdo->query("SELECT CASHAC_KEY, CASHAC_CODE FROM CASHACCOUNT WITH (NOLOCK)");
while ($x = $r->fetch()) $cashRef[(int)$x['CASHAC_KEY']] = $x;

$r = $pdo->query("SELECT BNKAC_KEY, BNKAC_CODE FROM BANKACCOUNT WITH (NOLOCK)");
while ($x = $r->fetch()) $bnkRef[(int)$x['BNKAC_KEY']] = $x;

$r = $pdo->query("SELECT BANK_KEY, BANK_INITIAL FROM BANKFILE WITH (NOLOCK)");
while ($x = $r->fetch()) $bankRef[(int)$x['BANK_KEY']] = $x;

// 6.2 โหลด CHEQUEIN
$keyinDiList = array_unique(array_column($aggGroups, 'K'));
$cqinByDi = [];
$cqinByKey = [];
for ($i = 0; $i < count($keyinDiList); $i += 1000) {
    $chunk = array_slice($keyinDiList, $i, 1000);
    $inSql = implode(',', $chunk);
    $r = $pdo->query("SELECT CQIN_KEY, CQIN_DI, CQIN_CHEQUE_NO, CQIN_CHEQUE_DD, CQIN_A_AMT, CQIN_BANK FROM CHEQUEIN WITH (NOLOCK) WHERE CQIN_DI IN ($inSql)");
    while ($x = $r->fetch()) {
        $cqinByDi[(int)$x['CQIN_DI']][] = $x;
        $cqinByKey[(int)$x['CQIN_KEY']] = $x;
    }
}

// 6.3 โหลด DOCINFO
$diList = array_keys($neededDIs);
for ($i = 0; $i < count($diList); $i += 1000) {
    $chunk = array_slice($diList, $i, 1000);
    $inSql = implode(',', $chunk);
    $r = $pdo->query("SELECT DI_KEY, DI_DATE, DI_REF, DI_DT FROM DOCINFO WITH (NOLOCK) WHERE DI_KEY IN ($inSql)");
    while ($x = $r->fetch()) {
        $docRef[(int)$x['DI_KEY']] = $x;
    }
}

// 6.4 จัดการ Cheque Matching และแตกแถว
$aggFinal = [];
foreach ($aggGroups as $g) {
    $keyinDi = (int)$g['K'];
    $src     = (int)$g['SRC'];
    $paidDi  = (int)$g['P'];

    if ($src === 0) {
        if (isset($cqinByDi[$keyinDi]) && count($cqinByDi[$keyinDi]) > 0) {
            foreach ($cqinByDi[$keyinDi] as $cq) {
                $aggFinal[] = $g + ['CQIN_KEY' => (int)$cq['CQIN_KEY'], 'CQIN_AMT' => (float)$cq['CQIN_A_AMT']];
            }
        } else {
            $aggFinal[] = $g + ['CQIN_KEY' => 0, 'CQIN_AMT' => 0.0];
        }
    } else {
        $cqPayments = [];
        foreach ($arpRows as $arp) {
            if ((int)$arp['ARP_PMT'] === 62 && (int)$arp['ARP_DI'] === $keyinDi && (int)$arp['ARD_DI'] === $paidDi) {
                $ck = (int)$arp['ARP_CQIN'];
                if ($ck) {
                    $cqPayments[] = [
                        'CQIN_KEY' => $ck,
                        'CQIN_AMT' => (float)$arp['ARP_BAHT']
                    ];
                }
            }
        }

        if (count($cqPayments) > 0) {
            foreach ($cqPayments as $cp) {
                $aggFinal[] = $g + $cp;
            }
        } else {
            $aggFinal[] = $g + ['CQIN_KEY' => 0, 'CQIN_AMT' => 0.0];
        }
    }
}

// 6.5 รวมแถวข้อมูลพร้อมจัดรูปแบบ 31 คอลัมน์
$rows = [];
foreach ($aggFinal as $g) {
    $kd  = (int)$g['K'];
    $pd  = (int)$g['P'];
    $arK = (int)$g['AR'];

    if (!isset($docRef[$kd]) || !isset($docRef[$pd])) continue;
    $dk  = $docRef[$kd];
    $dp  = $docRef[$pd];
    $dtK = (int)$dk['DI_DT'];

    if (!isset($dtRef[$dtK]) || !isset($arRef[$arK])) continue;

    $cqKey  = (int)$g['CQIN_KEY'];
    $cqData = ($cqKey && isset($cqinByKey[$cqKey])) ? $cqinByKey[$cqKey] : null;

    $rows[] = [
        'ARCAT_CODE'     => $arRef[$arK]['ARCAT_CODE'],
        'ARCAT_NAME'     => $arRef[$arK]['ARCAT_NAME'],
        'AR_CODE'        => $arRef[$arK]['AR_CODE'],
        'AR_NAME'        => $arRef[$arK]['AR_NAME'],
        'DT_DOCCODE'     => $dtRef[$dtK]['DT_DOCCODE'],
        'DT_THAIDESC'    => $dtRef[$dtK]['DT_THAIDESC'],
        'DI_DATE'        => (new DateTime($dk['DI_DATE']))->format('d/m/Y'),
        'DI_REF'         => $dk['DI_REF'],
        'ARD_DATE'       => (new DateTime($dp['DI_DATE']))->format('d/m/Y'),
        'ARD_REF'        => $dp['DI_REF'],
        'ARD_A_AMT'      => $g['A_AMT'],
        'ARP_CASH_DSC'   => $g['CASH_DSC'],
        'ARP_WH_TAX'     => $g['WH_TAX'],
        'ARP_CASH'       => $g['CASH'],
        'ARP_CQ_AMT'     => $g['CQIN_AMT'],
        'ARP_TRANSF'     => $g['TRANSF'],
        'ARP_OTHER'      => $g['OTHER'],
        'ARP_SHRT'       => $g['SHRT'],
        'ARP_FEE'        => $g['FEE'],
        'ARP_INT'        => $g['INT'],
        'ARP_LOSTXCHG'   => $g['LOSTXCHG'],
        'ARP_DN'         => $g['DN'],
        'ARP_CN'         => $g['CN'],
        'ARP_OVER'       => $g['OVER'],
        'CASHAC_CODE'    => ($g['CASHAC'] && isset($cashRef[$g['CASHAC']])) ? $cashRef[$g['CASHAC']]['CASHAC_CODE'] : null,
        'BNKAC_CODE'     => ($g['BNKAC'] && isset($bnkRef[$g['BNKAC']])) ? $bnkRef[$g['BNKAC']]['BNKAC_CODE'] : null,
        'BANK_INITIAL'   => ($cqData && ($bk = (int)$cqData['CQIN_BANK']) && isset($bankRef[$bk])) ? $bankRef[$bk]['BANK_INITIAL'] : null,
        'CQ_CHEQUE_NO'   => ($cqData) ? $cqData['CQIN_CHEQUE_NO'] : null,
        'CQ_CHEQUE_DD'   => ($cqData && !empty($cqData['CQIN_CHEQUE_DD'])) ? (new DateTime($cqData['CQIN_CHEQUE_DD']))->format('d/m/Y') : null,
        'PMT_NAME'       => ($g['PMT'] && isset($pmtRef[$g['PMT']])) ? $pmtRef[$g['PMT']]['PMT_NAME'] : null,
        'ARP_SOURCE'     => (int)$g['SRC'],
    ];
}

// จัดเรียงลำดับตาม BPlus Order By
usort($rows, function ($a, $b) {
    $c = strcmp($a['DT_DOCCODE'], $b['DT_DOCCODE']); if ($c) return $c;
    $c = strcmp($a['DI_DATE'], $b['DI_DATE']); if ($c) return $c;
    $c = strcmp($a['DI_REF'], $b['DI_REF']); if ($c) return $c;
    $c = $a['ARP_SOURCE'] - $b['ARP_SOURCE']; if ($c) return $c;
    $c = strcmp($a['ARD_DATE'], $b['ARD_DATE']); if ($c) return $c;
    return strcmp($a['ARD_REF'], $b['ARD_REF']);
});

// -------------------------------------------------------------
// 7. สร้างไฟล์ .XLSX ด้วย Native ZipArchive (ไม่ต้องพึ่ง Composer)
// -------------------------------------------------------------
$headers = [
    'ARCAT_CODE', 'ARCAT_NAME', 'AR_CODE', 'AR_NAME', 'DT_DOCCODE', 'DT_THAIDESC',
    'DI_DATE', 'DI_REF', 'ARD_DATE', 'ARD_REF', 'ARD_A_AMT', 'ARP_CASH_DSC',
    'ARP_WH_TAX', 'ARP_CASH', 'ARP_CQ_AMT', 'ARP_TRANSF', 'ARP_OTHER', 'ARP_SHRT',
    'ARP_FEE', 'ARP_INT', 'ARP_LOSTXCHG', 'ARP_DN', 'ARP_CN', 'ARP_OVER',
    'CASHAC_CODE', 'BNKAC_CODE', 'BANK_INITIAL', 'CQ_CHEQUE_NO', 'CQ_CHEQUE_DD',
    'PMT_NAME', 'ARP_SOURCE'
];

$sql_start_date = date('Ymd', strtotime($start_date));
$sql_end_date   = date('Ymd', strtotime($end_date));
$filename       = "payment-{$sql_start_date}-{$sql_end_date}.xlsx";

$tempFile = tempnam(sys_get_temp_dir(), 'bplus_xlsx_');

$zip = new ZipArchive();
if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("ไม่สามารถสร้างไฟล์ Excel ชั่วคราวได้");
}

// [Content_Types].xml
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

// _rels/.rels
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

// xl/_rels/workbook.xml.rels
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

// xl/workbook.xml
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="BplusData" sheetId="1" r:id="rId1"/></sheets>
</workbook>');

// xl/styles.xml
$zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="2">
  <font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>
  <font><b/><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>
</fonts>
<fills count="2">
  <fill><patternFill patternType="none"/></fill>
  <fill><patternFill patternType="gray125"/></fill>
</fills>
<borders count="1">
  <border><left/><right/><top/><bottom/><diagonal/></border>
</borders>
<cellStyleXfs count="1">
  <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
</cellStyleXfs>
<cellXfs count="3">
  <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
  <xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
  <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
</cellXfs>
<cellStyles count="1">
  <cellStyle name="Normal" xfId="0" builtinId="0"/>
</cellStyles>
</styleSheet>');

// xl/worksheets/sheet1.xml
$moneyCols = [
    'ARD_A_AMT', 'ARP_CASH_DSC', 'ARP_WH_TAX', 'ARP_CASH', 'ARP_CQ_AMT',
    'ARP_TRANSF', 'ARP_OTHER', 'ARP_SHRT', 'ARP_FEE', 'ARP_INT',
    'ARP_LOSTXCHG', 'ARP_DN', 'ARP_CN', 'ARP_OVER'
];
$moneySet = array_flip($moneyCols);

function getColLetter($colIdx) {
    $l = '';
    while ($colIdx > 0) {
        $m = ($colIdx - 1) % 26;
        $l = chr(65 + $m) . $l;
        $colIdx = intval(($colIdx - $m) / 26);
    }
    return $l;
}

$fh = fopen('php://temp', 'w+');
fwrite($fh, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
fwrite($fh, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n");
fwrite($fh, '<sheetData>' . "\n");

// Header Row (Row 1)
fwrite($fh, '<row r="1">');
foreach ($headers as $ci => $h) {
    $cellRef = getColLetter($ci + 1) . '1';
    $hXml = htmlspecialchars($h, ENT_XML1, 'UTF-8');
    fwrite($fh, '<c r="' . $cellRef . '" t="inlineStr" s="2"><is><t>' . $hXml . '</t></is></c>');
}
fwrite($fh, '</row>' . "\n");

// Data Rows
$rowNum = 2;
foreach ($rows as $r) {
    fwrite($fh, '<row r="' . $rowNum . '">');
    foreach ($headers as $ci => $h) {
        $cellRef = getColLetter($ci + 1) . $rowNum;
        $val = $r[$h] ?? null;

        if ($val === null || $val === '') {
            continue;
        }

        if (isset($moneySet[$h])) {
            $numVal = (float)$val;
            fwrite($fh, '<c r="' . $cellRef . '" s="1"><v>' . $numVal . '</v></c>');
        } elseif ($h === 'ARP_SOURCE') {
            $numVal = (int)$val;
            fwrite($fh, '<c r="' . $cellRef . '"><v>' . $numVal . '</v></c>');
        } else {
            $strVal = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
            fwrite($fh, '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $strVal . '</t></is></c>');
        }
    }
    fwrite($fh, '</row>' . "\n");
    $rowNum++;
}

fwrite($fh, '</sheetData></worksheet>');
rewind($fh);

$zip->addFromString('xl/worksheets/sheet1.xml', stream_get_contents($fh));
fclose($fh);
$zip->close();

if (isset($_GET['output_path']) && !empty($_GET['output_path'])) {
    copy($tempFile, $_GET['output_path']);
    @unlink($tempFile);
    echo "Saved to {$_GET['output_path']} (" . filesize($_GET['output_path']) . " bytes)\n";
    exit;
}

// -------------------------------------------------------------
// 8. ส่งออกไฟล์ไปยัง Browser เป็น .xlsx
// -------------------------------------------------------------
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Content-Length: ' . filesize($tempFile));
header('Cache-Control: max-age=0, must-revalidate');
header('Pragma: public');

readfile($tempFile);
@unlink($tempFile);
exit;