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
        echo "<script  type='text/javascript'>
               if (window.parent.dijit.byId('loadFileListaBlanca') == undefined) {
                   var myDialog = new window.parent.dijit.Dialog({
                        id: 'loadFileListaBlanca',
                        title: 'Cargar Archivo',
                        href: window.parent.base_url + '/uploads/lista-blanca-form'
                   });
               } else {
                   window.parent.dijit.byId('loadFileListaBlanca').set('href', window.parent.base_url + '/uploads/lista-blanca-form');
               }
               window.parent.dijit.byId('loadFileListaBlanca').show();
          </script>";
    }


    
}

