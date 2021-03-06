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
use npds\cache\cache;
use npds\security\ip;
use npds\language\language;
use npds\assets\css;
use npds\views\theme;
use npds\utility\str;
use npds\mailler\mailler;
use npds\banners\banner;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

/**
 * [viewbanner description]
 * @return [type] [description]
 */
function viewbanner() 
{
    global $NPDS_Prefix;
    
    $okprint = false; 
    $while_limit = 3; 
    $while_cpt = 0;

    $bresult = sql_query("SELECT bid FROM ".$NPDS_Prefix."banner WHERE userlevel!='9'");
    $numrows = sql_num_rows($bresult);
    
    while ((!$okprint) and ($while_cpt < $while_limit)) 
    {
        // More efficient random stuff, thanks to Cristian Arroyo from http://www.planetalinux.com.ar
        if ($numrows > 0) 
        {
            mt_srand((double)microtime()*1000000);
            $bannum = mt_rand(0, $numrows);
        } 
        else
        {
            break;
        }

        $bresult2 = sql_query("SELECT bid, userlevel FROM ".$NPDS_Prefix."banner WHERE userlevel!='9' LIMIT $bannum,1");
        list($bid, $userlevel) = sql_fetch_row($bresult2);
        
        if ($userlevel == 0) 
        {
            $okprint = true;
        } 
        else 
        {
            if ($userlevel == 1) 
            {
                if (auth::secur_static("member")) 
                {
                    $okprint = true;
                }
            }

            if ($userlevel == 3) 
            {
                if (auth::secur_static("admin")) 
                {
                    $okprint = true;
                }
            }
       }
       $while_cpt = $while_cpt+1;
    }
    
    // Le risque est de sortir sans un BID valide
    if (!isset($bid)) 
    {
        $rowQ1 = cache::Q_Select("SELECT bid FROM ".$NPDS_Prefix."banner WHERE userlevel='0' LIMIT 0,1", 86400);
        
        if($rowQ1) 
        {
            $myrow = $rowQ1[0];// erreur ?? l'install quand on n'a pas de banner dans la base ....
            $bid = $myrow['bid'];
            $okprint = true;
        }
    }

    if ($okprint) 
    {
        global $myIP;
        $myhost = ip::get();
        
        if ($myIP != $myhost) 
        {
            sql_query("UPDATE ".$NPDS_Prefix."banner SET impmade=impmade+1 WHERE bid='$bid'");
        }
        
        if (($numrows > 0) and ($bid)) 
        {
            $aborrar = sql_query("SELECT cid, imptotal, impmade, clicks, imageurl, clickurl, date FROM ".$NPDS_Prefix."banner WHERE bid='$bid'");
            list($cid, $imptotal, $impmade, $clicks, $imageurl, $clickurl, $date) = sql_fetch_row($aborrar);
            
            if ($imptotal == $impmade) 
            {
                sql_query("INSERT INTO ".$NPDS_Prefix."bannerfinish VALUES (NULL, '$cid', '$impmade', '$clicks', '$date', now())");
                sql_query("DELETE FROM ".$NPDS_Prefix."banner WHERE bid='$bid'");
            }

            if ($imageurl != '') 
            {
                echo'<a href="banners.php?op=click&amp;bid='.$bid.'" target="_blank"><img class="img-fluid" src="'.language::aff_langue($imageurl).'" alt="" /></a>';
            } 
            else 
            {
                if (stristr($clickurl, '.txt')) 
                {
                    if (file_exists($clickurl)) 
                    {
                        include_once($clickurl);
                    }
                } 
                else 
                {
                    echo $clickurl;
                }
            }
        }
    }
}

/**
 * [clickbanner description]
 * @param  [type] $bid [description]
 * @return [type]      [description]
 */
function clickbanner($bid) 
{
    global $NPDS_Prefix;
    
    $bresult = sql_query("SELECT clickurl FROM ".$NPDS_Prefix."banner WHERE bid='$bid'");
    list($clickurl) = sql_fetch_row($bresult);
    
    sql_query("UPDATE ".$NPDS_Prefix."banner SET clicks=clicks+1 WHERE bid='$bid'");
    sql_free_result($bresult);
    
    if ($clickurl == '') 
    {
        global $nuke_url;
        $clickurl = $nuke_url;
    }

    Header("Location: ".language::aff_langue($clickurl));
}

/**
 * [clientlogin description]
 * @return [type] [description]
 */
