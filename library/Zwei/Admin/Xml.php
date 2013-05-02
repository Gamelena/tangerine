<?php

/**
 * Parseo de componentes XML del back-office
 *
 * @category Zwei
 * @package Zwei_Admin
 * @version $Id:$
 * @since 0.1
 */



class Zwei_Admin_Xml extends Zwei_Utils_SimpleXML
{
    /**
     * Obtiene el path completo del archivo xml, según las convenciones de admportal,
     * si el archivo xml termina con .php se buscará el archivo por http y no por ruta interna,
     * si existe el atributo de tabla 'web_settings.url_from_local', buscara el archivo en host 'web_settings.url_from_local' en lugar de localhost,
     * este último caso es util para cuando se use tunel http. 
     * 
     * @param $file string
     * @return string
     */
    public static function getFullPath($file) {
        if (preg_match('/(.*).php/', $file)) {
            $prefix = BASE_URL ."/components/";
            
            $model = new SettingsModel();//Buscar URL 
            $select = $model->select();
            $select->where('id = ?', 'url_from_local');
            $select->where('value != ?', '');
            $select->where('value IS NOT NULL');
            Debug::writeBySettings($select->__toString(), 'query_log');
            $settings = $model->fetchAll($select);
            
            if ($settings->count() > 0) { $prefix = $settings[0]['value']."/components/";}  
            
        } else {
            $prefix = COMPONENTS_ADMIN_PATH."/";
        } 
        return $prefix.$file; 
    }
    
    
    public function getTabs($xpath = null)
    {
        $elements = false;
        if ($this->existsChildren('tabs')) {
            for ($i = 0; $i < $this->tabs->element->count(); $i++) {
                $elements[] = $this->tabs->element[$i];
            }
        }
        return $elements;
    }
    
    /**
     * 
     * @param string $xpath
     * @return array:
     */
    public function getElements($xpath = null, $root = '/elements', $toXml = false)
    {
        if ($xpath != null) $xpath = "[$xpath]";
        $elements = $this->xpath("/$root/element$xpath");
        if ($toXml) {
            $elements = self::parseXml($elements);
        } 
        return $elements;
    }
    
    /**
     * 
     * @param array $array
     * @return Zwei_Admin_Xml
     */
    public function parseXml($array) {
        $xml = Zwei_Utils_Array::toXml($array, new Zwei_Admin_Xml("<?xml version=\"1.0\"?><components></components>"));
        return $xml;
    }
    
    /**
     * @param string $index
     * @param Zwei_Utils_SimpleXML $son
     * @param Zwei_Utils_SimpleXML $father
     */
    public function inheritAttributes($index, $son, $father, $override = false)
    {
        if ($father == null) $father = $this;
        
        //Debug::write($son);
        //Debug::write($father);

        foreach ($father as $elementFather) {
            foreach ($elementFather->attributes() as $key => $val) {
                //if ($son->xpath(''))
                    //Debug::write("$key:$val");
    
            }
        }
    
        /*
         foreach ($father->attributes() as $key => $val) {
        if ($son->getAttribute($key) && !$override) {
        continue;
        } else if (isset($father->getElements("@$index='{$son->getAttribute($index)}'")[0])) {
        $son->addAttribute($key, $val );
        }
        }
        */
        return $son;
    }
    
    /**
     * 
     * @param $inheritFromElements boolean
     * @return array|false
     */
    public function getSearchers($inheritFromElements  = false)
    {
        $elements = array();
        
        if ($this->existsChildren('searchers')) {
            if ($this->xpath('/component/searchers/group')) {
                for ($i = 0; $i < $this->searchers->group->count(); $i++) {
                    if (!$this->searchers->group[$i]->getAttribute('type')) 
                        $this->searchers->group[$i]->addAttribute('type', 'dijit-form-validation-text-box');
                    
                    if (!$this->searchers->group[$i]->getAttribute('target'))
                        $this->searchers->group[$i]->addAttribute('target', $this->searchers->group[$i]->element->getAttribute('target'));
                    
                    $elements[$i] = $this->searchers->group[$i];
                }
            } else {
                for ($i = 0; $i < $this->searchers->element->count(); $i++) {
                    $elements[$i] = $this->searchers->element[$i];
                }
                
                if ($inheritFromElements) {
                    $elements = $this->inheritAttributes('target', $this->searchers->element, $this->elements->element);
                }
            }
        }
        //Debug::write($elements);
        return $elements;
    }
}