<?php
session_start();
error_reporting(0);

ini_set('memory_limit', '512M');
set_time_limit(180);

include(__DIR__ . "/../config/connect_sqlserver.php");
include(__DIR__ . '/../config/lang.php');

function toBplusDate($phpDate) {
    if (empty($phpDate)) {
        return date('m/d/Y');
    }
    $phpDate = trim($phpDate);
    if (strpos($phpDate, '-') !== false && strlen($phpDate) == 10) {
        $parts = explode('-', $phpDate);
        if (strlen($parts[0]) == 2 && strlen($parts[2]) == 4) {
            // dd-mm-yyyy -> m/d/Y
            return $parts[1] . '/' . $parts[0] . '/' . $parts[2];
        }
        if (strlen($parts[0]) == 4 && strlen($parts[2]) == 2) {
            // yyyy-mm-dd -> m/d/Y
            return $parts[1] . '/' . $parts[2] . '/' . $parts[0];
        }
    }
    if (strpos($phpDate, '/') !== false && strlen($phpDate) == 10) {
        $parts = explode('/', $phpDate);
        if (strlen($parts[0]) == 2 && strlen($parts[2]) == 4) {
            // dd/mm/yyyy -> m/d/Y
            return $parts[1] . '/' . $parts[0] . '/' . $parts[2];
        }
        if (strlen($parts[0]) == 4 && strlen($parts[2]) == 2) {
            // yyyy/mm/dd -> m/d/Y
            return $parts[1] . '/' . $parts[2] . '/' . $parts[0];
        }
    }
    $ts = strtotime($phpDate);
    return $ts !== false ? date('m/d/Y', $ts) : date('m/d/Y');
}

