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
use npds\groupes\groupe;
use npds\error\error;


/*
 * auth
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

    /**
     * Retourne true ou false en fonction des paramètres d'autorisation 
     * de Npds Two (Administrateur, anonyme, Membre, Groupe de Membre, Tous)
     * @param  [type] $auto [description]
     * @return [type]       [description]
     */
    public static function autorisation($auto) 
    {
        global $user, $admin;
        
        $affich = false;
        
        if (($auto == -1) and (!$user)) 
        {
            $affich = true;
        }
        
        if (($auto == 1) and (isset($user))) 
        {
            $affich = true;
        }
        
        if ($auto > 1) 
        {
            $tab_groupe = groupe::valid_group($user);
            
            if ($tab_groupe) 
            {
                foreach($tab_groupe as $groupevalue) 
                {
                    if ($groupevalue == $auto) 
                    {
                        $affich = true;
                        break;
                    }
                }
            }
        }

        if ($auto == 0) 
        {
            $affich = true;
        }

        if (($auto == -127) and ($admin)) 
        {
            $affich = true;
        }
        
        return $affich;
    }

    /**
     * Gestion + fine des destinataires (-1, 0, 1, 2 -> 127, -127)
     * @param  [type]  $ihome [description]
     * @param  integer $catid [description]
     * @return [type]         [description]
     */
    public static function ctrl_aff($ihome, $catid=0) 
    {
        global $user;
        
        $affich = false;
        
        if ($ihome == -1 and (!$user)) 
        {
            $affich = true;
        }
         elseif ($ihome == 0) 
        {
            $affich = true;
        } 
        elseif ($ihome == 1) 
        {
            if ($catid > 0) 
            {
                $affich = false;
            } 
            else 
            {
                $affich = true;
            }
        } 
        elseif (($ihome > 1) and ($ihome <= 127)) 
        {
            $tab_groupe = groupe::valid_group($user);
            
            if ($tab_groupe) 
            {
                foreach($tab_groupe as $groupevalue) 
                {
                    if ($groupevalue == $ihome) 
                    {
                        $affich = true;
                        break;
                    }
                }
            }
        } 
        else 
        {
            if ($user)
            { 
                $affich = true;
            }
        }
        
        return $affich;
    }

    /**
     * Affiche URL et Email d'un auteur
     * @param  [type] $aid [description]
     * @return [type]      [description]
     */
    public static function formatAidHeader($aid) 
    {
        global $NPDS_Prefix;
           
        $holder = sql_query("SELECT url, email FROM ".$NPDS_Prefix."authors WHERE aid='$aid'");
           
        if ($holder) 
        {
            list($url, $email) = sql_fetch_row($holder);
              
            if (isset($url)) 
            {
                echo '<a href="'.$url.'" >'.$aid.'</a>';
            } 
            elseif (isset($email)) 
            {
                echo '<a href="mailto:'.$email.'" >'.$aid.'</a>';
            } 
            else 
            {
                echo $aid;
            }
        }
    }

    /**
     * Pour savoir si le visiteur est un : membre ou admin 
     * (static.php et banners.php par exemple)
     * @param  [type] $sec_type [description]
     * @return [type]           [description]
     */
    public static function secur_static($sec_type) 
    {
        global $user, $admin;

        switch ($sec_type) 
        {
            case 'member':
                if (isset($user)) 
                {
                    return true;
                } 
                else 
                {
                    return false;
                }
            break;

            case 'admin':
                if (isset($admin)) 
                {
                    return true;
                } 
                else 
                {
                    return false;
                }
            break;
        }
    }

    /**
     * [get_userdata_from_id description]
     * @param  [type] $userid [description]
     * @return [type]         [description]
     */
    public static function get_userdata_from_id($userid) 
    {
        global $NPDS_Prefix;
        
        $sql1 = "SELECT * FROM ".$NPDS_Prefix."users WHERE uid='$userid'";
        $sql2 = "SELECT * FROM ".$NPDS_Prefix."users_status WHERE uid='$userid'";
        
        if (!$result = sql_query($sql1))
        {
            error::forumerror('0016');
        }
        
        if (!$myrow = sql_fetch_assoc($result))
        {
            $myrow = array( "uid" => 1);
        }
        else
        {
            $myrow = array_merge($myrow, (array)sql_fetch_assoc(sql_query($sql2)));
        }
        
        return $myrow;
    }

    /**
     * [get_userdata_extend_from_id description]
     * @param  [type] $userid [description]
     * @return [type]         [description]
     */
    public static function get_userdata_extend_from_id($userid) 
    {
        global $NPDS_Prefix;
           
        $sql1 = "SELECT * FROM ".$NPDS_Prefix."users_extend WHERE uid='$userid'";
        /*
        $sql2 = "SELECT * FROM ".$NPDS_Prefix."users_status WHERE uid='$userid'";

        if (!$result = sql_query($sql1))
        {  
            error::forumerror('0016');
        }

        if (!$myrow = sql_fetch_assoc($result))
        {
            $myrow = array( "uid" => 1);}
        else
        {
            $myrow = array_merge($myrow, (array)sql_fetch_assoc(sql_query($sql1)));
        }
        */
        $myrow = (array)sql_fetch_assoc(sql_query($sql1));
         
        return $myrow;
    }

    /**
     * [get_userdata description]
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public static function get_userdata($username) 
    {
        global $NPDS_Prefix;
        
        $sql = "SELECT * FROM ".$NPDS_Prefix."users WHERE uname='$username'";
        
        if (!$result = sql_query($sql))
        {
            error::forumerror('0016');
        }
        
        if (!$myrow = sql_fetch_assoc($result))
        {
            $myrow = array( "uid" => 1);
        }
        
        return $myrow;
    }

}
