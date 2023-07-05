@echo off
:loop
php mq_send_job.php
timeout /t 30 /nobreak > NUL
goto :loop