<?php
require_once __DIR__ . '/../config/connect_db.php';

set_time_limit(0);
date_default_timezone_set('Asia/Bangkok');

function writeLog($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message" . PHP_EOL;
}

$successCount = 0;
$errorCount   = 0;
$skippedCount = 0;
$totalSavedMb = 0;

try {
    $startTime = microtime(true);
    $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
    
    // ดึงข้อมูลตารางทั้งหมดใน Query เดียว
    $stmt = $conn->query(
        "SELECT TABLE_NAME, TABLE_TYPE, ENGINE, 
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                ROUND(DATA_FREE / 1024 / 1024, 2) AS free_mb
         FROM information_schema.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         ORDER BY TABLE_NAME"
    );
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    writeLog("=== เริ่มต้น High-Speed Optimize/Reindex ทั้งหมด " . count($tables) . " ตาราง ใน DB: $dbName ===");

    foreach ($tables as $info) {
        $table = $info['TABLE_NAME'];
        
        // ข้าม View
        if ($info['TABLE_TYPE'] === 'VIEW') {
            writeLog("⏭️  [VIEW] `$table` → ข้าม");
            $skippedCount++;
            continue;
        }

        try {
            $oldEngine   = strtoupper($info['ENGINE'] ?? '');
            $tableSizeMb = (float)($info['size_mb'] ?? 0);
            $freeMb      = (float)($info['free_mb'] ?? 0);
            $beforeSize  = $tableSizeMb;
            $afterSize   = $tableSizeMb;
            $didRebuild  = false;
            $statusMsg   = "";

            if ($oldEngine !== 'INNODB') {
                $conn->exec("ALTER TABLE `$table` ENGINE = InnoDB");
                $statusMsg = "🔄 แปลงจาก $oldEngine → INNODB";
                $didRebuild = true;
            } else {
                // Smart Defrag
                if ($freeMb > 5 && $tableSizeMb <= 50) {
                    $conn->exec("ALTER TABLE `$table` ENGINE = InnoDB");
                    $statusMsg = "⚡ INNODB (Defrag คืนพื้นที่ {$freeMb} MB)";
                    $didRebuild = true;
                } else if ($tableSizeMb > 50) {
                    $statusMsg = "⚡ INNODB (Refresh Stats - {$tableSizeMb} MB)";
                } else {
                    $statusMsg = "⚡ INNODB (Stats Optimized)";
                }
            }

            $analyzeResult = $conn->query("ANALYZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $analyzeMsg    = $analyzeResult[0]['Msg_text'] ?? 'OK';

            if ($didRebuild) {
                $stmtSize = $conn->prepare("SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
                $stmtSize->execute([$table]);
                $afterSize = (float)$stmtSize->fetchColumn();
            }

            $saved = max(0, $beforeSize - $afterSize);
            $totalSavedMb += $saved;
            $savedText = $saved > 0 ? " | คืนพื้นที่: " . round($saved, 2) . " MB" : "";

            writeLog("✅ [$statusMsg] `$table` [{$beforeSize} MB → {$afterSize} MB]{$savedText} | ANALYZE: $analyzeMsg");
            $successCount++;

        } catch (PDOException $e) {
            writeLog("❌ ERROR: `$table` → " . $e->getMessage());
            $errorCount++;
        }
    }

    $elapsed = round(microtime(true) - $startTime, 2);
    $speed = $elapsed > 0 ? round(count($tables) / $elapsed, 1) : count($tables);

    writeLog("=========================================================");
    writeLog("=== สรุปผล: สำเร็จ $successCount | ข้าม $skippedCount | ผิดพลาด $errorCount ===");
    writeLog("=== คืนพื้นที่ทั้งหมด: " . round($totalSavedMb, 2) . " MB ===");
    writeLog("=== ใช้เวลาทั้งหมด: {$elapsed} วินาที (ความเร็ว {$speed} ตาราง/วินาที) ===");
    writeLog("=== สิ้นสุดกระบวนการทั้งหมด ===");

} catch (PDOException $e) {
    writeLog("CRITICAL ERROR: " . $e->getMessage());
}