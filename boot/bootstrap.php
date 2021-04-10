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
use npds\security\protect;
use npds\security\extract;
use npds\utility\spam;
use npds\utility\str;
use npds\error\access;
use npds\error\debug;
use npds\session\session;
use npds\cookie\cookie;
use npds\language\language;
use npds\language\metalang;


defined('DS') || define('DS', DIRECTORY_SEPARATOR);

define('BASEPATH', realpath(__DIR__ .'/../') .DS);

include(BASEPATH."vendor/autoload.php");

// Check installation
check_install();

// Debugin repport
debug::reporting();

// Spam booting
spam::boot();

// include current charset
if (file_exists(BASEPATH."config/charset.php")) 
{
    include (BASEPATH."config/charset.php");
}
   
// include config
if (file_exists(BASEPATH."config/config.php")) 
{
    include (BASEPATH."config/config.php");
}

// include current charset
if (file_exists(BASEPATH."config/constant.php")) 
{
    include (BASEPATH."config/constant.php");
}

// include cache config
if (file_exists(BASEPATH."config/cache.config.php")) 
{
    include_once(BASEPATH.'config/cache.config.php');
}

// include cache timing
if (file_exists(BASEPATH."config/cache.timings.php")) 
{
    include_once(BASEPATH.'config/cache.timings.php');
}

// Get values, slash, filter and extract
if (!empty($_GET)) 
{
    array_walk_recursive($_GET, [str::class, 'addslashes_GPC']);
    reset($_GET);
      
    array_walk_recursive($_GET, [protect::class, 'url']);
    extract($_GET, EXTR_OVERWRITE);
}

if (!empty($_POST)) 
{
    array_walk_recursive($_POST, [str::class, 'addslashes_GPC']);
    reset($_POST);
      
    //array_walk_recursive($_POST, [protect::class, 'url']);
    extract($_POST, EXTR_OVERWRITE);
}

// Return cookie user
$user = extract::user();
   
// Return cookie user_language
$user_language = extract::user_language();

// Return cookie admin
$admin = extract::admin();

// 
if (!empty($_SERVER)) 
{
    extract($_SERVER, EXTR_OVERWRITE);
}

// 
if (!empty($_ENV)) 
{
    extract($_ENV, EXTR_OVERWRITE);
}

// 
if (!empty($_FILES)) 
{
    foreach ($_FILES as $key => $value) 
    {
        $$key = $value['tmp_name'];
    }
}

include(BASEPATH."multi-langue.php");
include(BASEPATH."language/$language/lang-$language.php");

include(BASEPATH.'npds/database/connexion.php');

Mysql_Connexion();

require_once(BASEPATH."admin/auth.inc.php");

if (isset($user)) 
{
   $cookie = cookie::decode($user);
}

session::manage();

$tab_langue = language::make_tab_langue();

global $meta_glossaire;
$meta_glossaire = metalang::charg_metalang();

if (function_exists("date_default_timezone_set")) 
{
   date_default_timezone_set("Europe/Paris");
}