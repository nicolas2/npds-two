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
namespace npds\poolboth;


/*
 * poolboth
 */
class poolboth {


    /**
     * Assure la gestion des sondages membres
     * @param  [type] $pollID [description]
     * @return [type]         [description]
     */
    public static function pollSecur($pollID) 
    {
        global $NPDS_Prefix, $user;
           
        $pollIDX = false;
        $result = sql_query("SELECT pollType FROM ".$NPDS_Prefix."poll_data WHERE pollID='$pollID'");
        
        if (sql_num_rows($result)) 
        {
            list($pollType) = sql_fetch_row($result);
            
            $pollClose = (($pollType / 128) >= 1 ? 1 : 0);
            $pollType = $pollType%128;
            
            if (($pollType == 1) and !isset($user)) 
            {
                $pollClose = 99;
            }
        }
        
        return array($pollID, $pollClose);
    }

}
