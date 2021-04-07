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


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

if ($SuperCache)
{
    $cache_obj = new cacheManager();
}
else
{
    $cache_obj = new SuperCacheEmpty();
}

include("header.php");

if (($SuperCache) and (!$user))
{
    $cache_obj->startCachingPage();
}

if (($cache_obj->genereting_output == 1) 
    or ($cache_obj->genereting_output == -1) 
    or (!$SuperCache) or ($user)) 
{
    $inclusion = false;
      
    if (file_exists("themes/$theme/html/top.html"))
    {
        $inclusion = "themes/$theme/html/top.html";
    }
    elseif (file_exists("themes/default/html/top.html"))
    {
        $inclusion = "themes/default/html/top.html";
    }
    else
    {
        echo "html/top.html / not find !<br />";
    }
      
    if ($inclusion) 
    {
        ob_start();
            include($inclusion);
            $Xcontent = ob_get_contents();
        ob_end_clean();
        
        echo meta_lang(aff_langue($Xcontent));
    }
}

// -- SuperCache
if (($SuperCache) and (!$user))
{
    $cache_obj->endCachingPage();
}
  
include("footer.php");
