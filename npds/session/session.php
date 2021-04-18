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
namespace npds\session;

use npds\security\hack;
use npds\security\ip;
use npds\utility\str;
use modules\geoloc\support\geosession;


/*
 * session
 */
class session {

     
    /**
     * Mise à jour la table session
     * @return [type] [description]
     */
    public static function manage() 
    {
        global $NPDS_Prefix, $cookie, $REQUEST_URI;

        $guest = 0;
        $ip = ip::get();
        
        $username = isset($cookie[1]) ? $cookie[1] : $ip;
           
        if($username == $ip)
        {
            $guest = 1;
        }
              
        // geoloc session 
        //geosession::init($ip);
        
        $past = time()-300;
        
        sql_query("DELETE FROM ".$NPDS_Prefix."session WHERE time < '$past'");
        $result = sql_query("SELECT time FROM ".$NPDS_Prefix."session WHERE username='$username'");
           
        if ($row = sql_fetch_assoc($result)) 
        {
            if ($row['time'] < (time()-30)) 
            {
                sql_query("UPDATE ".$NPDS_Prefix."session SET username='$username', time='".time()."', host_addr='$ip', guest='$guest', uri='$REQUEST_URI', agent='".getenv("HTTP_USER_AGENT")."' WHERE username='$username'");
                
                if ($guest == 0) 
                {
                    global $gmt;
                    sql_query("UPDATE ".$NPDS_Prefix."users SET user_lastvisit='".(time()+(integer)$gmt*3600)."' WHERE uname='$username'");
                }
            }
        } 
        else 
        {
            sql_query("INSERT INTO ".$NPDS_Prefix."session (username, time, host_addr, guest, uri, agent) VALUES ('$username', '".time()."', '$ip', '$guest', '$REQUEST_URI', '".getenv("HTTP_USER_AGENT")."')");
        }
    }

}
