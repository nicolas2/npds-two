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
namespace npds\who;


/*
 * online
 */
class online {


    /**
     * Qui est en ligne ? + message de bienvenue
     */
    public static function Who_Online() 
    {
        list($content1, $content2) = static::Who_Online_Sub();
        
        return array($content1, $content2);
    }

    /**
     * Qui est en ligne ? + message de bienvenue 
     * SOUS-Fonction Utilise Site_Load
     */
    public static function Who_Online_Sub() 
    {
        global $user, $cookie;
         
        list($member_online_num, $guest_online_num) = static::site_load();
        
        $content1 = "$guest_online_num ".translate("visiteur(s) et")." $member_online_num ".translate("membre(s) en ligne.");
         
        if ($user) 
        {
            $content2 = translate("Vous êtes connecté en tant que")." <b>".$cookie[1]."</b>";
        } 
        else 
        {
            $content2 = translate("Devenez membre privilégié en cliquant")." <a href=\"user.php?op=only_newuser\">".translate("ici")."</a>";
        }

        return array($content1, $content2);
    }

    /**
     * Maintient les informations de NB connexion (membre, anonyme) 
     * globalise la variable $who_online_num et maintient le fichier 
     * storage/cache/site_load.log &agrave; jour
     * Indispensable pour la gestion de la 'clean_limit' de SuperCache
     */
    public static function Site_Load() 
    {
        global $NPDS_Prefix, $SuperCache;
        
        // globalise la variable
        global $who_online_num;
           
        $guest_online_num = 0;
        $member_online_num = 0;
        $result = sql_query("SELECT COUNT(username) AS TheCount, guest FROM ".$NPDS_Prefix."session GROUP BY guest");
           
        while ($TheResult = sql_fetch_assoc($result)) 
        {
            if ($TheResult['guest'] == 0)
            {
                $member_online_num = $TheResult['TheCount'];
            }
            else
            {
                $guest_online_num = $TheResult['TheCount'];
            }
        }
           
        $who_online_num = $guest_online_num + $member_online_num;
           
        if ($SuperCache) 
        {
            $file = fopen("storage/cache/site_load.log", "w");
            fwrite($file, $who_online_num);
            fclose($file);
        }
           
        return array($member_online_num, $guest_online_num);
    }

    /**
     * liste des membres connectés  
     * Retourne un tableau dont la position 0 est le nombre, 
     * puis la liste des username | time 
     * Appel : $xx=online_members(); 
     * puis $xx[x]['username'] $xx[x]['time'] ...
     * @return [type] [description]
     */
    public static function online_members() 
    {
        global $NPDS_Prefix;

        $result = sql_query("SELECT username, guest, time FROM ".$NPDS_Prefix."session WHERE guest='0' ORDER BY username ASC");
        
        $i = 0;
        
        $members_online[$i] = sql_num_rows($result);
        
        while ($session = sql_fetch_assoc($result)) 
        {
            if (isset($session['guest']) and $session['guest'] == 0) 
            {
                $i++;
                $members_online[$i]['username'] = $session['username'];
                $members_online[$i]['time'] = $session['time'];
            }
        }
        
        return $members_online;
    }

}
