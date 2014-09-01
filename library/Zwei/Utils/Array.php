<?php 
/**
 * 
 * Utilidades para arrays
 * @author rodrigo.riquelme@zweicom.com
 *
 */
class Zwei_Utils_Array
{
    /**
     * 
     * Busca recursivamente en $array un $value con índice $key.
     * 
     * @param array - arreglo 
     * @param mixed - índice en el cual buscar
     * @param mixed - valor a buscar
     * @return array
     */
    static function search($array, $key, $value)
    {
        $results = array();
    
        if (is_array($array))
        {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;
    
            foreach ($array as $subarray)
                $results = array_merge($results, self::search($subarray, $key, $value));
        }
    
        return $results;
    }
    
    /**
     * Compara 2 arrays con los mismos indices, retorna un array con los registros diferentes (o iguales según $returnDiffs). 
     * 
     * @param array
     * @param array
     * @param boolean - true: devuelve registros diferentes, false: devuelve registros iguales
     * @return array
     */
    static function getDifferences(array $array1, array $array2, $returnDiffs = true)
    {
        $results = array();
        
        foreach ($array1 as $i => $v) {
            if ($returnDiffs) {
                if ($array2[$i] != $v) $results[$i] = $v; 
            } else {
                if ($array2[$i] == $v) $results[$i] = $v;
            }
        }
        
        return $results;
    }
    
    /**
     * 
     * @param array $array
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    static function toXml(array $array, $xml)
    {
        foreach ($array as $k => $v) {
            if (is_object($v)) $v = (array) $v;
            is_array($v)
                ? self::toXml($v, $xml->addChild($k))
                : $xml->addChild($k, $v);
        }
        return $xml;
    }
}