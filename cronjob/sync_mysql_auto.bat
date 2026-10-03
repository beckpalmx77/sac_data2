@echo off
setlocal enabledelayedexpansion

REM ==============================================================================
REM  MySQL Database Automated Synchronization Script (Direct Stream - Data Only)
REM ==============================================================================
REM  Source      : 192.168.88.7:3307
REM  Destination : 192.168.88.40:3307
REM  Engine      : MySQL 9.2 (D:\wamp64\bin\mysql\mysql9.2.0\bin)
REM  Databases   : sac_data, sac_data2, sac_emp_dbs, sac_emp, sac_data_btc, sac_cust
REM  Mode        : Data Only (--no-create-info, --replace, --skip-triggers)
REM  Execution   : Fully Automated (Direct Stream, No Log Files Created)
REM ==============================================================================

REM ------------------------------------------------------------------------------
REM 1. Connection Configurations
REM ------------------------------------------------------------------------------
set "SRC_HOST=192.168.88.7"
set "SRC_PORT=3307"
set "SRC_USER=myadmin"
set "SRC_PASS=myadmin"

set "DST_HOST=192.168.88.40"
set "DST_PORT=3307"
set "DST_USER=myadmin"
set "DST_PASS=myadmin"

set "DATABASES=sac_data sac_data2 sac_emp_dbs sac_emp sac_data_btc sac_cust"

REM ------------------------------------------------------------------------------
REM 2. Locate MySQL 9.2 executables
REM ------------------------------------------------------------------------------
set "MYSQL_BIN_DIR=D:\wamp64\bin\mysql\mysql9.2.0\bin"

if exist "%MYSQL_BIN_DIR%\mysql.exe" if exist "%MYSQL_BIN_DIR%\mysqldump.exe" (
    set "MYSQL_CMD=%MYSQL_BIN_DIR%\mysql.exe"
    set "DUMP_CMD=%MYSQL_BIN_DIR%\mysqldump.exe"
) else (
    where mysqldump.exe >nul 2>nul
    if !ERRORLEVEL! equ 0 (
        set "MYSQL_CMD=mysql.exe"
        set "DUMP_CMD=mysqldump.exe"
    ) else (
        echo [ERROR] MySQL 9.2 not found in %MYSQL_BIN_DIR% and not in PATH.
        exit /b 1
    )
)

REM ------------------------------------------------------------------------------
REM 3. Header Display
REM ------------------------------------------------------------------------------
echo ==============================================================================
echo           MySQL Database Auto Sync (Direct Stream - Data Only)
echo ==============================================================================
echo  Source      : %SRC_HOST%:%SRC_PORT% (User: %SRC_USER%)
echo  Destination : %DST_HOST%:%DST_PORT% (User: %DST_USER%)
echo  MySQL Engine: MySQL 9.2 (%MYSQL_CMD%)
echo  Databases   : %DATABASES%
echo  Start Time  : %date% %time%
echo ==============================================================================
echo.

REM ------------------------------------------------------------------------------
REM 4. Pre-flight Connection Tests
REM ------------------------------------------------------------------------------
echo Testing database connections...

set "MYSQL_PWD=%SRC_PASS%"
"%MYSQL_CMD%" -h %SRC_HOST% -P %SRC_PORT% -u %SRC_USER% -e "SELECT 1;" >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Cannot connect to Source server [%SRC_HOST%:%SRC_PORT%]
    exit /b 1
)
echo  [OK] Source connected successfully.

set "MYSQL_PWD=%DST_PASS%"
"%MYSQL_CMD%" -h %DST_HOST% -P %DST_PORT% -u %DST_USER% -e "SELECT 1;" >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Cannot connect to Destination server [%DST_HOST%:%DST_PORT%]
    exit /b 1
)
echo  [OK] Destination connected successfully.
echo.

REM ------------------------------------------------------------------------------
REM 5. Direct Stream Synchronization Loop
REM ------------------------------------------------------------------------------
set "SUCCESS_COUNT=0"
set "FAIL_COUNT=0"

REM Count total databases
set "TOTAL_DBS=0"
for %%D in (%DATABASES%) do set /a TOTAL_DBS+=1

set "CURRENT_INDEX=0"
for %%D in (%DATABASES%) do (
    set /a CURRENT_INDEX+=1
    call :SYNC_ONE_DIRECT %%D !CURRENT_INDEX! !TOTAL_DBS!
)

REM ------------------------------------------------------------------------------
REM 6. Summary Output
REM ------------------------------------------------------------------------------
title MySQL Sync Finished - Success: !SUCCESS_COUNT!, Failed: !FAIL_COUNT!

echo.
echo ==============================================================================
echo                             Execution Summary
echo ==============================================================================
echo  Finished At : %date% %time%
echo  Successful  : !SUCCESS_COUNT! / !TOTAL_DBS! databases
echo  Failed      : !FAIL_COUNT! / !TOTAL_DBS! databases
echo ==============================================================================

exit /b !FAIL_COUNT!

REM ==============================================================================
REM Subroutine: Direct Stream Sync for a single database
REM ==============================================================================
:SYNC_ONE_DIRECT
set "CURR_DB=%~1"
set "IDX=%~2"
set "TOTAL=%~3"
set /a PCT=(IDX * 100) / TOTAL

REM Update Command Prompt window title
title [!IDX!/!TOTAL!] (!PCT!%%) Syncing: %CURR_DB% ...

echo.
echo ==============================================================================
echo  [%time%] Progress: [!IDX!/!TOTAL!] (!PCT!%%) - Syncing database: %CURR_DB%
echo ==============================================================================

REM 1. Ensure database exists on destination
set "MYSQL_PWD=%DST_PASS%"
"%MYSQL_CMD%" -h %DST_HOST% -P %DST_PORT% -u %DST_USER% --default-character-set=utf8mb4 -e "CREATE DATABASE IF NOT EXISTS %CURR_DB% DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>&1

REM 2. Pipe dump from source directly into destination with --verbose (Data Only)
set "MYSQL_PWD=%SRC_PASS%"
"%DUMP_CMD%" -h %SRC_HOST% -P %SRC_PORT% -u %SRC_USER% --single-transaction --quick --no-create-info --replace --skip-triggers --max_allowed_packet=512M --default-character-set=utf8mb4 --verbose --databases %CURR_DB% | "%MYSQL_CMD%" -h %DST_HOST% -P %DST_PORT% -u %DST_USER% --max_allowed_packet=512M --default-character-set=utf8mb4

if %ERRORLEVEL% equ 0 (
    echo.
    echo [%time%] [SUCCESS] Database %CURR_DB% synced successfully.
    set /a SUCCESS_COUNT+=1
) else (
    echo.
    echo [%time%] [FAILED] Error syncing database %CURR_DB% - Error code: %ERRORLEVEL%
    set /a FAIL_COUNT+=1
)
exit /b 0
