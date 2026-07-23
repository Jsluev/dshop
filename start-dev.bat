@echo off
echo ========================================
echo   DShop - Start Development
echo ========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Docker is not running!
    echo Please start Docker Desktop and try again.
    pause
    exit /b 1
)

REM Start containers
echo Starting Docker containers...
docker-compose up -d
echo.

REM Wait for services
echo Waiting for services to start...
timeout /t 10 /nobreak >nul
echo.

echo ========================================
echo   Development environment is ready!
echo ========================================
echo.
echo WordPress: http://localhost:8080
echo phpMyAdmin: http://localhost:8081
echo.
echo To stop: docker-compose down
echo To view logs: docker-compose logs -f
echo.
pause
