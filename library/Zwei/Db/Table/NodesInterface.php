<?php
interface Zwei_Db_Table_NodesInterface
{
    public function getXAxis();
    public function getYAxis();
    public function setPosition($nodeId, $nodeType);
    public function getPosition($nodeId, $nodeType);
}