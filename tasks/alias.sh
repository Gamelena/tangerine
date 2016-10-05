if [ "$(id -u)" != "0" ]; then
	echo "This script must be run as root or sudo" 1>&2
	exit 1
else 
	ROOT_DIR="../"
	unamestr=`uname`

	if [[ "$unamestr" == 'Darwin' ]]; then
		echo "Se instentara usar greadlink, de no estar disponible debe instalar el paquete coreutils"
    	TANGERINEPATH=`echo $(dirname $(greadlink -f $PWD))`
	else
		TANGERINEPATH=`echo $(dirname $(readlink -e $PWD))`
	fi

	APACHE_EXEC=$(which apache2)

	if [ ! -x "$APACHE_EXEC" ]; then
		APACHE_EXEC=$(which httpd)
		if [ ! -x "$APACHE_EXEC" ]; then
			echo "No se encontró una instalación de Apache, abortando."
			exit 1;
		else
			APACHE_SERVICE="httpd"
			if [[ "$unamestr" == 'Darwin' ]]; then 
				echo "Detectado OS X"
				APACHE_PATH="/etc/apache2"
				APACHE_CONF="/etc/apache2/httpd.conf"
				APACHE_USER="_www"
			else
				APACHE_PATH="/etc/httpd"
				APACHE_CONF="/etc/httpd/conf/httpd.conf"
				APACHE_USER="apache"
			fi
		fi
	else
		APACHE_SERVICE="apache2"
		APACHE_PATH="/etc/apache2"
		APACHE_CONF="/etc/apache2/apache2.conf"
		APACHE_USER="www-data"
	fi
	
	mkdir -p $APACHE_PATH/alias

	if grep -i  "include alias\/\*\.conf" $APACHE_CONF; then
    		echo "Se encontró la directiva include alias/* en $APACHE_CONF, no se hace nada."
	else
		DATE=$(date +"%Y-%m-%d-%H-%M-%S")
		echo "Creando backup de $APACHE_CONF en $APACHE_CONF $APACHE_CONF.$DATE"
		cp $APACHE_CONF $APACHE_CONF.$DATE

    		echo "Se intentará agregar la directiva Include alias/*.conf en $APACHE_CONF"
		echo "# Generado por AdmPortal" >> $APACHE_CONF
		echo "Include $APACHE_PATH/alias/*.conf\n" >> $APACHE_CONF
	fi

	APACHE_VERSION=`$APACHE_SERVICE -v | grep -o '2\.[0-9]*'` 
	APACHE_VERSION=`echo $APACHE_VERSION | sed 's/\.//g'`

	if [ $APACHE_VERSION -lt "24" ]; then
		grant="Order allow,deny\nAllow from all"
	else
		grant="Require all granted"
	fi
	
	
	ALIAS_FILE=$APACHE_PATH/alias/libs.conf

	if [ -f "$ALIAS_FILE" ]; then
		echo "Ya existe '$ALIAS_FILE', no se hace nada."
	else
		echo "Creando archivo $ALIAS_FILE"
		echo "Alias /libs \"$TANGERINEPATH/public/js/libs\"
<Directory \"$TANGERINEPATH/public/js/libs\">
	Options Indexes FollowSymLinks
	AllowOverride None
	$grant
</Directory>" > $ALIAS_FILE
	fi

	ALIAS_FILE=$APACHE_PATH/alias/dojotoolkit.conf

	if [ -f "$ALIAS_FILE" ]; then
		echo "Ya existe '$ALIAS_FILE', no se hace nada."
	else
		echo "Creando archivo $ALIAS_FILE"
		echo "Alias /dojotoolkit \"$TANGERINEPATH/bower_components\"
<Directory \"$TANGERINEPATH/bower_components\">
	Options Indexes FollowSymLinks
	AllowOverride None
	$grant
</Directory>" > $ALIAS_FILE
	fi
	echo "\nTerminado\nPara ver los cambios reinicie apache\nservice $APACHE_SERVICE restart\n"
fi
