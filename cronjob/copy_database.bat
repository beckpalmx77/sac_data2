@echo off
set source=D:\BplusDB-BAKUP
set destination=\\192.168.39.10\BackBplusnew\Bplus_payroll

xcopy %source%\* %destination% /E /H /C /I /Y

echo Files copied from %source% to %destination%

@echo off
set source=D:\backups
set destination=\\192.168.39.10\BackBplusnew\Syy_WebApp

xcopy %source%\* %destination% /E /H /C /I /Y

echo Files copied from %source% to %destination%




REM del /f file.txt