function formatDisplayDate($dateVal) {
    if (empty($dateVal)) return '';
    if ($dateVal instanceof DateTime) {
        return $dateVal->format('d-m-Y');
    }
    $ts = strtotime($dateVal);
    return $ts !== false ? date('d-m-Y', $ts) : trim((string)$dateVal);
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

function fetchPaymentReceiveRows($pdo, $start_date, $end_date) {
    if (!$pdo) {
        return [];
    }

    $bFrom = toBplusDate($start_date);
    $bTo   = toBplusDate($end_date);

    // 3.1 TRANPAYA (DT_PROPERTIES = 404)
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
    $tpaRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3.2 ARDETAIL (Direct Cash Sales/Receipts)
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
    $ardRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3.3 ARPAYMENT
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
    $arpRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3.4 TRANPAYD
    $stmt = $pdo->prepare("
        SELECT TPD_TPH, TPD_PMT, TPD_BAHT, TPD_CASHAC, TPD_BNKAC, TPH_DI, TPH_AR
        FROM TRANPAYD WITH (NOLOCK)
        JOIN TRANPAYH WITH (NOLOCK) ON TPD_TPH = TPH_KEY
        JOIN DOCINFO WITH (NOLOCK) ON TPH_DI = DI_KEY
        JOIN DOCTYPE WITH (NOLOCK) ON DI_DT = DT_KEY
        WHERE DI_ACTIVE = 0 AND DI_DATE >= ? AND DI_DATE <= ?
    ");
    $stmt->execute([$bFrom, $bTo]);
    $tpdRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tpdCashByDi = [];
    foreach ($tpdRows as $t) {
        if ((int)$t['TPD_PMT'] === 51) {
            $tpdCashByDi[(int)$t['TPH_DI']] = (int)$t['TPD_CASHAC'];
        }
    }

    // 3.5 TRANPAYH
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
    $tpaBase = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Assemble Allocations
    $alloc = [];

    // 4.1 TRANPAYA
    foreach ($tpaRows as $r) {
        $key = allocKey((int)$r['DI_KEY'], (int)$r['TPA_REFER_DI'], 1);
        if (!isset($alloc[$key])) {
            $alloc[$key] = emptyRow((int)$r['DI_KEY'], (int)$r['TPA_REFER_DI'], 1, (int)$r['AR_KEY'], 1);
        }
        $alloc[$key]['A_AMT'] += (float)$r['TPA_B4_A_AMT'];
        $alloc[$key]['CASH_DSC'] += (float)$r['TPA_DISC_A'];
    }

    // 4.2 ARDETAIL
    foreach ($ardRows as $r) {
        $key = allocKey((int)$r['DI_KEY'], (int)$r['DI_KEY'], 1);
        if (!isset($alloc[$key])) {
            $alloc[$key] = emptyRow((int)$r['DI_KEY'], (int)$r['DI_KEY'], 1, (int)$r['ARD_AR'], 1);
        }
        $alloc[$key]['A_AMT'] += (float)$r['ARD_A_AMT'];
    }

    // 4.3 ARPAYMENT
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

    // 5. Overpayment calculations
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

    // 6. Group By
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

    // 6.1 Master Tables
    $arRef = []; $dtRef = []; $pmtRef = []; $cashRef = []; $bnkRef = []; $bankRef = []; $docRef = [];

    $r = $pdo->query("SELECT AR_KEY, AR_CODE, AR_NAME, ARCAT_CODE, ARCAT_NAME FROM ARFILE WITH (NOLOCK) JOIN ARCAT WITH (NOLOCK) ON AR_ARCAT=ARCAT_KEY JOIN DEPTTAB WITH (NOLOCK) ON AR_DEPT=DEPT_KEY WHERE AR_KEY>=0");
    while ($x = $r->fetch(PDO::FETCH_ASSOC)) $arRef[(int)$x['AR_KEY']] = $x;

    $r = $pdo->query("SELECT DT_KEY, DT_DOCCODE, DT_THAIDESC FROM DOCTYPE WITH (NOLOCK)");
    while ($x = $r->fetch(PDO::FETCH_ASSOC)) $dtRef[(int)$x['DT_KEY']] = $x;

    $r = $pdo->query("SELECT PMT_KEY, PMT_NAME FROM PAYMENTTYPE WITH (NOLOCK)");
    while ($x = $r->fetch(PDO::FETCH_ASSOC)) $pmtRef[(int)$x['PMT_KEY']] = $x;

    $r = $pdo->query("SELECT CASHAC_KEY, CASHAC_CODE FROM CASHACCOUNT WITH (NOLOCK)");
    while ($x = $r->fetch(PDO::FETCH_ASSOC)) $cashRef[(int)$x['CASHAC_KEY']] = $x;

    $r = $pdo->query("SELECT BNKAC_KEY, BNKAC_CODE FROM BANKACCOUNT WITH (NOLOCK)");
    while ($x = $r->fetch(PDO::FETCH_ASSOC)) $bnkRef[(int)$x['BNKAC_KEY']] = $x;

    $r = $pdo->query("SELECT BANK_KEY, BANK_INITIAL FROM BANKFILE WITH (NOLOCK)");
    while ($x = $r->fetch(PDO::FETCH_ASSOC)) $bankRef[(int)$x['BANK_KEY']] = $x;

    // 6.2 CHEQUEIN
    $keyinDiList = array_unique(array_column($aggGroups, 'K'));
    $cqinByDi = [];
    $cqinByKey = [];
    for ($i = 0; $i < count($keyinDiList); $i += 1000) {
        $chunk = array_slice($keyinDiList, $i, 1000);
        $inSql = implode(',', $chunk);
        if (!empty($inSql)) {
            $r = $pdo->query("SELECT CQIN_KEY, CQIN_DI, CQIN_CHEQUE_NO, CQIN_CHEQUE_DD, CQIN_A_AMT, CQIN_BANK FROM CHEQUEIN WITH (NOLOCK) WHERE CQIN_DI IN ($inSql)");
            while ($x = $r->fetch(PDO::FETCH_ASSOC)) {
                $cqinByDi[(int)$x['CQIN_DI']][] = $x;
                $cqinByKey[(int)$x['CQIN_KEY']] = $x;
            }
        }
    }

    // 6.3 DOCINFO
    $diList = array_keys($neededDIs);
    for ($i = 0; $i < count($diList); $i += 1000) {
        $chunk = array_slice($diList, $i, 1000);
        $inSql = implode(',', $chunk);
        if (!empty($inSql)) {
            $r = $pdo->query("SELECT DI_KEY, DI_DATE, DI_REF, DI_DT FROM DOCINFO WITH (NOLOCK) WHERE DI_KEY IN ($inSql)");
            while ($x = $r->fetch(PDO::FETCH_ASSOC)) {
                $docRef[(int)$x['DI_KEY']] = $x;
            }
        }
    }

    // 6.4 Cheque Matching
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

    // 6.5 Final Rows (31 Columns matching payment_export_data.php)
    $rows = [];
    $rowIndex = 1;
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
            'row_id'         => $rowIndex++,
            'ARCAT_CODE'     => trim((string)$arRef[$arK]['ARCAT_CODE']),
            'ARCAT_NAME'     => trim((string)$arRef[$arK]['ARCAT_NAME']),
            'AR_CODE'        => trim((string)$arRef[$arK]['AR_CODE']),
            'AR_NAME'        => trim((string)$arRef[$arK]['AR_NAME']),
            'DT_DOCCODE'     => trim((string)$dtRef[$dtK]['DT_DOCCODE']),
            'DT_THAIDESC'    => trim((string)$dtRef[$dtK]['DT_THAIDESC']),
            'DI_DATE'        => (new DateTime($dk['DI_DATE']))->format('d/m/Y'),
            'DI_DATE_RAW'    => (new DateTime($dk['DI_DATE']))->format('Y-m-d'),
            'DI_REF'         => trim((string)$dk['DI_REF']),
            'ARD_DATE'       => (new DateTime($dp['DI_DATE']))->format('d/m/Y'),
            'ARD_DATE_RAW'   => (new DateTime($dp['DI_DATE']))->format('Y-m-d'),
            'ARD_REF'        => trim((string)$dp['DI_REF']),
            'ARD_A_AMT'      => (float)$g['A_AMT'],
            'ARP_CASH_DSC'   => (float)$g['CASH_DSC'],
            'ARP_WH_TAX'     => (float)$g['WH_TAX'],
            'ARP_CASH'       => (float)$g['CASH'],
            'ARP_CQ_AMT'     => (float)$g['CQIN_AMT'],
            'ARP_TRANSF'     => (float)$g['TRANSF'],
            'ARP_OTHER'      => (float)$g['OTHER'],
            'ARP_SHRT'       => (float)$g['SHRT'],
            'ARP_FEE'        => (float)$g['FEE'],
            'ARP_INT'        => (float)$g['INT'],
            'ARP_LOSTXCHG'   => (float)$g['LOSTXCHG'],
            'ARP_DN'         => (float)$g['DN'],
            'ARP_CN'         => (float)$g['CN'],
            'ARP_OVER'       => (float)$g['OVER'],
            'CASHAC_CODE'    => ($g['CASHAC'] && isset($cashRef[$g['CASHAC']])) ? trim((string)$cashRef[$g['CASHAC']]['CASHAC_CODE']) : '',
            'BNKAC_CODE'     => ($g['BNKAC'] && isset($bnkRef[$g['BNKAC']])) ? trim((string)$bnkRef[$g['BNKAC']]['BNKAC_CODE']) : '',
            'BANK_INITIAL'   => ($cqData && ($bk = (int)$cqData['CQIN_BANK']) && isset($bankRef[$bk])) ? trim((string)$bankRef[$bk]['BANK_INITIAL']) : '',
            'CQ_CHEQUE_NO'   => ($cqData) ? trim((string)$cqData['CQIN_CHEQUE_NO']) : '',
            'CQ_CHEQUE_DD'   => ($cqData && !empty($cqData['CQIN_CHEQUE_DD'])) ? (new DateTime($cqData['CQIN_CHEQUE_DD']))->format('d/m/Y') : '',
            'PMT_NAME'       => ($g['PMT'] && isset($pmtRef[$g['PMT']])) ? trim((string)$pmtRef[$g['PMT']]['PMT_NAME']) : '',
            'ARP_SOURCE'     => (int)$g['SRC'],
            'ARP_SOURCE_DESC'=> ((int)$g['SRC'] === 0) ? 'รับเกิน/ปรับปรุง' : 'ตัดชำระตามบิล'
        ];
    }

    return $rows;
}

