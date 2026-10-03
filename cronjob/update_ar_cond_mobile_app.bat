@echo off
REM เปลี่ยน Code Page ของ Terminal ให้เป็น UTF-8 เพื่อรองรับภาษาไทย
chcp 65001 > nul

echo ==========================================
echo เริ่มการทำงาน SAC Price Group Update...
echo ==========================================

REM กำหนดชื่อไฟล์ PHP ของคุณ
set SCRIPT_PATH="D:\website\sac_data2\cronjob\update_ar_cond_mobile_app.php"

REM เรียกใช้คำสั่ง php ได้โดยตรงเลย
php %SCRIPT_PATH%

REM เช็คสถานะการทำงานของสคริปต์ PHP
if %ERRORLEVEL% EQU 0 (
    echo.
    echo ------------------------------------------
    echo [ STATUS: SUCCESS - อัปเดตข้อมูลสำเร็จ! ]
    echo ------------------------------------------
) else (
    echo.
    echo ------------------------------------------
    echo [ STATUS: FAILED - เกิดข้อผิดพลาดในการทำงาน! ]
    echo ------------------------------------------
)

echo.
echo ==========================================
REM หน่วงเวลาหน้าจอไว้ 5 วินาที
REM timeout /t 5