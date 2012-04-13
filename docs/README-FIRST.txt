
README
======

Descargar Zend Framework de la web 
http://http://framework.zend.com/download/overview.
descomprimirlo en /usr/share/php/Zend y agregar /usr/share/php/ al include_path en php.ini.

También se puede descargar desde repositorios. 

Para crear un nuevo proyecto con AdmPortal:

1. Instalar ZF Tool, en algunas distribuciones Linux se puede encontrar en repos, si no, bajar manualmente y configurar el path 
$ sudo apt-get install zend-framework-bin
 
2. Ubicarse en consola en la carpeta padre de donde se creará el proyecto o donde se añadirá soporte para ZF Tool. 
$ zf create project {proyecto}
Se puede añadir soporte ZF Tool a un proyecto existente reemplazando {proyecto} por el nombre de la carpeta raiz del proyecto web (usualmente 'web','php' o 'public_html').

3. Sobreescribir las carpetas creadas con ZF Tool con los archivos de AdmPortal (sobreescribir todo).

4. Crear la base de datos de ser necesario basandose en docs/db/createdb.sql (opcional).

5. Configurar conexion en configs/application.ini.

6. Ejecutar SQL de docs/db/admportal.sql.

7. Configurar vhost según el archivo README.txt generado con ZF Tool (opcional).

8. Loguearse con usuario Soporte.

Perfiles y Usuarios por defecto:

-Soporte         user: zweicom, password: zweicomadmin2011
-Administrador   user: admin,   password: admin
-Consultas       user: consultas, password: consultas


TIP: para agregar nuevos módulos ejecutar dentro del proyecto
$ zf create module {módulo} 
