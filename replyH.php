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
use npds\cache\cache;
use npds\error\error;
use npds\forum\forumtopics;
use npds\forum\forumauth;
use npds\forum\forumaddon;
use npds\auth\auth;
use npds\security\ip;
use npds\utility\spam;
use npds\logs\logs;
use npds\utility\code;
use npds\security\hack;
use npds\pixels\pixel;
use npds\mailler\mailler;
use npds\subscribe\subscribe;
use npds\views\theme;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

if ($SuperCache)
{
    $cache_obj = new cacheManager();
}
else
{
    $cache_obj = new cacheEmpty();
}

include('auth.php');

global $NPDS_Prefix;

settype($cancel, 'string');

if ($cancel)
{
    header("Location: viewtopicH.php?topic=$topic&forum=$forum");
}

$rowQ1 = cache::Q_Select("SELECT forum_name, forum_moderator, forum_type, forum_pass, forum_access, arbre FROM ".$NPDS_Prefix."forums WHERE forum_id = '$forum'", 3600);

if (!$rowQ1)
{
    error::forumerror('0001');
}

$myrow = $rowQ1[0];
$forum_name = $myrow['forum_name'];
$forum_access = $myrow['forum_access'];
$forum_type = $myrow['forum_type'];
$mod = $myrow['forum_moderator'];

if (($forum_type == 1) and ($Forum_passwd != $myrow['forum_pass']))
{
    header("Location: forum.php");
}

if ($forum_access == 9)
{
    header("Location: forum.php");
}

if (forumtopics::is_locked($topic))
{
    error::forumerror('0025');
}

if (!forumtopics::does_exists($forum, "forum") || !forumtopics::does_exists($topic, "topic"))
{
    error::forumerror('0026');
}

settype($submitS, 'string');
settype($stop, 'integer');

