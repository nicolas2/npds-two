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
use npds\auth\auth;
use npds\cache\cacheManager;
use npds\cache\cacheEmpty;
use npds\language\language; 
use npds\pagination\pagination;
use npds\time\time;
use modules\links\support\links;


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

global $links_DB, $NPDS_Prefix;

include_once("modules/$ModPath/config/links.php");

if ($links_DB == '') 
{
    $links_DB = $NPDS_Prefix;
}


function menu() 
{
    global $ModPath, $ModStart, $links_anonaddlinklock,$op;
       
    $ad_l = '';
    $ne_l = '';
    $in_l = '';
       
    if($op == 'NewLinks') 
    {
        $ne_l = 'active'; 
    }
    else 
    {
        $ne_l = '';
    }

    if($op == 'AddLink') 
    {
        $ad_l = 'active'; 
    }
    else 
    {
        $ad_l = '';
    }

    if($op == '') 
    {
        $in_l = 'active'; 
    }
    else 
    {
        $in_l = '';
    }

    echo '
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link '.$in_l.'" href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'" >'.translate("Index").'</a></li>';

    if (auth::autorisation($links_anonaddlinklock))
    {
        echo '
        <li class="nav-item" ><a class="nav-link '.$ad_l.'" href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=AddLink" >'.translate("Ajouter").'</a></li>';
    }

    echo '
        <li class="nav-item"><a class="nav-link '.$ne_l.'" href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=NewLinks" >'.translate("Nouveaut??s").'</a></li>
        <li class="nav-item"><a class="nav-link " href="#linksearchblock">'.translate("Recherche").'</a></li>
    </ul>';
}


function SearchForm() 
{
    global $ModPath, $ModStart, $NPDS_Prefix, $links_topic;
       
    echo '
    <div class="card card-body mb-3" id="linksearchblock">
        <h3 class="mb-3">'.translate("Recherche").'</h3>
        <form action="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=search" method="post">';
       
    if ($links_topic) 
    {
        echo '
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="topicL" >'.translate("S??lectionner un sujet").'</label>
            <div class="col-sm-8">
                <select class="custom-select form-control" id="topicL" name="topicL">';
        
        $toplist = sql_query("SELECT topicid, topictext FROM ".$NPDS_Prefix."topics ORDER BY topictext");
          
        echo '<option value="">'.translate("Tous les sujets").'</option>';
          
        while (list($topicid, $topics) = sql_fetch_row($toplist)) 
        {
            echo '<option value="'.$topicid.'">'.$topics.'</option>';
        }
          
        echo '
                </select>
            </div>
        </div>';
    }
       
    echo '
            <div class="form-group row">
                <label class="col-form-label col-sm-4" for="query">'.translate("Votre requ??te").'</label>
                <div class="col-sm-8">
                    <input class="form-control" type="text" id="query" name="query" />
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-8 ml-sm-auto">
                    <button class="btn btn-primary" type="submit">'.translate("Recherche").'</button>
                </div>
            </div>
        </form>
    </div>';
}


function mainheader() 
{
    menu();
}


function autorise_mod($lid, $aff) 
{
    global $ModPath, $ModStart, $links_DB, $NPDS_Prefix, $user, $admin;
       
    if ($admin) 
    {
        $Xadmin = base64_decode($admin);
        $Xadmin = explode(':', $Xadmin);
        
        $result = sql_query("SELECT radminsuper FROM ".$NPDS_Prefix."authors WHERE aid='$Xadmin[0]'");
        list($radminsuper) = sql_fetch_row($result);
        
        // faut remettre le controle des droits probablement pour les admin 
        // qui ont le droit link ??!!
        if ($radminsuper == 1) 
        {
            if ($aff)
            {
                echo '<a href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=modifylinkrequest&amp;lid='.$lid.'&amp;author=-9" title="'.translate("Modifier").'" data-toggle="tooltip"><i class="fa fa-edit fa-lg"></i></a>';
            }
            
            return(true);
        }
    } 
    elseif ($user != '') 
    {
        global $cookie;
        
        $resultX = sql_query("SELECT submitter FROM ".$links_DB."links_links WHERE submitter='$cookie[1]' AND lid='$lid'");
        list($submitter) = sql_fetch_row($resultX);
        
        if ($submitter == $cookie[1]) 
        {
            if ($aff)
            {
                echo '<a href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=modifylinkrequest&amp;lid='.$lid.'&amp;author='.$cookie[1].'" title="'.translate("Modifier").'" data-toggle="tooltip" ><i class="fa fa-edit fa-lg"></i></a>';
            }
            return true;
        } 
        else
        {
            return false;
        }
    } 
    else
    {
        return false;
    }
}