function clientlogin() 
{
    banner::header_page();
    echo '
    <div class="card card-body mb-3">
    <h3 class="mb-4"><i class="fas fa-sign-in-alt fa-lg mr-3"></i>'.translate("Connection").'</h3>
        <form action="banners.php" method="post">
        <fieldset>
            <div class="form-group row">
                <label class="form-control-label col-sm-4" for="login">'.translate("Identifiant ").'</label>
                <div class="col-sm-8">
                    <input class="form-control" type="text" id="login" name="login" maxlength="10" required="required" />
                </div>
            </div>
            <div class="form-group row">
                <label class="form-control-label col-sm-4" for="pass">'.translate("Mot de passe").'</label>
                <div class="col-sm-8">
                    <input class="form-control" type="password" id="pass" name="pass" maxlength="10" required="required" />
                    <span class="help-block">'.translate("Merci de saisir vos informations").'</span>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-8 ml-sm-auto">
                    <input type="hidden" name="op" value="Ok" />
                    <button class="btn btn-primary col-sm-6 col-12" type="submit">'.translate("Valider").'</button>
                </div>
            </div>
        </fieldset>
        </form>
    </div>';
    css::adminfoot('fv', '', '', 'no');
    
    banner::footer_page();
}

/**
 * [bannerstats description]
 * @param  [type] $login [description]
 * @param  [type] $pass  [description]
 * @return [type]        [description]
 */
