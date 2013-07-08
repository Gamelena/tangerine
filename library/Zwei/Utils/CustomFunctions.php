<?php 
/**
 * Funciones auxiliares para ser invocadas por los componentes XML.
 * 
 * @category Zwei
 * @package Zwei_Utils
 * @version $Id:$
 * @since 0.1
 * 
 *
 * @example: <section type="table_dojo" functions="assign_request,enviar_reporte" (...)> para $CustomFunctions->assignRequest(...) y $CustomFunctions->enviarReporte(...)
 */

class Zwei_Utils_CustomFunctions extends Zwei_Utils_CustomFunctionsBase
{
    //[TODO] personalizar este código, es solo una base de ejemplo para modificar. 
    public function enviarReporte()
    {
        Debug::write($_REQUEST);
        echo "<script  type='text/javascript'>
                window.parent.location.href = '".BASE_URL."/yo-puedo-ser-cualquier-cosa';
                </script>";
    }
}
