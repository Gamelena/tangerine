composer install && composer update && bower install && cp -R dojotoolkit/* bower_components/ && cp -n $PWD/vendor/zend/zendframework/bin/zf.sh $PWD/vendor/zend/zendframework/bin/zf

echo "Dependencias instaladas"
echo " "
echo "----------------------------------------------------------------------------------"
echo "Variables de Ambiente"
echo "----------------------------------------------------------------------------------"
echo "Recuerde agregar estas variable de ambiente a su archivo ~/.bashrc (o equivalente)"
echo "ZWC_ADMPORTAL=$PWD"
echo "export PATH=\$ZWC_ADMPORTAL/tools:\$PATH"
echo "export PATH=$PWD/vendor/zend/zendframework/bin:\$PATH"
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
echo "Alias /dojotoolkit \"$PWD/bower_components\"
<Directory \"$PWD/bower_components\">
	Options Indexes FollowSymLinks
	AllowOverride None
	Require all granted
	Allow from all
</Directory>
"
echo " "
echo "Crear archivo /etc/apache2/alias/libs.conf o /etc/httpd/alias/libs.conf con el contenido:"
echo " "
echo "Alias /libs \"$PWD/public/js/libs\"
<Directory \"$PWD/public/js/libs\">
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
echo ":$PWD/vendor/zend/zendframework/library:$PWD/vendor/phpunit/phpunit"
echo " "

