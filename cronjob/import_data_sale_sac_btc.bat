@echo off
php -d memory_limit=512M -f "%~dp0import_data_sale_sac_btc.php" %*