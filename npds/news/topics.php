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
namespace npds\news;


/*
 * topics
 */
class topics {


    /**
     * Retourne le nom, l'image et le texte d'un topic ou False
     * @param  [type] $s_sid [description]
     * @return [type]        [description]
     */
    public static function getTopics($s_sid) 
    {
        global $NPDS_Prefix, $topicname, $topicimage, $topictext;
        
        $sid = $s_sid;
           
        $result = sql_query("SELECT topic FROM ".$NPDS_Prefix."stories WHERE sid='$sid'");
           
        if ($result) 
        {
            list($topic) = sql_fetch_row($result);
            $result = sql_query("SELECT topicid, topicname, topicimage, topictext FROM ".$NPDS_Prefix."topics WHERE topicid='$topic'");
            
            if ($result) 
            {
                list($topicid, $topicname, $topicimage, $topictext) = sql_fetch_row($result);
                
                return true;
            } 
            else 
            {
                return false;
            }
        } 
        else 
        {
            return false;
        }
    }

}
