@echo off

rem *********************************************************
rem set this variable to the root directory of the local repo
rem *********************************************************

set app_root=c:\websrv\www\BlueBird-CRM

cd %app_root%\civicrm
if exist core\ rmdir core
if exist core del core
mklink /D core ..\modules\civicrm
cd %app_root%\drupal\sites\all
if exist modules\ rmdir modules
if exist modules del modules
mklink /D modules ..\..\..\modules
cd %app_root%\drupal\sites\default
if exist themes\ rmdir themes
if exist themes del themes
mklink /D themes ..\..\..\themes

cd %app_root%
git update-index --assume-unchanged .\civicrm\core
git update-index --assume-unchanged .\drupal\sites\all\modules
git update-index --assume-unchanged .\drupal\sites\default\themes
