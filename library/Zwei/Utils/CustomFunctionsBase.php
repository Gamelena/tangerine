<?php
/**
 * Funciones para ser llamadas vía componentes XML (no obligatorio).
 * [IMPORTANTE] para añadir funciones adicionales esta clase debe ser heradada por una clase llamada
 * Zwei_Utils_CustomFunctions() para cada proyecto y escribir las funciones pertinentes
 * 
 * Pueden ser invocadas mediante el atributo functions de los components xml del admin.
 * 
 * @category Zwei
 * @package  Zwei_Utils
 * @version  $Id:$
 * @since    0.1
 * 
 * @example: 
 * <helpers>
 *    <customFunction target="enviarReporte" {...} />
 * </helpers>
 */

class Zwei_Utils_CustomFunctionsBase
{
    /**
     * Id Capturada de la fila seleccionada de la grilla al llamar a la función .
     * @var mixed
     */
    protected $_id;
    
    /**
     * URL de busqueda original.
     * @var string
     */
    
    protected $_uri;
    
    
     /**
     * Parámetros de búsqueda.
     * @var array
     */
    
    protected $_query_params;
    
    /**
     * Permisos de usuario en sesión.
     * @var Zwei_Admin_Acl
     */
    
    protected $_acl;
    
    /**
     * Datos de sesión de usuario.
     * @var Zend_Auth_Storage
     */
    protected $_user_info;
    
    
    /**
     * Objeto $_REQUEST.
     * @var Zwei_Utils_Form
     */
    protected $_form;
    
    
    public function __construct()
    {
        if (Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
            $this->_acl = new Zwei_Admin_Acl($this->_user_info->user_name);
        }
        $this->_form = new Zwei_Utils_Form();
    }
    
    public function setId($value)
    {
        $this->_id = $value;
    }
}