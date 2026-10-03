@echo off
set "BATCH_NAME=D:\website\sac_data2\cronjob\mq_receive.bat"
set "WINDOW_TITLE=MQ_RECEIVE_WATCHDOG_TITLE"
set "BATCH_PATH=D:\website\sac_data2\cronjob\mq_receive.bat"

:CHECK_LOOP
CLS
echo ตรวจสอบสถานะของไฟล์: %BATCH_NAME%
echo.

REM ใช้ tasklist /v เพื่อดูชื่อหน้าต่าง และกรองหาชื่อที่เรากำหนดไว้
tasklist /v /fi "WINDOWTITLE eq %WINDOW_TITLE%" | findstr /i "%WINDOW_TITLE%" >nul

REM ตรวจสอบค่า Errorlevel:
REM 0 = พบ (กำลังทำงาน), 1 = ไม่พบ (ถูกปิดแล้ว)
if ERRORLEVEL 1 (
    echo [!!! ERROR !!!] %BATCH_NAME% ถูกปิด/ไม่พบ
    echo กำลังเปิด %BATCH_NAME% ใหม่...

    REM ใช้ START เพื่อเปิดไฟล์ Batch
    start "" "%BATCH_PATH%"

    REM หน่วงเวลา 5 วินาที หลังเปิดใหม่ เพื่อให้ระบบเริ่มทำงาน
    timeout /t 5 /nobreak >nul
) else (
    echo [OK] %BATCH_NAME% กำลังทำงานอยู่
)

echo.
echo ตรวจสอบอีกครั้งใน 15 วินาที...
timeout /t 15 /nobreak >nul

REM วนกลับไปตรวจสอบซ้ำเรื่อยๆ
goto CHECK_LOOP