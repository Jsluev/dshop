@echo off
echo ========================================
echo   DShop - Quick Setup Script
echo ========================================
echo.

REM Check if Docker is running
echo Checking Docker...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Docker is not running!
    echo Please start Docker Desktop and try again.
    pause
    exit /b 1
)
echo Docker is running!
echo.

REM Create plugins directory
echo Creating plugins directory...
if not exist "plugins\dshop" (
    mkdir plugins\dshop
)
echo.

REM Copy plugin files
echo Copying plugin files...
xcopy /E /I /Y src plugins\dshop\src
copy /Y dshop.php plugins\dshop\
copy /Y composer.json plugins\dshop\
copy /Y README.md plugins\dshop\
echo.

REM Start Docker containers
echo Starting Docker containers...
docker-compose up -d
echo.

REM Wait for MySQL to be ready
echo Waiting for MySQL to be ready...
timeout /t 30 /nobreak >nul
echo.

echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo WordPress: http://localhost:8080
echo phpMyAdmin: http://localhost:8081
echo.
echo Next steps:
echo 1. Open http://localhost:8080 in your browser
echo 2. Complete WordPress installation
echo 3. Go to Plugins and activate DShop
echo 4. Go to DShop settings and configure
echo.
pause
