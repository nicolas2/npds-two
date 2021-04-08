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
use npds\utility\spam;
use npds\assets\css;
use npds\logs\logs;
use npds\security\hack;
use npds\mailler\mailler;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

/**
 * [FriendSend description]
 * @param [type] $sid     [description]
 * @param [type] $archive [description]
 */
function FriendSend($sid, $archive) 
{
    global $NPDS_Prefix;
      
    settype($sid, "integer");
    settype($archive, "integer");
      
    $result = sql_query("SELECT title, aid FROM ".$NPDS_Prefix."stories WHERE sid='$sid'");
    list($title, $aid) = sql_fetch_row($result);
      
    if (!$aid)
    {
        header("Location: index.php");
    }
      
    include ("header.php");

    echo '
    <div class="card card-body">
    <h2><i class="fa fa-at fa-lg text-muted"></i>&nbsp;'.translate("Envoi de l'article à un ami").'</h2>
    <hr />
    <p class="lead">'.translate("Vous allez envoyer cet article").' : <strong>'.language::aff_langue($title).'</strong></p>
    <form id="friendsendstory" action="friend.php" method="post">
        <input type="hidden" name="sid" value="'.$sid.'" />';
      
    global $user;
    $yn = ''; 
    $ye = '';
      
    if ($user) 
    {
        global $cookie;
        $result = sql_query("SELECT name, email FROM ".$NPDS_Prefix."users WHERE uname='$cookie[1]'");
        list($yn, $ye) = sql_fetch_row($result);
    }

     echo '
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="fname">'.translate("Nom du destinataire").'</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="fname" name="fname" required="required" maxlength="100" />
                <span class="help-block text-right"><span class="muted" id="countcar_fname"></span></span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="fmail">'.translate("Email du destinataire").'</label>
            <div class="col-sm-8">
                <input type="email" class="form-control" id="fmail" name="fmail" required="required" maxlength="100" />
                <span class="help-block text-right"><span class="muted" id="countcar_fmail"></span></span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="yname">'.translate("Votre nom").'</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="yname" name="yname" value="'.$yn.'" maxlength="100" required="required" />
                <span class="help-block text-right"><span class="muted" id="countcar_yname"></span></span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="ymail">'.translate("Votre Email").'</label>
            <div class="col-sm-8">
                <input type="email" class="form-control" id="ymail" name="ymail" value="'.$ye.'" maxlength="100" required="required" />
                <span class="help-block text-right"><span class="muted" id="countcar_ymail"></span></span>
            </div>
        </div>';
      
    echo ''.spam::Q_spambot();
      
    echo '
    <input type="hidden" name="archive" value="'.$archive.'" />
    <input type="hidden" name="op" value="SendStory" />
        <div class="form-group row">
            <div class="col-sm-8 ml-sm-auto">
               <button type="submit" class="btn btn-primary" title="'.translate("Envoyer").'"><i class="fa fa-lg fa-at"></i>&nbsp;'.translate("Envoyer").'</button>
            </div>
        </div>
    </form>';
     
    $arg1 = '
        var formulid = ["friendsendstory"];
        inpandfieldlen("yname",100);
        inpandfieldlen("ymail",100);
        inpandfieldlen("fname",100);
        inpandfieldlen("fmail",100);';
      
    css::adminfoot('fv', '', $arg1, '');
}

/**
 * [SendStory description]
 * @param [type] $sid          [description]
 * @param [type] $yname        [description]
 * @param [type] $ymail        [description]
 * @param [type] $fname        [description]
 * @param [type] $fmail        [description]
 * @param [type] $archive      [description]
 * @param [type] $asb_question [description]
 * @param [type] $asb_reponse  [description]
 */
