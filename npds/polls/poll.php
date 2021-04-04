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
namespace npds\polls;


/*
 * poll
 */
class poll {


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

    /**
     * Bloc Sondage 
     * syntaxe : function#pollnewest
     * params#ID_du_sondage OU vide (dernier sondage créé)
     * @param string $id [description]
     */
    public static function PollNewest($id='') 
    {
        global $NPDS_Prefix;
           
        // snipe : multi-poll evolution
        if ($id != 0) 
        {
            settype($id, "integer");
            list($ibid, $pollClose) = static::pollSecur($id);
            
            if ($ibid) 
            {
                pollMain($ibid, $pollClose);
            }
        } 
        elseif ($result = sql_query("SELECT pollID FROM ".$NPDS_Prefix."poll_data ORDER BY pollID DESC LIMIT 1")) 
        {
            list($pollID) = sql_fetch_row($result);
            list($ibid, $pollClose) = static::pollSecur($pollID);
            
            if ($ibid) 
            {
                pollMain($ibid, $pollClose);
            }
        }
    }

}
