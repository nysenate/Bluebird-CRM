@echo off
if "%1"=="" goto :params
if not exist %1 goto :dirnotfound
set app_root=%1
echo Set app_root to %1

echo Linking %app_root%\civicrm\core =^> %app_root%\modules\civicrm
set t_root=%app_root%\civicrm
if not exist %t_root% goto :tnotfound
if not exist %t_root%\..\modules\civicrm goto :tnotfound
cd %t_root%
if exist core\ rmdir core
if exist core del core
mklink /D core ..\modules\civicrm
echo Done.

echo Linking %app_root%\drupal\sites\all\modules =^> %app_root%\modules
set t_root=%app_root%\drupal\sites\all
if not exist %t_root% goto :tnotfound
if not exist %t_root%\..\..\..\modules goto :tnotfound
cd %t_root%
if exist modules\ rmdir modules
if exist modules del modules
mklink /D modules ..\..\..\modules
echo Done.

echo Linking %app_root%\drupal\sites\all\docs =^> %app_root%\civicrm\docs
set t_root=%app_root%\drupal\sites\all
if not exist %t_root% goto :tnotfound
if not exist %t_root%\..\..\..\civicrm\docs goto :tnotfound
cd %t_root%
if exist docs\ rmdir docs
if exist docs del docs
mklink /D docs ..\..\..\civicrm\docs
echo Done.

echo Linking %app_root%\drupal\sites\all\ext =^> %app_root%\civicrm\custom\ext
set t_root=%app_root%\drupal\sites\all
if not exist %t_root% goto :tnotfound
if not exist %t_root%\..\..\..\civicrm\custom\ext goto :tnotfound
cd %t_root%
if exist ext\ rmdir ext
if exist ext del ext
mklink /D ext ..\..\..\civicrm\custom\ext
echo Done.

echo Linking %app_root%\drupal\sites\all\mosaico =^> %app_root%\civicrm\custom\mosaico
set t_root=%app_root%\drupal\sites\all
if not exist %t_root% goto :tnotfound
if not exist %t_root%\..\..\..\civicrm\custom\mosaico goto :tnotfound
cd %t_root%
if exist mosaico\ rmdir mosaico
if exist mosaico del mosaico
mklink /D mosaico ..\..\..\civicrm\custom\mosaico
echo Done.

echo Linking %app_root%\drupal\sites\default\themes =^> %app_root%\themes
set t_root=%app_root%\drupal\sites\default
if not exist %t_root% goto :tnotfound
if not exist %t_root%\..\..\..\themes goto :tnotfound
cd %t_root%
if exist themes\ rmdir themes
if exist themes del themes
mklink /D themes ..\..\..\themes
echo Done.

rem tell git to ignore the new symlinks
echo Telling git to ignore the new symlinks
cd %app_root%
git update-index --assume-unchanged .\civicrm\core
git update-index --assume-unchanged .\drupal\sites\all\modules
git update-index --assume-unchanged .\drupal\sites\default\themes
echo Done.
goto :eol

:params
echo Usage: %0 ^<BlueBird_root_directory^>
echo   example: %0 c:\websrv\www\BlueBird-CRM
goto :eol

:dirnotfound
echo ERROR - Could not find root directory "%1"
echo.
goto :params

:tnotfound
echo ERROR - either the symlink candidate's parent dir or its target it missing.
echo Check the installation for all original directories.
echo   error occurred while processing %t_root%
goto :eol

:eol
