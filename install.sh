#!/bin/bash

export COMPOSER_PROCESS_TIMEOUT=1200
COMPOSER_EXEC=$(which composer)
if [ ! -x "$COMPOSER_EXEC" ];
then
	if [ ! -f /tmp/composer.phar ];
	then
		cd /tmp
		curl -sS https://getcomposer.org/installer | php --
		if [ ! -f /tmp/composer.phar ];
		then
			echo "No se pudo obtener composer!!!!!!"
			exit 255;
		fi;
		COMPOSER_EXEC=/tmp/composer.sh
	fi
fi


BOWER_EXEC=$(which bower)
if [ ! -x "$BOWER_EXEC" ];
then
	npm install bower -g
	if [ ! -f /usr/bin/bower ];
	then
		echo "No se pudo instalar bower!!!!!!"
		exit 255;
	fi;
fi

if [ -z "$ADMPORTALPATH" ];
then
        export ADMPORTALPATH="/opt/admportal"
	        echo "No esta definida la variable ( ADMPORTALPATH ), usando $ADMPORTALPATH para generar la documentación post-instalación."
fi

COMPOSER_OPT="--no-dev"
if [ "$1" = "--dev" ];
then
        COMPOSER_OPT=""
fi

$COMPOSER_EXEC install $COMPOSER_OPT
$COMPOSER_EXEC update $COMPOSER_OPT
$COMPOSER_EXEC archive --dir ..
gzip -f -9 ../zweicom-admportal*.tar

echo " "
echo "======================================================================================"
echo "Se genero ../zweicom-admportal*.tar.gz, PARA INSTALAR EJECUTE LOS SIGUIENTES COMANDOS:"
echo "--------------------------------------------------------------------------------------"
sudo mkdir -p $ADMPORTALPATH
sudo tar -zxf ../zweicom-admportal* -C $ADMPORTALPATH
"
echo "============================================================================"
echo " "

echo "Dependencias instaladas"
echo " "
echo "----------------------------------------------------------------------------------"
echo "Variables de Ambiente para desarrollo"
echo "(La ruta \"$ADMPORTALPATH\" aplica solo si fueron ejecutados los comandos del recuadro anterior)"
echo "----------------------------------------------------------------------------------"
echo " "
echo "Recuerde agregar estas variable de ambiente a su archivo ~/.bashrc (o equivalente)"
echo "ZWC_ADMPORTAL=$ADMPORTALPATH"
echo "export PATH=\$ZWC_ADMPORTAL/tools:\$PATH"
echo "export PATH=$COMPOSER_PATH/zend/zendframework/bin:\$PATH"
echo " "
echo "----------------------------------------------------------------------------------"
echo "Apache aliases"
echo "----------------------------------------------------------------------------------"
echo "considere los siguientes alias de apache"
echo "Una buena idea es generar agregar la línea
	Include alias/*.conf 
	al final de su archivo /etc/apache2/apache2.conf o /etc/httpd/httpd.conf"
echo " "
echo "Crear archivo /etc/apache2/alias/dojotoolkit.conf o /etc/httpd/alias/dojotoolkit.conf con el contenido:"
echo " "
echo " "
echo "Alias /dojotoolkit \"$ADMPORTALPATH/bower_components\"
<Directory \"$ADMPORTALPATH/bower_components\">
	Options Indexes FollowSymLinks
	AllowOverride None
	Require all granted #Apache2.4 (eliminar en apache 2.2)
   	#Order allow,deny #Apache 2.2 (descomentar en apache 2.2)
   	#Allow from all #Apache 2.2 (descomentar en apache 2.2)
</Directory>
"
echo " "
echo "Crear archivo /etc/apache2/alias/libs.conf o /etc/httpd/alias/libs.conf con el contenido:"
echo " "
echo "Alias /libs \"$ADMPORTALPATH/public/js/libs\"
<Directory \"$ADMPORTALPATH/public/js/libs\">
	Options Indexes FollowSymLinks
	AllowOverride None
	Require all granted #Apache2.4 (eliminar en apache 2.2)
   	#Order allow,deny #Apache 2.2 (descomentar en apache 2.2)
   	#Allow from all #Apache 2.2 (descomentar en apache 2.2)
</Directory>
"
echo "Recuerde habilitar Mod Rewrite"
echo " "
echo "-----------------------------------------------------------------------------------"
echo "php.ini"
echo "-----------------------------------------------------------------------------------"
echo "agregar a la variable \"include_path\" las rutas" 
echo ":$ADMPORTALPATH/vendor/zend/zendframework/library:$ADMPORTAL/application/controllers:$ADMPORTALPATH/vendor/phpunit/phpunit"
echo " "

