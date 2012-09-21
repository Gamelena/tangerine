
README
======

Descargar Zend Framework de la web 
http://http://framework.zend.com/download/overview.
descomprimirlo en /usr/share/php/Zend y agregar /usr/share/php/ al include_path en php.ini.

También se puede descargar desde repositorios. 

Para crear un nuevo proyecto con AdmPortal:

1. Instalar ZF Tool, en algunas distribuciones Linux se puede encontrar en repos, si no, bajar manualmente y configurar el path 
$ sudo apt-get install zend-framework-bin

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
