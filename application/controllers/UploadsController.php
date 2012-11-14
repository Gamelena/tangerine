<?php

/**
 * Controlador para uploads
 *
 *
 * @package Controllers
 * @version Id:$
 * @since versión 0.5
 */
class UploadsController extends Zend_Controller_Action
{
	public function init()
	{
		if(!Zwei_Admin_Auth::getInstance()->hasIdentity()) $this->_redirect('index/login');
		$this->_helper->layout()->disableLayout();
		$this->view->base_url=BASE_URL;
	}

	/**
	 * Carga de archivo de abonados para Bonos de Consumo
	 */
	public function abonadosPromocionAction()
	{
		$Form=new Zwei_Utils_Form();
		Zwei_Utils_Debug::write($_FILES);
		//$FileHandler = new Zwei_Utils_FileHandler();
		//$result = $FileHandler->saveFilesToUserDir(ROOT_DIR. '/upload/');

		$target = "/tmp/";
		$target = $target . "abonados_promociones_".$Form->id ;

		Zwei_Utils_Debug::write($target);
		if (move_uploaded_file($_FILES['uploadedfiles']['tmp_name'][0], $target)) {
				
			chmod($target, 0777);

			$Abonados = new AbonadosModel();

			$db = $Abonados->getAdapter();
				
			$db->query("DELETE FROM abonados_tmp");
			$query = "LOAD DATA LOCAL INFILE '$target' IGNORE INTO TABLE abonados_tmp ( `msisdn` );";

			Zwei_Utils_Debug::writeBySettings($query, 'query_log');

			if($db->query($query)){
				try{
			 	//if($Abonados->insert(array("msisdn"=>"SELECT msisdn FROM abonados_tmp","id_promocion"=>$Form->id))){
					$query="INSERT IGNORE INTO abonados (msisdn, id_promocion) SELECT msisdn, $Form->id FROM abonados_tmp";
					Zwei_Utils_Debug::writeBySettings($query, 'query_log');
					if($db->query($query)){
						unlink($target);
						echo "<script>alert('Archivo procesado correctamente.')</script>";
					}else{
						echo "<script>alert('Error en la carga de abonados.')</script>";
							
					}
				}catch(Zend_Db_Exception $e){
					echo "<script>alert('Error en la carga de abonados.')</script>";
					Zwei_Utils_Debug::write($e->getMessage().$e->getCode());
				}

			}else{
				echo "<script>alert('Error al procesar archivo.')</script>";
			}
		}else{
			echo "<script>alert('Problemas al cargar archivo.')</script>";
		}
	}

	public function indexAction()
	{

	}
}
