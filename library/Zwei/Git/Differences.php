<?php

class Zwei_Git_Differences{

	var $json1;
	var $json2;

	var $salidaJson;

	
	function __construct($json1, $json2){
		$this->json1 = $json1;
		$this->json2 = $json2;
	}

	function getJsonUno(){
		return $this->json1;
	}

	function getJsonDos(){
		return $this->json2;
	}




	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Funcion: getRaiz
	//Entrada:
		//$path:          Path de entrada que indica la ruta donde se ha realizado la accion
	//Salida:
		//$arrayPath[0]:  Posicion del array que indica el nodo raiz, para este caso particular retornará (nodes o edges).
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	function getRaiz($path){

		//Genera Path Completo
		$arrayPath = explode(".",$path);

		return $arrayPath[0];
	}




	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Funcion: getKeyNumerica
	//Entrada:
		//$path:          Path de entrada que indica la ruta donde se ha realizado la acción
	//Salida:
		//$arrayPath[1]:  Retorna la key numérica que se encuentra en la Posición del array que se indica.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	function getKeyNumerica($path){

		//Genera Path Completo
		$arrayPath = explode(".",$path);

		return $arrayPath[1];
	}




	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Funcion: setPathCompleto
	//Entrada:
		//$arrayPath:     Array que va almacenando el Path recursivo.
	//Salida:
		//$pathCompleto:  String con el Path Completo obtenido a partir del Array Recursivo.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	function setPathCompleto($arrayPath){

		//Genera Path Completo
		$pathCompleto = "";
		for($j=0; $j<count($arrayPath); $j++){
			if($j==0){
				$pathCompleto = $arrayPath[$j];
			}else{
				$pathCompleto = $pathCompleto.".".$arrayPath[$j];
			}
		}
	
		//Fin Retorna Path Completo
		return $pathCompleto;
	}




	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Funcion: getDiferenciasEntreArrayFinal
	//Entrada:
		//$aArray1: Array de entrada 1
		//$aArray2: Array de entrada 2
	//Salida:
		//$arraySalida: Array de salida indicando si fue modificado, agregado, eliminado
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	function getDiferenciasEntreArrayFinal($aArray1, $aArray2)
	{
	  //echo "<br>----------------------------------------------";

	  static $pathActual = "";
	  static $arrayPath = array();
	  static $posicion = 0;

	  //array de retorno de funcion
	  static $arraySalida = array();
	
	  //Almacena las Claves (key), correspondientes al array que se esta recorriendo.
	  static $idGuardado ="";
	  static $idGuardadoClave2 ="";

	  //Recorrido de Array 1
	  foreach ($aArray1 as $mKey => $mValue)
	  {
		    //si existe la key ($mKey) del array 1, en el array 2
		    if (array_key_exists($mKey, $aArray2))
		    {

			      if (is_array($mValue)) //si el valor es array
			      {
						$arrayPath[$posicion] = $mKey;
						++$posicion;

						//Llamada recursiva
						$aRecursiveDiff = self::getDiferenciasEntreArrayFinal($mValue, $aArray2[$mKey]);
					
						if (count($aRecursiveDiff)) 
						{
							$aReturn[$mKey] = $aRecursiveDiff;
						
							//elimina ultima posicion del array
							unset($arrayPath[$posicion]);
							--$posicion;
						}
			      }//fin if
			      else              //si el valor NO es array 
			      {					
						if ($mValue != $aArray2[$mKey])//Si hay diferencias (modificado), entre ambos arrays
						{
							//Retorna Path Completo
							$arrayPath[$posicion] = $mKey;	
							++$posicion;

							//Setea el Path Completo 
							$pathCompleto = self::setPathCompleto($arrayPath);
							//Elimina ultima posicion del array
							--$posicion;
							unset($arrayPath[$posicion]);

							//Genera array de salida
							$posicionArraySalida = count($arraySalida);
							$arraySalida[$posicionArraySalida]["accion"] = "modificado";
							$arraySalida[$posicionArraySalida]["path"] = $pathCompleto;
							$arraySalida[$posicionArraySalida]["antiguo"] = $mValue;
							$arraySalida[$posicionArraySalida]["nuevo"] = $aArray2[$mKey];
						}
			      }//fin else
		    }//fin if 
		    else	//si NO existe la key ($mKey) del array 1, en el array 2
		    {

				$arrayPath[$posicion] = $mKey;
				++$posicion;

				//Setea el Path Completo
				$pathCompleto = self::setPathCompleto($arrayPath);
			
				//Elimina ultima posicion del array
				--$posicion;
				unset($arrayPath[$posicion]);

				$pathTemporal = $pathCompleto;
			
				//Genera array de salida
				$posicionArraySalida = count($arraySalida);

				$arraySalida[$posicionArraySalida]["accion"] = "eliminado";
				$arraySalida[$posicionArraySalida]["path"] = $pathTemporal;//$pathCompleto;
				$arraySalida[$posicionArraySalida]["antiguo"] = $mValue;
		
		    }//fin else

	  }//fin foreach ($aArray1 as $mKey => $mValue)


	  //Recorre Array2
	  foreach ($aArray2 as $clave2 => $valor2)
	  {		    
		    if (array_key_exists($clave2, $aArray1))
		    {
				//echo "<br> ARRAY 2 - EXISTE ";				
		    }//fin if
		    else //No Existe la clave2 del Array 2 en Array 1
		    {
				//Setea el Path Completo
				$pathCompleto = self::setPathCompleto($arrayPath);
				$pathTemporal = $pathCompleto.".".$clave2;

				//Genera array de salida
				$posicionArraySalida = count($arraySalida);
				$arraySalida[$posicionArraySalida]["accion"] = "agregado";
				$arraySalida[$posicionArraySalida]["path"]   = $pathTemporal;    //$pathCompleto;
				$arraySalida[$posicionArraySalida]["nuevo"]  = $aArray2[$clave2];
		    }//fin else

	  }//fin foreach array2

	  return $arraySalida;

	}//fin function arrayRecursiveDiff($aArray1, $aArray2)




	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Funcion: setFormatoArrayFinal
	//Entrada:
		//$arrayDiferencias:       Array de entrada que posee la información ordenada de acuerdo a indice. Ejemplo:  $arrayDiferencias[$i]
				  	   //$arrayDiferencias[$i][accion] -> Corresponde a la accion realizada (modificado, agregado, eliminado)
				  	   //$arrayDiferencias[$i][path]   -> Corresponde a la ruta donde se realizo la acción
					   //$arrayDiferencias[$i][antiguo]-> Indica el valor antiguo (para el caso de modificado o eliminado)
					   //$arrayDiferencias[$i][nuevo]  -> Indica el valor nuevo (para el caso de agregado)
					   //$i -> Corresponde al valor que se va incrementando.
	//Salida:
		//$arrayDiferenciasFinal:  Array de salida indexado por raiz (nodes, edges), luego por key numérica y finalmente por índices del array ($i)
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	function setFormatoArrayFinal($arrayDiferencias){

		$posicionNodes = 0;
		$posicionEdges = 0;
		$keySecundariaActual = "";

		$arrayDiferenciasFinal = array();

		foreach ($arrayDiferencias as $clave => $valor){

			$keyPrincipal = self::getRaiz($arrayDiferencias[$clave]["path"]);			

			$keySecundaria = self::getKeyNumerica($arrayDiferencias[$clave]["path"]);

			if($keyPrincipal == "nodes"){

				if($keySecundariaActual != $keySecundaria){
					$posicionNodes = 0;

					$arrayDiferenciasFinal[$keyPrincipal][$keySecundaria][$posicionNodes] = $valor;
					++$posicionNodes;

				}else if($keySecundariaActual == $keySecundaria){

					$arrayDiferenciasFinal[$keyPrincipal][$keySecundaria][$posicionNodes] = $valor;
					++$posicionNodes;
				}			
				$keySecundariaActual = $keySecundaria;

			}else if($keyPrincipal == "edges"){			

				if($keySecundariaActual != $keySecundaria){
					$posicionNodes = 0;
					
					$arrayDiferenciasFinal[$keyPrincipal][$keySecundaria][$posicionNodes] = $valor;
					++$posicionNodes;

				}else if($keySecundariaActual == $keySecundaria){

					$arrayDiferenciasFinal[$keyPrincipal][$keySecundaria][$posicionNodes] = $valor;
					++$posicionNodes;
				}			
				$keySecundariaActual = $keySecundaria;

			}//fin else
		
		}//fin foreach

		return $arrayDiferenciasFinal;
	}




	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Funcion: setJsonEncode
	//Entrada:
		//$arrayDiferenciasFinal:     Parametro que tiene formato de Array con las Diferencias formteadas en 2 arhivos jSon.
	//Salida:
		//$jSonFinal:  		      Retorna jSon, con estructuta jSon para ser procesada posteriormente.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	function setJsonEncode($arrayDiferenciasFinal){

		$jSonFinal = json_encode($arrayDiferenciasFinal, true);

		echo $jSonFinal;

		//Fin Retorna json con formato Json
		return $jSonFinal;
	}

}//fin clase
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/*
	$objeto = new Zwei_Git_Differences("json1_version3.json", "json2_version3.json");  
	//Get Nombre de jSon1 y jSon2
	$nombreJson1 = $objeto->getJsonUno(); //funciona
	$nombreJson2 = $objeto->getJsonDos(); //funciona
	
	echo "<br> El nombre del JSON 1 es:".$nombreJson1;
	echo "<br> El nombre del JSON 2 es:".$nombreJson2;	
	echo "<br><br><br><br>";
	
	//Procesa JSON 1
	//echo "<br><br><br>Procesando JSON 1<br>";
	//$data  = file_get_contents($nombreJson1);
	$data  = file_get_contents("/home/ddiaz/ZWEICOM/admportal/application/modules/components/controllers/".$nombreJson1);	
	$arrayJson1 = json_decode($data, true);
	echo "<br>";
	print_r($arrayJson1);
	echo "<br><br><br><br>";	
	
	//Procesa JSON 2
	//echo "<br><br><br>Procesando JSON 2<br>";
	//$data2  = file_get_contents($nombreJson2);
	$data2  = file_get_contents("/home/ddiaz/ZWEICOM/admportal/application/modules/components/controllers/".$nombreJson2);
	$arrayJson2 = json_decode($data2, true);
	echo "<br>";
	print_r($arrayJson2);
	echo "<br><br><br><br>";	
	
	//Genera Array de Diferencias
	$arrayDiferencias = $objeto->getDiferenciasEntreArrayFinal($arrayJson1, $arrayJson2);
	echo "<br><br><br>Imprime Estructura con las Diferencias Encontradas:<br>";
	print_r($arrayDiferencias);	echo "<br><br><br><br>";
	
	//invoca funcion que setea el array de acuerdo a la salida requerida (Agregando la raiz y key numerica)
	echo "<br><br><br>Imprime estructura con las Diferencias Formateadas:<br>";
	$arrayDiferenciasFinal = $objeto->setFormatoArrayFinal($arrayDiferencias);
	print_r($arrayDiferenciasFinal);    echo "<br><br><br><br>";
	*/	
	
	/*//echo "<br><br><br>Genera JSON a partir de Array de Diferencias Final:<br>";
	$jSonFinal = json_encode($arrayDiferenciasFinal, true);*/
?>
