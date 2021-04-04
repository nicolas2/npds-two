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

include("vendor/autoload.php");

use npds\session\session;
use npds\cookie\cookie;
use npds\language\language;

include("lib/grab_globals.php");
include("config/config.php");

include("lib/multi-langue.php");
include("language/$language/lang-$language.php");

include('npds/database/connexion.php');

Mysql_Connexion();

require_once("admin/auth.inc.php");

if (isset($user)) 
{
   $cookie = cookie::decode($user);
}

session::manage();

$tab_langue = language::make_tab_langue();

include("lib/metalang/metalang.php");

global $meta_glossaire;
$meta_glossaire = charg_metalang();

if (function_exists("date_default_timezone_set")) 
{
   date_default_timezone_set("Europe/Paris");
}
