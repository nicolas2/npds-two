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


global $theme; 
$rep = false;

settype($ContainerGlobal, 'string');

if (file_exists("themes/".$theme."/views/footer.html"))
{
    $rep = $theme;
}
elseif (file_exists("themes/default/views/footer.html"))
{
    $rep = "default";
}
else {
    echo "footer.html manquant / not find !<br />";
    die();
}

if ($rep) 
{
    ob_start();
        include("themes/".$rep."/views/footer.html");
        $Xcontent = ob_get_contents();
    ob_end_clean();
    
    if ($ContainerGlobal)
    {
        $Xcontent .= $ContainerGlobal;
    }

    echo metalang::meta_lang(language::aff_langue($Xcontent));
}
