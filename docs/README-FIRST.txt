Nota: Este archivo se llama README-FIRST.txt y no README.txt debido a que el comando "zf create project" 
ya crea un archivo llamado README.txt, por lo que este archivo sería pisado, por lo tanto este archivo NO DEBE RENOMBRARSE.

README
======

Requerimientos Básicos

- PHP Version
5.2.4 o superior (idealmente php 5.5).


- Zend Framework 1.11.*
- PhpUnit 3.7.*

INSTALACION DE DEPENDENCIAS
===========================

1. Instalar Composer

	curl -sS https://getcomposer.org/installer | php -- --install-dir=bin
	mv composer.phar /usr/local/bin/composer

Ahora debieramos tener disponible el comando 'composer'

2. Instalar dependencias de Admportal

Moverse a la raiz de admportal (donde está composer.json) y configurar composer como superusuario

	composer init
	composer install

3. Configurar PHP con los paquetes instalados
	
Agregar los siguientes paths
a las variables "include_path" en de los archivos php.ini. 
Son 2 archivos: un archivo para cli y un archivo para apache (en Ubuntu /etc/php5/cli/php.ini y etc/php5/apache2/php.ini)

	/usr/share/php/zendframework/zendframework1/library
	/usr/share/php/phpunit/phpunit/PHPUnit
	
Debiera quedar algo similar a esto
	include_path = ".:/usr/share/php:/usr/share/php/zendframework/zendframework1/library:/usr/share/php/phpunit/phpunit:/usr/share/php/phpunit/phpunit/PHPUnit"

	
Para evitar problemas de charset en los caracteres especiales en el despliegue web, se debe agregar 
al archivo php.ini cargado por apache.

	default_charset = "UTF-8" 

4. Agregar variables de ambiente
	sudo gedit ~/.bashrc
Agregar
	export PATH=/usr/share/php/bin:$PATH
	alias zf='/usr/share/php/zendframework/zendframework1/bin/zf.sh'

	
5. Instalar Dojo Toolkit 1.9.1

http://download.dojotoolkit.org/

Configurar Apache para ser accessible desde la url http://localhost/dojotoolkit. 
Considere agregar 
	Include alias/*.conf
	
Al final de apache2.conf y agregar cada uno de los alias.

Considerar el sig. ejemplo: 

	Alias /dojotoolkit/ "/usr/share/javascript/dojotoolkit/"
	<Directory "/usr/share/javascript/dojotoolkit/">
	    Options Indexes MultiViews FollowSymLinks
	    AllowOverride None
	    Order deny,allow
	    Allow from all
	</Directory>


Para crear un nuevo proyecto con AdmPortal
==========================================

1. Crear variables de entorno, agregar las siguiente líneas al ~/.bashrc asumiendo que AdmPortal está instalado en $DEVELBASE/admportal 

DEVELBASE="/{Carpeta de desarrollo de proyectos}"
export ZWC_ADMPORTAL=$DEVELBASE/admportal
export PATH=$PATH:$ZWC_ADMPORTAL/tools  
export APPLICATION_ENV=development

2. Hacer un Alias para las librerias javascript de zweicom, donde "/proyectos/admportal/" es la ruta donde se instaló admportal

Alias /libs "/proyectos/admportal/public/js/libs/"
<Directory "/proyectos/admportal/public/js/libs/">
    Options Indexes MultiViews FollowSymLinks
    AllowOverride None
    Order deny,allow
    Allow from all
</Directory>

3. Ubicarse en consola en la carpeta del proyecto, dentro de esta carpeta se creará una subcarpeta llamada web donde estará el proyecto web.

4. Ejecutar admportal-create 
    Deberá ingresar: Tipo de DB (MySQL por omisión), nombre de DB, Usuario DB, Password DB, 
        estos parámetros pueden ser ser de una instancia de DB ya existente o para crear una nueva DB.
    Deberá escoger:
        - Creación automática de Base de Datos y Usuario (S/N) (N por omisión) escoger S en caso de no usar una DB ya existente, sólo se crea la DB si no existe. 
        - Generación automática de tablas y datos base (S/N) (S por omisión) solo se (re)crean los datos básicos para el funcionamiento de AdmPortal.
        - Copiar modelos, módulos y controladores de AdmPortal (N por omisión) escoger S sólo en caso de necesitar modificar modelos, módulos, controladores/vistas por defecto,
         tener en cuenta que en este caso, las actualizaciones y mejoras que se hagan en AdmPortal en estos elementos no serán reflejados en la nueva aplicación.    


Perfiles y Usuarios por defecto en caso de generación automática de tablas:

-Soporte         user: zweicom, password: zweicom
-Administrador   user: admin,   password: admin
-Consultas       user: consultas, password: consultas


TIP: para agregar nuevos módulos ZF ejecutar dentro del proyecto
$ zf create module {módulo} 

NOTA: TODOS LOS COMPONENTES MVC deben ser creados con el comando "zf" y no manualmente para la generación
automatica de tests
ver
	zf create module ?
	zf create controller ?
	zf create model ?
	zf create db-table ?
	zf create form ?

Directiva Alias de ejemplo

Alias /ussd-admportal "/proyectos/ussd-admportal/web/public/"
<Directory "/proyectos/ussd-admportal/web/public/">
    AllowOverride All
        allow from all
    SetEnv APPLICATION_ENV development
</Directory>