function index() 
{
    global $ModPath, $ModStart, $links_DB, $SuperCache;
    
    include ("modules/$ModPath/config/links.php");
    include("header.php");
      
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
        $mainlink = 'in_l';
        menu($mainlink);

        $filen = "modules/$ModPath/views/ban_01.php";
        if (file_exists($filen)) 
        {
            include($filen);
        }

        echo '
        <table class="table table-bordered table-striped table-hover">';
        
        $result = sql_query("SELECT cid, title, cdescription FROM ".$links_DB."links_categories ORDER BY title");
          
        if ($result) 
        {
            while (list($cid, $title, $cdescription) = sql_fetch_row($result)) 
            {
                $cresult = sql_query("SELECT lid FROM ".$links_DB."links_links WHERE cid='$cid'");
                $cnumrows = sql_num_rows($cresult);
                
                echo '
                <tr>
                    <td>
                        <h4><a href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=viewlink&amp;cid='.$cid.'">'.language::aff_langue($title).'</a> <span class="badge badge-secondary float-right">'.$cnumrows.'</span></h4>';
                
                categorynewlinkgraphic($cid);
                
                if ($cdescription)
                {
                    echo '<p>'.language::aff_langue($cdescription).'</p>';
                }
               
                $result2 = sql_query("SELECT sid, title FROM ".$links_DB."links_subcategories WHERE cid='$cid' ORDER BY title $subcat_limit");
                
                while (list($sid, $stitle) = sql_fetch_row($result2)) 
                {
                    $cresult3 = sql_query("SELECT lid FROM ".$links_DB."links_links WHERE sid='$sid'");
                    $cnumrows = sql_num_rows($cresult3);
                   
                    echo '
                    <h5 class="ml-4"><a href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=viewslink&amp;sid='.$sid.'">'.language::aff_langue($stitle).'</a> <span class="badge badge-secondary float-right">'.$cnumrows.'</span></h5>';
                }

                echo '
                    </td>
                </tr>';
            }
        }

        echo '
        </table>';

        $result = sql_query("SELECT lid FROM ".$links_DB."links_links");
        if ($result) 
        {
            $numrows = sql_num_rows($result);
             
            echo '
            <p class="lead" align="center"><span>'.translate("Il y a").' <b>'.$numrows.'</b> '.translate("Liens").'
                <span class="btn btn-danger btn-sm" title="'.translate("Les nouveaux liens de cette cat??gorie ajout??s aujourd'hui").'" data-toggle="tooltip" >N</span>&nbsp;
                <span class="btn btn-success btn-sm" title="'.translate("Les nouveaux liens ajout??s dans cette cat??gorie dans les 3 derniers jours").'" data-toggle="tooltip" >N</span>&nbsp;
                <span class="btn btn-primary btn-sm" title="'.translate("Les nouveaux Liens ajout??s dans cette cat??gorie cette semaine").'" data-toggle="tooltip" >N</span>
            </p>';
        }
        
        SearchForm();
    }

    if ($SuperCache)
    {
        $cache_obj->endCachingPage();
    }
       
    global $admin;
    if ($admin) 
    {
        $result = sql_query("SELECT requestid FROM ".$links_DB."links_modrequest WHERE brokenlink=1");
          
        if ($result) 
        {
            $totalbrokenlinks = sql_num_rows($result);
            $result2 = sql_query("SELECT requestid FROM ".$links_DB."links_modrequest WHERE brokenlink=0");
            
            $totalmodrequests = sql_num_rows($result2);
            $result = sql_query("SELECT lid FROM ".$links_DB."links_newlink");
            $num = sql_num_rows($result);
            
            echo '
            <p class="lead p-2 text-center border rounded bg-light">
                <a href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'/admin"><i class="fa fa-cogs fa-2x mr-2 align-middle" title="Admin" data-toggle="tooltip"></i></a> '.translate("Liens").' : 
                <span class="badge badge-danger ml-2" title="'.translate("Lien(s) en attente de validation").'" data-toggle="tooltip">'.$num.'</span> 
                <span class="badge badge-danger ml-2" title="'.translate("Liens cass??s rapport??s par un ou plusieurs utilisateurs").'" data-toggle="tooltip">'.$totalbrokenlinks.'</span> 
                <span class="badge badge-danger ml-2" title="'.translate("Proposition de modification").'" data-toggle="tooltip">'.$totalmodrequests.'</span>';
            
            if($links_DB != '') 
            {
                echo 'Ref Tables => <strong>'.$links_DB.'</strong>';
            }
             
            echo '
            </p>';
        } 
        else 
        {
            echo "<p align=\"center\"><span> -: [ <a href=\"modules.php?ModStart=create_tables&amp;ModPath=$ModPath/admin/\">".translate("Cr??er")."</a> Tables : $links_DB ] :-</span></p>";
        }
    }

    include("footer.php");
}


