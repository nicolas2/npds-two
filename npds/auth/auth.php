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
namespace npds\auth;

use npds\cookie\cookie;


/*
 * error
 */
class auth {


    /**
     * Si AutoRegUser=true et que le user ne dispose pas du droit de connexion
     * RAZ du cookie Npds Two
     * retourne False ou True
     */
    public static function AutoReg() 
    {
        global $NPDS_Prefix, $AutoRegUser, $user;
           
        if (!$AutoRegUser) 
        {
            if (isset($user)) 
            {
                $cookie = explode(':', base64_decode($user));
                
                list($test) = sql_fetch_row(sql_query("SELECT open FROM ".$NPDS_Prefix."users_status WHERE uid='$cookie[0]'"));
                 
                if (!$test) 
                {
                    cookie::destroy('user');
                    return false;
                } 
                else 
                {
                    return true;
                }
            } 
            else 
            {
                return true; 
            }
        } 
        else 
        {
            return true;
        }
    }

    /**
     * Renvoi le contenu de la table users pour le user uname
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public static function getusrinfo($user) 
    {
        global $NPDS_Prefix;
           
        $cookie = explode(':', base64_decode($user));
        
        $result = sql_query("SELECT pass FROM ".$NPDS_Prefix."users WHERE uname='$cookie[1]'");
        list($pass) = sql_fetch_row($result);
        
        $userinfo = '';
           
        if (($cookie[2] == md5($pass)) AND ($pass != '')) 
        {
            $result = sql_query("SELECT uid, name, uname, email, femail, url, user_avatar, user_occ, user_from, user_intrest, user_sig, user_viewemail, user_theme, pass, storynum, umode, uorder, thold, noscore, bio, ublockon, ublock, theme, commentmax, user_journal, send_email, is_visible, mns, user_lnl FROM ".$NPDS_Prefix."users WHERE uname='$cookie[1]'");
              
            if (sql_num_rows($result) == 1) 
            {
                $userinfo = sql_fetch_assoc($result);
            } 
            else 
            {
                echo '<strong>'.translate("Un problème est survenu").'.</strong>';
            }
        }
        
        return $userinfo;
    }

    /**
     * permet de calculer le coût algorythmique optimum pour la 
     * procédure de hashage ($AlgoCrypt) 
     * d'un mot de pass ($pass) avec un temps minimum alloué ($min_ms)
     * @param  [type]  $pass      [description]
     * @param  [type]  $AlgoCrypt [description]
     * @param  integer $min_ms    [description]
     * @return [type]             [description]
     */
    public static function getOptimalBcryptCostParameter($pass, $AlgoCrypt, $min_ms=100) 
    {
        for ($i = 8; $i < 13; $i++) 
        {
            $calculCost = ['cost' => $i];
            $time_start = microtime(true);
            
            password_hash($pass, $AlgoCrypt, $calculCost);
            
            $time_end = microtime(true);
            
            if (($time_end - $time_start) * 1000 > $min_ms)
            {
                return $i;
            }
        }
    }

}
