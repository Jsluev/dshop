@echo off
echo ========================================
echo   DShop - Install Dependencies
echo ========================================
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo PHP is not installed.
    echo You can install PHP via:
    echo   - https://windows.php.net/download/
    echo   - Or use: choco install php
    echo.
    echo Alternatively, you can use Docker:
    echo   docker-compose exec wordpress bash
    echo   cd /var/www/html/wp-content/plugins/dshop
    echo   composer install
    echo.
    pause
    exit /b 1
)

REM Check if Composer is installed
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Composer is not installed.
    echo Please install Composer from: https://getcomposer.org/download/
    echo.
    pause
    exit /b 1
)

REM Install dependencies
echo Installing Composer dependencies...
composer install
echo.

echo ========================================
echo   Dependencies installed!
echo ========================================
echo.
pause