function FooterOrderBy($cid, $sid, $orderbyTrans, $linkop) 
{
    global $ModPath, $ModStart;
       
    echo "<p align=\"center\"><span style=\"font-size: 10px;\">".translate("Classement")." : ";
       
    if ($linkop == "viewlink") 
    {
        echo translate("Titre")." (<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewlink&amp;cid=$cid&amp;orderby=titleA\">A</a>\<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewlink&amp;cid=$cid&amp;orderby=titleD\">D</a>)
            ".translate("Date")." (<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewlink&amp;cid=$cid&amp;orderby=dateA\">A</a>\<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewlink&amp;cid=$cid&amp;orderby=dateD\">D</a>)";
    } 
    else 
    {
        echo translate("Titre")." (<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewslink&amp;sid=$sid&amp;orderby=titleA\">A</a>\<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewslink&amp;sid=$sid&amp;orderby=titleD\">D</a>)
            ".translate("Date")." (<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewslink&amp;sid=$sid&amp;orderby=dateA\">A</a>\<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewslink&amp;sid=$sid&amp;orderby=dateD\">D</a>)";
    }

    echo "<br />".translate("Sites class??s par")." : $orderbyTrans</span></p>";
}


function viewlink($cid, $min, $orderby, $show) 
{
    global $ModPath, $ModStart, $links_DB, $admin, $perpage, $SuperCache;
    
    include("header.php");

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
        if (!isset($max)) 
        {
            $max = $min+$perpage;
        }
        
        mainheader();

        settype($affsouscat, 'string');

        $filen = "modules/$ModPath/views/ban_02.php";
        if (file_exists($filen)) 
        {
            include($filen);
        }
          
        $result = sql_query("SELECT title FROM ".$links_DB."links_categories WHERE cid='$cid'");
        list($title) = sql_fetch_row($result);
          
        echo '
        <h3 class="mb-3">'.language::aff_langue($title).'</h3>';

        $subresult = sql_query("SELECT sid, title FROM ".$links_DB."links_subcategories WHERE cid='$cid' ORDER BY title");
        $numrows = sql_num_rows($subresult);
          
        settype($numrows_lst,'integer');
          
        $affsouscat .= '
        <ul class="list-group">
            <li class="list-group-item "><h4 class="w-100">'.translate("Sous-cat??gories").'<span class="badge badge-secondary float-right"> '.$numrows.'</span></h4></li>';
             
        while(list($sid, $title) = sql_fetch_row($subresult)) 
        {
            $result2 = sql_query("SELECT lid FROM ".$links_DB."links_links WHERE sid='$sid'");
            $numrows_lst = sql_num_rows($result2);
            
            $affsouscat .= '
            <li class="list-group-item list-group-item-action justify-content-between align-self-start"><a href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=viewslink&amp;sid='.$sid.'">'.language::aff_langue($title).'</a></li>';
        }

        $affsouscat .= '
        </ul>';
          
        if ($numrows != 0)
        {
            echo $affsouscat;
        }
          
        $orderbyTrans = convertorderbytrans($orderby);
          
        settype($min, "integer");
        settype($perpage, "integer");
          
        $result = sql_query("SELECT lid, url, title, description, date, hits, topicid_card, cid, sid FROM ".$links_DB."links_links WHERE cid='$cid' AND sid=0 ORDER BY $orderby LIMIT $min,$perpage");
        
        $fullcountresult = sql_query("SELECT lid, title, description, date, hits FROM ".$links_DB."links_links WHERE cid='$cid' AND sid=0");
        $totalselectedlinks = sql_num_rows($fullcountresult);
          
        echo "<br />\n";
          
        $link_fiche_detail = '';
        
        include_once("modules/$ModPath/links-view.php");

        $orderby = convertorderbyout($orderby);
        //Calculates how many pages exist.  Which page one should be on, etc...
        $linkpagesint = ($totalselectedlinks / $perpage);
        $linkpageremainder = ($totalselectedlinks % $perpage);

        if ($linkpageremainder != 0) 
        {
            $linkpages = ceil($linkpagesint);
            if ($totalselectedlinks < $perpage)
            {
                $linkpageremainder = 0;
            }
        } 
        else
        {
            $linkpages = $linkpagesint;
        }

        $nbPages = ceil($totalselectedlinks/$perpage);
        $current = 1;
        if ($min >= 1)
        {
            $current = $min/$perpage;
        }
        else if ($min < 1)
        {
            $current = 0;
        }
        else
        {
            $current = $nbPages;
        }

        $start = ($current*$perpage);

        echo pagination::paginate('modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=viewlink&amp;cid='.$cid.'&amp;min=', '&amp;orderby='.$orderby.'&amp;show='.$perpage, $nbPages, $current, $adj=3, $perpage, $start);

        if (isset($sid)) 
        {
            FooterOrderBy($cid, $sid, $orderbyTrans, 'viewlink');
        }
        
        SearchForm();
    }

    if ($SuperCache)
    {
        $cache_obj->endCachingPage();
    }
    
    include("footer.php");
}


function viewslink($sid, $min, $orderby, $show) 
{
    global $ModPath, $ModStart, $links_DB, $admin, $perpage, $SuperCache;

    include("header.php");

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
        mainheader();
        
        $filen = "modules/$ModPath/views/ban_03.php";
        if (file_exists($filen)) 
        {
            include($filen);
        }
          
        if (!isset($max)) 
        {
            $max = $min+$perpage;
        }
          
        $result = sql_query("SELECT cid, title FROM ".$links_DB."links_subcategories WHERE sid='$sid'");
        list($cid, $stitle) = sql_fetch_row($result);

        $result2 = sql_query("SELECT cid, title FROM ".$links_DB."links_categories WHERE cid='$cid'");
        list($cid, $title) = sql_fetch_row($result2);

        echo "<table class=\"table table-bordered\"><tr><td class=\"header\">\n";
        echo "<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath\" class=\"box\">".translate("Index")."</a> / <a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewlink&amp;cid=$cid\" class=\"box\">".language::aff_langue($title)."</a> / ".language::aff_langue($stitle);
        
        echo "</td></tr></table>";

        $orderbyTrans = convertorderbytrans($orderby);
          
        settype($min,'integer');
        settype($perpage,'integer');
          
        $result = sql_query("SELECT lid, url, title, description, date, hits, topicid_card, cid, sid FROM ".$links_DB."links_links WHERE sid='$sid' ORDER BY $orderby LIMIT $min,$perpage");

        $fullcountresult = sql_query("SELECT lid, title, description, date, hits FROM ".$links_DB."links_links WHERE sid='$sid'");
        $totalselectedlinks = sql_num_rows($fullcountresult);
          
        echo "<br />\n";
          
        $link_fiche_detail = '';
        
        include_once("modules/$ModPath/links-view.php");
        
        echo "<br />\n";

        $orderby = convertorderbyout($orderby);
          
        //Calculates how many pages exist.  Which page one should be on, etc...
        $linkpagesint = ($totalselectedlinks / $perpage);
        $linkpageremainder = ($totalselectedlinks % $perpage);

        if ($linkpageremainder != 0) 
        {
            $linkpages = ceil($linkpagesint);
            if ($totalselectedlinks < $perpage)
            {
                $linkpageremainder = 0;
            }
        }
        else
        {
            $linkpages = $linkpagesint;
        }
          
        //Page Numbering
        if ($linkpages != 1 && $linkpages != 0) 
        {
            echo "<p align=\"center\">";
            echo translate("S??lectionner la page")." :&nbsp;&nbsp;";
             
            $prev = $min-$perpage;
            $counter = 1;
            $currentpage = ($max / $perpage);
             
            while ($counter <= $linkpages ) 
            {
                $cpage = $counter;
                $mintemp = ($perpage * $counter) - $perpage;
                
                if ($counter == $currentpage) 
                {
                    echo "<font class=\"rouge\">$counter</font>&nbsp;";
                }
                else 
                {
                    echo "<a href=\"modules.php?ModStart=$ModStart&amp;ModPath=$ModPath&amp;op=viewslink&amp;sid=$sid&amp;min=$mintemp&amp;orderby=$orderby&amp;show=$show\">$counter</a>&nbsp;";
                }
                $counter++;
            }
        }
        echo "</p><br />";
        FooterOrderBy($cid, $sid, $orderbyTrans, "viewslink");
    }

    if ($SuperCache)
    {
        $cache_obj->endCachingPage();
    }
    
    include("footer.php");
}


