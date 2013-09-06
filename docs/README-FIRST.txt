Nota: Este archivo se llama README-FIRST.txt y no README.txt debido a que el comando "zf create project" 
ya crea un archivo llamado README.txt, por lo que este archivo sería pisado, por lo tanto este archivo NO DEBE RENOMBRARSE.

README
======

Requerimientos Básicos

- Zend Framework 1.12 
http://www.zend.com/community/downloads.
descomprimirlo en /usr/share/php/Zend y agregar /usr/share/php/ al include_path en php.ini.

- Dojo Toolkit 1.9.3
http://download.dojotoolkit.org/

Configurar Apache para ser accessible desde la url http://localhost/dojotoolkit. 
Considerar el sig. ejemplo: 

Alias /dojotoolkit/ "/usr/share/javascript/dojotoolkit/"
<Directory "/usr/share/javascript/dojotoolkit/">
Options Indexes MultiViews FollowSymLinks
AllowOverride None
Order deny,allow
Deny from all
Allow from 127.0.0.0/255.0.0.0 ::1/128
</Directory>


- PHP Unit 3.5.15 (Opcional para hacer pruebas unitarias)


Para crear un nuevo proyecto con AdmPortal:

1. Instalar ZF Tool, en algunas distribuciones Linux se puede encontrar en los repositorios por defecto, 
$ sudo apt-get install zend-framework-bin

Si no se encuentra en los repositorios, hacer un link simbólico a archivo zf.sh de forma de poder ejectuarlo como "zf"

ln -s /usr/share/php/Zend/bin/zf.sh /usr/bin/zf


2. Crear variables de entorno, agregar las siguiente líneas al ~/.bashrc asumiendo que AdmPortal está instalado en $DEVELBASE/admportal 

DEVELBASE="/{Carpeta de desarrollo de proyectos}"
export ZWC_ADMPORTAL=$DEVELBASE/admportal
export PATH=$PATH:$ZWC_ADMPORTAL/tools  

3. Hacer un Alias para las librerias javascript de zweicom, donde "/proyectos/admportal/" es la ruta donde se instaló admportal

Alias /libs "/proyectos/admportal/public/js/libs/"
<Directory "/proyectos/admportal/public/js/libs/">
    Options Indexes MultiViews FollowSymLinks
    AllowOverride None
    Order deny,allow
    Deny from all
    Allow from 127.0.0.0/255.0.0.0 ::1/128
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

Directiva Alias de ejemplo

Alias /ussd-admportal "/proyectos/ussd-admportal/web/public/"
<Directory "/proyectos/ussd-admportal/web/public/">
    AllowOverride All
        allow from all
    SetEnv APPLICATION_ENV development
</Directory>

