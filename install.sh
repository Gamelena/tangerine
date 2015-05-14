sudo composer install && sudo composer update && bower install && cp -R dojotoolkit/* bower_components/

echo "Dependencias instaladas"
echo " "
echo "----------------------------------------------------------------------------------"
echo "Variables de Ambiente"
echo "----------------------------------------------------------------------------------"
echo "Recuerde agregar estas variable de ambiente a su archivo ~/.bashrc (o equivalente)"
echo "ZWC_ADMPORTAL=$PWD"
echo "export PATH=\$ZWC_ADMPORTAL/tools:\$PATH"
echo "export PATH=/usr/share/php/zend/zendframework/bin:\$PATH"
echo " "
echo "----------------------------------------------------------------------------------"
echo "Apache aliases"
echo "----------------------------------------------------------------------------------"
echo "considere los siguientes alias de apache"
echo "Una buena idea es generar agregar 
	Include alias/*.conf a su archivo apache.conf o http.conf"
echo " "
echo "dojotoolkit.conf"
echo "Alias /dojotoolkit \"$PWD/bower_components\"
<Directory \"$PWD/bower_components\">
	Options Indexes FollowSymLinks
	AllowOverride None
	Allow from all
</Directory>
"
echo " "
echo "libs.conf"
echo "Alias /libs \"$PWD/public/js/libs\"
<Directory \"$PWD/public/js/libs\">
	Options Indexes FollowSymLinks
	AllowOverride None
	Allow from all
</Directory>
"
echo "Recuerde habilitar Mod Rewrite"
echo " "
echo "-----------------------------------------------------------------------------------"
echo "php.ini"
echo "-----------------------------------------------------------------------------------"
echo "agregar a la variable \"include_path\" las rutas" 
echo ":/usr/share/php/zend/zendframework/library:/usr/share/php/phpunit/phpunit/PHPUnit"
echo " "


