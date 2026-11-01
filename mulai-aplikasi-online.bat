@echo off
title Menjalankan Monitoring PT DIKTA (Online & Multi-Device)
echo ================================================================
echo        MONITORING TEKNISI - PT DIKTA (MULTI-DEVICE)
echo ================================================================
echo.

:: 1. Cek & Tampilkan IP Lokal
for /f "tokens=4" %%a in ('route print 0.0.0.0 ^| findstr 0.0.0.0.*0.0.0.0') do set LOCAL_IP=%%a

echo [1] Akses Jaringan Lokal (Satu Wi-Fi):
echo     http://%LOCAL_IP%:8000
echo.
echo [2] Menjalankan Server Laravel...
start /b php artisan serve --host 0.0.0.0 --port 8000
timeout /t 2 >nul

echo.
echo [3] Membuka Akses Online & HTTPS untuk HP (Kamera & GPS aktif)...
echo ================================================================
".\cloudflared.exe" tunnel --protocol http2 --url http://localhost:8000
pause
