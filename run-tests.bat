@echo off
chcp 65001 >nul
REM Script for running PHPUnit tests

echo ========================================
echo Running PHPUnit tests
echo ========================================
echo.

REM Check vendor directory
if not exist vendor (
    echo Installing dependencies...
    call composer install
    if errorlevel 1 (
        echo.
        echo ERROR: Failed to install dependencies
        pause
        exit /b 1
    )
    echo.
)

REM Check if PHPUnit exists
if not exist vendor\bin\phpunit (
    echo PHPUnit not found. Installing dependencies...
    call composer install
    if errorlevel 1 (
        echo.
        echo ERROR: Failed to install PHPUnit
        pause
        exit /b 1
    )
    echo.
)

REM Run tests
echo Running tests...
echo.
php vendor\phpunit\phpunit\phpunit --colors=always

if errorlevel 1 (
    echo.
    echo ========================================
    echo Tests FAILED
    echo ========================================
) else (
    echo.
    echo ========================================
    echo Tests PASSED
    echo ========================================
)

pause
