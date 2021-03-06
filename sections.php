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
use npds\cache\cacheManager;
use npds\cache\cacheEmpty;
use npds\auth\auth;
use npds\language\language;
use npds\language\metalang;
use npds\utility\code;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

/**
 * [autorisation_section description]
 * @param  [type] $userlevel [description]
 * @return [type]            [description]
 */
function autorisation_section($userlevel) 
{
    $okprint = false;
    $tmp_auto = explode(',', $userlevel);

    foreach($tmp_auto as $userlevel)
    {
        $okprint = auth::autorisation($userlevel);
        if ($okprint) 
        {
            break;
        }

    }

    return $okprint;
}

/**
 * [listsections description]
 * @param  [type] $rubric [description]
 * @return [type]         [description]
 */
function listsections($rubric) 
{
    global $sitename, $admin, $NPDS_Prefix;

    include ('header.php');

    if (file_exists("config/sections.php"))
    {
        include ("config/sections.php");
    }

    global $SuperCache;
    if ($SuperCache) 
    {
        $cache_obj = new cacheManager();
        $cache_obj->startCachingPage();
    } 
    else
    {
        $cache_obj = new cacheEmpty();
    }
   
    if (($cache_obj->genereting_output == 1) 
        or ($cache_obj->genereting_output == -1) 
        or (!$SuperCache)) 
    {
        settype($rubric, 'integer');
        settype($nb_r, 'integer');

        if ($rubric)
        {
            $sqladd = "AND rubid='".$rubric."'";
        }
        else 
        {
            $sqladd = '';
        }

        if ($admin) 
        {
            $result = sql_query("SELECT rubid, rubname, intro FROM ".$NPDS_Prefix."rubriques WHERE rubname<>'Divers' AND rubname<>'Presse-papiers' $sqladd ORDER BY ordre");
            $nb_r = sql_num_rows($result);
        } 
        else 
        {
            $result = sql_query("SELECT rubid, rubname, intro FROM ".$NPDS_Prefix."rubriques WHERE enligne='1' AND rubname<>'Divers' AND rubname<>'Presse-papiers' $sqladd ORDER BY ordre");
            $nb_r = sql_num_rows($result);
        }

        $aff = '';
      
        if ($rubric)
        {
            $aff .= '<span class="lead"><a href="sections.php" title="'.translate("Retour ?? l'index des rubriques").'" data-toggle="tooltip">Index</a></span><hr />';
        }
      
        $aff .= '
        <h2>'.translate("Rubriques").'<span class="float-right badge badge-secondary">'.$nb_r.'</span></h2>';
      
        if (sql_num_rows($result) > 0) 
        {
            while (list($rubid, $rubname, $intro) = sql_fetch_row($result)) 
            {
                $result2 = sql_query("SELECT secid, secname, image, userlevel, intro FROM ".$NPDS_Prefix."sections WHERE rubid='$rubid' ORDER BY ordre");
                $nb_section = sql_num_rows($result2);
            
                $aff.='
                <hr />
                <h3>';

                if($nb_section !== 0)
                {
                    $aff .= '
                    <a href="#" class="arrow-toggle text-primary" data-toggle="collapse" data-target="#rub-'.$rubid.'" ><i class="toggle-icon fa fa-caret-down"></i></a>';
                }            
                else
                {
                    $aff .= '<i class="fa fa-caret-down text-muted invisible "></i>';
                }
            
                $aff .= '
                <a class="ml-2" href="sections.php?rubric='.$rubid.'">'.language::aff_langue($rubname).'</a><span class=" float-right">#NEW#<span class="badge badge-secondary" title="'.translate("Sous-rubrique").'" data-toggle="tooltip" data-placement="left">'.$nb_section.'</span></span>
                </h3>';

                if ($intro != '')
                {
                    $aff .= '<p class="text-muted">'.language::aff_langue($intro).'</p>';
                }
            
                $aff .= '
                <div id="rub-'.$rubid.'" class="collapse" >';
            
                while (list($secid, $secname, $image, $userlevel, $intro) = sql_fetch_row($result2)) 
                {
                    $okprintLV1 = autorisation_section($userlevel);
                    $aff1 = ''; 
                    $aff2 = '';
               
                    if ($okprintLV1) 
                    {
                        $result3 = sql_query("SELECT artid, title, counter, userlevel, timestamp FROM ".$NPDS_Prefix."seccont WHERE secid='$secid' ORDER BY ordre");
                        $nb_art = sql_num_rows($result3);
                  
                        $aff .= '
                        <div class="card card-body mb-2" id="rub_'.$rubid.'sec_'.$secid.'">
                        <h4 class="mb-2">';
            
                        if($nb_art !== 0)
                        {
                            $aff .= '
                            <a href="#" class="arrow-toggle text-primary" data-toggle="collapse" data-target="#sec'.$secid.'" aria-expanded="true" aria-controls="sec'.$secid.'"><i class="toggle-icon fa fa-caret-up"></i></a>&nbsp;';
                        }
                  
                        $aff1 = language::aff_langue($secname).'<span class=" float-right">#NEW#<span class="badge badge-secondary" title="'.translate("Articles").'" data-toggle="tooltip" data-placement="left">'.$nb_art.'</span></span>';
                  
                        if ($image != '') 
                        {
                            if (file_exists("assets/images/sections/$image")) 
                            {
                                $imgtmp = "assets/images/sections/$image";
                            } 
                            else 
                            {
                                $imgtmp = $image;
                            }

                            $suffix = strtoLower(substr(strrchr(basename($image), '.'), 1 ));
                            $aff1 .= '<img class="img-fluid" src="'.$imgtmp.'" alt="'.language::aff_langue($secname).'" /><br />';
                        }

                        $aff1 .= '
                        </h4>';

                        if ($intro != '')
                        {
                            $aff1 .= '<p class="">'.language::aff_langue($intro).'</p>';
                        }
                  
                        $aff2 = '
                        <div id="sec'.$secid.'" class="collapse show">
                        <div class="">';
                  
                        $noartid = false;
                        while (list($artid, $title, $counter, $userlevel, $timestamp) = sql_fetch_row($result3)) 
                        {
                            $okprintLV2 = autorisation_section($userlevel);
                            $nouveau = '';
                     
                            if ($okprintLV2) 
                            {
                                $noartid = true;
                                $nouveau = 'oo';
                        
                                if ((time()-$timestamp) < (86400*7))
                                {
                                    $nouveau = '';
                                }
                        
                                $aff2 .= '<a href="sections.php?op=viewarticle&amp;artid='.$artid.'">'.language::aff_langue($title).'</a><span class="float-right"><small>'.translate("lu : ").' '.$counter.' '.translate("Fois").'</small>';
                        
                                if ($nouveau == '') 
                                {
                                    $aff2 .= '<i class="far fa-star ml-3 text-success"></i>';
                                    $aff1 = str_replace('#NEW#','<span class="mr-2 badge badge-success animated faa-flash">N</span>',$aff1);
                                    $aff = str_replace('#NEW#','<span class="mr-2 badge badge-success animated faa-flash">N</span>',$aff);
                                }

                                $aff2 .= '</span><br />';
                            }
                        }

                        $aff = str_replace('#NEW#', '', $aff);
                        $aff1 = str_replace('#NEW#', '', $aff1);
                        $aff2 .= '
                                </div>
                            </div>
                        </div>';

                    }
                    $aff .= $aff1.$aff2;
                }
                $aff .= '</div>';
            }
        }

        echo $aff; //la sortie doit se faire en html !!!
      
        /*
        if ($rubric)
        {
            echo '<a class="btn btn-secondary" href="sections.php">'.translate("Return to Sections Index").'</a>';
        }
        */
        sql_free_result($result);
    }

    if ($SuperCache)
    {
        $cache_obj->endCachingPage();
    }

    include ('footer.php');
}