function fiche_detail ($Xlid) 
{
    global $ModPath, $ModStart, $SuperCache;
    
    include("header.php");

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
        settype($xlid,'integer');
        
        $browse_key = $Xlid;
        $link_fiche_detail = "fiche_detail";
        $inter = 'cid';
        
        include ("lib/sform/links/link_detail.php");
    }
       
    if ($SuperCache)
    {
        $cache_obj->endCachingPage();
    }
       
    include("footer.php");
}


function categorynewlinkgraphic($cat) 
{
    global $OnCatNewLink, $locale, $links_DB;
    
    if ($OnCatNewLink == '1') 
    {
        $newresult = sql_query("SELECT date FROM ".$links_DB."links_links WHERE cid='$cat' ORDER BY date DESC LIMIT 1");
        list($time) = sql_fetch_row($newresult);
        
        if (isset($ime)) 
        {
            setlocale(LC_TIME, language::aff_langue($locale));
            
            preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $time, $datetime);
            $count = round((time()- mktime($datetime[4], $datetime[5], $datetime[6], $datetime[2], $datetime[3], $datetime[1]))/86400, 0);
            
            popgraphics($count);
        }
    }
}


function popgraphics($count) 
{
    if ($count < 1) 
    {
        echo '<span class="btn btn-danger btn-sm float-right" title="'.translate("Les nouveaux liens de cette cat??gorie ajout??s aujourd'hui").'" data-toggle="tooltip" data-placement="left">N</span>';
    }
    
    if ($count <= 3 && $count >= 1)
    {
        echo '<span class="btn btn-success btn-sm float-right" title="'.translate("Les nouveaux liens ajout??s dans cette cat??gorie dans les 3 derniers jours").'" data-toggle="tooltip" data-placement="left">N</span>';
    }
    
    if ($count <= 7 && $count > 3) 
    {
        echo '<span class="btn btn-infos btn-sm float-right" title="'.translate("Les nouveaux Liens ajout??s dans cette cat??gorie cette semaine").'" data-toggle="tooltip" data-placement="left">N</span>';
    }
}


