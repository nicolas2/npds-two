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

if (strstr($bnid, '..') 
	|| strstr($bnid, './') 
	|| stristr($bnid, 'script') 
	|| stristr($bnid, 'cookie') 
	|| stristr($bnid, 'iframe') 
	|| stristr($bnid, 'applet') 
	|| stristr($bnid, 'object') 
	|| stristr($bnid, 'meta')) 
{
   die();
}

$result = sql_query("SELECT texte FROM ".$NPDS_Prefix."blocnotes WHERE bnid='$bnid'");
if (sql_num_rows($result) > 0) 
{
   	list($texte) = sql_fetch_row($result);

   	$texte = stripslashes($texte);
   	$texte = str_replace(chr(13).chr(10), "\\n", str_replace("'", "\'", $texte));
   
   	echo '$(function(){ $("#texteBlocNote_'.$bnid.'").val(unescape("'.str_replace('"', '\\"', $texte).'")); })';
}
