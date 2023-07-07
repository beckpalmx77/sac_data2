@echo off
:loop
php mq_send.php
timeout /t 5 /nobreak > NUL
goto :loop