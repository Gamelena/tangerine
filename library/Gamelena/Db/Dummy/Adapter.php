<?php
/**
 * Adaptador dummy, útil para implementar en Modelos No DB que usen la api rest CrudRequestController.
 *
 * @example
 * <code>
 * class MyCrazyModel implements Gamelena_Admin_ModelInterface
 * {
 *     public function getAdapter()
 *     {
 *         return new Gamelena_Db_Dummy_Adapter();
 *     }
 * }
 * </code>
 */
class Gamelena_Db_Dummy_Adapter
{
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        if ($count === null) {
            return str_replace('?', $this->quote($value, $type), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $this->quote($value, $type), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }
    
    public function quote($value, $type = null)
    {
    
        return "'" . str_replace("'", "\'", $value) . "'";
    }
    
    public function quoteIdentifier($value)
    {
    
        return $this->quote($value);
    }
}