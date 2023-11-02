@echo off

set SCRIPT=%1
if "%SCRIPT%"=="" (set SCRIPT=package)

composer run-script make %SCRIPT%
