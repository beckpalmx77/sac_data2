<?php
/**
 * สคริปต์สำหรับทดสอบการทำงานของ Session ข้าม Server (2 Servers Test)
 * วิธีทดสอบ:
 * 1. เปิดหน้านี้ผ่าน Load Balancer หรือ IP เครื่อง Server 1 และ Server 2
 * 2. กด Refresh (F5) หน้านี้หลายๆ ครั้ง
 * 3. ตรวจสอบว่า ค่า Session ID และ Counter ยังคงนับต่อเนื่องหรือไม่ แม้ว่า Server ตอบสนองจะสลับเครื่องไปมา
 */

// เริ่มต้น Session
session_start();

$server_ip = $_SERVER['SERVER_ADDR'] ?? gethostname();
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $client_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
}

$now = date('Y-m-d H:i:s');

if (!isset($_SESSION['test_init_time'])) {
    $_SESSION['test_init_time'] = $now;
    $_SESSION['test_counter'] = 1;
    $_SESSION['created_by_server'] = $server_ip;
    $status_title = "เริ่มต้นสร้าง Session ใหม่สำเร็จ";
    $status_color = "#10b981";
} else {
    $_SESSION['test_counter']++;
    $status_title = "อ่าน Session เก่าต่อเนื่องสำเร็จ (Shared Session OK)";
    $status_color = "#3b82f6";
}

$_SESSION['last_served_by'] = $server_ip;
$_SESSION['last_access_time'] = $now;

// ตรวจสอบ Save Handler ปัจจุบัน
$session_save_handler = ini_get('session.save_handler');
$session_save_path = ini_get('session.save_path');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทดสอบ Redis Multi-Server Session - SAC DATA</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Sarabun:wght@400;600&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0b0f19;
            color: #f1f5f9;
            font-family: 'Sarabun', 'Outfit', sans-serif;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 720px;
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .badge {
            background: <?php echo $status_color; ?>;
            color: #fff;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        h2 { margin-bottom: 20px; font-size: 22px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 12px 14px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); font-size: 14px; }
        .info-table td.label { color: #94a3b8; width: 40%; font-weight: 600; }
        .info-table td.val { color: #fff; font-family: 'JetBrains Mono', monospace; }
        .server-highlight { color: #f59e0b; font-weight: 700; font-size: 16px; }
        pre {
            background: #090d16;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 16px;
            color: #38bdf8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        .hint {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #93c5fd;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="badge"><?php echo $status_title; ?></span>
        <h2>ผลการทดสอบการอ่าน/เขียน Session</h2>

        <table class="info-table">
            <tr>
                <td class="label">Session ID:</td>
                <td class="val"><?php echo session_id(); ?></td>
            </tr>
            <tr>
                <td class="label">PHP Session Save Handler:</td>
                <td class="val"><strong style="color: <?php echo ($session_save_handler === 'redis') ? '#10b981' : '#ef4444'; ?>;"><?php echo htmlspecialchars($session_save_handler); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Session Save Path:</td>
                <td class="val" style="word-break: break-all; font-size: 12px;"><?php echo htmlspecialchars($session_save_path); ?></td>
            </tr>
            <tr>
                <td class="label">เซิร์ฟเวอร์ที่กำลังตอบสนอง:</td>
                <td class="val"><span class="server-highlight"><?php echo htmlspecialchars($server_ip); ?></span></td>
            </tr>
            <tr>
                <td class="label">IP ของผู้ใช้งาน (Client IP):</td>
                <td class="val"><?php echo htmlspecialchars($client_ip); ?></td>
            </tr>
            <tr>
                <td class="label">เซิร์ฟเวอร์ที่สร้าง Session ครั้งแรก:</td>
                <td class="val"><?php echo htmlspecialchars($_SESSION['created_by_server']); ?></td>
            </tr>
            <tr>
                <td class="label">จำนวนครั้งที่ Refresh หน้านี้:</td>
                <td class="val"><strong style="font-size: 18px; color: #10b981;"><?php echo $_SESSION['test_counter']; ?></strong> ครั้ง</td>
            </tr>
        </table>

        <h4>ข้อมูลใน $_SESSION:</h4>
        <pre><?php print_r($_SESSION); ?></pre>

        <div class="hint">
            <strong>คำแนะนำการทดสอบ:</strong> ลองกด <strong>F5 (Refresh)</strong> หลายๆ ครั้ง หากระบบรันบน 2 Servers แล้ว <code>session.save_handler = redis</code> ตัวเลข <strong>Counter จะต้องเพิ่มขึ้นต่อเนื่องเรื่อยๆ</strong> แม้ว่า <strong>เซิร์ฟเวอร์ที่กำลังตอบสนอง</strong> จะสลับไปมาระหว่าง Server 1 และ Server 2
        </div>
    </div>
</body>
</html>
