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

$enc = cur_charset;

// For More security
if (!stristr($_SERVER['HTTP_REFERER'], "modules.php?ModPath=wspad&ModStart=wspad")) 
{
	die();
}

settype($verrou_groupe, 'integer');

//cur_charset not dispo ???
$verrou_page = stripslashes(htmlspecialchars(urldecode($verrou_page), ENT_QUOTES, 'utf-8'));

//cur_charset not dispo ???
$verrou_user = stripslashes(htmlspecialchars(urldecode($verrou_user), ENT_QUOTES, 'utf-8'));

// For More security

// For IE cache control
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-chek=0", false);
header("Pragma: no-cache");
// For IE cache control

$fp = fopen("modules/wspad/storage/locks/$verrou_page-vgp-$verrou_groupe.txt", 'w');
fwrite($fp, $verrou_user);
fclose($fp);