if ($submitS) 
{
    if ($message == '')
    { 
        $stop = 1;
    }

    if (!isset($user)) 
    {
        if ($forum_access == 0) 
        {
            $userdata = array("uid" => 1);
            $modo = '';

            include("header.php");
        } 
        else 
        {
            if (($username == '') or ($password == '')) 
            {
                error::forumerror('0027');
            } 
            else 
            {
                $result = sql_query("SELECT pass FROM ".$NPDS_Prefix."users WHERE uname='$username'");
                list($pass) = sql_fetch_row($result);

                if ((password_verify($password, $pass)) and ($pass != '')) 
                {
                    $userdata = auth::get_userdata($username);
               
                    if ($userdata['uid'] == 1)
                    {
                        error::forumerror('0027');
                    }
                    else
                    {
                        include("header.php");
                    }
                }
                else
                {
                    error::forumerror('0028');
                }

                $modo = forumauth::user_is_moderator($username, $pass, $forum_access);
            
                if ($forum_access == 2) 
                {
                    if (!$modo)
                    {
                        error::forumerror('0027');
                    }
                }
            }
        }
    } 
    else 
    {
        $userX = base64_decode($user);
        $userdata = explode(':', $userX);
        $modo = forumauth::user_is_moderator($userdata[0], $userdata[2], $forum_access);
      
        if ($forum_access == 2) 
        {
            if (!$modo)
            {
                error::forumerror('0027');
            }
        }

        $userdata = auth::get_userdata($userdata[1]);
      
        include("header.php");
    }

    // Either valid user/pass, or valid session. continue with post.
    if ($stop != 1) 
    {
        $poster_ip = ip::get();
      
        if ($dns_verif)
        {
            $hostname = @gethostbyaddr($poster_ip);
        }
        else
        {
            $hostname = '';
        }

        // anti flood
        forumaddon::anti_flood($modo, $anti_flood, $poster_ip, $userdata, $gmt);

        //anti_spambot
        if (!spam::R_spambot($asb_question, $asb_reponse, $message)) 
        {
            logs::Ecr_Log('security', 'Forum Anti-Spam : forum='.$forum.' / topic='.$topic, '');
         
            redirect_url("index.php");
            die();
        }

        if ($allow_html == 0 || isset($html)) 
        {
            $message = htmlspecialchars($message, ENT_COMPAT|ENT_HTML401, cur_charset);
        }
      
        if (isset($sig) && $userdata['uid'] != 1)
        {
            $message .= ' [addsig]';
        } 

        if (($forum_type != '6') and ($forum_type != '5')) 
        {
            $message = code::aff_code($message);
            $message = str_replace("\n", '<br />', $message);
        }

        if (($allow_bbcode == 1) and ($forum_type != '6') and ($forum_type != '5')) 
        {
            $message = pixel::smile($message);
        }

        if (($forum_type != '6') and ($forum_type != '5'))
        {
            $message = forumaddon::make_clickable($message);
            $message = hack::remove($message);
        }

        $image_subject = hack::remove($image_subject);
        $message = addslashes($message);
        $time = date("Y-m-d H:i:s", time()+((integer)$gmt*3600));
      
        $sql = "INSERT INTO ".$NPDS_Prefix."posts (topic_id, image, forum_id, poster_id, post_text, post_time, poster_ip, poster_dns, post_idH) VALUES ('$topic', '$image_subject', '$forum', '".$userdata['uid']."', '$message', '$time', '$poster_ip', '$hostname', $post)";
      
        if (!$result = sql_query($sql))
        {
            error::forumerror('0020');
        }
        else
        {
            $IdPost = sql_last_id();
        }
      
        $sql = "UPDATE ".$NPDS_Prefix."forumtopics SET topic_time = '$time', current_poster = '".$userdata['uid']."' WHERE topic_id = '$topic'";
      
        if (!$result = sql_query($sql))
        {
            error::forumerror('0020');
        }
      
        $sql = "UPDATE ".$NPDS_Prefix."forum_read SET status='0' where topicid = '$topic' and uid <> '".$userdata['uid']."'";
      
        if (!$r = sql_query($sql))
        {
            error::forumerror('0001');
        }
      
        $sql = "UPDATE ".$NPDS_Prefix."users_status SET posts=posts+1 WHERE (uid = '".$userdata['uid']."')";
        $result = sql_query($sql);
      
        if (!$result)
        {
            error::forumerror('0029');
        }
      
        $sql = "SELECT t.topic_notify, u.email, u.uname, u.uid, u.user_langue FROM ".$NPDS_Prefix."forumtopics t, ".$NPDS_Prefix."users u WHERE t.topic_id = '$topic' AND t.topic_poster = u.uid";
      
        if (!$result = sql_query($sql))
        {
            error::forumerror('0022');
        }
      
        $m = sql_fetch_assoc($result);
        $sauf = '';
      
        if ( ($m['topic_notify'] == 1) && ($m['uname'] != $userdata['uname']) ) 
        {
            include_once("language/lang-multi.php");

            $resultZ = sql_query("SELECT topic_title FROM ".$NPDS_Prefix."forumtopics WHERE topic_id='$topic'");
            list($title_topic) = sql_fetch_row($resultZ);
         
            $subject = strip_tags($forum_name)."/".$title_topic." : ".translate_ml($m['user_langue'], "Une r??ponse ?? votre dernier Commentaire a ??t?? post??.");
            $message = $m['uname']."\n\n";
            $message .= translate_ml($m['user_langue'], "Vous recevez ce Mail car vous avez demand?? ?? ??tre inform?? lors de la publication d'une r??ponse.")."\n";
            $message .= translate_ml($m['user_langue'], "Pour lire la r??ponse")." : ";
            $message .= "<a href=\"$nuke_url/viewtopicH.php?topic=$topic&forum=$forum\">$nuke_url/viewtopicH.php?topic=$topic&forum=$forum</a>\n\n";
         
            include("config/signat.php");

            mailler::send_email($m['email'], $subject, $message, '', true, 'html');
            $sauf = $m['uid'];
        }

        global $subscribe;
        if ($subscribe) 
        {
            if (subscribe::subscribe_query($userdata['uid'], "forum", $forum)) 
            {
                $sauf = $userdata['uid'];
            }
            subscribe::subscribe_mail('forum', $topic, $forum, '', $sauf);
        }

        if (isset($upload)) 
        {
            include("modules/upload/upload_forum.php");
            win_upload("forum_npds", $IdPost, $forum, $topic, "win");
        }

        redirect_url("viewtopicH.php?forum=$forum&topic=$topic");
    } 
    else 
    {
        echo "<p align=\"center\">".translate("Vous devez taper un message ?? poster.")."<br /><br />";
        echo "[ <a href=\"javascript:history.go(-1)\" class=\"noir\">".translate("Retour en arri??re")."</a> ]</p>";
    }
} 
else 
{
    include('header.php');

    if ($allow_bbcode == 1)
    {
        include("assets/formhelp.java.php");
    }

    list($topic_title, $topic_status) = sql_fetch_row(sql_query("select topic_title, topic_status from ".$NPDS_Prefix."forumtopics where topic_id='$topic'"));
   
    $userX = base64_decode($user);
    $userdata = explode(':', $userX);
    $moderator = forumauth::get_moderator($mod);
    $moderator = explode(' ', $moderator);
    $Mmod = false;

    echo '
    <p class="lead">
        <a href="forum.php">'.translate("Index du forum").'</a>&nbsp;&raquo;&raquo;&nbsp;
        <a href="viewforum.php?forum='.$forum.'">'.stripslashes($forum_name).'</a>&nbsp;&raquo;&raquo;&nbsp;'.$topic_title.'
    </p>
    <div class="card">
        <div class="card-block-small">
            '.translate("Mod??r?? par : ");
   
    for ($i = 0; $i < count($moderator); $i++) 
    {
        $modera = auth::get_userdata($moderator[$i]);
      
        if ($modera['user_avatar'] != '') 
        {
            if (stristr($modera['user_avatar'],"users_private")) 
            {
                $imgtmp = $modera['user_avatar'];
            } 
            else 
            {
                if ($ibid = theme::theme_image("forum/avatar/".$modera['user_avatar'])) 
                {
                    $imgtmp = $ibid;
                } 
                else 
                {
                    $imgtmp = "assets/images/forum/avatar/".$modera['user_avatar'];
                }
            }
        }

        echo '<a href="user.php?op=userinfo&amp;uname='.$moderator[$i].'"><img width="48" height="48" class=" img-thumbnail img-fluid n-ava" src="'.$imgtmp.'" alt="'.$modera['uname'].'" title="'.$modera['uname'].'" data-toggle="tooltip" /></a>';
      
        if (isset($user))
        {
            if (($userdata[1] == $moderator[$i])) 
            { 
                $Mmod = true;
            }
        }
    }

    echo '
        </div>
    </div>
    <h4 class="hidden-xs-down">'.translate("Poster une r??ponse dans le sujet").'</h4>
    <form action="replyH.php" method="post" name="coolsus">';

    echo '<blockquote class="blockquote hidden-xs-down"><p>'.translate("A propos des messages publi??s :").'<br />';
   
    if ($forum_access == 0) 
    {
        echo translate("Les utilisateurs anonymes peuvent poster de nouveaux sujets et des r??ponses dans ce forum.");
    } 
    else if($forum_access == 1) 
    {
        echo translate("Tous les utilisateurs enregistr??s peuvent poster de nouveaux sujets et r??pondre dans ce forum.");
    } 
    else if($forum_access == 2) 
    {
        echo translate("Seuls les mod??rateurs peuvent poster de nouveaux sujets et r??pondre dans ce forum.");
    }

    echo '</p></blockquote>';

    $allow_to_reply = false;
   
    if ($forum_access == 0) 
    {
        $allow_to_reply = true;
    } 
    elseif ($forum_access == 1) 
    {
        if (isset($user)) 
        {
            $allow_to_reply = true;
        }
    } 
    elseif ($forum_access == 2) 
    {
        if (forumauth::user_is_moderator($userdata[0], $userdata[2], $forum_access)) 
        {
            $allow_to_reply = true;
        }
    }

    if ($topic_status != 0)
    {
        $allow_to_reply = false;
    }

    settype($submitP, 'string');
    settype($citation, 'integer');
   
    if ($allow_to_reply) 
    {
        if ($submitP)
        {
            $acc = 'reply';
            $message = stripslashes($message);
         
            include ("preview.php");
        } 
        else 
        {
            $message = '';
        }

        echo '
        <br />
        <span class="lead">'.translate("Identifiant : ");
      
        if (isset($user))
        {
            echo $userdata[1].'</span>';
        }
        else
        {
            echo $anonymous.'</span>';
        }

        settype($image_subject, 'string');
   
        if ($smilies) 
        {
            echo '
            <div class="hidden-xs-down form-group row">
                <label class="form-control-label col-sm-12">'.translate("Icone du message").'</label>
                <div class="col-sm-12">
                    <div class="card card-body n-fond_subject d-flex flex-row flex-wrap">
                        '.pixel::emotion_add($image_subject).'
                    </div>
                </div>
            </div>';
        }

        echo '
            <div class="form-group row">
                <label class="form-control-label col-sm-12" for="message">'.translate("Message").'</label>
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-left">';
   
        pixel::putitems('ta_replyh');
      
        echo '</div>';

        if ($allow_html == 1) 
        {
            echo '<span class="text-success float-right mt-2" title="HTML '.translate("Activ??").'" data-toggle="tooltip"><i class="fa fa-code fa-lg"></i></span>'.forumaddon::HTML_Add();
        } 
        else
        {
            echo '<span class="text-danger float-right mt-2" title="HTML '.translate("D??sactiv??").'" data-toggle="tooltip"><i class="fa fa-code fa-lg"></i></span>';
        }
   
        echo '
                </div>
            <div class="card-body">';
      
        if ($citation && !$submitP) 
        {
            $sql = "SELECT p.post_text, p.post_time, u.uname FROM ".$NPDS_Prefix."posts p, ".$NPDS_Prefix."users u WHERE post_id = '$post' AND p.poster_id = u.uid";
         
            if ($r = sql_query($sql)) 
            {
                $m = sql_fetch_assoc($r);
                $text = $m['post_text'];
            
                if (($allow_bbcode) and ($forum_type != 6) and ($forum_type != 5)) 
                {
                    $text = pixel::smile($text);
                    $text = str_replace('<br />', "\n", $text);
                } 
                else
                {
                    $text = htmlspecialchars($text, ENT_COMPAT|ENT_HTML401, cur_charset);
                }
            
                $text = stripslashes($text);
            
                if ($m['post_time'] != '' && $m['uname'] != '')
                {
                    $reply = '<blockquote class="blockquote">'.translate("Citation").' : <strong>'.$m['uname'].'</strong><br />'.$text.'</blockquote>';
                }
                else
                {
                    $reply = $text."\n";
                }
            
                $reply = preg_replace("#\[hide\](.*?)\[\/hide\]#si", '', $reply);
            } 
            else
            {
                $reply = translate("Erreur de connexion ?? la base de donn??es")."\n";
            }
         
            $message = $reply;
        }

        if ($allow_bbcode)
        {
            $xJava = ' onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onfocus="storeForm(this)"';
        }
      
        echo '
                        <textarea id="ta_replyh" class="form-control" '.$xJava.' name="message" rows="15" >'.$message.'</textarea>
                    </div>
                    <div class="card-footer p-0">
                        <span class="d-block">
                        <button class="btn btn-link" type="submit" value="'.translate("Pr??visualiser").'" name="submitP" title="'.translate("Pr??visualiser").'" data-toggle="tooltip" ><i class="fa fa-eye fa-lg"></i></button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label class="form-control-label col-sm-12">'.translate("Options").'</label>';
      
        if (($allow_html == 1) and ($forum_type != '6') and ($forum_type != '5')) 
        {
            if (isset($html)) 
            {
                $sethtml = 'checked="checked"';
            } 
            else 
            {
                $sethtml = '';
            }

            echo '
            <div class="col-sm-12">
                <div class="checkbox">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="html" name="html" '.$sethtml.' />
                        <label class="custom-control-label" for="html">'.translate("D??sactiver le html pour cet envoi").'</label>
                    </div>
                </div>';
        }

        if ($user) 
        {
            if ($allow_sig == 1) 
            {
                $asig = sql_query("SELECT attachsig FROM ".$NPDS_Prefix."users_status WHERE uid='$cookie[0]'");
                list($attachsig) = sql_fetch_row($asig);
            
                if ($attachsig == 1) 
                {
                    $s = 'checked="checked"'; 
                }
                else 
                {
                    $s = '';
                }
            
                if (($forum_type != '6') and ($forum_type != '5'))
                {
               
                    echo '
                    <div class="checkbox">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="sig" name="sig" '.$s.' />
                            <label class="custom-control-label" for="sig">'.translate("Afficher la signature").'</label>
                            <small class="help-text">'.translate("Cela peut ??tre retir?? ou ajout?? dans vos param??tres personnels").'</small>
                        </div>
                    </div>';
                }
            }

            settype($upload, 'string');
            settype($up, 'string');
         
            if ($allow_upload_forum) 
            {
                if ($upload == 'on') 
                {
                    $up = 'checked="checked"';
                }

                echo '
                    <div class="checkbox">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="upload" name="upload" '.$up.' />
                            <label class="custom-control-label" for="upload">'.translate("Charger un fichier une fois l'envoi accept??").'</label>
                        </div>
                    </div>';
            }
        }

        echo '
            </div>
        </div>'
        .spam::Q_spambot().'
        <div class="form-group row">
            <div class="col-sm-12">
                <input type="hidden" name="forum" value="'.$forum.'" />
                <input type="hidden" name="topic" value="'.$topic.'" />
                <input type="hidden" name="post" value="'.$post.'" />
                <button class="btn btn-primary" type="submit" name="submitS" value="'.translate("Valider").'" accesskey="s" />'.translate("Valider").'</button>&nbsp;
                <button class="btn btn-danger" type="submit" value="'.translate("Annuler la contribution").'" name="cancel" title="'.translate("Annuler la contribution").'" data-toggle="tooltip" >'.translate("Annuler la contribution").'</button>
            </div>
        </div>';
    } 
    else 
    {
        echo '
        <div class="alert alert-danger">'.translate("Vous n'??tes pas autoris?? ?? participer ?? ce forum").'</div>';
    }

    echo '
    </form>';
}

include('footer.php');
