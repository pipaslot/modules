@ECHO OFF
:loop

cls
SET BIN_TARGET=%~dp0/../vendor/nette/tester/src/tester
php "%BIN_TARGET%" -c ./php.ini --coverage ./code-coverage.html --coverage-src ../src . -j 4

set /p temp="Press enter to repeat"
goto loop