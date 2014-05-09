@echo off
if "%1" == "" goto :noparams
if "%2" == "" goto :noparams
set _t=%2
set _root=%_t:\=/%
mysql -u root -p -D %1 --init-command="set @root = '%_root%';" < setCiviDirs.sql
goto :eol
:noparams
echo Usage: %0 ^<full_database_name^> ^<BlueBird_root_directory^>
echo   example: %0 senate_c_dev
goto :eol
:eol