function SendStory($sid, $yname, $ymail, $fname, $fmail, $archive, $asb_question, $asb_reponse) {
    global $user;
      
    if (!$user) 
    {
        //anti_spambot
        if (!spam::R_spambot($asb_question, $asb_reponse, '')) 
        {
            logs::Ecr_Log('security', "Send-Story Anti-Spam : name=".$yname." / mail=".$ymail, '');
            redirect_url("index.php");
            die();
        }
    }

    global $sitename, $nuke_url, $NPDS_Prefix;
      
    settype($sid, 'integer');
    settype($archive, 'integer');
      
    $result2 = sql_query("SELECT title, time, topic FROM ".$NPDS_Prefix."stories WHERE sid='$sid'");
    list($title, $time, $topic) = sql_fetch_row($result2);
      
    $result3 = sql_query("SELECT topictext FROM ".$NPDS_Prefix."topics WHERE topicid='$topic'");
    list($topictext) = sql_fetch_row($result3);
      
    $subject = translate("Article intéressant sur")." $sitename";
    $fname = hack::remove($fname);
    $message = translate("Bonjour")." $fname :\n\n".translate("Votre ami")." $yname ".translate("a trouvé cet article intéressant et a souhaité vous l'envoyer.")."\n\n".language::aff_langue($title)."\n".translate("Date :")." $time\n".translate("Sujet : ")." ".language::aff_langue($topictext)."\n\n".translate("L'article")." : <a href=\"$nuke_url/article.php?sid=$sid&amp;archive=$archive\">$nuke_url/article.php?sid=$sid&amp;archive=$archive</a>\n\n";
      
    include("config/signat.php");
      
    $fmail = hack::remove($fmail);
    $subject = hack::remove($subject);
    $message = hack::remove($message);
    $yname = hack::remove($yname);
    $ymail = hack::remove($ymail);
    $stop = false;
      
    if ((!$fmail) || ($fmail == "") || (!preg_match('#^[_\.0-9a-z-]+@[0-9a-z-\.]+\.+[a-z]{2,4}$#i', $fmail)))
    { 
        $stop = true;
    }

    if ((!$ymail) || ($ymail == "") || (!preg_match('#^[_\.0-9a-z-]+@[0-9a-z-\.]+\.+[a-z]{2,4}$#i', $ymail)))
    { 
        $stop = true;
    }

    if (!$stop) 
    {
        mailler::send_email($fmail, $subject, $message, $ymail, false,'html');
    } 
    else 
    {
        $title = '';
        $fname = '';
    }

    $title = urlencode(language::aff_langue($title));
    $fname = urlencode($fname);
      
    Header("Location: friend.php?op=StorySent&title=$title&fname=$fname");
}

/**
 * [StorySent description]
 * @param [type] $title [description]
 * @param [type] $fname [description]
 */
function StorySent($title, $fname) 
{
    include ("header.php");

    $title = urldecode($title);
    $fname = urldecode($fname);
      
    if ($fname == '')
    {
        echo '<div class="alert alert-danger">'.translate("Erreur : Email invalide").'</div>';
    }
    else
    {
        echo '<div class="alert alert-success">'.translate("L'article").' <strong>'.stripslashes($title).'</strong> '.translate("a été envoyé à").'&nbsp;'.$fname.'<br />'.translate("Merci").'</div>';
    }
      
    include ("footer.php");
}

/**
 * [RecommendSite description]
 */
function RecommendSite() 
{
    global $user;

    if ($user) 
    {
        global $cookie, $NPDS_Prefix;
        $result = sql_query("SELECT name, email FROM ".$NPDS_Prefix."users WHERE uname='$cookie[1]'");
        list($yn, $ye) = sql_fetch_row($result);
    } 
    else 
    {
        $yn = ''; 
        $ye = '';
    }

    include ("header.php");
      
    echo '
    <div class="card card-body">
    <h2>'.translate("Recommander ce site à un ami").'</h2>
    <hr />
    <form id="friendrecomsite" action="friend.php" method="post">
        <input type="hidden" name="op" value="SendSite" />
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="yname">'.translate("Votre nom").'</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="yname" name="yname" value="'.$yn.'" required="required" maxlength="100" />
                <span class="help-block text-right"><span class="muted" id="countcar_yname"></span></span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="ymail">'.translate("Votre Email").'</label>
            <div class="col-sm-8">
                <input type="email" class="form-control" id="ymail" name="ymail" value="'.$ye.'" required="required" maxlength="100" />
                <span class="help-block text-right"><span class="muted" id="countcar_ymail"></span></span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="fname">'.translate("Nom du destinataire").'</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="fname" name="fname" required="required" maxlength="100" />
                <span class="help-block text-right"><span class="muted" id="countcar_fname"></span></span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="fmail">'.translate("Email du destinataire").'</label>
            <div class="col-sm-8">
                <input type="email" class="form-control" id="fmail" name="fmail" required="required" maxlength="100" />
                <span class="help-block text-right"><span class="muted" id="countcar_fmail"></span></span>
            </div>
        </div>
        '.spam::Q_spambot().'
        <div class="form-group row">
            <div class="col-sm-8 ml-sm-auto">
               <button type="submit" class="btn btn-primary"><i class="fa fa-lg fa-at"></i>&nbsp;'.translate("Envoyer").'</button>
            </div>
        </div>
    </form>';
     
    $arg1 = '
        var formulid = ["friendrecomsite"];
        inpandfieldlen("yname",100);
        inpandfieldlen("ymail",100);
        inpandfieldlen("fname",100);
        inpandfieldlen("fmail",100);';
      
    css::adminfoot('fv', '', $arg1, '');
}

