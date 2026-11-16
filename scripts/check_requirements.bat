@echo off
setlocal
title Verification des prerequis - Scandale Bouffe
set "PS=%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe"
if not exist "%PS%" set "PS=powershell.exe"
"%PS%" -NoProfile -ExecutionPolicy Bypass -File "%~dp0scandale.ps1" check %*
exit /b %ERRORLEVEL%
