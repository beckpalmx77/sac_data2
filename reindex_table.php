<?php
require_once 'config/connect_db.php';

// ==========================================
// API: ปรับปรุงโครงสร้าง Index (Structure Modify & Optimization)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'apply_indexes') {
    @ini_set('max_execution_time', '600');
    @set_time_limit(600);
    $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

    try {
        $conn->exec("SET SESSION lock_wait_timeout = 15;");
        $conn->exec("SET SESSION innodb_lock_wait_timeout = 15;");
    } catch (Exception $e) {}

    $response = ['success' => false, 'log' => []];
    
    // 1. รายการ Index ที่ซ้ำซ้อนและต้องลบออกเพื่อคืนพื้นที่ดิสก์ (Redundant Indexes)
    $redundant_indexes_to_drop = [
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_sale_sac_ar'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_sale_sac_ref'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_sale_sac_sku'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_di_date'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_product'],
        ['table' => 'ims_data_sale_sac_all', 'index' => 'idx_sale'],
        ['table' => 'ims_data_sale_sac_all', 'index' => 'idx_di_year'],
        ['table' => 'ims_document_bill', 'index' => 'idx_id'],
        ['table' => 'ims_customer_ar', 'index' => 'idx_customer_id'],
        ['table' => 'ims_forecast_auto', 'index' => 'idx_target_ym'],
        ['table' => 'ims_user_column_config', 'index' => 'idx_user']
    ];

    // 2. รายการ Index จำเป็นที่ต้องสร้างเพื่อเร่งความเร็ว Query (Performance Indexes)
    $indexes_to_create = [
        // ตารางยอดขายหลัก (ims_data_sale_sac_all & ims_product_sale_sac)
        ['table' => 'ims_data_sale_sac_all', 'index' => 'idx_seq_record', 'cols' => '`seq_record`'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_year_month', 'cols' => '`DI_YEAR`, `DI_MONTH`'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_pgroup_year', 'cols' => '`PGROUP`, `DI_YEAR`'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_di_ref', 'cols' => '`DI_REF`'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_ar_code', 'cols' => '`AR_CODE`'],
        ['table' => 'ims_product_sale_sac', 'index' => 'idx_sku_code', 'cols' => '`SKU_CODE`'],

        // ตาราง Cockpit & Dashboard
        ['table' => 'ims_product_sale_cockpit', 'index' => 'idx_pgroup_year', 'cols' => '`PGROUP`(50), `DI_YEAR`(10)'],
        ['table' => 'ims_product_sale_cockpit', 'index' => 'idx_year_month', 'cols' => '`DI_YEAR`(10), `DI_MONTH`(10)'],
        ['table' => 'ims_product_sale_cockpit', 'index' => 'idx_di_date', 'cols' => '`DI_DATE`(20)'],

        // ตาราง Forecast แผนการขายยาง
        ['table' => 'ims_tires_plan_forecast', 'index' => 'idx_month_year', 'cols' => '`forcast_month`, `forcast_year`'],
        ['table' => 'ims_tires_plan_forecast', 'index' => 'idx_forecast_lookup', 'cols' => '`forcast_month`, `forcast_year`, `ar_code`(20)'],

        // ตารางวางบิล & กำหนดชำระ
        ['table' => 'ims_document_bill', 'index' => 'idx_di_ref', 'cols' => '`DI_REF`'],
        ['table' => 'ims_document_bill', 'index' => 'idx_di_active', 'cols' => '`DI_ACTIVE`'],
        ['table' => 'ims_document_bill_load', 'index' => 'idx_tpa_refer_ref', 'cols' => '`TPA_REFER_REF`'],

        // ตารางคลังสินค้า & ความเคลื่อนไหวสินค้า
        ['table' => 'wh_product_master', 'index' => 'idx_product_id', 'cols' => '`product_id`'],
        ['table' => 'wh_stock_record', 'index' => 'idx_doc_line_prod', 'cols' => '`doc_id`(100), `line_no`, `product_id`(100)'],
        ['table' => 'wh_stock_movement', 'index' => 'idx_product_id', 'cols' => '`product_id`'],
        ['table' => 'wh_stock_movement', 'index' => 'idx_create_by', 'cols' => '`create_by`'],

        // ตารางข้อมูลยาง & ผู้ใช้งาน
        ['table' => 'ims_tires_number', 'index' => 'idx_tires_num', 'cols' => '`tires_number`(100)'],
        ['table' => 'ims_tires_number', 'index' => 'idx_cust_name', 'cols' => '`customer_name`(100)'],
        ['table' => 'ims_user', 'index' => 'idx_user_id', 'cols' => '`user_id`']
    ];
    
    $log = [];
    $success = true;
    
    try {
        // ดึง Index ทั้งหมดที่มีอยู่แล้วในระบบด้วย Query เดียว (รวดเร็ว < 10ms)
        $idxStmt = $conn->query(
            "SELECT TABLE_NAME, INDEX_NAME 
             FROM information_schema.statistics 
             WHERE TABLE_SCHEMA = DATABASE()"
        );
        $existingIndexes = [];
        while ($row = $idxStmt->fetch(PDO::FETCH_ASSOC)) {
            $existingIndexes[strtolower($row['TABLE_NAME']) . '.' . strtolower($row['INDEX_NAME'])] = true;
        }
        $idxStmt->closeCursor();

        // 1. ดำเนินการลบ Index ซ้ำซ้อน
        foreach ($redundant_indexes_to_drop as $idx) {
            $table = $idx['table'];
            $index_name = $idx['index'];
            $key = strtolower($table) . '.' . strtolower($index_name);
            
            if (isset($existingIndexes[$key])) {
                try {
                    $conn->exec("ALTER TABLE `$table` DROP INDEX `$index_name`");
                    $log[] = "🗑️ ตาราง `$table`: ลบดัชนีซ้ำซ้อน `$index_name` สำเร็จ (ลดขนาดพื้นที่)";
                    unset($existingIndexes[$key]);
                } catch (PDOException $ex) {
                    $log[] = "❌ ตาราง `$table`: ลบ `$index_name` ไม่สำเร็จ - " . $ex->getMessage();
                    $success = false;
                }
            } else {
                $log[] = "⏭️ ตาราง `$table`: ดัชนี `$index_name` ถูกลบออกแล้ว (สะอาด)";
            }
        }

        // 2. ดำเนินการสร้าง Index ที่จำเป็น
        foreach ($indexes_to_create as $idx) {
            $table = $idx['table'];
            $index_name = $idx['index'];
            $cols = $idx['cols'];
            $key = strtolower($table) . '.' . strtolower($index_name);
            
            if (isset($existingIndexes[$key])) {
                $log[] = "⏭️ ตาราง `$table`: ดัชนี `$index_name` มีอยู่แล้ว [พร้อมใช้งาน]";
            } else {
                try {
                    $conn->exec("ALTER TABLE `$table` ADD INDEX `$index_name` ($cols)");
                    $log[] = "✅ ตาราง `$table`: สร้างดัชนี `$index_name` ($cols) สำเร็จ";
                    $existingIndexes[$key] = true;
                } catch (PDOException $ex) {
                    $log[] = "❌ ตาราง `$table`: เกิดข้อผิดพลาดในการสร้าง `$index_name` - " . $ex->getMessage();
                    $success = false;
                }
            }
        }
    } catch (PDOException $e) {
        $log[] = "❌ เกิดข้อผิดพลาดในการตรวจสอบดัชนี: " . $e->getMessage();
        $success = false;
    }
    
    $response['success'] = $success;
    $response['log'] = $log;
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// ==========================================
// ==========================================
// API: Optimize Table (Single or Batch)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'optimize') {
    @ini_set('max_execution_time', '180');
    @set_time_limit(180);
    @ini_set('memory_limit', '512M');
    @ignore_user_abort(false);
    $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    // ป้องกัน MySQL รอ Metadata Lock นานเกินไปจนทำให้หน้าเว็บค้าง
    try {
        $conn->exec("SET SESSION lock_wait_timeout = 10;");
        $conn->exec("SET SESSION innodb_lock_wait_timeout = 10;");
    } catch (Exception $e) {}
    
    $tableParam = $_GET['table'] ?? $_GET['tables'] ?? '';
    $tables = array_filter(array_map('trim', explode(',', $tableParam)));
    $mode = $_GET['mode'] ?? 'smart'; // 'turbo', 'smart', 'rebuild'
    
    if (empty($tables)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ไม่พบรายชื่อตารางที่ต้องการ Optimize']);
        exit;
    }
    
    // ดึงข้อมูลขนาดเฉพาะกรณีที่ client ไม่ได้ส่ง metadata มา (เพื่อความเร็วสูงสุด < 1ms)
    $tableMeta = [];
    $needLookup = false;
    foreach ($tables as $t) {
        if (!isset($_GET['engine']) || !isset($_GET['size_mb'])) {
            $needLookup = true;
            break;
        }
    }

    if ($needLookup) {
        $placeholders = implode(',', array_fill(0, count($tables), '?'));
        $infoStmt = $conn->prepare(
            "SELECT TABLE_NAME, TABLE_TYPE, ENGINE, 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    ROUND(DATA_FREE / 1024 / 1024, 2) AS free_mb
             FROM information_schema.TABLES 
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ($placeholders)"
        );
        $infoStmt->execute($tables);
        while ($row = $infoStmt->fetch(PDO::FETCH_ASSOC)) {
            $tableMeta[$row['TABLE_NAME']] = $row;
        }
        $infoStmt->closeCursor();
    }
    
    $results = [];
    
    foreach ($tables as $table) {
        $response = ['table' => $table, 'success' => false, 'skipped' => false, 'before' => 0, 'after' => 0, 'message' => ''];
        
        $info = $tableMeta[$table] ?? [];
        $tableType   = strtoupper($info['TABLE_TYPE'] ?? $_GET['type'] ?? 'BASE TABLE');
        $oldEngine   = strtoupper($_GET['engine'] ?? $info['ENGINE'] ?? 'INNODB');
        $tableSizeMb = (float)($_GET['size_mb'] ?? $info['size_mb'] ?? 0);
        $freeMb      = (float)($_GET['free_mb'] ?? $info['free_mb'] ?? 0);

        // ข้าม VIEW
        if ($tableType === 'VIEW') {
            $response['skipped'] = true;
            $response['success'] = true;
            $response['message'] = "⏭️ ข้าม: `$table` [VIEW]";
            $results[] = $response;
            continue;
        }

        $response['before'] = $tableSizeMb;
        $response['after']  = $tableSizeMb;
        $didRebuild  = false;
        $actionStatus = "";

        try {
            if ($oldEngine !== 'INNODB') {
                // แปลงตารางเป็น InnoDB ทันที
                $conn->exec("ALTER TABLE `$table` ENGINE = InnoDB");
                $actionStatus = "🔄 แปลงจาก $oldEngine → INNODB";
                $didRebuild = true;
            } else {
                // กรณีเป็น InnoDB อยู่แล้ว
                if ($mode === 'turbo') {
                    // Turbo: ปรับปรุงสถิติดัชนีสายฟ้าแลบ (ไม่ Rebuild ดิสก์)
                    $actionStatus = "⚡ INNODB (Turbo Stats)";
                } else if ($mode === 'smart') {
                    // Smart: Rebuild เฉพาะตารางขนาดเล็ก (<= 20MB) ที่มี Fragmentation สูงจริง (>15MB และ >25% ของขนาดตาราง)
                    if ($freeMb > 15 && $tableSizeMb <= 20 && ($freeMb / max(1, $tableSizeMb)) > 0.25) {
                        $conn->exec("ALTER TABLE `$table` ENGINE = InnoDB");
                        $actionStatus = "⚡ INNODB (Defrag คืนพื้นที่ {$freeMb} MB)";
                        $didRebuild = true;
                    } else if ($tableSizeMb > 50) {
                        $actionStatus = "⚡ INNODB (Refresh Stats - ตารางใหญ่ {$tableSizeMb} MB)";
                    } else {
                        $actionStatus = "⚡ INNODB (Stats Optimized)";
                    }
                } else {
                    // Full Rebuild & Defrag: Rebuild เฉพาะตารางที่มี Fragmentation จริง (freeMb > 10MB) และขนาด <= 30MB เพื่อป้องกัน timeout
                    if ($tableSizeMb <= 30 && $freeMb > 10) {
                        $conn->exec("ALTER TABLE `$table` ENGINE = InnoDB");
                        $actionStatus = "⚡ INNODB (Full Rebuild & Defrag)";
                        $didRebuild = true;
                    } else if ($tableSizeMb > 30) {
                        $actionStatus = "⚡ INNODB (Large Table Stats Refresh)";
                    } else {
                        $actionStatus = "⚡ INNODB (Stats Refresh - Fragmentation ต่ำ ไม่จำเป็นต้อง Rebuild)";
                    }
                }
            }

            // ANALYZE TABLE เพื่อ Refresh Cardinality/Index Statistics สำหรับ Query Optimizer
            $stmtAnalyze = $conn->query("ANALYZE TABLE `$table`");
            $analyzeResult = $stmtAnalyze->fetchAll(PDO::FETCH_ASSOC);
            $stmtAnalyze->closeCursor();
            $analyzeMsg    = $analyzeResult[0]['Msg_text'] ?? 'OK';

            // ถ้ามีการ Rebuild Table ให้ดึงขนาดใหม่
            if ($didRebuild) {
                $stmtSize = $conn->prepare("SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
                $stmtSize->execute([$table]);
                $response['after'] = (float)$stmtSize->fetchColumn();
                $stmtSize->closeCursor();
            }

            $response['success'] = true;
            $saved = max(0, $response['before'] - $response['after']);
            $savedText = $saved > 0 ? " | ลดพื้นที่: " . round($saved, 2) . " MB" : "";

            $response['message'] = "✅ [$actionStatus] `$table` [{$response['before']} MB → {$response['after']} MB]{$savedText} | ANALYZE: $analyzeMsg";

        } catch (PDOException $e) {
            $isLockTimeout = (strpos($e->getMessage(), '1205') !== false || stripos($e->getMessage(), 'Lock wait timeout') !== false);
            if ($isLockTimeout) {
                $response['skipped'] = true;
                $response['success'] = true;
                $response['message'] = "⚠️ ข้ามชั่วคราว: `$table` [มีกระบวนการอื่นเปิดใช้งานตารางนี้อยู่ - ข้ามอัตโนมัติเพื่อป้องกันระบบค้าง]";
            } else {
                $response['message'] = "❌ ผิดพลาด: `$table` - " . $e->getMessage();
            }
        }

        $results[] = $response;
    }

    header('Content-Type: application/json; charset=utf-8');
    if (count($tables) === 1 && !isset($_GET['batch'])) {
        echo json_encode($results[0]);
    } else {
        echo json_encode(['success' => true, 'results' => $results]);
    }
    exit;
}