/**
 * [SendSite description]
 * @param [type] $yname        [description]
 * @param [type] $ymail        [description]
 * @param [type] $fname        [description]
 * @param [type] $fmail        [description]
 * @param [type] $asb_question [description]
 * @param [type] $asb_reponse  [description]
 */
function SendSite($yname, $ymail, $fname, $fmail, $asb_question, $asb_reponse) 
{
    global $user;

    if (!$user) 
    {
        //anti_spambot
        if (!spam::R_spambot($asb_question, $asb_reponse, '')) 
        {
            logs::Ecr_Log('security', "Friend Anti-Spam : name=".$yname." / mail=".$ymail, '');
            redirect_url("index.php");
            die();
        }
    }

    global $sitename, $nuke_url;

    $subject = translate("Site à découvrir : ")." $sitename";
    $fname = hack::remove($fname);
    $message = translate("Bonjour")." $fname :\n\n".translate("Votre ami")." $yname ".translate("a trouvé notre site")." $sitename ".translate("intéressant et a voulu vous le faire connaître.")."\n\n$sitename : <a href=\"$nuke_url\">$nuke_url</a>\n\n";
     
    include("config/signat.php");
      
    $fmail = hack::remove($fmail);
    $subject = hack::remove($subject);
    $message = hack::remove($message);
    $yname = hack::remove($yname);
    $ymail = hack::remove($ymail);
    $stop = false;
      
    if ((!$fmail) || ($fmail == '') || (!preg_match('#^[_\.0-9a-z-]+@[0-9a-z-\.]+\.+[a-z]{2,4}$#i', $fmail))) 
    {
        $stop = true;
    }
      
    if ((!$ymail) || ($ymail == '') || (!preg_match('#^[_\.0-9a-z-]+@[0-9a-z-\.]+\.+[a-z]{2,4}$#i', $ymail))) 
    {
        $stop = true;
    }
      
    if (!$stop)
    {
        mailler::send_email($fmail, $subject, $message, $ymail, false, 'html');
    }
    else
    {
        $fname = '';
    }

    Header("Location: friend.php?op=SiteSent&fname=$fname");
}

/**
 * [SiteSent description]
 * @param [type] $fname [description]
 */
function SiteSent($fname) 
{
    include ('header.php');
      
    if ($fname == '')
    {
        echo '
        <div class="alert alert-danger lead" role="alert">
            <i class="fa fa-exclamation-triangle fa-lg"></i>&nbsp;
               '.translate("Erreur : Email invalide").'
        </div>';
    }
    else
    {
        echo '
        <div class="alert alert-success lead" role="alert">
            <i class="fa fa-exclamation-triangle fa-lg"></i>&nbsp;
            '.translate("Nos références ont été envoyées à ").' '.$fname.', <br />
            <strong>'.translate("Merci de nous avoir recommandé").'</strong>
        </div>';
    }

    include ('footer.php');
}

settype($op, 'string');
settype($archive, 'string');

switch ($op) 
{
    case 'FriendSend':
        FriendSend($sid, $archive);
    break;

    case 'SendStory':
        SendStory($sid, $yname, $ymail, $fname, $fmail, $archive, $asb_question, $asb_reponse);
    break;

    case 'StorySent':
        StorySent($title, $fname);
    break;

    case 'SendSite':
        SendSite($yname, $ymail, $fname, $fmail, $asb_question, $asb_reponse);
    break;

    case 'SiteSent':
        SiteSent($fname);
    break;

    default:
        RecommendSite();
    break;
}
