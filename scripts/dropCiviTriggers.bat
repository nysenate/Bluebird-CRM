@echo off
mysql -u root -proot -e "select trigger_name from information_schema.triggers" > script-tmp.txt 2> nul
set /p triggerList= < script-tmp.txt
for /F "tokens=*" %%A in (script-tmp.txt) do (
if not %%A == trigger_name (
mysql -u root -proot -e "DROP TRIGGER IF EXISTS `%1`.`%%A`" > nul
)
)
del script-tmp.txt
echo Done.
