<?php
class Zwei_Utils_SimpleXML extends SimpleXMLElement
{
  public function getAttribute($name)
  {
     foreach($this->attributes() as $key=>$val)
     {
        if($key == $name)
        {
           return (string)$val;
        }
     }
  }

  public function getChildrenCount()
  {
        $cnt = 0;
        foreach($this->children() as $node){
            $cnt++;
        }
        return (int)$cnt;
  }

   public function getChildrenCountName($name)
  {
        $cnt = 0;
        foreach($this->children() as $node)
    {
        if( $node->getName() == $name )
               $cnt++;
        }
        return (int)$cnt;
  }

 
  public function existChildren($name)
  {
        
        foreach($this->children() as $node){
       if( $node->getName() == $name )
        return true;
        }
        return false;
  }

   public function getAttributeCount()
   {
        $cnt = 0;
        foreach($this->attributes() as $key=>$val){
            $cnt++;
        }
        return (int)$cnt;
   }

   public function getAttributesArray($names)
   {
        $len = count($names);
        $arrTemp = array();
        for($i = 0; $i < $len; $i++){
            $arrTemp[$names[$i]] = $this->getAttribute((string)$names[$i]);
        }
        return (array)$arrTemp;
    }
}
