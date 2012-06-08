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
	 * @var array
	 */
	protected $_icons;
	
	/**
	 * Id Capturada de la fila seleccionada de la grilla al llamar a la función 
	 * @var mixed
	 */
	protected $_id;
	
    /**
     * URL de busqueda original
     * @var string
     */
	
	protected $_uri;
	
	
	 /**
     * Parámetros de búsqueda
     * @var array
     */
    
    protected $_query_params;
	
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
		if (!Zend_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
    	
    	$this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
        $this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
        $this->_form = new Zwei_Utils_Form();
        if (!empty($_REQUEST['uri'])) {
            parse_str(urldecode($_REQUEST['uri']), $aParams);
            $subForm = new Zwei_Utils_Form($aParams);
            $this->initQueryParams($subForm);
        }		
	}
	
	public function setId($value)
	{
		$this->_id=$value;
	}
	
	public function getName($index){
		return $this->_names[$index];
	}
	
	public function getIcon($index)
	{
		return !empty($this->_icons[$index]) ? $this->_icons[$index] : "dijitIconFunction";
	}
	
   /**
     * Inicializa $this->_query_params con un array asociativo de parámetros de busqueda
     * @return array
     */
    public function initQueryParams($form)
    {
        Debug::write($form);
        if (!empty($form->search) || $form->search === '0') {
            $searchFields = explode(";", $form->search_fields);
            $search = explode(';', $form->search);
            $betweened = false;
            
            if (isset($form->between) && (!empty($form->between) || $form->between === '0')) $between = explode(';', $form->between);
            $i = 0;    
            foreach ($search as $v) {
                if (!empty($searchFields[$i])) {
                    if (!in_array($searchFields[$i], $between)) {
                        if ($betweened) {
                           if ($search[$i+1] != '') $this->_query_params[$searchFields[$i]] = $search[$i+1];
                        } else {
                           if ($search[$i] != '')  $this->_query_params[$searchFields[$i]] = $search[$i];
                        }    
                    } else {
                        $betweened = true;
                        $this->_query_params[$searchFields[$i]] = array();
                        if ($search[$i] != '') $this->_query_params[$searchFields[$i]][] = $search[$i];
                        if ($search[$i+1] != '') $this->_query_params[$searchFields[$i]][] = $search[$i+1];                            
                    }
                    $i++;
                }    
            }
        }
        Debug::write($this->_query_params);
        return $this->_query_params;
    }
	
	
	
	/**
	 * A continuación se ponen ejemplos como apoyo, no son son usados de ninguna otra forma 
	 * sólo son de referencia para construir la clase Zwei_Utils_CustomFunctions en cada proyecto   
	 * 
	 */
	
	
	
	/**
	 * Ejemplo Clonar Promoción de Bonos Consumo 
	 * @example
	 * <code><component {...} functions="clonar_promocion_examples" functions_permissions="add" ...</code>
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
	 * Ejemplo Procesar Archivo abonados de Bonos Consumo 
     * @example
     * <code><componenent {...} functions="procesar_archivo_abonado_example" functions_permissions="add" ...</code>
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