function newlinkgraphic($datetime, $time) 
{
    global $locale;
    
    setlocale(LC_TIME, language::aff_langue($locale));
    
    preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $time, $datetime);
    
    $count = round((time()- mktime($datetime[4], $datetime[5], $datetime[6], $datetime[2], $datetime[3], $datetime[1]))/86400,0);
    
    popgraphics($count);
}


function detecteditorial($lid, $ttitle) 
{
    global $ModPath, $ModStart, $links_DB;
    
    $resulted2 = sql_query("SELECT adminid FROM ".$links_DB."links_editorials WHERE linkid='$lid'");
    $recordexist = sql_num_rows($resulted2);
    
    if ($recordexist != 0) 
    {
        echo '<a class="mr-3" href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=viewlinkeditorial&amp;lid='.$lid.'&amp;ttitle='.$ttitle.'"><i class="far fa-sticky-note fa-lg" title="'.translate("EDITO").'" data-toggle="tooltip"></i></a>';
    }
}


//Reusable Link Sorting Functions
function convertorderbyin($orderby) 
{
    $orderbyIn = 'title ASC';

    if ($orderby == 'titleA')
    {
        $orderbyIn = 'title ASC';
    }

    if ($orderby == 'dateA') 
    {
        $orderbyIn = 'date ASC';
    }

    if ($orderby == 'titleD') 
    {
        $orderbyIn = 'title DESC';
    }

    if ($orderby == 'dateD') 
    {
        $orderbyIn = 'date DESC';
    }
    
    return $orderbyIn;
}


