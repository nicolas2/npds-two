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
use npds\assets\css;
use npds\language\language;
use npds\security\hack;
use npds\editeur\tiny;
use npds\views\theme;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

settype($m_keywords, 'string');
settype($m_description, 'string');

$skin = '';

function head($tiny_mce_init, $css_pages_ref, $css, $tmp_theme, $skin, $js, $m_description, $m_keywords) 
{
    global $slogan, $Titlesitename, $banners, $Default_Theme, $theme, $gzhandler, $language, $topic, $hlpfile, $user, $hr, $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5, $bgcolor6, $textcolor1, $textcolor2, $long_chain, $bargif, $theme_width, $bloc_width, $page_width;
   
    settype($m_keywords, 'string');
    settype($m_description, 'string');
   
    if ($gzhandler == 1)
    { 
        ob_start("ob_gzhandler");
    }

    include("themes/$tmp_theme/theme.php");

    // Meta
    if (file_exists("config/meta.php")) 
    {
        $meta_op = '';
        include ("config/meta.php");
    }

    // Favicon
    if (file_exists("themes/$tmp_theme/images/favicon.ico"))
    {
        $favico = "themes/$tmp_theme/images/favicon.ico";
    }
    else
    {
        $favico = 'assets/images/favicon.ico';
    }
   
    echo '<link rel="shortcut icon" href="'.$favico.'" type="image/x-icon" />';

    // Syndication RSS & autres
    global $sitename, $nuke_url, $REQUEST_URI;

    // Canonical
    $uri = $REQUEST_URI;
    $drname = dirname($uri);
    if ($drname == '.')
    {
        $uri = $nuke_url.'/'.$uri;
    }
    elseif($drname == '/')
    {
        $uri = $nuke_url.$uri;
    }
    else
    {
        $uri = 'http://'.$_SERVER['SERVER_NAME'].$uri;
    }

    echo '<link rel="canonical" href="'.str_replace('&','&amp;',str_replace('&amp;','&',$uri)).'" />';

    // humans.txt
    if (file_exists("humans.txt"))
    {
        echo '<link type="text/plain" rel="author" href="'.$nuke_url.'/humans.txt" />';
    }

    echo '
    <link href="backend.php?op=RSS0.91" title="'.$sitename.' - RSS 0.91" rel="alternate" type="text/xml" />
    <link href="backend.php?op=RSS1.0" title="'.$sitename.' - RSS 1.0" rel="alternate" type="text/xml" />
    <link href="backend.php?op=RSS2.0" title="'.$sitename.' - RSS 2.0" rel="alternate" type="text/xml" />
    <link href="backend.php?op=ATOM" title="'.$sitename.' - ATOM" rel="alternate" type="application/atom+xml" />';

    // Tiny_mce
    if ($tiny_mce_init)
    {
        echo tiny::aff_editeur("tiny_mce", "begin");
    }

    // include externe JAVASCRIPT file from 
    // lib/include or themes/.../include for 
    // functions, codes in the <body onload="..." event...
    $body_onloadH = '
    <script type="text/javascript">
        //<![CDATA[
            function init() {';
   
    $body_onloadF = '
            }
        //]]>
    </script>';
   
    if (file_exists("themes/include/body_onload.inc")) 
    {
        echo $body_onloadH;
        include ("themes/include/body_onload.inc");
        echo $body_onloadF;
    }

    if (file_exists("themes/$tmp_theme/include/body_onload.inc")) 
    {
        echo $body_onloadH;
        include ("themes/$tmp_theme/include/body_onload.inc");
        echo $body_onloadF;
    }

    // include externe file from lib/include or 
    // themes/.../include for functions, codes ... - skin motor
    if (file_exists("themes/include/header_head.inc")) 
    {
        ob_start();
            include "themes/include/header_head.inc";
            $hH = ob_get_contents();
        ob_end_clean();

        if ($skin != '' and substr($tmp_theme, -3) == "_sk") 
        {
            $hH = str_replace(
                'assets/shared/bootstrap/dist/css/bootstrap.min.css',
                'themes/_skins/'.$skin.'/bootstrap.min.css', $hH
            );
            $hH = str_replace(
                'assets/shared/bootstrap/dist/css/extra.css',
                'themes/_skins/'.$skin.'/extra.css', $hH
            );
        }
        echo $hH;
    }

    if (file_exists("themes/$tmp_theme/include/header_head.inc")) 
    {
        include ("themes/$tmp_theme/include/header_head.inc");
    }

    echo css::import_css($tmp_theme, $language, '', $css_pages_ref, $css);

    // Mod by Jireck - Chargeur de JS via PAGES.PHP
    if ($js) 
    {
        if (is_array($js)) 
        {
            foreach ($js as $k => $tab_js) 
            {
                if (stristr($tab_js, 'http://') || stristr($tab_js, 'https://'))
                {
                    echo '<script type="text/javascript" src="'.$tab_js.'"></script>';
                }
                else 
                {
                    if (file_exists("themes/$tmp_theme/js/$tab_js") and ($tab_js != ''))
                    {
                        echo '<script type="text/javascript" src="themes/'.$tmp_theme.'/js/'.$tab_js.'"></script>';
                    } 
                    elseif (file_exists("$tab_js") and ($tab_js != "")) 
                    {
                        echo '<script type="text/javascript" src="'.$tab_js.'"></script>';
                    }
                }
            }
        } 
        else 
        {
            if (file_exists("themes/$tmp_theme/js/$js")) 
            {
                echo '<script type="text/javascript" src="themes/'.$tmp_theme.'/js/'.$js.'"></script>';
            } 
            elseif (file_exists("$js")) 
            {
                echo '<script type="text/javascript" src="'.$js.'"></script>';
            }
        }
    }

    echo '
    </head>';
   
    include("themes/$tmp_theme/header.php");
}

$header = 1;
   
// include externe file from lib/include for functions, codes ...
if (file_exists("themes/include/header_before.inc")) 
{
    include ("themes/include/header_before.inc");
}

// take the right theme location !
// nouvel version de la gestion des Themes et Skins
list($theme, $skin, $tmp_theme) = theme::getUsetOrDefaultThemeAndSkin();
   
include('pages.php');

head($tiny_mce_init, $css_pages_ref, $css, $tmp_theme, $skin, $js, $m_description,$m_keywords);

global $httpref, $nuke_url, $httprefmax, $admin, $NPDS_Prefix; 
if ($httpref == 1) 
{
    $referer = htmlentities(strip_tags(hack::remove(getenv("HTTP_REFERER"))), ENT_QUOTES,cur_charset);
    
    if ($referer != '' 
        and !strstr($referer, "unknown") 
        and !stristr($referer, $_SERVER['SERVER_NAME'])) 
    {
        sql_query("INSERT INTO ".$NPDS_Prefix."referer VALUES (NULL, '$referer')");
    }
}

include("counter.php");

// include externe file from lib/include for functions, codes ...
if (file_exists("themes/include/header_after.inc")) 
{
    include ("themes/include/header_after.inc");
}
