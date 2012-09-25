
README
======

Requerimientos Básicos

- Zend Framework 1.12 
http://www.zend.com/community/downloads.
descomprimirlo en /usr/share/php/Zend y agregar /usr/share/php/ al include_path en php.ini.

- Dojo Toolkit 1.7
http://download.dojotoolkit.org/

Y configurar Apache para ser accessible desde la url http://localhost/dojotoolkit. 
Considerar el sig. ejemplo: 

Alias /dojotoolkit/ "/usr/share/javascript/dojotoolkit/"
<Directory "/usr/share/javascript/dojotoolkit/">
Options Indexes MultiViews FollowSymLinks
AllowOverride None
Order deny,allow
Deny from all
Allow from 127.0.0.0/255.0.0.0 ::1/128
</Directory>


Para crear un nuevo proyecto con AdmPortal:

1. Instalar ZF Tool, en algunas distribuciones Linux se puede encontrar en los repositorios por defecto, 
$ sudo apt-get install zend-framework-bin

Si no se encuentra en los repositorios, basta con agregar al path de archivos ejecutables php los archivos zf.sh y zf.php, 
este path se puede encuentra con.

$ which php


2. Crear variables de entorno, agregar las siguiente líneas al ~/.bashrc asumiendo que AdmPortal está instalado en $DEVELBASE/admportal 

DEVELBASE="/{Carpeta de desarrollo de proyectos}"
export ZWC_ADMPORTAL=$DEVELBASE/admportal
export PATH=$PATH:$ZWC_ADMPORTAL/tools  
 
3. Ubicarse en consola en la carpeta del proyecto, dentro de esta carpeta se creará una subcarpeta llamada web donde estará el proyecto web.

4. Ejecutar admportal-create 
    Deberá ingresar: Tipo de DB (MySQL por omisión), nombre de DB, Usuario DB, Password DB, 
        estos parámetros pueden ser ser de una instancia de DB ya existente o para crear una nueva DB.
    Deberá escoger:
        - Creación automática de Base de Datos y Usuario (S/N) (N por omisión) escoger S en caso de no usar una DB ya existente, sólo se crea la DB si no existe. 
        - Generación automática de tablas y datos base (S/N) (S por omisión) solo se (re)crean los datos básicos para el funcionamiento de AdmPortal.
    

Perfiles y Usuarios por defecto en caso de generación automática de tablas:

-Soporte         user: zweicom, password: zweicom
-Administrador   user: admin,   password: admin
-Consultas       user: consultas, password: consultas


TIP: para agregar nuevos módulos ZF ejecutar dentro del proyecto
$ zf create module {módulo} 
