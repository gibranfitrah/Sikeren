@echo off
REM ============================================================
REM  Starter SIMPATI Mock API (Windows, sekali klik)
REM  Menjalankan server di http://127.0.0.1:3000
REM  Biarkan jendela ini terbuka selama memakai menu Integrasi SIMPATI.
REM ============================================================
cd /d "%~dp0"
echo Memeriksa Node.js...
node --version
if errorlevel 1 (
  echo [ERROR] Node.js tidak ditemukan. Install dulu dari https://nodejs.org/
  pause
  exit /b 1
)
echo.
echo Menjalankan SIMPATI Mock API di http://127.0.0.1:3000 ...
echo (Tutup jendela ini untuk menghentikan server)
echo.
node simpati-server.js
pause
