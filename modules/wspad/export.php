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


// For More security
if (!stristr($_SERVER['PHP_SELF'], 'modules.php')) 
{
    die();
}

if (strstr($ModPath, '..') 
    || strstr($ModStart, '..') 
    || stristr($ModPath, 'script') 
    || stristr($ModPath, 'cookie') 
    || stristr($ModPath, 'iframe') 
    || stristr($ModPath, 'applet') 
    || stristr($ModPath, 'object') 
    || stristr($ModPath, 'meta') 
    || stristr($ModStart, 'script') 
    || stristr($ModStart, 'cookie') 
    || stristr($ModStart, 'iframe') 
    || stristr($ModStart, 'applet') 
    || stristr($ModStart, 'object') 
    || stristr($ModStart, 'meta')) 
{
    die();
}

global $NPDS_Prefix;
$wspad = rawurldecode(decrypt($pad));
$wspad = explode("#wspad#", $wspad);
   
switch($type) 
{
    case "doc":
        include "lib/html2doc.php";
        
        $htmltodoc = new HTML_TO_DOC();
        
        $row = sql_fetch_assoc(sql_query("SELECT content FROM ".$NPDS_Prefix."wspad WHERE page='".$wspad[0]."' AND member='".$wspad[1]."' AND ranq='".$wspad[2]."'"));
        
        // nettoyage des SPAN
        $tmp = preg_replace('#style="[^\"]*\"#', "", aff_langue($row['content']));
        $htmltodoc->createDoc($tmp, $wspad[0]."-".$wspad[2], true);
    break;
     
    default:
    break;
}
