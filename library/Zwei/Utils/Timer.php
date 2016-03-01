<?php
Class Zwei_Utils_Timer
{
    private $start;

    public function __construct() 
    {
        $this->start = $this->getMicrotime();
        return true;
    }
    
    private function getMicrotime() 
    {
        return microtime(true);
    }

    public function stop($format = false, $decimals = 0) 
    {
        $time = $this->getMicrotime() - $this->start;
        return ($format) ? number_format($time, $decimals):$time;
    }
}