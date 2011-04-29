<?php
/*
$dsn_root_localhost
$dsn_root_localhost
$dsn_root_localhost
$dsn_ussd_dan
$dsn_ussd_stats_dan
*/

error_reporting(E_ERROR);

# Definicion DSN
# Para uso de la clase de AUTENTICACION 
# localhost - USSD_STATS
$dsn_auth = array("dsn" => "mysql://ussd:72zw04@127.0.0.1:3309/USSD_STATS",
                  "table" => "usuario",
                  "usernamecol" => "USER_LOGIN",
                  "usergroup"   => "GRUPO_ID",
                  "passwordcol" => "USER_PASSWD");
#-----------------------------------------------------------------------------------

#EPCS=0, Movistar Chile=1, Movistar Peru=2
$cliente=1;

# Definicion DSN
# Acceso a la base de datos para las clases

# DAN - USSD_STATS
$dsn_ussd_stats_dan = 	array(	'phptype'  => 'mysql',
								'username' => 'ussd',
								'password' => '72zw04',
								'hostspec' => '127.0.0.1',
								'port'     => 3309,
								'database' => 'USSD_STATS');


$dsn_rrvv = 	array(	'phptype'  => 'mysql',
								'username' => 'rrvv_user',
								'password' => 'rrvv_pass',
								'hostspec' => '127.0.0.1',
								'port'     => 3309,
								'database' => 'RRVV');

$dsn_localiza =	array(	'phptype'  => 'mysql',
								'username' => 'loc_user',
								'password' => 'loc_pass',
								'hostspec' => '127.0.0.1',
								'port'     => 3309,
								'database' => 'LOCALIZA');


#-----------------------------------------------------------------------------------

$URL_QUERY = "http://localhost/ws/rrvv.php";
$TIMEOUT_QUERY = 15;
$MAX_REG_CLIENTES	= 100;
$MAX_REG_PTS_VENTA	= 100;


$logo_operadora="logo_movistar.jpg";


#-----------------------------------------------------------------------------------
# Colores
$titleColor = "#d8ecff";
$blankColor = "#ffffff";
$grayColor = "#EEEEEE";
#-----------------------------------------------------------------------------------

#descomentar esta linea para usar tonos rojos
#$red='_red';
$red='';
#-----------------------------------------------------------------------------------
# Imagenes
$_img_delete = "../images/deleted.gif";
$_img_flecha = "../images/flechita.gif";
$_img_exito = "../images/yes.gif"; #respuestas
$_img_logica = "../images/txt.gif"; #lista maquinas
$_img_pass = "../images/iconkey.gif"; #password
$_img_engranage ="../images/applications-system{$red}.png";
$_img_flecha_split ="../images/arrow-split{$red}.png";
#-----------------------------------------------------------------------------------


// diametro: diametro de las pelotitas
// radio   : separaciones entre las pelotitas
// factor  : separaciones entre las pelotitas de diferente banda e igual posicion
$nivelesdezoom = array(                                                      // niveles de zoom
                       array('zoom' => 1, 'nivel'=>array( 0,10), 'flaglabels' => 1, 'diametro' =>  3,'diametroE' =>  9, 'radio' =>0.0020, 'factor' =>0.0020),
                       array('zoom' => 2, 'nivel'=>array(11,13), 'flaglabels' => 1, 'diametro' =>  5,'diametroE' => 11, 'radio' =>0.0012, 'factor' =>0.0012),
                       array('zoom' => 3, 'nivel'=>array(14,16), 'flaglabels' => 1, 'diametro' =>  9,'diametroE' => 13, 'radio' =>0.0005, 'factor' =>0.0005),
                       array('zoom' => 4, 'nivel'=>array(17,21), 'flaglabels' => 1, 'diametro' => 17,'diametroE' => 17, 'radio' =>0.0002, 'factor' =>0.0002)
                      );

?>
