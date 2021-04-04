<?php
/**
 * Npds Two
 *
 * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier
 * 
 * @author Nicolas2
 * @version 1.0
 * @date 02/04/2021
 */
namespace npds\time;


/*
 * time
 */
class time {

 
    /**
     * Pour obtenir Nuit ou Jour...
     */
    public static function NightDay() 
    {
        global $lever, $coucher;
       
        $Maintenant = strtotime("now");
        
        $Jour = strtotime($lever);
        $Nuit = strtotime($coucher);
       
        if ($Maintenant - $Jour < 0 xor $Maintenant - $Nuit > 0) 
        {
        	return "Nuit";
        } 
        else 
        {
        	return "Jour";
        }
    }

    /**
     * Retourne le temps en micro-seconde
     * Note : a supprimer remplacé par fonction native php5 microtime(true)
     * @return [type] [description]
     */
    public static function getmicrotime() 
    {
        list($usec, $sec) = explode(' ',microtime());
       
        return ((float)$usec + (float)$sec);
    }
    
}
