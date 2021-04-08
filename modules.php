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
use npds\error\access;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

/**
 * [filtre_module description]
 * @param  [type] $strtmp [description]
 * @return [type]         [description]
 */
function filtre_module($strtmp) 
{
    if (strstr($strtmp, '..') 
        || stristr($strtmp, 'script') 
        || stristr($strtmp, 'cookie') 
        || stristr($strtmp, 'iframe') 
        || stristr($strtmp, 'applet') 
        || stristr($strtmp, 'object'))
    {
        access::denied();
    }
    else 
    {
        if ($strtmp != '')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

if (filtre_module($ModPath) and filtre_module($ModStart)) 
{
    if (file_exists("modules/$ModPath/$ModStart.php")) 
    {
        include("modules/$ModPath/$ModStart.php");
        die();
    } 
    else
    {
        access::denied();
    }
} 
else
{
   access::denied();
}
