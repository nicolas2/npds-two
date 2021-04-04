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


if (!defined('NPDS_GRAB_GLOBALS_INCLUDED')) 
{
    define('NPDS_GRAB_GLOBALS_INCLUDED', 1);

    // Debugin repport
    debug::reporting();

    // Spam booting
    spam::boot();

    // include current charset
    if (file_exists("config/charset.php")) {
        include ("config/charset.php");
    }
   
    // include current charset
    if (file_exists("config/constant.php")) {
        include ("config/constant.php");
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
    if (!empty($_SERVER)) {
        extract($_SERVER, EXTR_OVERWRITE);
    }

    // 
    if (!empty($_ENV)) {
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
}
