@echo off
:loop
php mq_send.php
timeout /t 30 /nobreak > NUL
goto :loop