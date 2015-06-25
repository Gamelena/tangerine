#!/bin/bash

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
        echo "No esta definida la ruta de instalacion para la web de ussd ( USSDPATH ), tomando por defecto $ADMPORTALPATH"
fi

$COMPOSER_EXEC install $1
$COMPOSER_EXEC update $1
rm -f /tmp/zweicom_admporta*
$COMPOSER_EXEC archive --dir /tmp
mkdir -p $ADMPORTALPATH
tar xf /tmp/zweicom-admportal* -C $ADMPORTALPATH


echo "Dependencias instaladas"
echo " "
echo "----------------------------------------------------------------------------------"
echo "Variables de Ambiente"
echo "----------------------------------------------------------------------------------"
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
echo " "
echo "Crear archivo /etc/apache2/alias/libs.conf o /etc/httpd/alias/libs.conf con el contenido:"
echo " "
echo "Alias /libs \"$ADMPORTALPATH/public/js/libs\"
<Directory \"$ADMPORTALPATH/public/js/libs\">
	Options Indexes FollowSymLinks
	AllowOverride None
	Require all granted
	Allow from all
</Directory>
"
echo "Recuerde habilitar Mod Rewrite"
echo " "
echo "-----------------------------------------------------------------------------------"
echo "php.ini"
echo "-----------------------------------------------------------------------------------"
echo "agregar a la variable \"include_path\" las rutas" 
echo ":$ADMPORTALPATH/zend/zendframework/library:$ADMPORTALPATH/phpunit/phpunit"
echo " "

