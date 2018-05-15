<?php

/**
 * Conversiones de formatos y tipos de datos
 */

Class Gamelena_Utils_Types
{
    /**
     * Convierte un número de bits a una unidad leible
     * 
     * @param  int    $bytes
     * @param  int    $decimals
     * @param  string $dec_sep
     * @param  string $thous_sep
     * @return string
     */
    public function bytesToReadable($bytes, $decimals=2, $dec_sep=",", $thous_sep=".")
    {
        if ($bytes < 1024) {
            return $bytes." bytes";
        } else if ($bytes<1048576) {
            return Gamelena_Utils_Types::bytesToKb($bytes, $decimals, $dec_sep, $thous_sep);
        } else if ($bytes<1073741824) {
            return Gamelena_Utils_Types::bytesToMb($bytes, $decimals, $dec_sep, $thous_sep);
        } else if ($bytes<1099511627776) {
            return Gamelena_Utils_Types::bytesToGb($bytes, $decimals, $dec_sep, $thous_sep);
        } else {
            return Gamelena_Utils_Types::bytesToTb($bytes, $decimals, $dec_sep, $thous_sep);
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