// ==========================================
// ส่วนการแสดงผล (UI)
// ==========================================
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
} else {
    // ดึงข้อมูลตารางทั้งหมด และประเภท Engine ในครั้งเดียว
    $stmt = $conn->query(
        "SELECT TABLE_NAME, TABLE_TYPE, ENGINE, 
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                ROUND(DATA_FREE / 1024 / 1024, 2) AS free_mb
         FROM information_schema.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         ORDER BY TABLE_NAME"
    );
    $allTables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    
    $baseTables = array_values(array_filter($allTables, fn($t) => $t['TABLE_TYPE'] !== 'VIEW'));
    $viewTables = array_values(array_filter($allTables, fn($t) => $t['TABLE_TYPE'] === 'VIEW'));
    
    $baseTableNames = array_column($baseTables, 'TABLE_NAME');
    $viewTableNames = array_column($viewTables, 'TABLE_NAME');

    // สร้าง Map ข้อมูลตารางเพื่อส่งให้ Frontend (ไม่ต้อง Query ซ้ำซ้อน 150 ครั้ง)
    $tableMetaMap = [];
    foreach ($allTables as $t) {
        $tableMetaMap[$t['TABLE_NAME']] = [
            'type'    => $t['TABLE_TYPE'],
            'engine'  => strtoupper($t['ENGINE'] ?? 'INNODB'),
            'size_mb' => (float)($t['size_mb'] ?? 0),
            'free_mb' => (float)($t['free_mb'] ?? 0)
        ];
    }
    
    $totalCount     = count($allTables);
    $baseCount      = count($baseTables);
    $viewCount      = count($viewTables);
    $innodbCount    = count(array_filter($baseTables, fn($t) => strtoupper($t['ENGINE'] ?? '') === 'INNODB'));
    $nonInnodbCount = $baseCount - $innodbCount;

    $dashboard_url = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <style>
            .sidebar-lock {
                position: fixed;
                top: 0; left: 0;
                width: 250px; height: 100%;
                background: rgba(0,0,0,0.1);
                z-index: 9999;
                cursor: not-allowed;
                display: none;
            }
            .working-overlay {
                pointer-events: none;
                opacity: 0.7;
            }
            .stat-card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            }
            .badge-turbo {
                background: linear-gradient(45deg, #ff7b00, #ffae00);
                color: #fff;
                font-weight: bold;
                padding: 3px 8px;
                border-radius: 12px;
                font-size: 11px;
            }
        </style>
    </head>
    <body id="page-top">
    <div id="lock-overlay" class="sidebar-lock"></div>

    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><i class="fas fa-tachometer-alt text-primary mr-2"></i>Database Optimization & InnoDB Engine</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-database mr-2"></i>MySQL Table Optimizer & Convert to InnoDB</h6>
                                    <a href="<?php echo $dashboard_url; ?>" class="btn btn-sm btn-light shadow-sm text-primary">
                                        <i class="fas fa-home fa-sm"></i> Home
                                    </a>
                                </div>
                                <div class="card-body">
                                    <!-- Alert Details -->
                                    <div class="alert alert-light border mb-4 text-left small text-muted">
                                        <i class="fas fa-info-circle text-primary mr-1"></i> <strong>การทำงาน:</strong> ระบบจะปรับปรุง Storage Engine ทุกตารางให้เป็น <strong>InnoDB</strong> พร้อมประมวลผลแบบคู่ขนาน (Parallel Multi-worker) คืนพื้นที่ดิสก์ที่ไม่ได้ใช้งาน (Defragment) และรีเฟรชสถิติดัชนี (<code>ANALYZE TABLE</code>) เพื่อความเร็วสูงสุดในการ Query
                                    </div>

                                    <!-- Summary Counters -->
                                    <div class="row text-center mb-4">
                                        <div class="col-md-2 col-6 border-right mb-2">
                                            <span class="text-muted small">ตารางทั้งหมด (BASE TABLE)</span>
                                            <div class="h3 font-weight-bold text-dark"><?php echo $baseCount; ?></div>
                                        </div>
                                        <div class="col-md-3 col-6 border-right mb-2">
                                            <span class="text-muted small">ตารางที่เป็น InnoDB</span>
                                            <div class="h3 font-weight-bold text-primary"><?php echo $innodbCount; ?> <span class="small font-weight-normal text-muted"><?php echo $nonInnodbCount > 0 ? "($nonInnodbCount ไม่ใช่ InnoDB)" : "(ครบ 100%)"; ?></span></div>
                                        </div>
                                        <div class="col-md-2 col-6 border-right mb-2">
                                            <span class="text-muted small">VIEW (ข้ามอัตโนมัติ)</span>
                                            <div class="h3 font-weight-bold text-warning"><?php echo $viewCount; ?></div>
                                        </div>
                                        <div class="col-md-3 col-6 border-right mb-2">
                                            <span class="text-muted small">Total Space Saved</span>
                                            <div class="h3 font-weight-bold text-success"><span id="total-saved">0.00</span> MB</div>
                                        </div>
                                        <div class="col-md-2 col-12 mb-2">
                                            <span class="text-muted small">ความเร็วในการทำงาน</span>
                                            <div class="h3 font-weight-bold text-info"><span id="live-speed">0</span> <span class="small font-weight-normal text-muted">ตาราง/วิ</span></div>
                                        </div>
                                    </div>

                                    <!-- Mode & Performance Options -->
                                    <div class="row justify-content-center mb-4 text-left">
                                        <div class="col-lg-7 col-md-12 mb-3">
                                            <div class="card bg-light border p-3 h-100">
                                                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-sliders-h mr-1 text-primary"></i> 1. เลือกรูปแบบการ Optimize:</h6>
                                                
                                                <div class="custom-control custom-radio mb-3">
                                                    <input type="radio" id="mode-smart" name="optimize_mode" class="custom-control-input" value="smart" checked>
                                                    <label class="custom-control-label" for="mode-smart">
                                                        <strong>Smart Optimize (แนะนำสูงสุด)</strong> <span class="badge-turbo ml-1">Speed & Safety</span><br>
                                                        <small class="text-muted">แปลง Engine เป็น InnoDB อัตโนมัติ, Rebuild เฉพาะตารางที่มี Fragmentation สูงจริง และ Refresh ดัชนีด้วย <code>ANALYZE TABLE</code> ทันที รวดเร็ว ปลอดภัย ไม่เกิด Timeout บนตารางใหญ่</small>
                                                    </label>
                                                </div>

                                                <div class="custom-control custom-radio mb-3">
                                                    <input type="radio" id="mode-turbo" name="optimize_mode" class="custom-control-input" value="turbo">
                                                    <label class="custom-control-label" for="mode-turbo">
                                                        <strong>Ultra Turbo Optimize (ด่วนพิเศษ)</strong> <span class="badge badge-success ml-1">Ultra Fast &lt; 5s</span><br>
                                                        <small class="text-muted">รีเฟรช Index Statistics ทุกตารางพร้อมกันแบบสายฟ้าแลบ (&lt; 5 วิ) เหมาะสำหรับการบำรุงรักษาประจำวันขณะมีผู้ใช้งานระบบ</small>
                                                    </label>
                                                </div>

                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="mode-rebuild" name="optimize_mode" class="custom-control-input" value="rebuild">
                                                    <label class="custom-control-label" for="mode-rebuild">
                                                        <strong>Full Rebuild & Defrag (เต็มรูปแบบ)</strong><br>
                                                        <small class="text-muted">สั่ง Rebuild และ Defrag ตารางที่มีพื้นที่ว่าง (&lt;= 30MB) เพื่อคืนพื้นที่ดิสก์ (ตารางขนาดใหญ่มากจะ Refresh Stats เพื่อป้องกันระบบค้าง)</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-5 col-md-12 mb-3">
                                            <div class="card bg-light border p-3 h-100">
                                                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-bolt mr-1 text-warning"></i> 2. ระดับความเร็ว Multi-Worker:</h6>
                                                
                                                <div class="custom-control custom-radio mb-3">
                                                    <input type="radio" id="worker-safe" name="concurrency_mode" class="custom-control-input" value="1" checked>
                                                    <label class="custom-control-label" for="worker-safe">
                                                        <strong>Safe Sequential (1 worker)</strong> <span class="badge badge-success ml-1">แนะนำ เสถียรสูงสุด 100%</span><br>
                                                        <small class="text-muted">ประมวลผลทีละ 1 ตารางตามลำดับ ทำงานเร็ว ไม่เกิดปัญหา Lock ชนกันหรือ Timeout</small>
                                                    </label>
                                                </div>

                                                <div class="custom-control custom-radio mb-3">
                                                    <input type="radio" id="worker-fast" name="concurrency_mode" class="custom-control-input" value="2">
                                                    <label class="custom-control-label" for="worker-fast">
                                                        <strong>Fast Parallel (2 workers)</strong> <span class="badge badge-primary ml-1">เร็วปานกลาง</span><br>
                                                        <small class="text-muted">ประมวลผลคู่ขนาน 2 ตารางพร้อมกัน</small>
                                                    </label>
                                                </div>

                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="worker-turbo" name="concurrency_mode" class="custom-control-input" value="3">
                                                    <label class="custom-control-label" for="worker-turbo">
                                                        <strong>Turbo Multi-Thread (3 workers)</strong><br>
                                                        <small class="text-muted">ประมวลผลคู่ขนาน 3 ตารางพร้อมกัน (สำหรับช่วงเวลาที่ไม่มีผู้ใช้งานระบบ)</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="text-center mb-4">
                                        <button id="start-btn" class="btn btn-primary btn-lg px-4 shadow">
                                            <i class="fas fa-play mr-2"></i>เริ่มรัน Optimize & ปรับ Engine ทุกตาราง
                                        </button>
                                        <div id="after-action-btns" class="d-none">
                                            <button id="reset-btn" class="btn btn-warning btn-lg px-4 shadow-sm mr-2">
                                                <i class="fas fa-undo mr-2"></i>Reset หน้าจอ
                                            </button>
                                            <button id="download-btn" class="btn btn-outline-info btn-lg px-4 shadow-sm mr-2">
                                                <i class="fas fa-file-alt mr-2"></i>ดาวน์โหลด Log รายงาน
                                            </button>
                                            <a href="<?php echo $dashboard_url; ?>" class="btn btn-outline-secondary btn-lg px-4 shadow-sm">
                                                <i class="fas fa-home mr-2"></i>กลับหน้าหลัก
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Progress & Log Section -->
                                    <div id="ui-section" class="d-none">
                                        <div class="progress mb-3" style="height: 26px; border-radius: 8px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);">
                                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success font-weight-bold" style="width: 0%;">0%</div>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span id="status-text" class="font-weight-bold text-primary small">รอดำเนินการ...</span>
                                            <span class="small text-muted">
                                                ⏱️ เวลา: <strong id="elapsed-time" class="text-dark">0.0s</strong> | 
                                                ดำเนินการแล้ว: <strong id="count-text" class="text-dark">0 / <?php echo $totalCount; ?></strong>
                                            </span>
                                        </div>
                                        <div id="log-window" style="background-color: #1a1a1a; color: #dcdccc; padding: 18px; border-radius: 8px; height: 350px; overflow-y: auto; font-family: 'Consolas', 'Fira Code', monospace; font-size: 13px; line-height: 1.5; text-align: left; box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);">
                                            <div style="color: #666;">--- กดปุ่มด้านบนเพื่อเริ่มกระบวนการ ---</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Index Structure Optimization -->
                        <div class="col-lg-12 mt-2">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-success text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-bolt mr-2"></i>Index Structure Optimization (ปรับปรุงโครงสร้างดัชนี)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle mr-1"></i> <strong>การปรับปรุงโครงสร้างดัชนี (Index Optimization):</strong> ระบบจะทำการตรวจสอบและสร้าง Index ประสิทธิภาพสูงสำหรับหน้า Dashboard, ยอดขาย, Cockpit, Forecast, คลังสินค้า, กำหนดชำระเงิน พร้อม<strong>ลบ Index ที่ซ้ำซ้อน (Redundant Indexes)</strong> เพื่อคืนพื้นที่ดิสก์และเร่งความเร็วในการทำงานอัตโนมัติ
                                    </div>
                                    <div class="text-center mb-4">
                                        <button id="apply-indexes-btn" class="btn btn-success btn-lg px-4 shadow">
                                            <i class="fas fa-magic mr-2"></i>ตรวจสอบและปรับปรุงโครงสร้าง Index
                                        </button>
                                    </div>
                                    <div id="index-ui-section" class="d-none">
                                        <div id="index-log-window" style="background-color: #1a1a1a; color: #dcdccc; padding: 18px; border-radius: 8px; max-height: 250px; overflow-y: auto; font-family: 'Consolas', 'Fira Code', monospace; font-size: 13px; line-height: 1.5; text-align: left; box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const baseTables      = <?php echo json_encode($baseTableNames); ?>;
            const viewTables      = <?php echo json_encode($viewTableNames); ?>;
            const tableMetaMap    = <?php echo json_encode($tableMetaMap); ?>;
            const totalTableCount = <?php echo $totalCount; ?>;

            const startBtn        = document.getElementById('start-btn');
            const resetBtn        = document.getElementById('reset-btn');
            const downloadBtn     = document.getElementById('download-btn');
            const afterActionBtns = document.getElementById('after-action-btns');
            const progressBar     = document.getElementById('progress-bar');
            const uiSection       = document.getElementById('ui-section');
            const logWindow       = document.getElementById('log-window');
            const statusText      = document.getElementById('status-text');
            const countText       = document.getElementById('count-text');
            const elapsedTimeEl   = document.getElementById('elapsed-time');
            const liveSpeedEl     = document.getElementById('live-speed');
            const totalSavedLabel = document.getElementById('total-saved');
            const lockOverlay     = document.getElementById('lock-overlay');
            const sidebar         = document.getElementById('accordionSidebar');

            let logContent  = "";
            let totalSaved  = 0;
            let timerInterval = null;
            let startTime   = 0;

            function setInterfaceLock(isLocked) {
                lockOverlay.style.display = isLocked ? 'block' : 'none';
                if (sidebar) sidebar.classList.toggle('working-overlay', isLocked);
                startBtn.disabled = isLocked;
                document.querySelectorAll('input[name="optimize_mode"], input[name="concurrency_mode"]').forEach(el => el.disabled = isLocked);
            }

            function appendLog(message, color = '#dcdccc', isSkipped = false) {
                const time    = new Date().toLocaleTimeString();
                const logLine = `[${time}] ${message}`;
                const div     = document.createElement('div');
                div.style.color        = color;
                div.style.marginBottom = '3px';
                div.style.opacity      = isSkipped ? '0.55' : '1';
                div.innerText          = logLine;
                logWindow.appendChild(div);
                logWindow.scrollTop    = logWindow.scrollHeight;
                logContent            += logLine + "\n";
            }

            // ==========================================
            // Main Optimize Execution with Worker Pool
            // ==========================================
            startBtn.addEventListener('click', async () => {
                const selectedMode = document.querySelector('input[name="optimize_mode"]:checked')?.value || 'smart';
                const concurrency  = parseInt(document.querySelector('input[name="concurrency_mode"]:checked')?.value || '3', 10);
                
                const modeName = selectedMode === 'turbo' ? 'Ultra Turbo' : (selectedMode === 'smart' ? 'Smart Optimize' : 'Full Rebuild');
                if (!confirm(`ยืนยันการเริ่มทำงานในโหมด [${modeName}] ความเร็ว [${concurrency}x]? ระบบจะระงับเมนูชั่วคราวจนกว่าจะเสร็จสิ้น`)) return;

                setInterfaceLock(true);
                afterActionBtns.classList.add('d-none');
                startBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการอย่างรวดเร็ว...';
                uiSection.classList.remove('d-none');
                logWindow.innerHTML = '';
                totalSaved = 0;
                totalSavedLabel.innerText = "0.00";
                liveSpeedEl.innerText = "0";

                logContent = "Database Optimization Report\nDate: " + new Date().toLocaleString() + "\nMode: " + modeName + " (Concurrency: " + concurrency + "x)\n" + "=".repeat(65) + "\n";

                let completed = 0;
                startTime = performance.now();

                // Start live timer & speed tracker
                timerInterval = setInterval(() => {
                    const elapsed = (performance.now() - startTime) / 1000;
                    elapsedTimeEl.innerText = elapsed.toFixed(1) + 's';
                    if (elapsed > 0) {
                        const speed = (completed / elapsed).toFixed(1);
                        liveSpeedEl.innerText = speed;
                    }
                }, 200);

                // 1. Mark and log VIEWs instantly in 0ms (no wasted HTTP roundtrips)
                if (viewTables.length > 0) {
                    for (const view of viewTables) {
                        appendLog(`⏭️ ข้าม: \`${view}\` [VIEW]`, '#888888', true);
                        completed++;
                    }
                    const initialPercent = Math.round((completed / totalTableCount) * 100);
                    progressBar.style.width = initialPercent + '%';
                    progressBar.innerText   = initialPercent + '%';
                    countText.innerText     = `${completed} / ${totalTableCount}`;
                }

                // 2. Process Base Tables via Concurrent Async Worker Pool
                const queue = [...baseTables];
                const activeWorkers = [];

                async function worker() {
                    while (queue.length > 0) {
                        const table = queue.shift();
                        const meta  = tableMetaMap[table] || { engine: 'INNODB', size_mb: 0, free_mb: 0, type: 'BASE TABLE' };

                        if (meta.size_mb > 50) {
                            statusText.innerText = `⚡ กำลังปรับปรุงตารางขนาดใหญ่: ${table} (${meta.size_mb} MB)... (${queue.length} ตารางที่เหลือ)`;
                        } else {
                            statusText.innerText = `กำลังจัดการ: ${table}... (${queue.length} ตารางที่เหลือ)`;
                        }

                        const controller = new AbortController();
                        const timeoutId  = setTimeout(() => controller.abort(), 120000); // 120s timeout

                        try {
                            const queryParams = new URLSearchParams({
                                action:  'optimize',
                                table:   table,
                                mode:    selectedMode,
                                engine:  meta.engine,
                                size_mb: meta.size_mb,
                                free_mb: meta.free_mb,
                                type:    meta.type
                            });

                            const res = await fetch(`?${queryParams.toString()}`, { signal: controller.signal });
                            clearTimeout(timeoutId);

                            const text = await res.text();
                            let result;
                            try {
                                result = JSON.parse(text);
                            } catch (e) {
                                throw new Error(text.substring(0, 120) || 'การตอบกลับจาก Server ไม่ถูกต้อง');
                            }

                            if (result.skipped) {
                                const skipColor = result.message.includes('⚠️') ? '#ffae00' : '#888888';
                                appendLog(result.message, skipColor, true);
                            } else if (result.success) {
                                const saved = Math.max(0, result.before - result.after);
                                if (saved > 0) {
                                    totalSaved += saved;
                                    totalSavedLabel.innerText = totalSaved.toFixed(2);
                                }
                                appendLog(result.message, '#8cf68c');
                            } else {
                                appendLog(result.message, '#ff6b6b');
                            }
                        } catch (error) {
                            clearTimeout(timeoutId);
                            const errMsg = error.name === 'AbortError' ? 'หมดเวลาเชื่อมต่อ (เกิน 120 วิ - ข้ามตารางนี้อัตโนมัติ)' : error.message;
                            appendLog(`⚠️ ข้ามตาราง: ${table} [สาเหตุ: ${errMsg}]`, '#ffae00');
                            // Delay สั้นๆ เพื่อให้ Apache / MySQL เคลียร์ connection ป้องกันการสะสมของคิว
                            await new Promise(r => setTimeout(r, 200));
                        }

                        completed++;
                        const percent = Math.round((completed / totalTableCount) * 100);
                        progressBar.style.width = percent + '%';
                        progressBar.innerText   = percent + '%';
                        countText.innerText     = `${completed} / ${totalTableCount}`;
                    }
                }

                // Launch concurrent workers
                const workerCount = Math.min(concurrency, baseTables.length || 1);
                for (let i = 0; i < workerCount; i++) {
                    activeWorkers.push(worker());
                }

                await Promise.all(activeWorkers);

                // Stop timer
                clearInterval(timerInterval);
                const totalElapsed = ((performance.now() - startTime) / 1000).toFixed(2);
                elapsedTimeEl.innerText = totalElapsed + 's';
                const finalSpeed = (totalTableCount / totalElapsed).toFixed(1);
                liveSpeedEl.innerText = finalSpeed;

                logContent += "=".repeat(65) + "\n";
                logContent += `Completed in: ${totalElapsed} seconds (${finalSpeed} tables/sec)\n`;
                logContent += `Total Space Saved: ${totalSaved.toFixed(2)} MB\n`;

                statusText.innerText = `✅ เสร็จสมบูรณ์ใน ${totalElapsed} วินาที! (${finalSpeed} ตาราง/วินาที)`;
                startBtn.classList.add('d-none');
                afterActionBtns.classList.remove('d-none');
                setInterfaceLock(false);
            });

            // ==========================================
            // Reset & Download Handlers
            // ==========================================
            resetBtn.addEventListener('click', () => {
                startBtn.classList.remove('d-none');
                startBtn.innerHTML = '<i class="fas fa-play mr-2"></i>เริ่มรัน Optimize & ปรับ Engine ทุกตาราง';
                afterActionBtns.classList.add('d-none');
                uiSection.classList.add('d-none');
                totalSavedLabel.innerText = "0.00";
                liveSpeedEl.innerText     = "0";
                elapsedTimeEl.innerText   = "0.0s";
                progressBar.style.width   = '0%';
                progressBar.innerText     = '0%';
                logWindow.innerHTML       = '<div style="color: #666;">--- กดปุ่มด้านบนเพื่อเริ่มกระบวนการ ---</div>';
            });

            downloadBtn.addEventListener('click', () => {
                const blob = new Blob([logContent], { type: 'text/plain;charset=utf-8' });
                const url  = window.URL.createObjectURL(blob);
                const a    = document.createElement('a');
                a.href     = url;
                a.download = `db_optimize_report_${new Date().toISOString().slice(0,10)}.txt`;
                a.click();
                window.URL.revokeObjectURL(url);
            });

            // ==========================================
            // Index Structure Optimization Script
            // ==========================================
            const applyIndexesBtn = document.getElementById('apply-indexes-btn');
            const indexUiSection  = document.getElementById('index-ui-section');
            const indexLogWindow  = document.getElementById('index-log-window');

            applyIndexesBtn.addEventListener('click', async () => {
                if (!confirm('ต้องการเริ่มตรวจสอบและปรับปรุงโครงสร้างดัชนี (Index)?')) return;

                setInterfaceLock(true);
                applyIndexesBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังตรวจสอบและปรับปรุง...';
                indexUiSection.classList.remove('d-none');
                indexLogWindow.innerHTML = '<div style="color: #888;">กำลังตรวจสอบและติดตั้ง Index ด้วยระบบความเร็วสูง...</div>';

                try {
                    const res = await fetch('?action=apply_indexes');
                    const result = await res.json();
                    
                    indexLogWindow.innerHTML = '';
                    if (result.log && result.log.length > 0) {
                        result.log.forEach(msg => {
                            const div = document.createElement('div');
                            div.style.marginBottom = '4px';
                            if (msg.includes('✅')) {
                                div.style.color = '#8cf68c';
                            } else if (msg.includes('🗑️')) {
                                div.style.color = '#ffae00';
                            } else if (msg.includes('⏭️')) {
                                div.style.color = '#888888';
                                div.style.opacity = '0.7';
                            } else {
                                div.style.color = '#ff6b6b';
                            }
                            div.innerText = msg;
                            indexLogWindow.appendChild(div);
                        });
                    }
                    
                    if (result.success) {
                        if (typeof alertify !== 'undefined') {
                            alertify.success('ปรับปรุงโครงสร้าง Index สำเร็จเรียบร้อย!');
                        }
                    } else {
                        if (typeof alertify !== 'undefined') {
                            alertify.error('พบบางรายการผิดพลาด กรุณาตรวจสอบ Log');
                        }
                    }
                } catch (error) {
                    const div = document.createElement('div');
                    div.style.color = '#ff6b6b';
                    div.innerText = '❌ เกิดข้อผิดพลาดร้ายแรงในการเชื่อมต่อ: ' + error.message;
                    indexLogWindow.appendChild(div);
                }

                applyIndexesBtn.innerHTML = '<i class="fas fa-magic mr-2"></i>ตรวจสอบและปรับปรุงโครงสร้าง Index';
                setInterfaceLock(false);
            });
        });
    </script>
    </body>
    </html>
<?php } ?>