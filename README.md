[![Build Status](https://travis-ci.org/Gamelena/tangerine.svg?branch=develop)](https://travis-ci.org/Gamelena/tangerine)

Requeriments
======================

- PHP Version >= 5.5
- MYSQL Version >= 5

Dependencies
============

1. Composer
2. Bower
3. Ant

for full build
	
```	
ant
```

for quick build
```
ant quick-build
```


CRUD CREATION
============

4. Run
```
	tangerine-create 
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
   Tangerine.

	

 - Copiar modelos, módulos y controladores de Tangerine (N por omisión)
   escoger S sólo en caso de necesitar modificar modelos, módulos,
   controladores/vistas por defecto, tener en cuenta que en este caso de
   escoger S, se crea una copia del MVC, lo cual permite personalizar
   estos componentes pero las actualizaciones y mejoras que se hagan en
   Tangerine en estos elementos no serán reflejados.




