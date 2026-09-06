@echo off
echo Repairing Git index...
if exist .git\\index.lock del /f /q .git\\index.lock
if exist .git\\index del /f /q .git\\index
git reset
git update-index --refresh
echo Git index successfully restored!
