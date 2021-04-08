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
use npds\language\language;
use npds\language\metalang;
use npds\cache\cacheManager;
use npds\cache\cacheEmpty;


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
    $cache_obj = new CacheEmpty();
}
       
settype($op, 'string');
settype($Subforumid, 'array');
       
if ($op == "maj_subscribe") 
{
    if ($user) 
    {
        settype($cookie[0], "integer");
        
        $result = sql_query("DELETE FROM ".$NPDS_Prefix."subscribe WHERE uid='$cookie[0]' AND forumid!='NULL'");
        $result = sql_query("SELECT forum_id FROM ".$NPDS_Prefix."forums ORDER BY forum_index,forum_id");
             
        while(list($forumid) = sql_fetch_row($result)) 
        {
            if (is_array($Subforumid)) 
            {
                if (array_key_exists($forumid,$Subforumid)) 
                {
                    $resultX = sql_query("INSERT INTO ".$NPDS_Prefix."subscribe (forumid, uid) VALUES ('$forumid','$cookie[0]')");
                }
            }
        }
    }
}

include('header.php');  

if (($SuperCache) and (!$user)) 
{
    $cache_obj->startCachingPage();
}

if (($cache_obj->genereting_output == 1) 
    or ($cache_obj->genereting_output == -1) 
    or (!$SuperCache) or ($user)) 
{
    $inclusion = false;
        
    settype($catid, 'integer');
        
    if ($catid != '') 
    {
        if (file_exists("themes/$theme/views/forum-cat$catid.html")) 
        {
            $inclusion = "themes/$theme/views/forum-cat$catid.html";
        } 
        elseif (file_exists("themes/default/views/forum-cat$catid.html")) 
        {
            $inclusion = "themes/default/views/forum-cat$catid.html";
        }      
    }

    if ($inclusion == false) 
    {
        if (file_exists("themes/$theme/views/forum-adv.html")) 
        {
            $inclusion = "themes/$theme/views/forum-adv.html";
        } 
        elseif (file_exists("themes/$theme/views/forum.html")) 
        {
            $inclusion = "themes/$theme/views/forum.html";
        } 
        elseif (file_exists("themes/default/views/forum.html")) 
        {
            $inclusion = "themes/default/views/forum.html";
        } 
        else 
        {
            echo "views/forum.html / not find !<br />";
        }
    }

    if ($inclusion) 
    {
        $Xcontent = join('', file($inclusion));
        echo metalang::meta_lang(language::aff_langue($Xcontent));
    }
}

// -- SuperCache
if (($SuperCache) and (!$user))
{
    $cache_obj->endCachingPage();
}
    
include('footer.php');
