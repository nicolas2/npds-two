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

if (isset($user)) 
{
    if ($cookie[9] == '') 
    {
        $cookie[9] = $Default_Theme;
    }
      
    if (isset($theme)) 
    {
        $cookie[9] = $theme;
    }

    $tmp_theme = $cookie[9];
      
    if (!$file = @opendir("themes/$cookie[9]")) 
    {
        $tmp_theme = $Default_Theme;
    }
} 
else 
{
    $tmp_theme = $Default_Theme;
}

include('config/meta.php');
global $site_font;
   
echo '<link rel="stylesheet" href="themes/_skins/default/bootstrap.min.css">';
echo import_css($tmp_theme, $language, $site_font, '', '');
   
include('assets/formhelp.java.php');
   
echo '
    </head>
    <body class="p-2">
    '.putitems_more().'
    </body>
</html>';
