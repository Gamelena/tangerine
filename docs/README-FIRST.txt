Nota: Este archivo se llama README-FIRST.txt y no README.txt debido a que el comando "zf create project" 
ya crea un archivo llamado README.txt, por lo que este archivo sería pisado, por lo tanto este archivo NO DEBE RENOMBRARSE.

README
======

Requerimientos Básicos

- PHP Version
5.2.4 o superior.

"Zend recommends the most current release of PHP for critical security and performance enhancements" 

- Zend Framework 1.11.11 
http://www.zend.com/community/downloads.
Descomprimirlo en y agregar la ruta de instalación a la variable "include_path" en los 2 archivos php.ini 
	(un archivo es cargado por Apache y el otro para procesos CLI como phpunit) .
Ejemplo:

	include_path = ".:/usr/share/php:/usr/share/php/libzend-framework-php"
	
Verificar el charset configurado, para evitar problemas de charset se recomienda agregar.

	default_charset = "UTF-8" 
	
	al archivo php.ini cargado por apache

- Dojo Toolkit 1.9.1
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


- PHP Unit 3.5.15 o 3.7.28 (Opcional para hacer pruebas unitarias), no funciona con 3.6.*, otras versiones no han sido probadas.


Para crear un nuevo proyecto con AdmPortal:

1. Instalar ZF Tool, en algunas distribuciones Linux se puede encontrar en los repositorios por defecto, 
$ sudo apt-get install zend-framework-bin

Si no se encuentra en los repositorios, hacer un link simbólico a archivo zf.sh de forma de poder ejectuarlo como "zf"

ln -s /usr/share/php/Zend/bin/zf.sh /usr/bin/zf


2. Crear variables de entorno, agregar las siguiente líneas al ~/.bashrc asumiendo que AdmPortal está instalado en $DEVELBASE/admportal 

DEVELBASE="/{Carpeta de desarrollo de proyectos}"
export ZWC_ADMPORTAL=$DEVELBASE/admportal
export PATH=$PATH:$ZWC_ADMPORTAL/tools  
export APPLICATION_ENV=development

3. Hacer un Alias para las librerias javascript de zweicom, donde "/proyectos/admportal/" es la ruta donde se instaló admportal

Alias /libs "/proyectos/admportal/public/js/libs/"
<Directory "/proyectos/admportal/public/js/libs/">
    Options Indexes MultiViews FollowSymLinks
    AllowOverride None
    Order deny,allow
    Allow from all
</Directory>

4. Ubicarse en consola en la carpeta del proyecto, dentro de esta carpeta se creará una subcarpeta llamada web donde estará el proyecto web.

5. Ejecutar admportal-create 
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

Directiva Alias de ejemplo

Alias /ussd-admportal "/proyectos/ussd-admportal/web/public/"
<Directory "/proyectos/ussd-admportal/web/public/">
    AllowOverride All
        allow from all
    SetEnv APPLICATION_ENV development
</Directory>

