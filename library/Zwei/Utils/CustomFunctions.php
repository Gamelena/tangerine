<?php 
class Zwei_Utils_CustomFunctions extends Zwei_Utils_CustomFunctionsBase
{
    //[TODO] personalizar este código, es solo una base de ejemplo para modificar. 
    protected $_names = array(
        'enviarReporte' => 'Enviar Reporte'
    );
    
    protected $_icons = array(
        'enviarReporte' => 'dijitIconMail'
    );
    
    public function enviarReporte()
    {
        echo "<script  type='text/javascript'>
                window.parent.location.href = '".BASE_URL."/yo-puedo-ser-cualquier-cosa';
                </script>";
    }
    
}
