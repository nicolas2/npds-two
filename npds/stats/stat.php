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
namespace npds\stats;


/*
 * stat
 */
class stat {

 
    /**
     * Retourne un tableau contenant les nombres pour les statistiques du site (stats.php)
     * @return [type] [description]
     */
    public static function req_stat() 
    {
        global $NPDS_Prefix;
           
        // Les membres
        $result = sql_query("SELECT uid FROM ".$NPDS_Prefix."users");
        if ($result) 
        {
            $xtab[0] = sql_num_rows($result);
        } 
        else 
        {
            $xtab[0] = "0";
        }
        
        // Les Nouvelles (News)
        $result = sql_query("SELECT sid FROM ".$NPDS_Prefix."stories");
        if ($result) 
        {
            $xtab[1] = sql_num_rows($result);
        } 
        else 
        {
            $xtab[1] = "0";
        }
        
        // Les Critiques (Reviews))
        $result = sql_query("SELECT id FROM ".$NPDS_Prefix."reviews");
        if ($result) 
        {
            $xtab[2] = sql_num_rows($result);
        } 
        else 
        {
            $xtab[2] = "0";
        }
        
        // Les Forums
        $result = sql_query("SELECT forum_id FROM ".$NPDS_Prefix."forums");
        if ($result) 
        {
            $xtab[3] = sql_num_rows($result);
        } 
        else 
        {
            $xtab[3] = "0";
        }
        
        // Les Sujets (topics)
        $result = sql_query("SELECT topicid FROM ".$NPDS_Prefix."topics");
        if ($result) 
        {
            $xtab[4] = sql_num_rows($result);
        } 
        else 
        {
            $xtab[4] = "0";
        }
        
        // Nombre de pages vues
        $result = sql_query("SELECT count FROM ".$NPDS_Prefix."counter WHERE type='total'");
        if ($result) 
        {
            list($totalz) = sql_fetch_row($result);
        }
        
        $totalz++;
        $xtab[5] = $totalz++;
        sql_free_result($result);
        
        return $xtab;
    }

}
