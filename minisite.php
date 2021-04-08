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
use npds\auth\auth;
use npds\views\theme;
use npds\groupes\groupe;
use npds\security\hack;
use npds\media\video;


if (!function_exists('Mysql_Connexion')) {
    include('boot/bootstrap.php');
}

settype($gr_name, 'string');
settype($new_pages, 'string');

define('CITRON', 'tarteaucitron');

/**
 * [convert_ressources description]
 * @param  [type] $Xcontent [description]
 * @return [type]           [description]
 */
function convert_ressources($Xcontent)
{
    global $op;

    for ($i = 0; $i < strlen($Xcontent); $i++) 
    {
        if (strtoupper(substr($Xcontent, $i, 4)) == "src=") 
        {
            if ((strtoupper(substr($Xcontent, $i + 4, 3)) != "HTT") 
                and (strtoupper(substr($Xcontent, $i + 4, 4)) != "\"HTT")) 
            {
                $Xcontent = substr_replace($Xcontent, "src=getfile.php?att_id=$op&amp;apli=minisite&amp;att_type=&amp;att_size=&amp;att_name=", $i, 4);
            }
            $i = $i + 4;
        }
    }

    return language::aff_langue($Xcontent);
}

// Npds Two copyright ... don't remove !
$copyright = '<span class="blog_sname">' . $sitename . '</span>&nbsp;<span class="blog_npds">NPDS&nbsp;HUB-BLOG&nbsp;<a href="http://www.npds.org">NPDS</a></span>';

// Troll Control for security
$affich = false;
if (($op != '') and ($op)) 
{
    if (
        preg_match('#^[a-z0-9_\.-]#i', $op)
        and !stristr($op, ".*://")
        and !stristr($op, "..")
        and !stristr($op, "../")
        and !stristr($op, "script")
        and !stristr($op, "cookie")
        and !stristr($op, "iframe")
        and !stristr($op, "applet")
        and !stristr($op, "object")
        and !stristr($op, "meta")
    ) 
    {
        global $user, $super_admintest;

        if ($super_admintest)
        {
            $adminblog = true;
        }
        else
        {
            $adminblog = false;
        }

        $dir = "storage/users_private/$op/mns/";

        if (dirname($op) != 'groupe') 
        {
            // single user
            $userdata = auth::get_userdata($op);
            if ($userdata['mns'] == true) 
            {
                $affich = true;

                if (stristr($userdata['user_avatar'], "users_private"))
                {
                    $direktori = '';
                }
                else 
                {
                    global $theme;
                    $direktori = 'assets/images/forum/avatar/';
                    if (function_exists("theme_image")) 
                    {
                        if (theme::theme_image("forum/avatar/blank.gif"))
                        {
                            $direktori = "themes/$theme/images/forum/avatar/";
                        }
                    }
                }
                $avatar_mns = $direktori . $userdata['user_avatar'];
            }

            $userX = base64_decode($user);
            $userdataX = explode(':', $userX);

            if (array_key_exists(1, $userdataX)) 
            {
                if ($userdataX[1] == $op)
                {
                    $adminblog = true;
                }
            }
        } 
        else 
        {
            // groupe
            if (is_dir($dir)) 
            {
                $affich = true;
                $avatar_mns = 'storage/users_private/' . $op . '/groupe.png';
                
                function adm_translate()
                {
                }

                $gX = groupe::liste_group();

                foreach ($gX as $g_id => $g_name) 
                {
                    if ($g_id == basename($op)) 
                    {
                        $gr_name = $g_name;
                    }
                }
            }

            $tabgp = groupe::valid_group($user);
            if (is_array($tabgp)) 
            {
                foreach ($tabgp as $auto) 
                {
                    if ($auto == basename($op))
                    {
                        $adminblog = true;
                    }
                }
            }
        }
    }
}

