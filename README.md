README
======

Requerimientos Básicos

- PHP Version

>= 5.2.4 para sitios web (requerimiento mínimo pero no recomendado)

>= 5.4 o superior para tests unitarios (versión recomendada)


INSTALACION DE DEPENDENCIAS
===========================

1. Instalar Composer

	curl -sS https://getcomposer.org/installer | php -- 
	mv composer.phar /usr/local/bin/composer

Ahora debieramos tener disponible el comando 'composer'

2. Instalar Bower

Bower es un paquete NPM, por lo que instalaremos Node.js y NPM
	sudo apt-get install nodejs npm nodejs-legacy
	sudo npm install bower -g
	
3. ejecutar 

> ./install.sh

 
	
y seguir las instrucciones post instalación

	
Para evitar problemas de charset en los caracteres especiales en el despliegue web, se debe agregar 
al archivo php.ini cargado por apache.

	default_charset = "UTF-8" 

CREACION DE MANTENEDORES
========================
4. Moverse a una nueva carpeta y ejecutar 
	admportal-create 
	Deberá ingresar: 
	Tipo de DB (MySQL por omisión), 
	nombre de DB, Usuario DB, Password DB, 
	estos parámetros son de una instancia de DB ya existente o para crear una nueva DB.

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

Los componentes deben ser creados con el comando "zf" y no manualmente para la generación automatica de tests
ver

 - zf create module  ?
 - zf create controller ?
 - zf create model ?
 - zf create db-table ?
 - zf create form ?

Directiva Alias de ejemplo

> Alias /ussd-admportal "/proyectos/ussd-admportal/web/public/"
> 	&lt;Directory "/proyectos/ussd-admportal/web/public/"&gt;
> 	    AllowOverride All
> 	        allow from all
> 	    SetEnv APPLICATION_ENV development
> &lt;/Directory&gt;

---
Para solucionar problemas de permisos "forbidden by application config"

Agregar a apache2.conf

> 	&lt;Directory /$carpeta de proyectos/&gt; 		
>   Options Indexes
>   FollowSymLinks
>   AllowOverride None 		
>    Require all granted
> 	&lt;/Directory&gt;

