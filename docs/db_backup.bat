@echo off

SET MYSQLBINDIR=D:\xampp\mysql\bin
SET BACKUPDIR=D:\db_backup\

SET x=%TIME%
SET y=%DATE%
rem set y=2014/8/7 Thue
rem set x= 9:08:05.24
for /f "tokens=1 delims=:" %%m in ('echo %x%') do set hour=%%m
rem echo %hour%
for /f "tokens=2 delims=:" %%m in ('echo %x%') do set min=%%m
for /f "tokens=3 delims=:" %%m in ('echo %x%') do set s=%%m
for /f "tokens=1 delims=." %%m in ('echo %s%') do set sec=%%m

for /f "tokens=1 delims=/" %%m in ('echo %y%') do set year=%%m
for /f "tokens=2 delims=/" %%m in ('echo %y%') do set month=%%m
for /f "tokens=3 delims=/" %%m in ('echo %y%') do set d=%%m
rem for /f "tokens=1 delims=-" %%m in ('echo %y%') do set year=%%m
rem for /f "tokens=2 delims=-" %%m in ('echo %y%') do set month=%%m
rem for /f "tokens=3 delims=-" %%m in ('echo %y%') do set d=%%m
for /f "tokens=1 delims= " %%m in ('echo %d%') do set day=%%m

if %hour% lss 10 SET "hour=0%hour%"

rem if %day% lss 10 SET "day=0%day%"

SET FILENAME=%year%%month%%day%%hour%%min%%sec%
rem SET FILENAME=%date:~0,4%%date:~5,2%%date:~8,2%%time:~0,2%%time:~3,2%%time:~6,2%
"%MYSQLBINDIR%\.\mysqldump.exe" -h 127.0.0.1 -u backup001 -p19Ac*8 cmdb>"%BACKUPDIR%%FILENAME%".sql

rem pause