if ($affich) 
{
    $fic = $dir . 'index.html';

    if (file_exists($fic)) 
    {
        $Titlesitename = "Minisite - $op";
        $nuke_url = $nuke_url . '/minisite.php?op=' . $op;

        include("config/meta.php");

        echo '
            <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />
            <script type="text/javascript" src="assets/js/jquery.min.js"></script>
            <script type="text/javascript" src="assets/shared/bootstrap/dist/js/bootstrap.bundle.min.js"></script>';
        echo '<style type="text/css">';
        
        readfile($dir . "style.css");
        
        echo '</style>';

        if (defined('CITRON')) 
        {
            echo '
            <script type="text/javascript"> var tarteaucitronForceLanguage = "' . language::language_iso(1, '', '') . '"; </script>
            <script type="text/javascript" src="assets/shared/tarteaucitron/tarteaucitron.js"></script>
            <script type="text/javascript">
                //<![CDATA[
                    tarteaucitron.init({
                        "privacyUrl": "", /* Privacy policy url */
                        "hashtag": "#tarteaucitron", /* Ouverture automatique du panel avec le hashtag */
                        "cookieName": "tartaucitron", /* Cookie name */
                        "orientation": "top", /* le bandeau doit être en haut (top) ou en bas (bottom) ? */
                        "showAlertSmall": true, /* afficher le petit bandeau en bas à droite ? */
                        "cookieslist": true, /* Afficher la liste des cookies installés ? */
                        "adblocker": false, /* Afficher un message si un adblocker est détecté */
                        "AcceptAllCta" : true, /* Show the accept all button when highPrivacy on */
                        "highPrivacy": false, /* désactiver le consentement implicite (en naviguant) ? */
                        "handleBrowserDNTRequest": false, /* If Do Not Track == 1, disallow all */
                        "removeCredit": true, /* supprimer le lien vers la source ? */
                        "moreInfoLink": true, /* Show more info link */
                        "useExternalCss": false, /* If false, the tarteaucitron.css file will be loaded */
                        "cookieDomain": "" /* Nom de domaine sur lequel sera posé le cookie - pour les multisites / sous-domaines - Facultatif */
                    });
                //]]
            </script>';
        }

        $Xcontent = '
        </head>
        <body>';

        $fp = fopen($fic, "r");
        if (filesize($fic) > 0)
        {
            $Xcontent .= fread($fp, filesize($fic));
        }

        fclose($fp);

        //compteur
        $compteur = $dir . "compteur.txt";
        if (!file_exists($compteur)) 
        {
            $fp = fopen($compteur, "w");
            fwrite($fp, "1");
            fclose($fp);
        } 
        else 
        {
            $cpt = file($compteur);
            $cpt = $cpt[0] + 1;
            $fp = fopen($compteur, "w");
            fwrite($fp, $cpt);
            fclose($fp);
        }

        // Analyse et convertion des liens et images, blog, header, footer ...
        if (strstr($Xcontent, '!blog_page!')) 
        {
            $perpage = substr($Xcontent, strpos($Xcontent, "!blog_page!", 0) + 11, 2);
        } 
        else 
        {
            $perpage = 4;
        }

        if (strstr($Xcontent, '!blog!')) 
        {
            include("modules/blog/readnews.php");

            settype($startpage, 'integer');
            settype($perpage, 'integer');
            settype($action, 'string');

            $content = readnews($dir, $op, $perpage, $startpage, $action, $adminblog);

            if (strstr($content, '!l_new_pages!')) 
            {
               $new_pages = substr($content, strpos($content, "!l_new_pages!") + 13);
               $content = substr($content, 0, strpos($content, "!l_new_pages!"));
            }
        }

        $Hcontent = '';
        if (strstr($Xcontent, '!l_header!')) 
        {
            $l_fic = $dir . 'header.html';
            if (file_exists($l_fic)) 
            {
                $fp = fopen($l_fic, 'r');
                if (filesize($l_fic) > 0)
                {
                    $Hcontent = convert_ressources(fread($fp, filesize($l_fic)));
                }

                fclose($fp);
            }
        }

        $Fcontent = '';
        if (strstr($Xcontent, '!l_footer!')) 
        {
            $l_fic = $dir . 'footer.html';
            if (file_exists($l_fic)) 
            {
                $fp = fopen($l_fic, 'r');
                if (filesize($l_fic) > 0)
                {
                    $Fcontent = convert_ressources(fread($fp, filesize($l_fic)));
                }

                fclose($fp);
            }
        }

        if (($adminblog) and (strstr($Xcontent, '!l_blog_ajouter!')))
        {
            $blog_ajouter = '!l_blog_ajouterOK!';
        }
        else
        {
            $blog_ajouter = '';            
        }

        $Xcontent = convert_ressources($Xcontent);
         
        // Meta-lang et removehack local
        $MNS_METALANG_words = array(
            "'!l_header!'i"           => "$Hcontent",
            "'!l_footer!'i"           => "$Fcontent",
            "'!blog_page!$perpage'i"  => '',
            "'!l_compteur!'i"         => $cpt,
            "'!l_new_pages!'i"        => $new_pages,
            "'!l_blog_ajouter!'i"     => $blog_ajouter,
            "'!blog!'i"               => $content,
            "'!copyright!'i"          => $copyright,
            "'!avatar!'i"             => $avatar_mns,
            "'!id_mns!'i"             => $op,
            "'!gr_name!'i"            => language::aff_langue($gr_name)
        );

        $Xcontent = preg_replace(
            array_keys($MNS_METALANG_words), 
            array_values($MNS_METALANG_words), 
            $Xcontent
        );

        $Xcontent = metalang::meta_lang(hack::MNSremove($Xcontent));
         
        //applique aff_video que sur la partie affichage
        $rupt = strpos($Xcontent, '#v_yt#');
        
        /*
        $a = substr($Xcontent, 0, $rupt);
        $b = aff_video_yt(substr($Xcontent, $rupt+6));
        */

        //echo $a.$b;         
        echo substr($Xcontent, 0, $rupt);
        echo video::aff_video_yt(substr($Xcontent, $rupt + 6));

        if ($adminblog)
        {
            echo '
            <script type="text/javascript">
                //<![CDATA[
                    $(".modal-body").load("modules/blog/matrice/readme.' . $language . '.txt"
                        , function(dataaide, textStatus, jqxhr) {
                            $("#aide_mns").html(dataaide.replace(/(\r\n|\n\r|\r|\n)/g, "<br />"));
                    });
                //]]>
            </script>';
        }

        if (defined('CITRON')) 
        {
            echo '
            <script type="text/javascript">
                //<![CDATA[
                    (tarteaucitron.job = tarteaucitron.job || []).push("vimeo");
                    (tarteaucitron.job = tarteaucitron.job || []).push("youtube");
                    (tarteaucitron.job = tarteaucitron.job || []).push("dailymotion");
                    tarteaucitron.user.gtagUa = "UA-6423983-2";
                    tarteaucitron.user.gtagMore = function () { /* add here your optionnal gtag() */ };
                    (tarteaucitron.job = tarteaucitron.job || []).push("gtag");
                //]]
            </script>';
        }
         
        echo '
                <script type="text/javascript" src="assets/js/npds_adapt.js"></script>
            </body>
        </html>';
    }
}
