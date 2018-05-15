if [ "$(id -u)" != "0" ]; then
	echo "This script must be run as root or sudo" 1>&2
	exit 1
else
	cd ../..
	SITENAME=`echo "${PWD##*/}"`
	cd - 
	ROOT_DIR="../"
	SITEPATH=`echo $(dirname $(readlink -e $PWD))`
	echo "Creando alias basados en $SITEPATH"

	APACHE_EXEC=$(which apache2)
	if [ ! -x "$APACHE_EXEC" ]; then
		APACHE_EXEC=$(which httpd)
		if [ ! -x "$APACHE_EXEC" ]; then
			echo "No se encontró una instalación de Apache, abortando."
			exit 1;
		else
			APACHE_SERVICE="httpd"
			APACHE_PATH="/etc/httpd"
			APACHE_CONF="/etc/httpd/conf/httpd.conf"
			APACHE_USER="apache"
		fi
	else
		APACHE_SERVICE="apache2"
		APACHE_PATH="/etc/apache2"
		APACHE_CONF="/etc/apache2/apache2.conf"
		APACHE_USER="www-data"
	fi
	
	mkdir -p $APACHE_PATH/vhosts

	if grep -i  "include vhosts\/\*\.conf" $APACHE_CONF; then
		echo "Se encontró la directiva include vhosts/* en $APACHE_CONF, no se hace nada."
	else
		DATE=$(date +"%Y-%m-%d-%H-%M-%S")
		echo "Creando backup de $APACHE_CONF en $APACHE_CONF $APACHE_CONF.$DATE"
		cp $APACHE_CONF $APACHE_CONF.$DATE

		echo "Se intentará agregar la directiva Include vhosts/*.conf en $APACHE_CONF"
		echo "# Generado por Tangerine" >> $APACHE_CONF
		echo "Include vhosts/*.conf\n" >> $APACHE_CONF
	fi

	APACHE_VERSION=`$APACHE_SERVICE -v | grep -o '2\.[0-9]*'` 
	APACHE_VERSION=`echo $APACHE_VERSION | sed 's/\.//g'`

	if [ $APACHE_VERSION -lt "24" ]; then
		grant="Order allow,deny\nAllow from all"
	else
		grant="Require all granted"
	fi
	
	
	VHOST_FILE=$APACHE_PATH/vhosts/$SITENAME.conf

	if [ -f "$VHOST_FILE" ]; then
		echo "Ya existe '$VHOST_FILE', no se hace nada."
	else
		if [ -z "$APPLICATION_ENV" ];
			then
			export APPLICATION_ENV="development"
			echo "No esta definida la variable ( APPLICATION_ENV ), usando APPLICATION_ENV $APPLICATION_ENV para generar el alias."
			echo "Puede cambiar esto modificando $VHOST_FILE."
		fi

		echo "Creando archivo $VHOST_FILE"
		printf "<VirtualHost *:80>
	DocumentRoot \"$SITEPATH/public\"
	ServerName $SITENAME.local
	<Directory \"$SITEPATH/public\">
		Options Indexes FollowSymLinks
		AllowOverride All
		$grant
		DirectoryIndex index.html index.php    
		SetEnv APPLICATION_ENV $APPLICATION_ENV
	</Directory>
</VirtualHost>" > $VHOST_FILE
	fi
	
	echo "Otorgando permisos a $APACHE_USER"
	chown $APACHE_USER -R $SITEPATH/log $SITEPATH/cache $SITEPATH/public/upfiles
	
	printf "\nTerminado\nPara ver los cambios reinicie apache\nservice $APACHE_SERVICE restart\n"
	printf "\nRecuerde agregar $SITENAME.local a su archivo /etc/hosts o equivalente\n"
fi
