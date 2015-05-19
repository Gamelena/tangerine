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
    public static function getFullPath($file) 
    {
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
    
    
    /**
     * 
     * @param string $xpath
     * @param string $root
     * @param string $toXml
     * @return <multitype:, Zwei_Admin_Xml, SimpleXMLElement>
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
     * @param boolean $inherit
     * @param string $xpath
     * @return array(SimpleXMLElement)
     */
    public function getTabsWithElements($inherits = false, $xpath = null)
    {
        $elements = array();
        
        if ($xpath != null) $xpath = "[$xpath]";
        
        if ($this->xpath('//component/forms/tabs/tab'/*.$xpath*/)) {
            for ($i = 0; $i < count($this->forms->tabs->tab); $i++) {
                for ($j = 0; $j < count($this->forms->tabs->tab[$i]); $j++) {
                    if ($inherits && $this->forms->tabs->tab[$i]->element[$j]) $this->inheritAttributes($this->forms->tabs->tab[$i]->element[$j]);
                }
            }
            
            foreach ($this->forms->tabs->tab as $i => $v) {
                
            }
            
            $elements = $this->forms->tabs->tab;
        } else {
            $elements = array($this->xpath('//component/elements/element'.$xpath));
        }
        return $elements;
    }
    /**
     * 
     * @param array $array
     * @return Zwei_Admin_Xml
     */
    public function parseXml($array) 
    {
        $xml = Zwei_Utils_Array::toXml($array, new Zwei_Admin_Xml("<?xml version=\"1.0\"?><components></components>"));
        return $xml;
    }
    
    /**
     * @param string $index
     * @param Zwei_Utils_SimpleXML $son
     * @param Zwei_Utils_SimpleXML $father
     */
    public function inheritAttributes($son, $fatherLevel = '//elements/element', $index='target', $override = false)
    {
        $tmpFathers = $this->xpath($fatherLevel."[@$index='{$son->getAttribute('target')}']");
        if ($tmpFathers) {
            $father = $tmpFathers[0];
            
            foreach ($father->attributes() as $key => $value) {
                if ($override || !$son->getAttribute($key)) 
                    $son->addAttribute($key, $value);
            }
        }
        return $son;
    }
    
    /**
     * 
     * @param boolean $inherits
     * @param string $xpath
     * @return array(SimpleXMLElement)
     */
    public function getSearchers($inherits = false, $xpath = null)
    {
        $elements = array();
        if ($xpath != null) $xpath = "[$xpath]";
        
        if ($this->existsChildren('searchers')) {
            if ($this->xpath('//component/searchers/group'.$xpath)) {
                for ($i = 0; $i < count($this->searchers->group); $i++) {
                    //Seteo de target, si no existe en el grupo se busca en el primer elemento hijo
                    $target = $this->searchers->group[$i]->getAttribute('target');
                    if (!$target) {
                        $target = $this->searchers->group[$i]->element->getAttribute('target');
                        $this->searchers->group[$i]->addAttribute('target', $target);
                    }
                    
                    //Seteo de DojoType, si no existe en el grupo se busca en el primer elemento hijo, si no existe en este se busca en nodo /elements
                    if (!$this->searchers->group[$i]->getAttribute('type')) {
                        if ($this->searchers->group[$i]->element->getAttribute('type')) {
                            $this->searchers->group[$i]->addAttribute('type', $this->searchers->group[$i]->element->getAttribute('type'));
                        } else if ($type = $this->xpath("//component/elements/element[@target='$target']/@type")) {
                            $this->searchers->group[$i]->addAttribute('type', $type[0]);
                        } else {
                            $this->searchers->group[$i]->addAttribute('type', 'dijit-form-validation-text-box');
                        }
                    }
                    
                    //Se intenta obtener 'sufix' y 'prefix' desde primer elemento hijo si no está declarado en grupo
                    if (!$this->searchers->group[$i]->getAttribute('prefix')) {
                        if ($prefix = $this->searchers->group[$i]->element->getAttribute('prefix')) {
                            $this->searchers->group[$i]->addAttribute('prefix', $prefix);
                        }
                    }
                    
                    if (!$this->searchers->group[$i]->getAttribute('sufix')) {
                        if ($sufix = $this->searchers->group[$i]->element->getAttribute('sufix')) {
                            $this->searchers->group[$i]->addAttribute('sufix', $sufix);
                        }
                    }
                    
                    $elements[] = $this->searchers->group[$i];
                }
            }
            
            //Se terminan de heredar atributos de /elements
            if ($this->xpath('//component/searchers/element'.$xpath)) {
                for ($i = 0; $i <  count($this->searchers->element); $i++) {
                    if ($inherits) $this->inheritAttributes($this->searchers->element[$i]);
                    $elements[] = $this->searchers->element[$i];
                }
            }
        }
        return $elements;
    }
}