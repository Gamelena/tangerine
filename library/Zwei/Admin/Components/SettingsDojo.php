<?php
/**
 * Componente para mantener variables configuración general mediante dojo tabs
 *
 * Las variables deben ser previamente creadas en tabla,
 * por defecto se usa modelo SettingModel el cual puede ser cambiado en atributo opcional "target".<br/>
 *
 * Ejemplo:
 * <code>
 * <section name="Configuraci&amp;oacute;n" type="settings_dojo" target="settings" target="app_settings" />
 * </code>
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Components_SettingsDojo implements Zwei_Admin_ComponentsInterface
{
    /**
     * Nombre del archivo XML
     * @var string
     */
    public $page;
    /**
     * Nombre del modelo Zend_Db_Table
     * @var string
     */
    private $_model;

    public function __construct($page, $zend_view=null)
    {
        $this->page = $page;
        $xml = new Zwei_Admin_Xml();
         
        $file = Zwei_Admin_Xml::getFullPath($this->page);

        $xml->parse($file);
        $this->_model=isset($xml->elements[0]['TARGET']) ? Zwei_Utils_String::toClassWord($xml->elements[0]['TARGET'])."Model" : "SettingsModel";

        /*
         $zend_view->dojo()
         ->requireModule("dijit.layout.TabContainer")
         $zend_view->headStyle()->appendStyle('
         @import "/dojotoolkit/dojox/grid/resources/Grid.css";

         ');
         */
    }


    public function display()
    {
        $form = new Zwei_Utils_Form();
        $settings = new $this->_model();
        
        $out = "<img id=\"settingsSwitchMainPaneButton\" src=\"http://localhost/promociones/images/expand.png\" onclick=\"switchMainPane()\" style=\"position: relative;float: left;\">";
        $out .= "<script>if(typeof(switchMainPane) != \"function\") { dojo.byId('settingsSwitchMainPaneButton').style.display='none'; }</script>";
        
        $out .= "<h2>Configuraci&oacute;n del Sitio</h2>\r\n";

        if(isset($form->save)){
            foreach ($form->settings_id as $i=>$v){
                $value=isset($form->value[$i])?$form->value[$i]:"";
                $where=$settings->getAdapter()->quoteInto('id = ?', $v);
                $data=array('value'=>$value);
                $settings->update($data, $where);
            }
        }

        if (isset($form->f) && isset($form->v)) {
            echo $form->f;
            if(class_exists($form->f)){
                $f=new $form->f;
                $f->setValue($form->v);
                return $f->run();
            }
        }

        $out .= "<div dojoType=\"dijit.layout.TabContainer\" style=\"width: 100%; height: 100%;\" tabStrip=\"true\">\r\n";

        $groupies = $settings->loadGroups();
        $groups = array();
        $h = 'style="background:#cccccc"';
        $i = 0;
        foreach ($groupies as $group) {
            $g = !empty($group['group']) ? $group['group'] : 'General';
            $groups[] = $group['group'];
            $selected = ($i == 0) ? ' selected="true"' : '';
            $out .= "\t<div dojoType=\"dijit.layout.ContentPane\" title=\"{$group['group']}\" $selected>\r\n";
            $out .= "\t\t<div dojoType=\"dijit.form.Form\" id=\"settings{$i}_form\" jsId=\"settings{$i}_form\" encType=\"multipart/form-data\" target=\"ifrm_process\" action=\"".BASE_URL."objects/multi-update\" method=\"post\">\r\n";

            //Subgrupos
            $settings = new $this->_model();
            $table=$settings->getName();

            $query = $settings->select()
                ->where($settings->getAdapter()->quoteInto($table.'.group = ?', $group['group']))
                ->order('ord ASC');

            $subgroups = $settings->fetchAll($query);

            foreach ($subgroups as $sg) {
                $out .= "\t\t\t<input type=\"hidden\" id=\"id[$i]\" name=\"id[$i]\" value=\"$sg[id]\" />\r\n";
                $out .= "\t\t\t<b>$sg[id]:</b> ";
                $ClassElement = "Zwei_Admin_Elements_".Zwei_Utils_String::toClassWord($sg['type']);
                $e = new $ClassElement(true, true, "", "value", $sg['value']);

                if (!empty($sg['enum'])) {
                    $out .= $this->_select("value[$i]", $sg['enum'], $sg['value']);
                }else{
                    $out .= $e->edit($i,0);
                }

                if (!empty($sg['function'])&&class_exists($sg['function'])) {
                    $f = new $sg['function'];
                    $f->setId($i);
                    $out .= $f->display();
                }
                $out .= "<br />".nl2br($sg['description']);
                $out .= "\r\n<br /><br />\r\n";
                $i++;
            }

            $out .="
        	<script type=\"dojo/method\" event=\"onSubmit\">
                if (this.validate())
                {
                    this.submit();
                }
                else
                {
                	alert('Por favor favor corrija los campos marcados');
                	return false;
                }

            </script>\r\n
            ";			

            $out.="<button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSave\" id=\"btnSaveSettings$i\">Guardar</button>";
            $out.="<input type=\"hidden\" name=\"save\" value=\"save\" />\r\n";
            $out.="<input type=\"hidden\" name=\"model\" value=\"$this->_model\" />\r\n";
            $out.="\t\t</div>\r\n";
            $out.="\t</div>\r\n";
        }

        $out.="</div>\r\n";
        return $out;
    }

    private function _select($name, $values, $value)
    {
        $r='<select dojoType="dijit.form.FilteringSelect" name="'.$name.'">';
        foreach(explode(',',$values) as $v){
            if($value==$v){
                $r.='<option value="'.$v.'" selected="selected">'.$v.'</option>';
            }else{
                $r.='<option value="'.$v.'">'.$v.'</option>';
            }
        }
        $r.='</select>';
        return $r;
    }
}
?>