// -------------------------------------------------------------
// 1. ดึงข้อมูลตาราง DataTables Server-Side (Action: GET_PAYMENT_RECIEVE_DATA)
// -------------------------------------------------------------
if (isset($_POST["action"]) && ($_POST["action"] === 'GET_PAYMENT_RECIEVE_DATA' || $_POST["action"] === 'GET_PAYMENT_RECEIVE_DATA')) {
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $row = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $ifAll = ($rowperpage == -1);
    if ($ifAll) {
        $rowperpage = 10000;
    }

    $columnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $columnName = isset($_POST['columns'][$columnIndex]['data']) ? $_POST['columns'][$columnIndex]['data'] : 'DI_DATE';
    $columnSortOrder = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
    $searchValue = isset($_POST['search']['value']) ? trim(mb_strtolower($_POST['search']['value'], 'UTF-8')) : '';

    $doc_date_start = isset($_POST['doc_date_start']) ? trim($_POST['doc_date_start']) : '';
    $doc_date_to = isset($_POST['doc_date_to']) ? trim($_POST['doc_date_to']) : '';

    if (empty($doc_date_start)) {
        $doc_date_start = date('d-m-Y');
    }
    if (empty($doc_date_to)) {
        $doc_date_to = date('d-m-Y');
    }

    $allRows = fetchPaymentReceiveRows($conn_sqlsvr, $doc_date_start, $doc_date_to);
    $totalRecords = count($allRows);

    // ค้นหาข้อมูล (Search Filter)
    $filteredRows = [];
    if ($searchValue !== '') {
        foreach ($allRows as $item) {
            if (
                str_contains(mb_strtolower($item['ARCAT_CODE'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['ARCAT_NAME'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['AR_CODE'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['AR_NAME'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['DT_DOCCODE'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['DT_THAIDESC'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['DI_DATE'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['DI_REF'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['ARD_DATE'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['ARD_REF'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['CASHAC_CODE'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['BNKAC_CODE'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['BANK_INITIAL'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['CQ_CHEQUE_NO'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['CQ_CHEQUE_DD'], 'UTF-8'), $searchValue) ||
                str_contains(mb_strtolower($item['PMT_NAME'], 'UTF-8'), $searchValue)
            ) {
                $filteredRows[] = $item;
            }
        }
    } else {
        $filteredRows = $allRows;
    }

    $totalRecordwithFilter = count($filteredRows);

    // แมปคอลัมน์สำหรับการเรียงลำดับ (Sorting)
    $sortKey = 'DI_DATE_RAW';
    $numFields = [
        'ARD_A_AMT', 'ARP_CASH_DSC', 'ARP_WH_TAX', 'ARP_CASH', 'ARP_CQ_AMT',
        'ARP_TRANSF', 'ARP_OTHER', 'ARP_SHRT', 'ARP_FEE', 'ARP_INT',
        'ARP_LOSTXCHG', 'ARP_DN', 'ARP_CN', 'ARP_OVER', 'ARP_SOURCE'
    ];

    if ($columnName === 'DI_DATE') $sortKey = 'DI_DATE_RAW';
    elseif ($columnName === 'ARD_DATE') $sortKey = 'ARD_DATE_RAW';
    elseif (isset($allRows[0][$columnName])) $sortKey = $columnName;

    $isNumericSort = in_array($sortKey, $numFields);

    usort($filteredRows, function ($a, $b) use ($sortKey, $columnSortOrder, $isNumericSort) {
        $valA = $a[$sortKey] ?? '';
        $valB = $b[$sortKey] ?? '';

        if ($isNumericSort) {
            $cmp = ((float)$valA) <=> ((float)$valB);
        } else {
            $cmp = strcmp((string)$valA, (string)$valB);
        }

        return ($columnSortOrder === 'ASC') ? $cmp : -$cmp;
    });

    // แบ่งหน้า (Pagination)
    $pagedRows = array_slice($filteredRows, $row, $rowperpage);

    $data = [];
    foreach ($pagedRows as $r) {
        $modalData = [
            'ARCAT_CODE'     => $r['ARCAT_CODE'],
            'ARCAT_NAME'     => $r['ARCAT_NAME'],
            'AR_CODE'        => $r['AR_CODE'],
            'AR_NAME'        => $r['AR_NAME'],
            'DT_DOCCODE'     => $r['DT_DOCCODE'],
            'DT_THAIDESC'    => $r['DT_THAIDESC'],
            'DI_DATE'        => $r['DI_DATE'],
            'DI_REF'         => $r['DI_REF'],
            'ARD_DATE'       => $r['ARD_DATE'],
            'ARD_REF'        => $r['ARD_REF'],
            'ARD_A_AMT'      => number_format($r['ARD_A_AMT'], 2),
            'ARP_CASH_DSC'   => number_format($r['ARP_CASH_DSC'], 2),
            'ARP_WH_TAX'     => number_format($r['ARP_WH_TAX'], 2),
            'ARP_CASH'       => number_format($r['ARP_CASH'], 2),
            'ARP_CQ_AMT'     => number_format($r['ARP_CQ_AMT'], 2),
            'ARP_TRANSF'     => number_format($r['ARP_TRANSF'], 2),
            'ARP_OTHER'      => number_format($r['ARP_OTHER'], 2),
            'ARP_SHRT'       => number_format($r['ARP_SHRT'], 2),
            'ARP_FEE'        => number_format($r['ARP_FEE'], 2),
            'ARP_INT'        => number_format($r['ARP_INT'], 2),
            'ARP_LOSTXCHG'   => number_format($r['ARP_LOSTXCHG'], 2),
            'ARP_DN'         => number_format($r['ARP_DN'], 2),
            'ARP_CN'         => number_format($r['ARP_CN'], 2),
            'ARP_OVER'       => number_format($r['ARP_OVER'], 2),
            'CASHAC_CODE'    => $r['CASHAC_CODE'],
            'BNKAC_CODE'     => $r['BNKAC_CODE'],
            'BANK_INITIAL'   => $r['BANK_INITIAL'],
            'CQ_CHEQUE_NO'   => $r['CQ_CHEQUE_NO'],
            'CQ_CHEQUE_DD'   => $r['CQ_CHEQUE_DD'],
            'PMT_NAME'       => $r['PMT_NAME'],
            'ARP_SOURCE'     => $r['ARP_SOURCE'],
            'ARP_SOURCE_DESC'=> $r['ARP_SOURCE_DESC']
        ];

        $jsonRow = htmlspecialchars(json_encode($modalData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

        $data[] = [
            'ARCAT_CODE'   => $r['ARCAT_CODE'],
            'ARCAT_NAME'   => $r['ARCAT_NAME'],
            'AR_CODE'      => $r['AR_CODE'],
            'AR_NAME'      => $r['AR_NAME'],
            'DT_DOCCODE'   => $r['DT_DOCCODE'],
            'DT_THAIDESC'  => $r['DT_THAIDESC'],
            'DI_DATE'      => $r['DI_DATE'],
            'DI_REF'       => $r['DI_REF'],
            'ARD_DATE'     => $r['ARD_DATE'],
            'ARD_REF'      => $r['ARD_REF'],
            'ARD_A_AMT'    => number_format($r['ARD_A_AMT'], 2),
            'ARP_CASH_DSC' => number_format($r['ARP_CASH_DSC'], 2),
            'ARP_WH_TAX'   => number_format($r['ARP_WH_TAX'], 2),
            'ARP_CASH'     => number_format($r['ARP_CASH'], 2),
            'ARP_CQ_AMT'   => number_format($r['ARP_CQ_AMT'], 2),
            'ARP_TRANSF'   => number_format($r['ARP_TRANSF'], 2),
            'ARP_OTHER'    => number_format($r['ARP_OTHER'], 2),
            'ARP_SHRT'     => number_format($r['ARP_SHRT'], 2),
            'ARP_FEE'      => number_format($r['ARP_FEE'], 2),
            'ARP_INT'      => number_format($r['ARP_INT'], 2),
            'ARP_LOSTXCHG' => number_format($r['ARP_LOSTXCHG'], 2),
            'ARP_DN'       => number_format($r['ARP_DN'], 2),
            'ARP_CN'       => number_format($r['ARP_CN'], 2),
            'ARP_OVER'     => number_format($r['ARP_OVER'], 2),
            'CASHAC_CODE'  => $r['CASHAC_CODE'],
            'BNKAC_CODE'   => $r['BNKAC_CODE'],
            'BANK_INITIAL' => $r['BANK_INITIAL'],
            'CQ_CHEQUE_NO' => $r['CQ_CHEQUE_NO'],
            'CQ_CHEQUE_DD' => $r['CQ_CHEQUE_DD'],
            'PMT_NAME'     => $r['PMT_NAME'],
            'ARP_SOURCE'   => $r['ARP_SOURCE'],
            'update'       => "<button type='button' class='btn btn-info btn-xs info' data-info='{$jsonRow}' data-toggle='tooltip' title='ดูรายละเอียด'><i class='fa fa-info-circle'></i> Info</button>"
        ];
    }

    $response = [
        "draw"            => $draw,
        "iTotalRecords"   => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData"          => $data
    ];

    echo json_encode($response);
    exit();
}
