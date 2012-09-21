
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

4. Crear base de datos y usuario de base de datos, tomar como referencia docs/db/createdb.sql.

4. Ejecutar admportal-create.sh. 
    Deberá ingresar: Tipo de DB (MySQL por omisión), nombre de DB, Usuario DB, Password DB.
    Deberá escoger: generación automática de tablas y datos base (S/N) (S por omisión) 
    

Perfiles y Usuarios por defecto en caso de generación automática de tablas:

-Soporte         user: zweicom, password: zweicom
-Administrador   user: admin,   password: admin
-Consultas       user: consultas, password: consultas


TIP: para agregar nuevos módulos ejecutar dentro del proyecto
$ zf create module {módulo} 
