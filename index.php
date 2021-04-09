<?php
/**
 * Npds Two
 *
 * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier
 * 
 * @author Nicolas DEVOY speudo Nicolas2
 * @version 1.0
 * @date 02/04/2021
 */
use npds\cache\cacheManager;
use npds\cache\cacheEmpty;
use npds\auth\auth;
use npds\edito\edito;
use npds\news\news;
use npds\views\theme;

if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}
 

//var_dump(theme::list());

/**
 * Redirect for default Start Page of the portal 
 * look at Admin Preferences for choice
 * @param  [type] $op [description]
 * @return [type]     [description]
 */
function select_start_page($op) 
{
    global $Start_Page, $index;

    if (!auth::AutoReg()) 
    { 
        global $user; 
        unset($user); 
    }

    if (($Start_Page == '') 
        or ($op == "index.php") 
        or ($op == "edito") 
        or ($op == "edito-nonews")) 
    {
        $index = 1;
        theindex($op, '', '');
        die('');
    } 
    else
    {
        Header("Location: $Start_Page");
    }
}

/**
 * [theindex description]
 * @param  [type] $op      [description]
 * @param  [type] $catid   [description]
 * @param  [type] $marqeur [description]
 * @return [type]          [description]
 */
function theindex($op, $catid, $marqeur) 
{
    include('header.php');
        
    // Include cache manager
    global $SuperCache;
        
    if ($SuperCache) 
    {
        $cache_obj = new cacheManager();
        $cache_obj->startCachingPage();
    } 
    else
    {
        $cache_obj = new cacheEmpty();
    }
        
    if (($cache_obj->genereting_output == 1) 
        or ($cache_obj->genereting_output == -1) 
        or (!$SuperCache)) 
    {
        // Appel de la publication de News et la purge automatique
        news::automatednews();

        global $theme;
        if (($op == 'newcategory') 
            or ($op == 'newtopic') 
            or ($op == 'newindex') 
            or ($op == 'edito-newindex')) 
        {
            news::aff_news($op, $catid, $marqeur);
        } 
        else 
        {
            if (file_exists("themes/$theme/central.php")) 
            {
                include("themes/$theme/central.php");
            } 
            else 
            {
                if (($op == 'edito') or ($op == 'edito-nonews')) 
                {
                    edito::aff_edito();
                }
                
                if ($op != 'edito-nonews') 
                {
                    news::aff_news($op, $catid, $marqeur);
                }
            }
        }
    }
        
    if ($SuperCache) 
    {
        $cache_obj->endCachingPage();
    }
        
    include('footer.php');
}

settype($op, 'string');
settype($catid, 'integer');
settype($marqeur, 'integer');

switch ($op) 
{
    case 'newindex':
    case 'edito-newindex':
    case 'newcategory':
        theindex($op, $catid, $marqeur);
    break;
       
    case 'newtopic':
        theindex($op, $topic, $marqeur);
    break;
       
    default:
        select_start_page($op, '');
    break;
}
