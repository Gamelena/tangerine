<?php

/**
 * @author rodrigo
 *
 */

Class Zwei_Utils_Types 
{
    public function bytesToReadable ($bytes, $decimals=2, $dec_sep=",", $thous_sep=".")
    {
        if ($bytes < 1024) {
            return $bytes." bytes";
        } else if ($bytes<1048576) {
            return Zwei_Utils_Types::bytesToKb($bytes, $decimals, $dec_sep, $thous_sep);
        } else if ($bytes<1073741824) {
            return Zwei_Utils_Types::bytesToMb($bytes, $decimals, $dec_sep, $thous_sep);
        } else if ($bytes<1099511627776) {
            return Zwei_Utils_Types::bytesToGb($bytes, $decimals, $dec_sep, $thous_sep);
        } else {
            return Zwei_Utils_Types::bytesToTb($bytes, $decimals, $dec_sep, $thous_sep);
        }
    }
    
    public function bytesToKb($bytes, $decimals=2, $dec_sep=",", $thous_sep=".")
    {
        return number_format($bytes/1024, $decimals, $dec_sep, $thous_sep)." KB";
    }
    
    public function bytesToMb($bytes, $decimals=2, $dec_sep=",", $thous_sep=".")
    {
        return number_format($bytes/1048576, $decimals, $dec_sep, $thous_sep)." MB";
    }
    
    public function bytesToGb($bytes, $decimals=2, $dec_sep=",", $thous_sep=".")
    {
        return number_format($bytes/1073741824, $decimals, $dec_sep, $thous_sep)." GB";
    }
    
    public function bytesToTb($bytes, $decimals=2, $dec_sep=",", $thous_sep=".")
    {
        return number_format($bytes/1099511627776, $decimals, $dec_sep, $thous_sep)." TB";
    }
    
    public function KbToBytes($kb)
    {
        return $kb*1024;
    }
        
    public function MbToBytes($mb)
    {
        return $mb*1048576;
    }
}