/**
 * [listarticles description]
 * @param  [type] $secid [description]
 * @return [type]        [description]
 */
function listarticles($secid) 
{
    global $user, $prev, $NPDS_Prefix;

    if (file_exists("config/sections.php"))
    {
        include ("config/sections.php");
    }

    $result = sql_query("SELECT secname, rubid, image, intro, userlevel FROM ".$NPDS_Prefix."sections WHERE secid='$secid'");
    list($secname, $rubid, $image, $intro, $userlevel) = sql_fetch_row($result);
   
    list($rubname) = sql_fetch_row(sql_query("SELECT rubname FROM ".$NPDS_Prefix."rubriques WHERE rubid='$rubid'"));
   
    if ($sections_chemin == 1)
    {
        $chemin = '<span class="lead"><a href="sections.php" title="'.translate("Retour ?? l'index des rubriques").'" data-toggle="tooltip">Index</a>&nbsp;/&nbsp;<a href="sections.php?rubric='.$rubid.'">'.language::aff_langue($rubname).'</a></span>'; 
    }
   
    $title = language::aff_langue($secname);
   
    include ('header.php');

    global $SuperCache;
    if ($SuperCache) 
    {
        $cache_obj = new cacheManager();
        $cache_obj->startCachingPage();
    } 
    else
    {
        $cache_obj = new cacheEmpty();
    }
   
    if (($cache_obj->genereting_output == 1) 
        or ($cache_obj->genereting_output == -1) 
        or (!$SuperCache)) 
    {
        $okprint1 = autorisation_section($userlevel);
      
        if ($okprint1) 
        {
            $result = sql_query("SELECT artid, secid, title, content, userlevel, counter, timestamp FROM ".$NPDS_Prefix."seccont WHERE secid='$secid' ORDER BY ordre");
            $nb_art = sql_num_rows($result);
         
            if ($prev == 1) 
            {
                echo '<input class="btn btn-primary" type="button" value="'.translate("Retour ?? l'administration").'" onclick="javascript:history.back()" /><br /><br />';
            }
         
            if (function_exists("themesection_title")) 
            {
                themesection_title($title);
            } 
            else 
            {
                echo $chemin.'
                <hr />
                <h3 class="mb-3">'.$title.'<span class="float-right"><span class="badge badge-secondary" title="'.translate("Articles").'" data-toggle="tooltip" data-placement="left">'.$nb_art.'</span></h3>';
            }

            if ($intro != '')
            {
                echo language::aff_langue($intro);
            }
         
            if ($image != '') 
            {
                if (file_exists("assets/images/sections/$image")) 
                {
                    $imgtmp = "assets/images/sections/$image";
                }
                else 
                {
                    $imgtmp = $image;
                }

                $suffix = strtoLower(substr(strrchr(basename($image), '.'), 1 ));
               
                echo '
                <p class="text-center"><img class="img-fluid" src="'.$imgtmp.'" alt="" /></p>';
            } 

            echo '
            <p>'.translate("Voici les articles publi??s dans cette rubrique.").'</p>
            <div class="card card-body mb-3">';

            while (list($artid, $secid, $title, $content, $userlevel, $counter, $timestamp) = sql_fetch_row($result)) 
            {
                $okprint2 = autorisation_section($userlevel);
           
                if ($okprint2) 
                {
                    $nouveau = 'oo';
               
                    if ((time()-$timestamp) < (86400*7)) 
                    {
                        $nouveau = '';
                    }

                    echo '
                    <div class="mb-1">
                    <a href="sections.php?op=viewarticle&amp;artid='.$artid.'">'.language::aff_langue($title).'</a><small>'.translate("lu : ").' '.$counter.' '.translate("Fois").'</small><span class="float-right"><a href="sections.php?op=printpage&amp;artid='.$artid.'" title="'.translate("Page sp??ciale pour impression").'" data-toggle="tooltip" data-placement="left"><i class="fa fa-print fa-lg"></i></a></span>';
               
                    if ($nouveau == '') 
                    {
                        echo '&nbsp;<i class="fa fa-star-o text-success"></i>';
                    }

                    echo '
                    </div>';
                }
            }

            echo '
            </div>';

            /*
            echo '
            <a class="btn btn-secondary" href="sections.php">'.translate("Return to Sections Index").'</a>';
            */
        } 
        else
        {
            redirect_url("sections.php");
        }
      
        sql_free_result($result);
    }

    if ($SuperCache)
    {
        $cache_obj->endCachingPage();
    }
   
    include ('footer.php');
}

