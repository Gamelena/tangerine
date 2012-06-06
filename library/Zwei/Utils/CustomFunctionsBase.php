<?php
/**
 * Funciones para ser llamadas vía componentes XML (no obligatorio).
 * [IMPORTANTE] para añadir funciones adicionales esta clase debe ser heradada por una clase llamada
 * Zwei_Utils_CustomFunctions() para cada proyecto y escribir las funciones pertinentes
 * 
 * Pueden ser invocadas mediante el atributo functions de los components xml del admin.
 * 
 * @package Zwei_Utils
 * @version $Id:$
 * @since 0.1
 * 
 * @example: <section type="table_dojo" functions="assign_request,excel_export" (...)> para $CustomFunctions->assignRequest(...) y $CustomFunctions->excelExport(...)
 *
 */

class Zwei_Utils_CustomFunctionsBase
{
	/**
	 * Icono Dijit para pintar boton [TODO] diseñar implementar configuración de esto en XMl 
	 * @var string
	 */
	protected $_icon;
	
	/**
	 * Id Capturada de la fila seleccionada de la grilla al llamar a la función 
	 * @var mixed
	 */
	protected $_id;
	
    
	/**
	 * Variable para control de permisos
	 * @var Zwei_Admin_Acl
	 */
	
	protected $_acl;
	
	/**
	 * 
	 * @var unknown_type
	 */
	protected $_user_info;
	
	
	/**
	 * Array asociativo entre el nombre del método y la descripción a mostrar, útil para pintar botones
	 * @var array
	 */
	
	protected $_names=array(
		'clonarPromocion'=>'Clonar Promocion',
		'procesarArchivoAbonados'=>'Procesar Archivo'
	);

	/**
	 * 
	 * @var Zwei_Utils_Form
	 */
	protected $_form;
	
	
	public function __construct($icon=false)
	{
		if(!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
    	
    	$this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
        $this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
        $this->_form = new Zwei_Utils_Form();		
		
		$this->_icon = $icon ? $icon : "dijitIconFunction";
	}
	
	public function setId($value)
	{
		$this->_id=$value;
	}
	
	public function getName($index){
		return $this->_names[$index];
	}
	
	public function getIcon()
	{
		return $this->_icon;
	}
	
	/**
	 * LO ANTERIOR ES LA BASE REQUERIDA PARA EL FUNCIONAMIENTO BÁSICO
	 * A CONTINUACIÓN SE MOSTRARÁN EJEMPLOS ESPECÍFICOS A MODO DE TUTORIAL, PUEDEN SER BORRADOS
	 * PARA CADA PROYECTO
	 */
	
	
	
	/**
	 * Clonar Promoción de Bonos Consumo //EJEMPLO
	 */
	public function clonarPromocionExample()
	{
		echo "
		<script type=\"text/javascript\">
			window.parent.cargarTabsPanelCentral('promociones', 'clone', 'id_promo');
		</script>
		";
	}
	
	/**
	 * Procesar Archivo abonados de Bonos Consumo //EJEMPLO
	 */
	
	public function procesarArchivoAbonadosExample($target)
	{
		chmod($target, 0777);
		$new_path = "/home/promociones/files/processed";	 
		$Abonados = new AbonadosModel();
			 
		$db = $Abonados->getAdapter();

		$db->query("DELETE FROM abonados_tmp");
		$query = "LOAD DATA LOCAL INFILE '$target' IGNORE INTO TABLE abonados_tmp ( `msisdn` );";
		Zwei_Utils_Debug::writeBySettings($query, 'query_log'); 
	
		if ($db->query($query)) {
		 	try{
			 	$query="INSERT IGNORE INTO abonados (msisdn, id_promocion) SELECT msisdn, $this->_id FROM abonados_tmp";
			 	Zwei_Utils_Debug::writeBySettings($query, 'query_log');
			 	if ($response=$db->query($query)) {
			 		$inserted_rows=$response->rowCount();
			 		
			 		$where = array();
					$where[] = $db->quoteInto('msisdn = ?', 0);
					$where[] = $db->quoteInto('id_promocion = ?', $this->_id);
					$Abonados->delete($where);
							 		
		 			$filename = substr($target, strrpos($target,'/')+1,strlen($target)-strrpos($target,'/'));
		 			$new_target=$new_path."/".$filename;
   					rename($target,$new_target);
   					$row_file="row_".str_replace('.','', $filename);
		 			echo "<script>alert('Archivo procesado correctamente, $inserted_rows registros insertados.');
		 			window.parent.get_url_contents('ajax/abonados-count?id=".$this->_id."','ajax_box');
		 			window.parent.document.getElementById('$row_file').style.display='none';
		 			window.parent.document.getElementById('ajax_loading_bar').style.background='#FFF';
		 			</script>";	
			 	} else {
					echo "<script>alert('Error en la carga de abonados.')</script>";				 		
			 		
			 	}
		 	} catch(Zend_Db_Exception $e) {
		 		echo "<script>alert('Error en la carga de abonados.')</script>";	
		 		Zwei_Utils_Debug::write($e->getMessage().$e->getCode());
		 	} 		
	
		} else {
			echo "<script>alert('Error al procesar archivo.')</script>";		 		
		}	
	}	
}