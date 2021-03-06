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

if($_REQUEST["them"]) 
{
    $depotskin = 'themes/_skins/';
    $response = array();
    $skins = array();
    $handle = opendir('themes/_skins');
   
    while (false !== ($file = readdir($handle))) 
    {
        if ( ($file[0] !== '_') 
            and (!strstr($file, '.')) 
            and (!strstr($file, 'assets')) 
            and (!strstr($file, 'fonts')) ) 
        {
            $skins[] = array(
                'name'           => $file, 
                'description'    => '', 
                'thumbnail'      => $depotskin.$file.'/thumbnail',
                'preview'        => $depotskin.$file.'/',
                'css'            => $depotskin.$file.'/bootstrap.css',
                'cssMin'         => $depotskin.$file.'/bootstrap.min.css',
                'cssxtra'        => $depotskin.$file.'/extra.css',
                'scss'           => $depotskin.$file.'/_bootswatch.scss',
                'scssVariables'  => $depotskin.$file.'/_variables.scss'
            );
        }
    }

    $response['skins'] = $skins;
    closedir($handle);

    $fp = fopen('themes/_skins/skins.json', 'w');
    fwrite($fp, stripslashes(json_encode($response)));
    fclose($fp);

    $nameskin = strtolower($_REQUEST['them']);

    $baseskin = '#<link id="bsth" rel="stylesheet" href="assets/shared/bootstrap/dist/css/bootstrap.min.css" />#';
    $newskin = '<link id="bsth" rel="stylesheet" href="'.$depotskin.$nameskin.'/bootstrap.css" /><link rel="stylesheet" href="'.$depotskin.$nameskin.'/extra.css" />';
    $headthem = file_get_contents('themes/'.$tmp_theme.'/include/header_head.inc');
    $replskin = "\1$nameskin\3";

   $extra = '<link rel="stylesheet" href="'.$depotskin.$nameskin.'/extra.css" />';

    if (preg_match($baseskin, $headthem)) 
    {
        $newcontent = preg_replace($baseskin, $newskin.$extra, $headthem);
    } 
    else
    {
        $newcontent = preg_replace('#(<link id="bsth" rel="stylesheet" href="themes/_skins/)(.*)(/bootstrap.css" />)#', "\\1$nameskin\\3$extra", $headthem);
    }

    $file = fopen('themes/'.$tmp_theme.'/include/header_head.inc', 'w');
    $content = $newcontent;
    fwrite($file, $content);
    fclose($file);
}
