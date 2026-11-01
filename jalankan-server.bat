@echo off
title Menjalankan Server PT DIKTA (Multi-Device)
echo ========================================================
echo   SISTEM MONITORING TEKNISI - PT DIKTA
echo   Menjalankan server agar bisa diakses dari HP / Device lain
echo ========================================================
echo.

:: Tampilkan alamat IP lokal aktif
for /f "tokens=4" %%a in ('route print 0.0.0.0 ^| findstr 0.0.0.0.*0.0.0.0') do set LOCAL_IP=%%a

echo [OK] IP Laptop Anda di jaringan: %LOCAL_IP%
echo [OK] Buka di browser HP/Laptop lain: http://%LOCAL_IP%:8000
echo.
echo Menjalankan Laravel Server (Tekan Ctrl+C untuk berhenti)...
echo ========================================================
php artisan serve --host 0.0.0.0 --port 8000
pause
