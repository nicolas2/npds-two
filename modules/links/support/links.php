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
namespace modules\links\support;

use npds\auth\auth;
use npds\editeur\tiny;
use npds\utility\spam;
use npds\assets\css;
use npds\logs\logs;
use npds\utility\str;
use npds\security\hack;


/*
 * links
 */
class links {


    /**
     * [error_head description]
     * @param  [type] $class [description]
     * @return [type]        [description]
     */
    public static function error_head($class) 
    {
        global $ModPath, $ModStart;
       
        include("header.php");
       
        $mainlink = 'ad_l';
        menu($mainlink);
       
        SearchForm();
       
        echo '
        <div class="alert '.$class.'" role="alert" align="center">';
    }

    /**
     * [error_foot description]
     * @return [type] [description]
     */
    public static function error_foot() 
    {
        echo '
        </div>';
       
        include("footer.php");
    }

    /**
     * [AddLink description]
     */
    public static function AddLink() 
    {
        global $ModPath, $ModStart, $links_DB, $NPDS_Prefix, $links_anonaddlinklock, $op, $user, $ad_l;
       
        include("header.php");
       
        mainheader();
       
        if (auth::autorisation($links_anonaddlinklock)) 
        {
            echo '
            <div class="card card-body mb-3">
                <h3 class="mb-3">Proposer un lien</h3>
                <div class="card card-outline-secondary mb-3">
                    <div class="card-body">
                        <span class="help-block">'.translate("Proposer un seul lien.").'<br />'.translate("Tous les liens proposés sont vérifiés avant insertion.").'<br />'.translate("Merci de ne pas abuser, le nom d'utilisateur et l'adresse IP sont enregistrés.").'</span>
                    </div>
                </div>
                <form id="addlink" method="post" action="modules.php" name="adminForm">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <div class="form-group row">
                        <label class="col-form-label col-sm-3" for="title">'.translate("Titre").'</label>
                        <div class="col-sm-9">
                            <input class="form-control" type="text" id="title" name="title" maxlength="100" required="required" />
                            <span class="help-block text-right" id="countcar_title"></span>
                        </div>
                    </div>';
          
            global $links_url;
            if (($links_url) or ($links_url == -1))
            {
                echo'
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="url">URL</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="url" id="url" name="url" maxlength="255" value="http://" required="required" />
                        <span class="help-block text-right" id="countcar_url"></span>
                    </div>
                </div>';
            }
            
            $result = sql_query("SELECT cid, title FROM ".$links_DB."links_categories ORDER BY title");
            
            echo'
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="cat">'.translate("Catégorie").'</label>
                    <div class="col-sm-9">
                        <select class="custom-select form-control" id="cat" name="cat">';
            
            while (list($cid, $title) = sql_fetch_row($result)) 
            {
                echo '<option value="'.$cid.'">'.language::aff_langue($title).'</option>';
               
                $result2 = sql_query("select sid, title from ".$links_DB."links_subcategories WHERE cid='$cid' ORDER BY title");
               
                while (list($sid, $stitle) = sql_fetch_row($result2)) 
                {
                    echo '<option value="'.$cid.'-'.$sid.'">'.language::aff_langue($title.'/'. $stitle).'</option>';
                }
            }

            echo '
                    </select>
                </div>
            </div>';
            
            global $links_topic;
            if ($links_topic) 
            {
                echo '
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="topicL">'.translate("Sujets").'</label>
                    <div class="col-sm-9">
                        <select class="custom-select form-control" id="topicL" name="topicL">';
               
                $toplist = sql_query("SELECT topicid, topictext FROM ".$NPDS_Prefix."topics ORDER BY topictext");
               
                echo '<option value="">'.translate("Tous les sujets").'</option>';
               
                while(list($topicid, $topics) = sql_fetch_row($toplist)) 
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
                    <label class="col-form-label col-sm-12" for="xtext">'.translate("Description").'</label>
                    <div class="col-sm-12">
                        <textarea class="tin form-control" name="xtext" id="xtext" rows="10"></textarea>
                    </div>
                </div>';

            echo tiny::aff_editeur('xtext', '');
            
            global $cookie;
            echo '
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="name">'.translate("Votre nom").'</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name" name="name" maxlength="60" value="'.$cookie[1].'" required="required" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="email">'.translate("Votre Email").'</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="email" name="email" maxlength="60" required="required" />
                        <span class="help-block text-right" id="countcar_email"></span>
                    </div>
                </div>';

            echo spam::Q_spambot();
            
            echo '
                        <div class="form-group row">
                            <input type="hidden" name="op" value="Add" />
                            <div class="col-sm-9 ml-sm-auto">
                                <input type="submit" class="btn btn-primary" value="'.translate("Ajouter une url").'" />
                            </div>
                        </div>
                        </form>
                    </div>
                <div>
            </div>';

            $arg1 = '
                var formulid = ["addlink"];
                inpandfieldlen("title",100);
                inpandfieldlen("url",255);
                inpandfieldlen("email",60);';

            SearchForm();
          
            css::adminfoot('fv', '', $arg1, '1');
          
            include("footer.php");
       } 
       else 
       {
            echo '
                <div class="alert alert-warning">'.translate("Vous n'êtes pas (encore) enregistré ou vous n'êtes pas (encore) connecté.").'<br />
                '.translate("Si vous étiez enregistré, vous pourriez proposer des liens.").'</div>';
          
            SearchForm();
          
            include("footer.php");
        }
    }

    /**
     * [Add description]
     * @param [type] $title        [description]
     * @param [type] $url          [description]
     * @param [type] $name         [description]
     * @param [type] $cat          [description]
     * @param [type] $description  [description]
     * @param [type] $email        [description]
     * @param [type] $topicL       [description]
     * @param [type] $asb_question [description]
     * @param [type] $asb_reponse  [description]
     */
    public static function Add($title, $url, $name, $cat, $description, $email, $topicL, $asb_question, $asb_reponse) 
    {
        global $ModPath, $ModStart, $links_DB, $troll_limit, $anonymous, $user, $admin;
       
        if (!$user and !$admin) 
        {
            //anti_spambot
            if (!spam::R_spambot($asb_question, $asb_reponse, '')) 
            {
                logs::Ecr_Log('security', 'Links Anti-Spam : url='.$url, '');
                redirect_url("index.php");
                die();
            }
        }

        $result = sql_query("SELECT lid FROM ".$links_DB."links_newlink");
        $numrows = sql_num_rows($result);
       
        if ($numrows >= $troll_limit) 
        {
            static::error_head("alert-danger");
            echo translate("Erreur : cette url est déjà présente dans la base de données").'<br />';
            static::error_foot();
            exit();
        }

        global $user;
        if (isset($user)) 
        {
            global $cookie;
            $submitter = $cookie[1];
        } 
        else
        {
            $submitter = $anonymous;
        }
       
        if ($title == '')
        {
            static::error_head('alert-danger');
            echo translate("Erreur : vous devez saisir un titre pour votre lien").'<br />';
            static::error_foot();
            exit();
        }

        if ($email == '') 
        {
            static::error_head('alert-danger');
            echo translate("Erreur : Email invalide").'<br />';
            static::error_foot();
            exit();
        }

        global $links_url;
        if (($url == '') and ($links_url == 1)) 
        {
            static::error_head('alert-danger');
            echo translate("Erreur : vous devez saisir une url pour votre lien").'<br />';
            static::error_foot();
            exit();
        }

        if ($description == '') 
        {
            static::error_head('alert-danger');
            echo translate("Erreur : vous devez saisir une description pour votre lien").'<br />';
            static::error_foot();
            exit();
        }

        $cat = explode('-', $cat);
        
        if (!array_key_exists(1, $cat)) 
        {
            $cat[1] = 0;
        }

        $title = hack::remove(stripslashes(str::FixQuotes($title)));
        $url = hack::remove(stripslashes(str::FixQuotes($url)));
        $description = hack::remove(stripslashes(str::FixQuotes($description)));
        $name = hack::remove(stripslashes(str::FixQuotes($name)));
        $email = hack::remove(stripslashes(str::FixQuotes($email)));
       
        sql_query("INSERT INTO ".$links_DB."links_newlink VALUES (NULL, '$cat[0]', '$cat[1]', '$title', '$url', '$description', '$name', '$email', '$submitter', '$topicL')");
       
        static::error_head('alert-success');
       
        echo translate("Nous avons bien reçu votre demande de lien, merci").'<br />';
        echo translate("Vous recevrez un mèl quand elle sera approuvée.").'<br />';
       
        static::error_foot();
    }

    /**
     * [links_search description]
     * @param  [type] $query  [description]
     * @param  [type] $topicL [description]
     * @param  [type] $min    [description]
     * @param  [type] $max    [description]
     * @param  [type] $offset [description]
     * @return [type]         [description]
     */
    public static function links_search($query, $topicL, $min, $max, $offset) 
    {
        global $ModPath, $ModStart, $links_DB;

        include ("header.php");

        mainheader();

        $filen = "modules/$ModPath/views/ban_02.php";

        if (file_exists($filen)) 
        {
            include($filen);
        }

        $query = hack::remove(stripslashes(htmlspecialchars($query, ENT_QUOTES, cur_charset))); // Romano et NoSP

        if ($topicL != '')
        {
            $result = sql_query("SELECT lid, url, title, description, date, hits, topicid_card, cid, sid FROM ".$links_DB."links_links WHERE topicid_card='$topicL' AND (title LIKE '%$query%' OR description LIKE '%$query%') ORDER BY lid ASC LIMIT $min,$offset");
        }
        else
        {
            $result = sql_query("SELECT lid, url, title, description, date, hits, topicid_card, cid, sid FROM ".$links_DB."links_links WHERE title LIKE '%$query%' OR description LIKE '%$query%' ORDER BY lid ASC LIMIT $min,$offset");
        }
       
        if ($result) 
        {
            $link_fiche_detail = '';
          
            include_once("modules/$ModPath/links-view.php");
          
            $prev = $min-$offset;
          
            if ($prev >= 0) 
            {
                echo "$min <a href=\"modules.php?ModPath=$ModPath&amp;ModStart=$ModStart&amp;op=search&min=$prev&amp;query=$query&amp;topicL=$topicL\" class=\"noir\">";
                echo translate("réponses précédentes")."</a>&nbsp;&nbsp;";
            }

            if ($x >= ($offset-1)) 
            {
                echo "<a href=\"modules.php?ModPath=$ModPath&amp;ModStart=$ModStart&amp;op=search&amp;min=$max&amp;query=$query&amp;topicL=$topicL\" class=\"noir\">";
                echo translate("réponses suivantes")."</a>";
            }
        }

        include("footer.php");
    }

    /**
     * [NewLinksDate description]
     * @param [type] $selectdate [description]
     */
    public static function NewLinksDate($selectdate) 
    {
        global $ModPath, $ModStart, $links_DB, $admin;

        $dateDB = (date("d-M-Y", $selectdate));

        include("header.php");

         mainheader('nl');

        $filen = "modules/$ModPath/views/ban_01.php";

        if (file_exists($filen)) 
        {
            include($filen);
        }

        $newlinkDB = Date("Y-m-d", $selectdate);
        $result = sql_query("SELECT lid FROM ".$links_DB."links_links WHERE date LIKE '%$newlinkDB%'");
       
        $totallinks = sql_num_rows($result);
        $result  = sql_query("SELECT lid, url, title, description, date, hits, topicid_card, cid, sid FROM ".$links_DB."links_links WHERE date LIKE '%$newlinkDB%' ORDER BY title ASC");
       
        $link_fiche_detail = '';
       
        include_once("modules/$ModPath/links-view.php");
       
        include("footer.php");
    }

    /**
     * [NewLinks description]
     * @param [type] $newlinkshowdays [description]
     */
    public static function NewLinks($newlinkshowdays) 
    {
        global $ModPath, $ModStart, $links_DB;
       
        include("header.php");
       
        mainheader('nl');
       
        $counter = 0;
        $allweeklinks = 0;
       
        while ($counter <= 7-1)
        {
            $newlinkdayRaw = (time()-(86400 * $counter));
            $newlinkday = date("d-M-Y", $newlinkdayRaw);
            $newlinkView = date("F d, Y", $newlinkdayRaw);
            $newlinkDB = Date("Y-m-d", $newlinkdayRaw);
          
            $result = sql_query("SELECT * FROM ".$links_DB."links_links WHERE date LIKE '%$newlinkDB%'");
            $totallinks = sql_num_rows($result);
          
            $counter++;
            $allweeklinks = $allweeklinks + $totallinks;
        }

        $counter = 0;
        $allmonthlinks = 0;
       
        while ($counter <= 30-1)
        {
            $newlinkdayRaw = (time()-(86400 * $counter));
            $newlinkDB = Date("Y-m-d", $newlinkdayRaw);
          
            $result = sql_query("SELECT * FROM ".$links_DB."links_links WHERE date LIKE '%$newlinkDB%'");
            $totallinks = sql_num_rows($result);
          
            $allmonthlinks = $allmonthlinks + $totallinks;
            $counter++;
        }

        echo '
        <div class="card card-body mb-3">
        <h3>'.translate("Nouveaux liens").'</h3>
        '.translate("Total des nouveaux liens pour la semaine dernière").' : '.$allweeklinks.' -/- '.translate("Pour les 30 derniers jours").' : '.$allmonthlinks;

        echo "<br />\n";

        echo "<blockquote>".translate("Montrer :")." [<a href=\"modules.php?ModStart=$ModStart&ModPath=$ModPath&op=NewLinks&newlinkshowdays=7\" class=\"noir\">".translate("semaine")."</a>, <a href=\"modules.php?ModStart=$ModStart&ModPath=$ModPath&op=NewLinks&newlinkshowdays=14\" class=\"noir\">2 ".translate("semaines")."</a>, <a href=\"modules.php?ModStart=$ModStart&ModPath=$ModPath&op=NewLinks&newlinkshowdays=30\" class=\"noir\">30 ".translate("jours")."</a>]</<blockquote>";
        
        $counter = 0;
        $allweeklinks = 0;
        
        echo '
        <blockquote>
        <ul>';
       
        while ($counter <= $newlinkshowdays-1) 
        {
            $newlinkdayRaw = (time()-(86400 * $counter));
            $newlinkday = date("d-M-Y", $newlinkdayRaw);
            $newlinkView = date(str_replace("%","",translate("linksdatestring")), $newlinkdayRaw);
            $newlinkDB = Date("Y-m-d", $newlinkdayRaw);
          
            $result = sql_query("SELECT * FROM ".$links_DB."links_links WHERE date LIKE '%$newlinkDB%'");
            $totallinks = sql_num_rows($result);
          
            $counter++;
            $allweeklinks = $allweeklinks + $totallinks;
          
            if ($totallinks > 0)
            {
                echo "<li><a href=\"modules.php?ModStart=$ModStart&ModPath=$ModPath&op=NewLinksDate&selectdate=$newlinkdayRaw\">$newlinkView</a>&nbsp( $totallinks )</li>";
            }
        }

        echo '
        </blockquote>
        </ul>
        </div>';
       
        SearchForm();
       
        $counter = 0;
       
        $allmonthlinks = 0;
       
        include("footer.php");
    }

    /**
     * [modifylinkrequest description]
     * @param  [type] $lid                         [description]
     * @param  [type] $modifylinkrequest_adv_infos [description]
     * @param  [type] $author                      [description]
     * @return [type]                              [description]
     */
    public static function modifylinkrequest($lid, $modifylinkrequest_adv_infos, $author) 
    {
        global $ModPath, $ModStart, $links_DB, $NPDS_Prefix;

        if (autorise_mod($lid, false)) 
        {
            if ($author == '-9')
            {
                Header("Location: modules.php?ModStart=$ModStart&ModPath=$ModPath/admin&op=LinksModLink&lid=$lid");
            }
         
            include("header.php");
         
            mainheader();
         
            $result = sql_query("SELECT cid, sid, title, url, description, topicid_card FROM ".$links_DB."links_links WHERE lid='$lid'");
            list($cid, $sid, $title, $url, $description, $topicid_card) = sql_fetch_row($result);
         
            $title = stripslashes($title);
            $description = stripslashes($description);
         
            echo '
            <h3 class="my-3">'.translate("Proposition de modification").' : <span class="text-muted">'.$title.'</span></h3>
            <form action="modules.php" method="post" name="adminForm">
                <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="title">'.translate("Titre").'</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" id="title" name="title" value="'.$title.'"  maxlength="100" required="required" />
                    </div>
                </div>';
         
            global $links_url;
            if ($links_url) 
            {
                echo '
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="url">URL</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="url" id="url" name="url" value="'.$url.'" maxlength="100" required="required" />
                    </div>
                </div>';
            }
         
            echo '
            <div class="form-group row">
                <label class="col-form-label col-sm-3" for="cat">'.translate("Catégorie").'</label>
                <div class="col-sm-9">
                <select class="custom-select form-control" id="cat" name="cat">';
         
            $result2 = sql_query("SELECT cid, title FROM ".$links_DB."links_categories ORDER BY title");
            while (list($ccid, $ctitle) = sql_fetch_row($result2)) 
            {
                $sel = '';
            
                if ($cid == $ccid AND $sid == 0) 
                {
                    $sel = 'selected';
                }
            
                echo '<option value="'.$ccid.'" '.$sel.'>'.language::aff_langue($ctitle).'</option>';
            
                $result3 = sql_query("SELECT sid, title FROM ".$links_DB."links_subcategories WHERE cid='$ccid' ORDER BY title");
            
                while (list($ssid, $stitle) = sql_fetch_row($result3)) 
                {
                    $sel = '';
                    if ($sid == $ssid) 
                    {
                        $sel = 'selected="selected"';
                    }
                    
                    echo '<option value="'.$ccid.'-'.$ssid.'" '.$sel.'>'.language::aff_langue($ctitle.' / '.$stitle).'</option>';
                }
            }

            echo '
                    </select>
                </div>
            </div>';
         
            global $links_topic;
            if ($links_topic) 
            {
                echo'
                <div class="form-group row">
                    <label class="col-form-label col-sm-3" for="topicL">'.translate("Sujets").'</label>
                    <div class="col-sm-9">
                    <select class="custom-select form-control" id="topicL" name="topicL">';
            
                $toplist = sql_query("SELECT topicid, topictext FROM ".$NPDS_Prefix."topics ORDER BY topictext");
            
                echo '<option value="">'.translate("Tous les sujets").'</option>';
            
                while(list($topicid, $topics) = sql_fetch_row($toplist)) 
                {
                    if ($topicid == $topicid_card) 
                    {
                        $sel = 'selected="selected" ';
                    }

                    echo '<option value="'.$topicid.'" '.$sel.'>'.$topics.'</option>';
                    $sel = '';
                }   

                echo '
                        </select>
                    </div>
                </div>';
            }

            echo'
            <div class="form-group row">
                <label class="col-form-label col-sm-12" for="xtext">'.translate("Description : (255 caractères max)").'</label>
                <div class="col-sm-12">
                    <textarea class="form-control tin" id="xtext" name="xtext" rows="10">'.$description.'</textarea>
                </div>
            </div>';
         
            tiny::aff_editeur('xtext','');
         
            echo '
                <div class="form-group row">
                    <input type="hidden" name="lid" value="'.$lid.'" />
                    <input type="hidden" name="modifysubmitter" value="'.$author.'" />
                    <input type="hidden" name="op" value="modifylinkrequestS" />
                    <div class="col-sm-12">
                        <input type="submit" class="btn btn-primary" value="'.translate("Envoyer une demande").'" />
                    </div>
                </div>
            </form>';
         
            $browse_key = $lid;
         
            include ("modules/$ModPath/sform/link_maj.php");
         
            css::adminfoot('fv', '', '', 'nodiv');
         
            include("footer.php");
        } 
        else
        {
            header("Location: modules.php?ModStart=$ModStart&ModPath=$ModPath");
        }
    }

    /**
     * [modifylinkrequestS description]
     * @param  [type] $lid             [description]
     * @param  [type] $cat             [description]
     * @param  [type] $title           [description]
     * @param  [type] $url             [description]
     * @param  [type] $description     [description]
     * @param  [type] $modifysubmitter [description]
     * @param  [type] $topicL          [description]
     * @return [type]                  [description]
     */
    public static function modifylinkrequestS($lid, $cat, $title, $url, $description, $modifysubmitter, $topicL)
     
    {
        global $links_DB;

        if (autorise_mod($lid, false)) 
        {
            $cat = explode('-', $cat);
         
            if (!array_key_exists(1, $cat))
            {
                $cat[1] = 0;
            }
         
            $title = stripslashes(str::FixQuotes($title));
            $url = stripslashes(str::FixQuotes($url));
            $description = stripslashes(str::FixQuotes($description));
         
            if ($modifysubmitter == -9) 
            {
                $modifysubmitter = '';
            }
         
            $result = sql_query("INSERT INTO ".$links_DB."links_modrequest VALUES (NULL, $lid, $cat[0], $cat[1], '$title', '$url', '$description', '$modifysubmitter', '0', '$topicL')");

            global $ModPath, $ModStart;
         
            include("header.php");
         
            echo '
            <h3 class="my-3">'.translate("Liens").'</h3>
            <hr />
            <h4 class="my-3">'.translate("Proposition de modification").'</h4>
            <div class="alert alert-success">'.translate("Merci pour cette information. Nous allons l'examiner dès que possible.").'</div>
                <a class="btn btn-primary" href="modules.php?ModPath=links&amp;ModStart=links">Index </a>';
         
            include("footer.php");
        }
    }

    /**
     * [brokenlink description]
     * @param  [type] $lid [description]
     * @return [type]      [description]
     */
    public static function brokenlink($lid) 
    {
        global $ModPath, $ModStart, $links_DB, $anonymous;
      
        include("header.php");
      
        global $user;
      
        if (isset($user)) 
        {
            global $cookie;
            $ratinguser = $cookie[1];
        } 
        else
        {
            $ratinguser = $anonymous;
        }
      
        mainheader();
      
        echo '
        <h3>'.translate("Rapporter un lien rompu").'</h3>
        <div class="alert alert-success my-3">
            '.translate("Merci de contribuer à la maintenance du site.").'
            <br />
            <strong>'.translate("Pour des raisons de sécurité, votre nom d'utilisateur et votre adresse IP vont être momentanément conservés.").'</strong>
            <br />
        </div>
        <form method="post" action="modules.php">
            <input type="hidden" name="ModPath" value="'.$ModPath.'" />
            <input type="hidden" name="ModStart" value="'.$ModStart.'" />
            <input type="hidden" name="lid" value="'.$lid.'" />
            <input type="hidden" name="modifysubmitter" value="'.$ratinguser.'" />
            <input type="hidden" name="op" value="brokenlinkS" />
            <input type="submit" class="btn btn-success" value="'.translate("Rapporter un lien rompu").'" />
        </form>';

        include("footer.php");
    }

    /**
     * [brokenlinkS description]
     * @param  [type] $lid             [description]
     * @param  [type] $modifysubmitter [description]
     * @return [type]                  [description]
     */
    public static function brokenlinkS($lid, $modifysubmitter) 
    {
        global $user, $links_DB, $ModPath, $ModStart;

        if (isset($user)) 
        {
            global $cookie;
            $ratinguser = $cookie[1];
        } 
        else
        {
            $ratinguser = $anonymous;
        }
      
        if ($modifysubmitter == $ratinguser) 
        {
            settype($lid,'integer');
            sql_query("INSERT INTO ".$links_DB."links_modrequest VALUES (NULL, $lid, 0, 0, '', '', '', '$ratinguser', 1,'')");
        }

        include("header.php");
      
        mainheader();
      
        echo '
        <h3>'.translate("Rapporter un lien rompu").'</h3>
        <div class="alert alert-success my-3">
        '.translate("Merci pour cette information. Nous allons l'examiner dès que possible.").'
        </div>';
      
        include("footer.php");
    }

}
