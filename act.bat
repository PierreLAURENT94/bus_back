@echo off
:loop
php bin/console app:actualisation2
echo "Actualisation faite a %TIME%"
timeout /t 30 /nobreak	
goto loop