/**
 * [viewarticle description]
 * @param  [type] $artid [description]
 * @param  [type] $page  [description]
 * @return [type]        [description]
 */
function viewarticle($artid, $page) 
{
    global $NPDS_Prefix, $prev, $user, $numpage;

    $numpage = $page;

    if (file_exists("config/sections.php"))
    {
        include ("config/sections.php");
    }

    if ($page == '')
    {
        sql_query("UPDATE ".$NPDS_Prefix."seccont SET counter=counter+1 WHERE artid='$artid'");
    }

    $result_S = sql_query("SELECT artid, secid, title, content, counter, userlevel FROM ".$NPDS_Prefix."seccont WHERE artid='$artid'");
    list($artid, $secid, $title, $Xcontent, $counter, $userlevel) = sql_fetch_row($result_S);
   
    list($secid, $secname, $rubid) = sql_fetch_row(sql_query("SELECT secid, secname, rubid FROM ".$NPDS_Prefix."sections WHERE secid='$secid'"));
   
    list($rubname) = sql_fetch_row(sql_query("SELECT rubname FROM ".$NPDS_Prefix."rubriques WHERE rubid='$rubid'"));
   
    $tmp_auto = explode(',', $userlevel);
   
    foreach($tmp_auto as $userlevel)
    {
        $okprint = autorisation_section($userlevel);
        if ($okprint)
        { 
            break;
        }
    }

    if ($okprint) 
    {
        $pindex = substr(substr($page, 5), 0, -1);
         
        if ($pindex != '')
        {
            $pindex = translate("Page").' '.$pindex;
        }
         
        if ($sections_chemin == 1)
        {
            $chemin = '<span class="lead"><a href="sections.php">Index</a>&nbsp;/&nbsp;<a href="sections.php?rubric='.$rubid.'">'.language::aff_langue($rubname).'</a>&nbsp;/&nbsp;<a href="sections.php?op=listarticles&amp;secid='.$secid.'">'.language::aff_langue($secname).'</a></span>'; 
        }
         
        $title = language::aff_langue($title);
         
        include("header.php");

        global $SuperCache;
        if ($SuperCache) 
        {
            $cache_obj = new cacheManager();
            $cache_obj->startCachingPage();
        } 
        else
        {
            $cache_obj = new cacheEmpty();
        }
      
        if (($cache_obj->genereting_output == 1) 
            or ($cache_obj->genereting_output == -1) 
            or (!$SuperCache)) 
        {
            $words = sizeof(explode(' ', $Xcontent));
         
            if ($prev == 1) 
            {
                echo '<input class="btn btn-secondary" type="button" value="'.translate("Retour ?? l'administration").'" onclick="javascript:history.back()" /><br /><br />';
            }
         
            if (function_exists("themesection_title")) 
            {
                themesection_title($title);
            } 
            else 
            {
                echo $chemin.'
                <hr />
                <h3 class="mb-2">'.$title.'<span class="small text-muted"> - '.$pindex.'</span></h3>
                <p><span class="text-muted small">('.$words.' '.translate("mots dans ce texte )").'&nbsp;-&nbsp;'.translate("lu : ").' '.$counter.' '.translate("Fois").'</span><span class="float-right"><a href="sections.php?op=printpage&amp;artid='.$artid.'" title="'.translate("Page sp??ciale pour impression").'" data-toggle="tooltip" data-placement="left"><i class="fa fa-print fa-lg ml-3"></i></a></span></p><hr />';
                preg_match_all('#\[page.*\]#', $Xcontent, $rs);
            } 
            
            $ndepages = count($rs[0]);

            if ($page != '') 
            {
                $Xcontent = substr($Xcontent, strpos($Xcontent, $page)+strlen($page)); 
                $multipage = true;
            } 
            else 
            {
                $multipage = false;
            }

            $pos_page = strpos($Xcontent, '[page');
            $longueur = mb_strpos($Xcontent,']', $pos_page, 'iso-8859-1')-$pos_page+1; 
         
            if ($pos_page) 
            {
                $pageS = substr($Xcontent, $pos_page, $longueur);
                $Xcontent = substr($Xcontent ,0, $pos_page);
            
                $Xcontent .= '
                <nav class="d-flex mt-3">
                    <ul class="mx-auto pagination pagination-sm">
                        <li class="page-item disabled"><a class="page-link" href="#">'.$ndepages.' pages</a></li>';
            
                if($pageS !== '[page0]')
                {
                    $Xcontent .= '
                    <li class="page-item"><a class="page-link" href="sections.php?op=viewarticle&amp;artid='.$artid.'">'.translate("D??but de l'article").'</a></li>';
                }
            
                $Xcontent .= '
                        <li class="page-item active"><a class="page-link">'.preg_replace('#\[(page)(.*)(\])#', '\1 \2', $pageS).'</a></li>
                        <li class="page-item"><a class="page-link" href="sections.php?op=viewarticle&amp;artid='.$artid.'&amp;page='.$pageS.'" >'.translate("Page suivante").'</a></li>
                    </ul>
                </nav>';
            } 
            else if ($multipage) 
            {
                $Xcontent .= '
                <nav class="d-flex mt-3">
                    <ul class="mx-auto pagination pagination-sm">
                        <li class="page-item"><a class="page-link" href="sections.php?op=viewarticle&amp;artid='.$artid.'&amp;page=[page0]">'.translate("D??but de l'article").'</a></li>
                    </ul>
                </nav>';
            }

            $Xcontent = code::aff_code(language::aff_langue($Xcontent));
            echo '<div id="art_sect">'.metalang::meta_lang($Xcontent).'</div>';

            $artidtempo = $artid;
            if ($rubname != 'Divers') 
            {
                /*
                echo '<hr /><p><a class="btn btn-secondary" href="sections.php">'.translate("Return to Sections Index").'</a></p>'; 

                echo '<h4>***<strong>'.translate("Back to chapter:").'</strong></h4>';
                echo '<ul class="list-group"><li class="list-group-item"><a href="sections.php?op=listarticles&amp;secid='.$secid.'">'.aff_langue($secname).'</a></li></ul>';
                */

                $result3 = sql_query("SELECT artid, secid, title, userlevel FROM ".$NPDS_Prefix."seccont WHERE (artid<>'$artid' AND secid='$secid') ORDER BY ordre");
                $nb_article = sql_num_rows($result3);
            
                if ($nb_article > 0) 
                {
                    echo '
                    <h4 class="my-3">'.translate("Autres publications de la sous-rubrique").'<span class="badge badge-secondary float-right">'.$nb_article.'</span></h4>
                    <ul class="list-group">';
               
                    while (list($artid, $secid, $title, $userlevel) = sql_fetch_row($result3)) 
                    {
                        $okprint2 = autorisation_section($userlevel);
                        if ($okprint2) 
                        {
                            echo '
                            <li class="list-group-item list-group-item-action"><a href="sections.php?op=viewarticle&amp;artid='.$artid.'">'.language::aff_langue($title).'</a></li>';
                        }
                    }

                    echo '
                    </ul>';
                }
            }

            $artid = $artidtempo;
            $resultconnexe = sql_query("SELECT id2 FROM ".$NPDS_Prefix."compatsujet WHERE id1='$artid'");
         
            if (sql_num_rows($resultconnexe) > 0) 
            {
                echo '
                <h4 class="my-3">'.translate("Cela pourrait vous int??resser").'<span class="badge badge-secondary float-right">'.sql_num_rows($resultconnexe).'</span></h4>
                <ul class="list-group">';
            
                while(list($connexe) = sql_fetch_row($resultconnexe)) 
                {
                    $resultpdtcompat = sql_query("SELECT artid, title, userlevel FROM ".$NPDS_Prefix."seccont WHERE artid='$connexe'");
                    list($artid2, $title, $userlevel) = sql_fetch_row($resultpdtcompat);
               
                    $okprint2 = autorisation_section($userlevel);
                    if ($okprint2) 
                    {
                        echo '
                        <li class="list-group-item list-group-item-action"><a href="sections.php?op=viewarticle&amp;artid='.$artid2.'">'.language::aff_langue($title).'</a></li>';
                    }
                }

                echo '
                </ul>';
            }
         
        }

        sql_free_result($result_S);
      
        if ($SuperCache)
        {
            $cache_obj->endCachingPage();
        }
      
        include ('footer.php');
    } 
    else
    {
        header("Location: sections.php");
    }
}

/**
 * [PrintSecPage description]
 * @param [type] $artid [description]
 */
function PrintSecPage($artid)
{
    global $NPDS_Prefix, $user, $cookie, $theme, $Default_Theme, $site_logo, $sitename, $nuke_url, $language, $site_font, $Titlesitename;

    include("config/meta.php");

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

    echo '
        <link rel="stylesheet" href="assets/shared/bootstrap/dist/css/bootstrap.min.css" />';

    //echo css::import_css($tmp_theme, $language, $site_font, '','');
    echo '
        </head>
        <body>
        <div id="print_sect" max-width="640" class="container p-1 n-hyphenate">
        <p class="text-center">';
   
    $pos = strpos($site_logo, "/");
   
    if ($pos)
    {
        echo '<img src="'.$site_logo.'" alt="" />';
    }
    else
    {
        echo '<img src="assets/images/'.$site_logo.'" alt="" />';
    }

    $result = sql_query("SELECT title, content FROM ".$NPDS_Prefix."seccont WHERE artid='$artid'");
    list($title, $content) = sql_fetch_row($result);

    echo '<br /><br /><strong>'.language::aff_langue($title).'</strong><br /><br /></p>';
   
    $content = code::aff_code(language::aff_langue($content));
    $pos_page = strpos($content,"[page");
   
    if ($pos_page) 
    {
        $conten = str_replace("[page", str_repeat("-", 50)."&nbsp;[page", $content);
    }

    echo metalang::meta_lang($content);
   
    echo '
        <hr />
        <p class="text-center">
        '.translate("Cet article provient de").' '.$sitename.'<br /><br />
        '.translate("L'url pour cet article est : ").'
        <a href="'.$nuke_url.'/sections.php?op=viewarticle&amp;artid='.$artid.'">'.$nuke_url.'/sections.php?op=viewarticle&amp;artid='.$artid.'</a>
        </p>
        </div>
        <script type="text/javascript" src="assets/js/jquery.min.js"></script>
        <script type="text/javascript" src="assets/js/npds_adapt.js"></script>
    </body>
    </html>';
}

/**
 * [verif_aff description]
 * @param  [type] $artid [description]
 * @return [type]        [description]
 */
function verif_aff($artid) 
{
    global $NPDS_Prefix;

    $result = sql_query("SELECT secid FROM ".$NPDS_Prefix."seccont WHERE artid='$artid'");
    list($secid) = sql_fetch_row($result);
   
    $result = sql_query("SELECT userlevel FROM ".$NPDS_Prefix."sections WHERE secid='$secid'");
    list($userlevel) = sql_fetch_row($result);
   
    $okprint = false;
    $okprint = autorisation_section($userlevel);
   
    return ($okprint);
}

settype($op, 'string');

switch ($op) 
{
    case 'viewarticle':
        if (verif_aff($artid)) 
        {
            settype($page, 'string');
            viewarticle($artid, $page);
        } 
        else
        {
            header ('location: sections.php');
        }
    break;

    case 'listarticles':
        listarticles($secid);
    break;

    case 'printpage':
        if (verif_aff($artid))
        {
            PrintSecPage($artid);
        }
        else
        {
            header ('location: sections.php');
        }
    break;
   
    default:
        settype($rubric, 'string');
        listsections($rubric);
    break;
}