function bannerstats($login, $pass) 
{
    global $NPDS_Prefix;
    
    $result = sql_query("SELECT cid, name, passwd FROM ".$NPDS_Prefix."bannerclient WHERE login='$login'");
    list($cid, $name, $passwd) = sql_fetch_row($result);
    
    if ($login == '' AND $pass == '' OR $pass == '') 
    {
        banner::IncorrectLogin();
    }  
    else 
    {
        if ($pass == $passwd) 
        {
            banner::header_page();
            echo '
            <h3>'.translate ("Banni??res actives pour").' '.$name.'</h3>
            <table data-toggle="table" data-search="true" data-striped="true" data-mobile-responsive="true" data-show-export="true" data-show-columns="true" data-icons="icons" data-icons-prefix="fa">
                <thead>
                    <tr>
                        <th class="n-t-col-xs-1" data-halign="center" data-align="right"  data-sortable="true">ID</th>
                        <th class="n-t-col-xs-2" data-halign="center" data-align="right" data-sortable="true">'.translate("R??alis??").'</th>
                        <th class="n-t-col-xs-2" data-halign="center" data-align="right" data-sortable="true">'.translate("Impressions").'</th>
                        <th class="n-t-col-xs-2" data-halign="center" data-align="right" data-sortable="true">'.translate("Imp. restantes").'</th>
                        <th class="n-t-col-xs-2" data-halign="center" data-align="right" data-sortable="true">'.translate("Clics").'</th>
                        <th class="n-t-col-xs-1" data-halign="center" data-align="right" data-sortable="true">% '.translate("Clics").'</th>
                        <th class="n-t-col-xs-1" data-halign="center" data-align="right">'.translate("Fonctions").'</th>
                    </tr>
                </thead>
                <tbody>';

            $result = sql_query("SELECT bid, imptotal, impmade, clicks, date FROM ".$NPDS_Prefix."banner WHERE cid='$cid'");
            
            while (list($bid, $imptotal, $impmade, $clicks, $date) = sql_fetch_row($result)) 
            {
                $rowcolor = theme::tablos();
                
                if ($impmade == 0) 
                {
                   $percent = 0;
                } 
                else 
                {
                   $percent = substr(100 * $clicks / $impmade, 0, 5);
                }

                if ($imptotal == 0) 
                {
                   $left = translate("Illimit??");
                } 
                else 
                {
                   $left = $imptotal-$impmade;
                }

                echo '
                <tr>
                    <td>'.$bid.'</td>
                    <td>'.$impmade.'</td>
                    <td>'.$imptotal.'</td>
                    <td>'.$left.'</td>
                    <td>'.$clicks.'</td>
                    <td>'.$percent.'%</td>
                    <td><a href="banners.php?op=EmailStats&amp;login='.$login.'&amp;cid='.$cid.'&amp;bid='.$bid.'" ><i class="far fa-envelope fa-lg mr-2" title="E-mail Stats"></i></a></td>
                </tr>';
            }
             
            global $nuke_url, $sitename;
            
            echo '
                    </tbody>
                </table>
            <a href="'.$nuke_url.'" class="header" target="_blank">'.$sitename.'</a>';
             
            $result = sql_query("SELECT bid, imageurl, clickurl FROM ".$NPDS_Prefix."banner WHERE cid='$cid'");

            while (list($bid, $imageurl, $clickurl) = sql_fetch_row($result)) 
            {
                $numrows = sql_num_rows($result);
                echo '<div class="card card-body mb-3">';

                if ($imageurl != '') 
                {
                    echo '
                    <p><img src="'.$imageurl.'" class="img-fluid" />';
                } 
                else 
                {
                    echo '<p>';
                    echo $clickurl;
                }

                echo '
                <h4 class="mb-2">Banner ID : '.$bid.'</h4>';
                
                if ($imageurl != '') 
                {
                   echo '<p>'.translate("Cette banni??re est affich??e sur l'url").' : <a href="'.language::aff_langue($clickurl).'" target="_Blank" >[ URL ]</a></p>';
                }

                echo '
                <form action="banners.php" method="get">';
                
                if ($imageurl != '') 
                {
                    echo '
                    <div class="form-group row">
                        <label class="control-label col-sm-12" for="url">'.translate("Changer").' URL</label>
                        <div class="col-sm-12">
                            <input class="form-control" type="text" name="url" maxlength="200" value="'.$clickurl.'" />
                        </div>
                    </div>';
                } 
                else 
                {
                    echo '
                    <div class="form-group row">
                        <label class="control-label col-sm-12" for="url">'.translate("Changer").' URL</label>
                        <div class="col-sm-12">
                            <input class="form-control" type="text" name="url" maxlength="200" value="'.htmlentities($clickurl, ENT_QUOTES, cur_charset).'" />
                        </div>
                    </div>';
                }

                echo '
                <input type="hidden" name="login" value="'.$login.'" />
                <input type="hidden" name="bid" value="'.$bid.'" />
                <input type="hidden" name="pass" value="'.$pass.'" />
                <input type="hidden" name="cid" value="'.$cid.'" />
                <input class="btn btn-primary" type="submit" name="op" value="'.translate("Changer").'" />
                </form>
                </p>
                </div>';
            }
         
            // Finnished Banners
            echo "<br />";
            echo '
            <h3>'.translate("Banni??res termin??es pour").' '.$name.'</h3>
            <table data-toggle="table" data-search="true" data-striped="true" data-mobile-responsive="true" data-show-export="true" data-show-columns="true" data-icons="icons" data-icons-prefix="fa">
                <thead>
                    <tr>
                        <th class="n-t-col-xs-1" data-halign="center" data-align="right" data-sortable="true">ID</td>
                        <th data-halign="center" data-align="right" data-sortable="true">'.translate("Impressions").'</th>
                        <th data-halign="center" data-align="right" data-sortable="true">'.translate("Clics").'</th>
                        <th class="n-t-col-xs-1" data-halign="center" data-align="right" data-sortable="true">% '.translate("Clics").'</th>
                        <th data-halign="center" data-align="right" data-sortable="true">'.translate("Date de d??but").'</th>
                        <th data-halign="center" data-align="right" data-sortable="true">'.translate("Date de fin").'</th>
                    </tr>
            </thead>
            <tbody>';
             
            $result = sql_query("SELECT bid, impressions, clicks, datestart, dateend FROM ".$NPDS_Prefix."bannerfinish WHERE cid='$cid'");
             
            while (list($bid, $impressions, $clicks, $datestart, $dateend) = sql_fetch_row($result)) 
            {
                $percent = substr(100 * $clicks / $impressions, 0, 5);
                echo '
                <tr>
                    <td>'.$bid.'</td>
                    <td>'.str::wrh($impressions).'</td>
                    <td>'.$clicks.'</td>
                    <td>'.$percent.' %</td>
                    <td><small>'.$datestart.'</small></td>
                    <td><small>'.$dateend.'</small></td>
                </tr>';
            }
            echo '
                </tbody>
            </table>';
            
            css::adminfoot('fv', '', '', 'no');
            
            banner::footer_page();
        } 
        else 
        {
            banner::IncorrectLogin();
        }
    }
}

/**
 * [EmailStats description]
 * @param [type] $login [description]
 * @param [type] $cid   [description]
 * @param [type] $bid   [description]
 */
