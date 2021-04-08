<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/* ===========================                                          */
/*                                                                      */
/* DYNAMIC THEME engine for NPDS                                        */
/* NPDS Copyright (c) 2002-2019 by Philippe Brunier                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
use npds\language\language;
use npds\language\metalang;

global $theme, $Start_Page; 

$rep = false;

$Start_Page = str_replace('/', '', $Start_Page);

settype($ContainerGlobal, 'string');

if (file_exists("themes/".$theme."/views/header.html"))
{
    $rep = $theme;
}
elseif (file_exists("themes/default/views/header.html"))
{
    $rep = 'default';
}
else 
{
    echo 'header.html manquant / not find !<br />';
    die();
}

if ($rep) 
{
    if (file_exists("themes/default/include/body_onload.inc") 
        or file_exists("themes/$theme/include/body_onload.inc"))
    {
        $onload_init = ' onload="init();"';
    }
    else
    {
        $onload_init = '';
    }
   
    if (!$ContainerGlobal)
    {
        echo '
        <body'.$onload_init.' class="body">';
    } 
    else 
    {
        echo '
        <body'.$onload_init.'>';
        echo $ContainerGlobal;
    }

    ob_start();
        // landing page
        if (stristr($_SERVER['REQUEST_URI'], $Start_Page) 
            and file_exists("themes/".$rep."/views/header_landing.html"))
        {
            include("themes/".$rep."/views/header_landing.html");
        }
        else
        {
            include("themes/".$rep."/views/header.html");
        }

        $Xcontent = ob_get_contents();
    ob_end_clean();
    
    echo metalang::meta_lang(language::aff_langue($Xcontent));
}