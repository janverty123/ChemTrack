@echo off
setlocal
cd /d "%~dp0"
if exist "%LOCALAPPDATA%\ChemTrack\tools\php.ini" set "PHPRC=%LOCALAPPDATA%\ChemTrack\tools\php.ini"
if exist "C:\xampp\php\php.exe" (
  "C:\xampp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
) else (
  php artisan serve --host=127.0.0.1 --port=8000
)
pause
