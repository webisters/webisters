@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
php "%SCRIPT_DIR%webisters" %*
set "EXIT_CODE=%ERRORLEVEL%"

endlocal & exit /b %EXIT_CODE%