function EmailStats($login, $cid, $bid) 
{
    global $NPDS_Prefix;
       
    $result = sql_query("SELECT login FROM ".$NPDS_Prefix."bannerclient WHERE cid='$cid'");
    list($loginBD) = sql_fetch_row($result);
       
    if ($login == $loginBD) 
    {
        $result2 = sql_query("SELECT name, email FROM ".$NPDS_Prefix."bannerclient WHERE cid='$cid'");
        list($name, $email) = sql_fetch_row($result2);
          
        if ($email == '') 
        {
            banner::header_page();
            
            echo "<p align=\"center\"><br />".translate("Les statistiques pour la banni??res ID")." : $bid ".translate("ne peuvent pas ??tre envoy??es.")."<br /><br />
                ".translate("Email non rempli pour : ")." $name<br /><br /><a href=\"javascript:history.go(-1)\" >".translate("Retour en arri??re")."</a></p>";
            
            banner::footer_page();
        } 
        else 
        {
            $result = sql_query("SELECT bid, imptotal, impmade, clicks, imageurl, clickurl, date FROM ".$NPDS_Prefix."banner WHERE bid='$bid' AND cid='$cid'");
            list($bid, $imptotal, $impmade, $clicks, $imageurl, $clickurl, $date) = sql_fetch_row($result);
            
            if ($impmade == 0) 
            {
                $percent = 0;
            } 
            else 
            {
                $percent = substr(100 * $clicks / $impmade, 0, 5);
            }

            if ($imptotal == 0) 
            {
                $left = translate("Illimit??");
                $imptotal = translate("Illimit??");
            } 
            else 
            {
                $left = $imptotal-$impmade;
            }
             
            global $sitename, $gmt;
             
            $fecha = date(translate("dateinternal"),time()+((integer)$gmt*3600));
            $subject = translate("Banni??res - Publicit??").' : '.$sitename;
            
            $message  = "Client : $name\n".translate("Banni??re")." ID : $bid\n".translate("Banni??re")." Image : $imageurl\n".translate("Banni??re")." URL : $clickurl\n\n";
            $message .= "Impressions ".translate("R??serv??es")." : $imptotal\nImpressions ".translate("R??alis??es")." : $impmade\nImpressions ".translate("Restantes")." : $left\nClicks ".translate("Re??us")." : $clicks\nClicks ".translate("Pourcentage")." : $percent%\n\n";
            $message .= translate("Rapport g??n??r?? le").' : '."$fecha\n\n";
            
            include("config/signat.php");

            mailler::send_email($email, $subject, $message, '', true, 'text');
            
            banner::header_page();
             
            echo '
            <div class="jumbotron">
                <p>'.$fecha.'</p>
                <p>'.translate("Statistics for Banner ID").' : '.$bid.' '.translate("ont ??t?? envoy??es.").'</p>
                <p>'.$email.' : Client : '.$name.'</p>
                <p><a href="javascript:history.go(-1)" class="btn btn-primary btn-lg">'.translate("Retour en arri??re").'</a></p>
            </div>';
        }
    } 
    else 
    {
        banner::header_page();
        
        echo "<p align=\"center\"><br />".translate("Identifiant incorrect !")."<br /><br />".translate("Merci de")." <a href=\"banners.php?op=login\" class=\"noir\">".translate("vous reconnecter.")."</a></p>";
    }

    banner::footer_page();
}

/**
 * [change_banner_url_by_client description]
 * @param  [type] $login [description]
 * @param  [type] $pass  [description]
 * @param  [type] $cid   [description]
 * @param  [type] $bid   [description]
 * @param  [type] $url   [description]
 * @return [type]        [description]
 */
function change_banner_url_by_client($login, $pass, $cid, $bid, $url) 
{
    global $NPDS_Prefix;
        
    banner::header_page();
        
    $result = sql_query("SELECT passwd FROM ".$NPDS_Prefix."bannerclient WHERE cid='$cid'");
    list($passwd) = sql_fetch_row($result);
        
    if (!empty($pass) AND $pass == $passwd) 
    {
        sql_query("UPDATE ".$NPDS_Prefix."banner SET clickurl='$url' WHERE bid='$bid'");
        sql_query("UPDATE ".$NPDS_Prefix."banner SET clickurl='$url' WHERE bid='$bid'");
        
        echo "<p align=\"center\"><br />".translate("Vous avez chang?? l'url de la banni??re")."<br /><br /><a href=\"javascript:history.go(-1)\" class=\"noir\">".translate("Retour en arri??re")."</a></p>";
    } 
    else 
    {
        echo "<p align=\"center\"><br />".translate("Identifiant incorrect !")."<br /><br />".translate("Merci de")." <a href=\"banners.php?op=login\" class=\"noir\">".translate("vous reconnecter.")."</a></p>";
    }
        
    banner::footer_page();
}

settype($op, 'string');

switch ($op) 
{
    case 'click':
        clickbanner($bid);
    break;

    case 'login':
        clientlogin();
    break;

    case 'Ok':
        bannerstats($login, $pass);
    break;

    case translate('Changer'):
        change_banner_url_by_client($login, $pass, $cid, $bid, $url);
    break;

    case 'EmailStats':
        EmailStats($login, $cid, $bid);
    break;

    default:
        if ($banners) 
        {
            viewbanner();
        } 
        else 
        {
            redirect_url('index.php');
        }
    break;
}
