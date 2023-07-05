@echo off
:loop
php mq_send.php
timeout /t 20 /nobreak > NUL
goto :loop