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
     * @param array 
     * @param mixed
     * @param mixed
     * @return array
     */
    function search($array, $key, $value)
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
}