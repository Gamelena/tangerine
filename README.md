README
======

Requerimientos Básicos

- PHP Version >= 5.4

- MYSQL Version >= 5


INSTALACION DE DEPENDENCIAS
===========================

1. Composer
	Automatiza la instalación y configuración las dependencias del lado servidor
	```
	curl -sS https://getcomposer.org/installer | php -- 
	mv composer.phar /usr/local/bin/composer
	```
    Ahora tenemos disponible el comando 'composer'

2. Bower
    Bower es un paquete NPM, por lo que instalaremos Node.js y NPM
	Automatiza la instalación y configuración las dependencias del lado cliente
	```
	sudo apt-get install nodejs npm nodejs-legacy
	sudo npm install bower -g
	```
	Ahora tenemos disponible el comando 'bower'
3. Ant 
	Se usa ant como herramienta de automatizacion de tareas tales como construcción y análisis estático del código.
	Para ejecutar los tests funcionales debe clonarse el proyecto admportal/admportal-testing.
	```
	sudo apt-get install ant
	```
	sudo apt-get install nodejs npm nodejs-legacy
 
    Para hacer un full build del proyecto ejecutar
	```	
	ant
	```
	En la raiz del proyecto, esto tambien ejecuta las tarea relacionadas con Bower y Composer.
	Nota: esto deployará el proyecto, pero lanzara una salida de error si no tenemos instaladas las herramientas de testing en forma global, lo cual se puede realizar con la tarea.
	```
	ant global-testing-deps
	```
	Para ver el detalle de las tareas disponibles.
	```
	cat build.xml
	``` 
	Ver detalle de mensajes (modo verboso).
	```
	ant -v
	```
	
Para evitar problemas de charset en los caracteres especiales en el despliegue web, se debe agregar 
al archivo php.ini cargado por apache.
    ```
	default_charset = "UTF-8" 
    ```
CREACION DE MANTENEDORES
========================
4. Moverse a una nueva carpeta y ejecutar 
```
	admportal-create 
```
Se pedirá ingresar: 
-	Tipo de DB (MySQL por omisión), 
-	nombre de DB, Usuario DB, Password DB,  estos parámetros son de una instancia de DB ya existente o para crear una nueva DB.

Después se podrán escoger las opciones:

 - Creación automática de Base de Datos y Usuario (S/N) (N por omisión)
   escoger S en caso de no usar una DB ya existente, sólo se crea la DB
   si no existe.

	

 - Generación automática de tablas y datos base (S/N) (S por omisión)
   solo se (re)crean los datos básicos para el funcionamiento de
   AdmPortal.

	

 - Copiar modelos, módulos y controladores de AdmPortal (N por omisión)
   escoger S sólo en caso de necesitar modificar modelos, módulos,
   controladores/vistas por defecto, tener en cuenta que en este caso de
   escoger S, se crea una copia del MVC, lo cual permite personalizar
   estos componentes pero las actualizaciones y mejoras que se hagan en
   AdmPortal en estos elementos no serán reflejados en la nueva
   aplicación.




TIP: para agregar nuevos módulos ZF ejecutar dentro del proyecto
$ zf create module {módulo} 

Los componentes deben ser creados con el comando "zf" para la generación automatica de tests
ver

 - zf create module  ?
 - zf create controller ?
 - zf create model ?
 - zf create db-table ?
 - zf create form ?

Directiva Alias de ejemplo

```
Alias /ussd-admportal "/proyectos/ussd-admportal/web/public/"
<Directory "/proyectos/ussd-admportal/web/public/">
	AllowOverride All
	Allow from all
    SetEnv APPLICATION_ENV development
</Directory>
```
TESTING
=======
Se requieren las librerías 
- PHPUnit
- PHP_CodeSniffer
- PHPLOC
- PHP_Depend
- PHPMD
- PHPCPD
- phpDox
Las cuales también son usadas por Jenkins para integración continua y pueden ser instaladas con ant
```
ant global-testing-deps
```

GENERACIÓN DE PLANTILLAS DE TESTS UNITARIOS
-------------------------------------------
Los Controllers y Actions deben ser creados con la herramienta
```
zf
```
Debe incluirse al archivo php.ini cli (en Debian/Ubuntu '/etc/php5/cli/php.ini', En RHEL/Centos '/etc/php.ini')
```
INCLUDE_PATH {paths ya existentes}:{admportal path}/vendor/phpunit/phpunit
```
En este punto zf intentará crear los Tests Unitarios, pero Zend Framework 1 por defecto no es compatible con PHPUnit 4 por lo que arrojará el siguiente error
```
zf create controller Pepito
Creating a controller {...blah}
Creating a controller test {...bleh}
PHP Fatal error: Class 'PHPUnit_Framework_TestCase' not found in {..blih}/vendor/zendframework/zendframework1/library/Zend/Test/PHPUnit/ControllerTestCase.php
PHP Stack trace:
{...bloh}
``` 
Para solucionar este problema se debe aplicar un parche a Zend_Test_PHPUnit
```
ant zend-test-patch
```

SELENIUM
--------
Para ejecutar los tests basados en browser se debe instalar Selenium server (Las pruebas con Selenium no están "Jenkinizadas").
Instalación de Selenium
- Descargar el .jar Selenium SERVER desde http://www.seleniumhq.org/download/
- Dejarlo el archivo en /usr/local/bin 
- Ejecutarlo como .jar
```
java -jar /usr/local/bin/selenium-server-standalone-2.42.2.jar
```


Para usarlo con phpunit debe estar instalada la extension phpunit-selenium la cual es instalada con composer.
Por convención los tests de selenium están escritos en web/tests/selenium/

Es necesario crear alias para selenium en ~/.bashrc (o equivalente).
```
alias selenium="java -jar /usr/local/bin/selenium-server-standalone-2.42.2.jar"
```
Ejecutar selenium antes de iniciar los tests unitarios


Se debe especificar la URL BASE en el archivo application.ini, reemplazando {BASE_URL} por la URL BASE del administrador web.
```
 zwei.uTesting.httpHost = "http://{BASE_URL}"
```

Los Tests se escriben en la carpeta web/tests

Ejemplo de configuración de suite de pruebas

```
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/3.7/phpunit.xsd"
    bootstrap="./bootstrap.php" colors="true">
    <testsuite name="Application Test Suite">
        <directory>./application</directory>
        <directory>./library</directory>
        <directory>./selenium</directory>
    </testsuite>
    <php>
        <const name="PHPUNIT_USERNAME" value="gamelena"/>
        <const name="PHPUNIT_PASSWORD" value="gamelena"/>
        <const name="PHPUNIT_BROWSER" value="opera"/>
        <const name="PHPUNIT_WAITSECONDS" value="5"/>
    </php>
</phpunit>
```



---

FAQ
===
Para solucionar problemas de permisos "forbidden by application config"

Agregar a apache2.conf

```
<Directory "/$carpeta de proyectos/">
   Options Indexes
   FollowSymLinks
   AllowOverride None 		
   Require all granted #Apache2.4
   #Order allow,deny #Apache 2.2 
   #Allow from all #Apache 2.2
</Directory>
```
```
<Directory "/opt/admportal/"> 		
   Options Indexes
   FollowSymLinks
   AllowOverride None 		
   Require all granted #Apache2.4
   #Order allow,deny #Apache 2.2 
   #Allow from all #Apache 2.2
</Directory>

```