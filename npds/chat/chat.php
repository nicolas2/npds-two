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
namespace npds\chat;

use npds\blocks\block;
use npds\security\hack;
use npds\security\ip;
use npds\utility\str;


/*
 * chat
 */
class chat {


    /**
     * Retourne le nombre de connect&eacute; au Chat
     * @param  [type] $pour [description]
     * @return [type]       [description]
     */
    public static function if_chat($pour) 
    {
        global $NPDS_Prefix;

        $auto = block::autorisation_block("params#".$pour);
        $dimauto = count($auto);
        $numofchatters = 0;

        if ($dimauto <= 1) 
        {
            $result = sql_query("SELECT DISTINCT ip FROM ".$NPDS_Prefix."chatbox WHERE id='".$auto[0]."' AND date >= ".(time()-(60*3))."");
            $numofchatters = sql_num_rows($result);
        }
           
        return $numofchatters;
    }

    /**
     * Insère un record dans la table Chat
     * on utilise id pour filtrer les messages 
     * id = l'id du groupe
     * @param  [type] $username [description]
     * @param  [type] $message  [description]
     * @param  [type] $dbname   [description]
     * @param  [type] $id       [description]
     * @return [type]           [description]
     */
    public static function insertChat($username, $message, $dbname, $id) 
    {
        global $NPDS_Prefix;
           
        if ($message != '') 
        {
            $username = hack::remove(stripslashes(str::FixQuotes(strip_tags(trim($username)))));
            $message =  hack::remove(stripslashes(str::FixQuotes(strip_tags(trim($message)))));
            
            $ip = ip::get();
            
            settype($id, 'integer');
            settype($dbname, 'integer');
            
            $result = sql_query("INSERT INTO ".$NPDS_Prefix."chatbox VALUES ('".$username."', '".$ip."', '".$message."', '".time()."', '$id', ".$dbname.")");
        }
    }

}