function convertorderbytrans($orderby) 
{
    $orderbyTrans = translate("Title (A to Z)");

    if ($orderby == 'title ASC') 
    {
        $orderbyTrans = translate("Titre (de A ?? Z)");
    }

    if ($orderby == 'title DESC') 
    {
        $orderbyTrans = translate("Titre (de Z ?? A)");
    }

    if ($orderby == 'date ASC') 
    {
        $orderbyTrans = translate("Date (les plus vieux liens en premier)");
    }

    if ($orderby == 'date DESC') 
    {
        $orderbyTrans = translate("Date (les liens les plus r??cents en premier)");
    }
    
    return $orderbyTrans;
}


function convertorderbyout($orderby) 
{
    $orderbyOut = 'titleA';

    if ($orderby == 'title ASC') 
    {
        $orderbyOut = 'titleA';
    }

    if ($orderby == 'date ASC') 
    {
        $orderbyOut = 'dateA';
    }

    if ($orderby == 'title DESC') 
    {
        $orderbyOut = 'titleD';
    }

    if ($orderby == 'date DESC') 
    {
        $orderbyOut = 'dateD';
    }
    return $orderbyOut;
}


function visit($lid) 
{
    global $links_DB;
    sql_query("UPDATE ".$links_DB."links_links SET hits=hits+1 WHERE lid='$lid'");
    
    $result = sql_query("SELECT url FROM ".$links_DB."links_links WHERE lid='$lid'");
    list($url) = sql_fetch_row($result);
    
    Header("Location: $url");
}


