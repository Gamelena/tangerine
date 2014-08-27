Selenium es una herramienta para automatizar tests basados en browser.

Para usarlo con phpunit debe estar instalada la extension phpunit-selenium

Instalación 
- Descargar el .jar Selenium SERVER desde http://www.seleniumhq.org/download/
- Dejarlo el archivo en /usr/local/bin 
- Ejecutarlo como .jar
java -jar /usr/local/bin/selenium-server-standalone-2.42.2.jar
- Se recomienda crear un alias en ~/.bashrc
alias selenium="java -jar /usr/local/bin/selenium-server-standalone-2.42.2.jar"
- Ejecutar selenium antes de iniciar los tests unitarios

