#!/bin/bash

if [ -z "$COMPOSER_PATH" ]; then
	echo "No esta definida la ruta de repositorio composer ( COMPOSER_PATH )"
	exit 255;
else
	mkdir -p $COMPOSER_PATH
fi

COMPOSER_EXEC=$(which composer)
if [ -x "$COMPOSER_EXEC" ] ; then
	if [ ! -f $COMPOSER_PATH/composer.phar ];
	then
		cd $COMPOSER_PATH
		curl -sS https://getcomposer.org/installer | php --
		if [ ! -f $COMPOSER_PATH/composer.phar ];
		then
			echo "No se pudo obtener composer!!!!!!"
			exit 255;
		fi;
		mv $COMPOSER_PATH/composer.phar $COMPOSER_PATH/composer.sh
		COMPOSER_EXEC=$COMPOSER_PATH/composer.sh
	fi
fi

ADMPORTALPATH=$COMPOSER_PATH/zweicom/admportal
if [ ! -d $ADMPORTALPATH ];
then
	svn co svn://saruman/admportal/trunk $ADMPORTALPATH
else
	svn update $ADMPORTALPATH
fi

if [ ! -f $COMPOSER_PATH/bower/bin/bower ];
then
	mkdir -p $COMPOSER_PATH/bower
	cd $COMPOSER_PATH/bower
	npm install bower
	if [ ! -f node_modules/bower/bin/bower ];
	then
		echo "No se pudo instalar bower!!!!!!"
		exit 255;
	fi;
	mv node_modules tmp
	mv  tmp/bower/* .
	rm -Rf tmp
fi

echo $ADMPORTALPATH;
cd $ADMPORTALPATH 

VENDOR_DIR="$( echo "$COMPOSER_PATH" | sed -e 's/\//\\\//g')"
SALIDA="$(sed -i -e "s/\${COMPOSER_PATH}/$VENDOR_DIR/" composer.json)"

$COMPOSER_EXEC install $1
$COMPOSER_EXEC update $1
$COMPOSER_PATH/bower/bin/bower install --allow-root
cp -R dojotoolkit/* bower_components/ 
cp -R bower_components/dojo-calendar/* bower_components/dojox/calendar/
cp -n $COMPOSER_PATH/zend/zendframework/bin/zf.sh $COMPOSER_PATH/zend/zendframework/bin/zf


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
echo ":$COMPOSER_PATH/zend/zendframework/library:$COMPOSER_PATH/phpunit/phpunit"
echo " "

