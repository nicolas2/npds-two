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
use npds\error\alert;
use npds\auth\auth;
use npds\cookie\cookie;


if ((isset($aid)) 
    and (isset($pwd)) 
    and ($op == 'login')) 
{
    if ($aid != '' 
        and $pwd != '') 
    {
        $result = sql_query("SELECT pwd, hashkey FROM ".$NPDS_Prefix."authors WHERE aid='$aid'");
      
        if (sql_num_rows($result) == 1) 
        {
            $setinfo = sql_fetch_assoc($result);
            $dbpass = $setinfo['pwd'];
            $pwd = utf8_decode($pwd);
            $scryptPass = null;
         
            if (password_verify($pwd, $dbpass) or (strcmp($dbpass, $pwd) == 0)) 
            {
                if(!$setinfo['hashkey']) 
                {
                    $AlgoCrypt = PASSWORD_BCRYPT;
                    $min_ms = 100;
                    $options = ['cost' => auth::getOptimalBcryptCostParameter($pwd, $AlgoCrypt, $min_ms)];
                    $hashpass = password_hash($pwd, $AlgoCrypt, $options);
                    $pwd = crypt($pwd, $hashpass);
               
                    sql_query("UPDATE ".$NPDS_Prefix."authors SET pwd='$pwd', hashkey='1' WHERE aid='$aid'");
                    $result = sql_query("SELECT pwd, hashkey FROM ".$NPDS_Prefix."authors WHERE aid = '$aid'");
               
                    if (sql_num_rows($result) == 1)
                    {
                        $setinfo = sql_fetch_assoc($result);
                    }
               
                    $dbpass = $setinfo['pwd'];
                    $scryptPass = crypt($dbpass, $hashpass);
                }
            }

            if(password_verify($pwd, $dbpass))
            {
                $CryptpPWD = $dbpass;
            }
            elseif (password_verify($dbpass, $scryptPass) 
                or strcmp($dbpass, $pwd) == 0)
            {
                $CryptpPWD = $pwd;
            }
            else 
            {
                alert::admin("Passwd not in DB#1 : $aid");
            }

            $admin = base64_encode("$aid:".md5($CryptpPWD));
         
            if ($admin_cook_duration <= 0) 
            {
                $admin_cook_duration = 1;
            }
         
            $timeX = time()+(3600*$admin_cook_duration);
            cookie::set('admin', $admin, $timeX);
            cookie::set('adm_exp', $timeX, $timeX);
        }
    }
}

#autodoc $admintest - $super_admintest : permet de savoir si un admin est connect&ecute; ($admintest=true) et s'il est SuperAdmin ($super_admintest=true)
$admintest = false;
$super_admintest = false;

if (isset($admin) and ($admin != '')) 
{
    $Xadmin = base64_decode($admin);
    $Xadmin = explode(':', $Xadmin);
    $aid = urlencode($Xadmin[0]);
    $AIpwd = $Xadmin[1];
   
    if ($aid == '' or $AIpwd == '')
    {
        alert::admin('Null Aid or Passwd');
    }
   
    $result = sql_query("SELECT pwd, radminsuper FROM ".$NPDS_Prefix."authors WHERE aid = '$aid'");
   
    if (!$result)
    {
        alert::admin("DB not ready #2 : $aid / $AIpwd");
    }
    else 
    {
        list($AIpass, $Xsuper_admintest) = sql_fetch_row($result);
     
        if (md5($AIpass) == $AIpwd and $AIpass != '') 
        {
            $admintest = true;
            $super_admintest = $Xsuper_admintest;
        } 
        else
        {
            alert::admin("Password in Cookies not Good #1 : $aid / $AIpwd");
        }
    }
   
    unset($AIpass);
    unset($AIpwd);
    unset($Xadmin);
    unset($Xsuper_admintest);
}