function viewlinkeditorial($lid, $ttitle) 
{
    global $ModPath, $ModStart, $links_DB;
    
    include("header.php");
    
    mainheader();
       
    $result2 = sql_query("SELECT url FROM ".$links_DB."links_links WHERE lid='$lid'");
    list($url) = sql_fetch_row($result2);
       
    $result = sql_query("SELECT adminid, editorialtimestamp, editorialtext, editorialtitle FROM ".$links_DB."links_editorials WHERE linkid = '$lid'");
    $recordexist = sql_num_rows($result);
    $displaytitle = stripslashes($ttitle);
       
    echo '
    <div class="card card-body">
    <h3>'.translate("EDITO").' : 
        <span class="text-muted">'.language::aff_langue($displaytitle).'</span>';
       
    if ($url != '') 
    {
        echo '
        <span class="float-right"><a href="modules.php?ModStart='.$ModStart.'&amp;ModPath='.$ModPath.'&amp;op=visit&amp;lid='.$lid.'" target="_blank" title="'.translate("Visiter ce site web").'" data-toggle="tooltip" data-placement="left"><i class="fas fa-external-link-alt"></i></a></span>';
    }
       
    echo '
    </h3>';
       
    if ($recordexist != 0)
    {
        while (list($adminid, $editorialtimestamp, $editorialtext, $editorialtitle) = sql_fetch_row($result))
        {
            $editorialtitle = stripslashes($editorialtitle); $editorialtext = stripslashes($editorialtext);
            $formatted_date = time::formatTimestamp($editorialtimestamp);
            
            echo '
            <h4>'.language::aff_langue($editorialtitle).'</h4>
            <p><span class="text-muted small">'.translate("Editorial par").' '.$adminid.' - '.$formatted_date.'</span></p>
            <hr/>'.language::aff_langue($editorialtext);
        }
    }
    else
    {
        echo '<p class="text-center">'.translate("Aucun ??dito n'est disponible pour ce site").'</p><br />';
    }
    echo '
    </div>';
    
    sql_free_result();
    
    include("footer.php");
}


function formatTimestampShort($time) 
{
    global $datetime, $locale, $gmt;

    setlocale(LC_TIME, lnguage::aff_langue($locale));
    
    preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $time, $datetime);
    
    $datetime = strftime("".translate("linksdatestring")."", mktime($datetime[4]+(integer)$gmt, $datetime[5], $datetime[6], $datetime[2], $datetime[3], $datetime[1]));
    
    if (cur_charset != 'utf-8')
    {
        $datetime = ucfirst($datetime);
    }

    return($datetime);
}

settype($op, 'string');

switch ($op) {

    case 'menu':
        menu($mainlink);
    break;

    case 'AddLink':
        links::AddLink();
    break;

    case 'Add':
        settype($asb_question, 'string');
        settype($asb_reponse, 'string');

        links::Add($title, $url, $name, $cat, $xtext, $email, $topicL, $asb_question, $asb_reponse);
    break;

    case 'NewLinks':
        if (!isset($newlinkshowdays)) 
        {
            $newlinkshowdays = 7;
        }
        links::NewLinks($newlinkshowdays);
    break;

    case 'NewLinksDate':
        links::NewLinksDate($selectdate);
    break;

    case 'viewlink':
        settype($show, 'string');
        
        if (!isset($min)) 
        {
            $min = 0;
        }

        if (isset($orderby)) 
        {
            $orderby = convertorderbyin($orderby); 
        }
        else 
        {
            $orderby = "title ASC";
        }

        viewlink($cid, $min, $orderby, $show);
    break;

    case 'viewslink':
        if (!isset($min)) 
        {
            $min = 0;
        }

        if (isset($orderby))
        {
            $orderby = convertorderbyin($orderby); 
        }
        else
         {
            $orderby = "title ASC";
        }

        if (isset($show)) 
        {
            $perpage = $show; 
        }
        else 
        {
            $show = $perpage;
        }

        viewslink($sid, $min, $orderby, $show);
    break;

    case 'brokenlink':
        links::brokenlink($lid);
    break;

    case 'brokenlinkS':
        links::brokenlinkS($lid, $modifysubmitter);
    break;

    case 'modifylinkrequest':
        settype($modifylinkrequest_adv_infos, 'string');
        links::modifylinkrequest($lid, $modifylinkrequest_adv_infos, $author);
    break;

    case 'modifylinkrequestS':
        links::modifylinkrequestS($lid, $cat, $title, $url, $xtext, $modifysubmitter, $topicL);
    break;

    case 'visit':
        visit($lid);
    break;

    case 'search':
        $offset = 10;

        if (!isset($min))
        { 
            $min = 0;
        }

        if (!isset($max)) 
        {
            $max = $min+$offset;
        }
        
        links::links_search($query, $topicL, $min, $max, $offset);
    break;

    case 'viewlinkeditorial':
        viewlinkeditorial($lid, $ttitle);
    break;

    case 'fiche_detail':
        fiche_detail ($lid);
    break;

    default:
        index();
    break;
}
