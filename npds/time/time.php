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

use npds\language\language;


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
        list($usec, $sec) = explode(' ', microtime());
       
        return ((float)$usec + (float)$sec);
    }
  
    /**
     * Formate un timestamp en fonction de la valeur de $locale (config/config.php)
     * si "nogmt" est concaténé devant la valeur de $time, le décalage gmt n'est pas appliqué
     * @param  [type] $time [description]
     * @return [type]       [description]
     */
    public static function formatTimestamp($time) 
    {
        global $datetime, $locale, $gmt;
        
        $local_gmt = $gmt;
        
        setlocale(LC_TIME, language::aff_langue($locale));
        
        if (substr($time, 0, 5) == 'nogmt') 
        {
            $time = substr($time, 5);
            $local_gmt = 0;
        }
        
        preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $time, $datetime);
        
        $datetime = strftime(translate("datestring"), mktime($datetime[4]+(integer)$local_gmt, $datetime[5], $datetime[6], $datetime[2], $datetime[3], $datetime[1]));
        
        return (ucfirst(htmlentities($datetime, ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML401, cur_charset))); 
    }

}
