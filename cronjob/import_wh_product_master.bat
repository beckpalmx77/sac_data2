@echo off
REM เปลี่ยน Code Page ของ Terminal ให้เป็น UTF-8 เพื่อรองรับภาษาไทย
chcp 65001 > nul

cd /d "%~dp0"

echo ==========================================
echo เริ่มการทำงาน SAC Product Master Import...
echo ==========================================

REM กำหนดชื่อไฟล์ PHP ของคุณ
set SCRIPT_PATH="%~dp0import_wh_product_master.php"

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