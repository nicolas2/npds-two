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
use npds\groupe\groupe;
use npds\forum\forumauth;
use npds\forum\forumposts;
use npds\auth\auth;
use npds\utility\spam;
use npds\views\theme;
use npds\date\date;
use npds\pixels\pixel;
use npds\media\video;


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

if ($allow_upload_forum)
{
    include("modules/upload/upload_forum.php");
}

$rowQ1 = cache::Q_Select("SELECT forum_id FROM ".$NPDS_Prefix."forumtopics WHERE topic_id='$topic'", 3600);

if (!$rowQ1)
{
    error::forumerror('0001');
}

$myrow = $rowQ1[0];
$forum = $myrow['forum_id'];

$rowQ1 = cache::Q_Select("SELECT forum_name, forum_moderator, forum_type, forum_pass, forum_access, arbre FROM ".$NPDS_Prefix."forums WHERE forum_id = '$forum'", 3600);

if (!$rowQ1)
{
    error::forumerror('0001');
}

$myrow = $rowQ1[0];
$forum_name = $myrow['forum_name'];
$mod = $myrow['forum_moderator'];
$forum_type = $myrow['forum_type'];
$forum_access = $myrow['forum_access'];

if (($forum_type == 1) and ($Forum_passwd != $myrow['forum_pass']))
{
    redirect_url("forum.php");
}

if (($forum_type == 5) or ($forum_type == 7)) 
{
    $ok_affiche = false;
    $tab_groupe = groupe::valid_group($user);
    $ok_affiche = groupe::groupe_forum($myrow['forum_pass'], $tab_groupe);
   
    if (!$ok_affiche)
    {
        redirect_url("forum.php");
    }
}

if (($forum_type == 9) and (!$user))
{
    redirect_url("forum.php");
}

// Moderator
if (isset($user)) 
{
    $userX = base64_decode($user);
    $userdata = explode(':', $userX);
}

$moderator = forumauth::get_moderator($mod);
$moderator = explode(' ', $moderator);
$Mmod = false;

if (isset($user)) 
{
    for ($i = 0; $i < count($moderator); $i++) 
    {
        if (($userdata[1] == $moderator[$i])) 
        { 
            $Mmod = true; 
            break;
        }
   }
}

$sql = "SELECT topic_title, topic_status, topic_poster FROM ".$NPDS_Prefix."forumtopics WHERE topic_id = '$topic'";
$total = forumposts::get_total_posts($forum, $topic, "topic", $Mmod);

if ($total > $posts_per_page) 
{
    $times = 0;
   
    for ($x = 0; $x < $total; $x += $posts_per_page)
    {
       $times++;
    }
    $pages = $times;
}

if (!$result = sql_query($sql))
{
    error::forumerror('0001');
}

$myrow = sql_fetch_assoc($result);
$topic_subject = stripslashes($myrow['topic_title']);
$lock_state = $myrow['topic_status'];
$original_poster = $myrow['topic_poster'];

$contributeurs = forumposts::get_contributeurs($forum, $topic);
$contributeurs = explode(' ', $contributeurs);
$total_contributeurs = count($contributeurs);

/**
 * [maketree description]
 * @param  [type] $rootcatid [description]
 * @param  [type] $sql       [description]
 * @param  [type] $maxlevel  [description]
 * @return [type]            [description]
 */
function maketree($rootcatid, $sql, $maxlevel)
{
global $idtog,$clas;

    $result = sql_query($sql);
    $max_post_id = 0;

    while($tempo = sql_fetch_assoc($result))
    {
        $table[$tempo['post_idH']][$tempo['post_id']] = serialize($tempo);
        if ($max_post_id < $tempo['post_id']) 
        {
            $max_post_id = $tempo['post_id'];
        }
    }
    $resultX = '';
    $resultX.= makebranch($rootcatid, $table, 0, $maxlevel, $max_post_id, $clas, $idtog);
   
    return $resultX;
}

/**
 * [makebranch description]
 * @param  [type] $parcat      [description]
 * @param  [type] $table       [description]
 * @param  [type] $level       [description]
 * @param  [type] $maxlevel    [description]
 * @param  [type] $max_post_id [description]
 * @param  [type] $clas        [description]
 * @param  [type] $idtog       [description]
 * @return [type]              [description]
 */
function makebranch($parcat, $table, $level, $maxlevel, $max_post_id, $clas, $idtog) 
{
    global $imgtmpPI, $imgtmpNE, $user;
    global $smilies, $theme, $forum, $forum_type, $allow_bbcode, $allow_to_post, $forum_access, $Mmod, $topic, $lock_state, $userdata;
    global $allow_upload_forum, $att, $anonymous, $short_user, $last_read, $toggle;
   
    settype($result, 'string');

    $my_rsos = array();
    $count = 0;
   
    settype($idtog, 'integer');
   
    $list = $table[$parcat];
   
    foreach($list as $key => $val) 
    {
        $myrow = unserialize($val);
      
        if ($level != '0') 
        {
            if ($level == 1) 
            {
                $clas = 'collapse show';
                $idtog = $idtog.($count+1);
            } 
            else 
            {
                $idtog = $idtog.($count+1);
                $clas = 'collapse show';
            }
        }
        else
        {
            $idtog = ($level+1).($count+1);
        }

        $posterdata = auth::get_userdata_from_id($myrow['poster_id']);

        if($myrow['poster_id'] !== '0') 
        {
            $posts = $posterdata['posts'];
            $socialnetworks = array(); 
            $posterdata_extend = array();
            $res_id = array();
            $my_rs = '';
         
            if (!$short_user) 
            {
                $posterdata_extend = auth::get_userdata_extend_from_id($myrow['poster_id']);
            
                include('modules/reseaux-sociaux/reseaux-sociaux.conf.php');
                include('modules/geoloc/config/geoloc.php');
            
                if($user or auth::autorisation(-127)) 
                {
                    if (array_key_exists('M2', $posterdata_extend)) 
                    {
                        if ($posterdata_extend['M2'] != '') 
                        {
                            $socialnetworks = explode(';', $posterdata_extend['M2']);
                     
                            foreach ($socialnetworks as $socialnetwork) 
                            {
                                $res_id[] = explode('|', $socialnetwork);
                            }

                            sort($res_id);
                            sort($rs);
                     
                            foreach ($rs as $v1) 
                            {
                                foreach($res_id as $y1) 
                                {
                                    $k = array_search($y1[0], $v1);
                           
                                    if (false !== $k) 
                                    {
                                        $my_rs .= '<a class="mr-3" href="';
                              
                                        if($v1[2] == 'skype') 
                                        {
                                            $my_rs .= $v1[1].$y1[1].'?chat'; 
                                        }
                                        else 
                                        {
                                            $my_rs .= $v1[1].$y1[1];
                                        }
                                        $my_rs .= '" target="_blank"><i class="fab fa-'.$v1[2].' fa-2x text-primary"></i></a> ';
                                        break;
                                    } 
                                    else 
                                    {
                                        $my_rs .= '';
                                    }
                                }
                            }
                        }
                    }
                }
            }

            settype($ch_lat, 'string');

            $useroutils = '';
         
            if($user or auth::autorisation(-127)) 
            {
                if ($posterdata['uid'] != 1 and $posterdata['uid'] != '')
                {
                    $useroutils .= '<a class="list-group-item list-group-item-action text-primary text-center text-md-left" href="user.php?op=userinfo&amp;uname='.$posterdata['uname'].'" target="_blank" title="'.translate("Profil").'" data-toggle="tooltip"><i class="fa fa-user fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Profil").'</span></a>';
                }
            
                if ($posterdata['uid'] != 1)
                {
                    $useroutils .= '<a class="list-group-item list-group-item-action text-primary text-center text-md-left" href="two_api.php?op=instant_message&amp;to_userid='.$posterdata["uname"].'" title="'.translate("Envoyer un message interne").'" data-toggle="tooltip"><i class="far fa-envelope fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Message").'</span></a>';
                }
            
                if ($posterdata['femail'] != '')
                {
                    $useroutils .= '<a class="list-group-item list-group-item-action text-primary text-center text-md-left" href="mailto:'.spam::anti_spam($posterdata['femail'],1).'" target="_blank" title="'.translate("Email").'" data-toggle="tooltip"><i class="fa fa-at fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Email").'</span></a>';
                }
            
                if ($myrow['poster_id'] != 1 and array_key_exists($ch_lat, $posterdata_extend))
                {
                    if ($posterdata_extend[$ch_lat] != '')
                    {
                        $useroutils .= '<a class="list-group-item list-group-item-action text-primary text-center text-md-left" href="modules.php?ModPath=geoloc&amp;ModStart=geoloc&amp;op=u'.$posterdata['uid'].'" title="'.translate("Localisation").'" ><i class="fas fa-map-marker-alt fa-2x align-middle fa-fw">&nbsp;</i><span class="ml-3 d-none d-md-inline">'.translate("Localisation").'</span></a>';
                    }
                }
            }
            
            if ($posterdata['url'] != '')
            {
               $useroutils .= '<a class="list-group-item list-group-item-action text-primary text-center text-md-left" href="'.$posterdata['url'].'" target="_blank" title="'.translate("Visiter ce site web").'" data-toggle="tooltip"><i class="fas fa-external-link-alt fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Visiter ce site web").'</span></a>';
            }
            
            if ($posterdata['mns'])
            {
                $useroutils .= '<a class="list-group-item list-group-item-action text-primary text-center text-md-left" href="minisite.php?op='.$posterdata['uname'].'" target="_blank" target="_blank" title="'.translate("Visitez le minisite").'" data-toggle="tooltip"><i class="fa fa-desktop fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Visitez le minisite").'</span></a>';
            }
        }

        echo '
        <div id="tog_'.$idtog.'" class="row  '.$clas.'">
            <a name="'.$forum.$topic.$myrow['post_id'].'"></a>';
      
        if ($myrow['post_id'] == $max_post_id)
        { 
            echo '<a name="lastpost"></a>';
        }
        $cardcla = '';
      
        if($count == 0 and $idtog == 11)
        { 
            $cardcla = "border-dark";
        }
      
        if($level > 0) 
        {
            $cardcla = "border-0";
        }
      
        if($idtog != 11 and $count != 0)
        {
            echo '
            <div class="d-flex justify-content-between pr-4 ">
                <div class="border-right" style="margin-left:4rem;height:2rem"></div>
            </div>';
        }

        echo '
            <div class="col-12">
                <div class="card '.$cardcla.' border-dark">
                    <div class="card-header">';
      
        if ($smilies) 
        {
            if($myrow['poster_id'] !== '0') 
            {
                if ($posterdata['user_avatar'] != '') 
                {
                    if (stristr($posterdata['user_avatar'],"users_private"))
                    {
                        $imgtmp = $posterdata['user_avatar'];
                    }
                    else 
                    {
                        if ($ibid = theme::theme_image("forum/avatar/".$posterdata['user_avatar'])) 
                        {
                            $imgtmp = $ibid; 
                        } 
                        else 
                        {
                            $imgtmp = "assets/images/forum/avatar/".$posterdata['user_avatar'];
                        }
                    }
                }

                echo '
                <a style="position:absolute; top:1rem;" tabindex="0" data-toggle="popover" data-trigger="focus" data-html="true" data-title="'.$posterdata['uname'].'" data-content=\'<div class="my-2 border rounded p-2">'.forumauth::member_qualif($posterdata['uname'], $posts,$posterdata['rang']).'</div><div class="list-group mb-3 text-center">'.$useroutils.'</div><div class="mx-auto text-center" style="max-width:170px;">'.$my_rs.'</div>\'><img class=" btn-outline-primary img-thumbnail img-fluid n-ava" src="'.$imgtmp.'" alt="'.$posterdata['uname'].'" /></a><span style="position:absolute; left:6em;" class="text-muted"><strong>'.$posterdata['uname'].'</strong></span>';
            } 
            else 
            {
                echo '
                    <a style="position:absolute; top:1rem;" title="'.$anonymous.'" data-toggle="tooltip"><img class=" btn-outline-primary img-thumbnail img-fluid n-ava" src="assets/images/forum/avatar/blank.gif" alt="'.$anonymous.'" /></a><span style="position:absolute; left:6em;" class="text-muted"><strong>'.$anonymous.'</strong></span>'; 
            }
        }  
        else 
        {
            if($myrow['poster_id'] !== '0')
            {
                echo '<span style="position:absolute; left:6em;" class="text-muted"><strong>'.$posterdata['uname'].'</strong></span>';
            }
            else
            {
                echo '<span class="text-muted"><strong>'.$anonymous.'</strong></span>';
            }
        }

        echo '<span class="float-right">';
         
        if ($myrow['image'] != '') 
        {
            if ($ibid = theme::theme_image("forum/subject/".$myrow['image'])) 
            {
                $imgtmp = $ibid;
            } 
            else 
            {
                $imgtmp = "assets/images/forum/subject/".$myrow['image'];
            }
            echo '<img class="n-smil" src="'.$imgtmp.'" alt="" />';
        } 
        else
        {
            echo '<img class="n-smil" src="'.$imgtmpPI.'" alt="" />';
        }
      
        echo '</span>
            </div>';
        
        $message = stripslashes($myrow['post_text']);
      
        echo '
            <div class="card-body">
                <div class="card-text pt-2">';
      
        $date_post = date::convertdateTOtimestamp($myrow['post_time']);

        if ($last_read != '') 
        {
            if (($last_read <= $date_post) AND $userdata[3] != '' AND $last_read != "0" AND $userdata[0] != $myrow['poster_id']) 
            {
                //echo '&nbsp;<img src="'.$imgtmpNE.'" alt="" />';
                echo '<span class="mr-2 badge badge-success animated faa-flash">NEW</span>';
            }
        }

        echo '
            </div>
            <div class="card-text pt-2">';
      
        if (($allow_bbcode) and ($forum_type != 6) and ($forum_type != 5)) 
        {
            $message = pixel::smilie($message);
            $message = video::aff_video_yt($message);
        }

        if (($forum_type == '6') or ($forum_type == '5'))
        {
            highlight_string(stripslashes($myrow['post_text'])).'<br /><br />';
        }
        else 
        {
            if(array_key_exists('user_sig', $posterdata))
            {
                $message = str_replace('[addsig]', '<div class="n-signature">'.nl2br($posterdata['user_sig']).'</div>', $message);
            }
         
            echo $message;
        } 

        if ($att > 0) 
        {
            $post_id = $myrow['post_id'];
            echo '<div class="card-text">';
            echo display_upload("forum_npds", $post_id, $Mmod);
            echo '</div>';
        }

        echo '
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class=" col-sm-6 text-muted small">'.date::post_convertdate($date_post).'</div>
                    <div class=" col-sm-6 text-right">';
      
        if ($forum_access != 9) 
        {
            if ($allow_to_post) 
            {
                if($idtog == 11)
                {
                    echo aff_pub_in($lock_state, $topic, $forum, 0);
                }
                else
                {
                    echo aff_pub_in($lock_state, $topic, $forum, $myrow['post_id']);
                }
            }

            if (($Mmod) or ($posterdata['uid'] == $userdata[0]) and (!$lock_state) and ($posterdata['uid'] != '')) 
            {
                echo '<a class="mr-3" href="editpost.php?post_id='.$myrow["post_id"].'&amp;topic='.$topic.'&amp;forum='.$forum.'&amp;arbre=1" title="'.translate("Editer").'" data-toggle="tooltip"><i class="fa fa-edit fa-lg"></i></a>';
            
                if ($allow_upload_forum) 
                {
                    $PopUp = win_upload("forum_npds", $myrow['post_id'], $forum, $topic, "popup");
                    echo '<a class="mr-3" href="javascript:void(0);" onclick="window.open('.$PopUp.');" title="'.translate("Fichiers").'" data-toggle="tooltip"><i class="fa fa-download fa-lg"></i></a>';
                }
            }

            if ($allow_to_post and !$lock_state and $posterdata['uid'] != '')
            {
                echo '<a class="mr-3" href="replyH.php?topic='.$topic.'&amp;forum='.$forum.'&amp;post='.$myrow['post_id'].'&amp;citation=1" title="'.translate("Citation").'" data-toggle="tooltip"><i class="fa fa-quote-left fa-lg"></i></a>';
            }
            
            echo '<a class="mr-3" href="prntopic.php?forum='.$forum.'&amp;topic='.$topic.'&amp;post_id='.$myrow['post_id'].'" title="'.translate("Imprimer").'" data-toggle="tooltip"><i class="fa fa-print fa-lg"></i></a>';
         
            if ($Mmod) 
            {
                echo '<a class="mr-3" href="topicadmin.php?mode=viewip&amp;topic='.$topic.'&amp;post='.$myrow['post_id'].'&amp;forum='.$forum.'&amp;arbre=1" title="IP" data-toggle="tooltip" ><i class="fa fa-laptop fa-lg"></i></a>';
            
                if (!$myrow['post_aff'])
                {
                    echo '&nbsp;<a href="topicadmin.php?mode=aff&amp;topic='.$topic.'&amp;post='.$myrow['post_id'].'&amp;ordre=1&amp;forum='.$forum.'&amp;arbre=1" title="'.translate("Afficher ce post").'" data-toggle="tooltip"><i class="fa fa-eye text-danger fa-lg"></i></a>&nbsp;';
                }
                else
                {
                    echo '&nbsp;<a href="topicadmin.php?mode=aff&amp;topic='.$topic.'&amp;post='.$myrow['post_id'].'&amp;ordre=0&amp;forum='.$forum.'&amp;arbre=1" title="'.translate("Masquer ce post").'" data-toggle="tooltip"><i class="fa fa-eye-slash fa-lg "></i></a>&nbsp;';
                }
            }
        }

        echo '
                    </div>
                </div>
            </div>';
      
        if ((isset($table[$key])) AND (($maxlevel > $level+1) OR ($maxlevel == '0'))) 
        {
            echo '
                <div class="d-flex justify-content-between pr-4 border-top">
                    <div class="border-right" style="margin-left:3rem"></div>
                    <a data-toggle="collapse" href="#tog_'.$idtog.'z" aria-expanded="false" aria-controls=""><i class="togglearbr-icon fas fa-level-down-alt fa-2x"></i></a>
                </div>
                <div id="tog_'.$idtog.'z" class="collapse">';
         
            $result .= makebranch($key, $table, $level+1, $maxlevel, $max_post_id, $clas, $idtog);
            
            echo'</div>';
        }

        echo '
                </div>
            </div>
        </div>';
   
        $count++;
    }

    return $result;
}

/**
 * [aff_pub description]
 * @param  [type] $lock_state [description]
 * @param  [type] $topic      [description]
 * @param  [type] $forum      [description]
 * @param  [type] $post       [description]
 * @param  [type] $bouton     [description]
 * @return [type]             [description]
 */
function aff_pub($lock_state, $topic, $forum, $post, $bouton) 
{
    global $language;

    $ibid = '';

    if ($lock_state == 0) 
    {
        if (($bouton == 2) or ($bouton == 9))
        {
           $ibid = '<a class="" href="newtopic.php?forum='.$forum.'" title="'.translate("Nouveau sujet").'" data-toggle="tooltip" ><i class="fa fa-plus-square "></i></a>&nbsp;';
        } 
    }
    else 
    {
        $ibid = '<i class="fa fa-lock fa-lg text-danger" title="'.translate("Ce sujet est verrouill?? : il ne peut accueillir aucune nouvelle contribution.").'" data-toggle="tooltip"></i>&nbsp;';
    }

    return $ibid;
}

/**
 * [aff_pub_in description]
 * @param  [type] $lock_state [description]
 * @param  [type] $topic      [description]
 * @param  [type] $forum      [description]
 * @param  [type] $post       [description]
 * @return [type]             [description]
 */
function aff_pub_in($lock_state, $topic, $forum, $post) 
{
    global $language;

    $ibid = '';
   
    if ($lock_state == 0)
    {
        $ibid = '<a class="mr-3" href="replyH.php?topic='.$topic.'&amp;forum='.$forum.'&amp;post='.$post.'" title="'.translate("R??pondre").'" data-toggle="tooltip"><span class="d-none d-md-inline"></span><i class="fa fa-reply mr-2"></i><span class="d-none d-md-inline">'.translate("R??pondre").'</span></a>';
    }
   
    return $ibid;
}

$contributeurs = forumposts::get_contributeurs($forum, $topic);
$contributeurs = explode(' ', $contributeurs);
$total_contributeurs = count($contributeurs);

$title = $forum_name; 
$post = $topic_subject;

include('header.php');

$r_to = '';
$n_to = '';
   
if ($forum_access != 9) 
{
    $allow_to_post = false;
    if ($forum_access == 0) 
    {
        $allow_to_post = true;
    }
    elseif ($forum_access == 1) 
    {
        if (isset($user)) 
        {
            $allow_to_post = true;
        }
    } 
    elseif ($forum_access == 2) 
    {
        if (forumauth::user_is_moderator($userdata[0], $userdata[2], $forum_access)) 
        {
            $allow_to_post = true;
        }
    }

    if ($allow_to_post) 
    {
        $n_to = aff_pub($lock_state, $topic, $forum, 0, 9);
        $r_to = aff_pub_in($lock_state, $topic, $forum, 0);
    }
}

 echo '
<a name="topofpage"></a>
<p class="lead">
    <a href="forum.php">'.translate("Index du forum").'</a>&nbsp;&raquo;&raquo;&nbsp;
    <a href="viewforum.php?forum='.$forum.'">'.stripslashes($forum_name).'</a>&nbsp;&raquo;&raquo;&nbsp;'.$topic_subject.'
</p>
<h3 class="mb-3">'.$n_to.$topic_subject.' <span class="text-muted">&nbsp;#'.$topic.'</span> '.$r_to.'</h3>
<div class="card mb-3">
    <div class="card-body p-2">
        <div class="d-flex ">
            <div class=" align-self-center badge badge-secondary mx-2 col-2 col-md-3 col-xl-2 bg-white text-muted py-2"><span class=" mr-1 lead">'.$total_contributeurs.'<i class="fa fa-edit fa-fw fa-lg ml-1 d-inline d-md-none" title="'.translate("Contributeur(s)").'" data-toggle="tooltip"></i></span><span class=" d-none d-md-inline">'.translate("Contributeur(s)").'</span></div>
            <div class=" align-self-center mr-auto">';
   
for ($i = 0; $i < $total_contributeurs; $i++) 
{
    if($contributeurs[$i] !== '0') 
    {
        $contri = auth::get_userdata_from_id($contributeurs[$i]);

if ($contri['uid'] > 1) {         
        if ($contri['user_avatar'] != '') 
        {
            if (stristr($contri['user_avatar'], "users_private")) 
            {
               $imgtmp = $contri['user_avatar'];
            } 
            else 
            {
                if ($ibid = theme::theme_image("forum/avatar/".$contri['user_avatar'])) 
                {
                    $imgtmp = $ibid;
                } 
                else 
                {
                    $imgtmp = "assets/images/forum/avatar/".$contri['user_avatar'];
                }
            }
        }
        echo '<img class="img-thumbnail img-fluid n-ava-small mr-1 mb-1" src="'.$imgtmp.'" alt="'.$contri['uname'].'" title="'.$contri['uname'].'" data-toggle="tooltip" />';
} 
else 
{
    echo '<img class="img-thumbnail img-fluid n-ava-small mb-1" src="assets/images/forum/avatar/blank.gif" alt="'.$anonymous.'" title="'.$anonymous.'" data-toggle="tooltip" />';
}    
    }
    else 
    {
        echo '<img class="img-thumbnail img-fluid n-ava-small mb-1" src="assets/images/forum/avatar/blank.gif" alt="'.$anonymous.'" title="'.$anonymous.'" data-toggle="tooltip" />';
    }
}

echo '
        </div>
    </div>';
   
$ibidcountmod = count($moderator);
   
echo '
<div class="d-flex">
    <div class="badge badge-secondary align-self-center mx-2 col-2 col-md-3 col-xl-2 bg-white text-muted py-2"><span class="mr-1 lead">'.$ibidcountmod.' <i class="fa fa-balance-scale fa-fw fa-lg ml-1 d-inline d-md-none" title="'.translate("Mod??rateur(s)").'" data-toggle="tooltip"></i></span><span class=" d-none d-md-inline">'.translate("Mod??rateur(s)").'</span></div>
        <div class=" align-self-center mr-auto">';
   
for ($i = 0; $i < $ibidcountmod; $i++) 
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

    echo '<a href="user.php?op=userinfo&amp;uname='.$moderator[$i].'"><img class=" img-thumbnail img-fluid n-ava-small mr-1 mb-1" src="'.$imgtmp.'" alt="'.$modera['uname'].'" title="'.translate("Mod??r?? par : ").' '.$modera['uname'].'" data-toggle="tooltip" /></a>';
}

echo '
            </div>
        </div>
    </div>
</div>';

// Forum Read
if (isset($user)) 
{
    $time_actu = time()+((integer)$gmt*3600);
      
    $sqlR = "SELECT last_read FROM ".$NPDS_Prefix."forum_read WHERE forum_id='$forum' AND uid='$userdata[0]' AND topicid='$topic'";
    $result_LR = sql_query($sqlR);
      
    $last_read = '';
      
    if (sql_num_rows($result_LR) == 0) 
    {
        $sqlR = "INSERT INTO ".$NPDS_Prefix."forum_read (forum_id, topicid, uid, last_read, status) VALUES ('$forum', '$topic', '$userdata[0]', '$time_actu', '1')";
         $resultR = sql_query($sqlR);
    } 
    else 
    {
        list($last_read) = sql_fetch_row($result_LR);
        $sqlR = "UPDATE ".$NPDS_Prefix."forum_read SET last_read='$time_actu', status='1' WHERE forum_id='$forum' AND uid='$userdata[0]' AND topicid='$topic'";
        $resultR = sql_query($sqlR);
    }
}

if ($ibid = theme::theme_image("forum/icons/posticon.gif")) 
{
    $imgtmpPI = $ibid;
} 
else 
{
    $imgtmpPI = "assets/images/forum/icons/posticon.gif";
}
   
if ($ibid = theme::theme_image("forum/icons/new.gif")) 
{
    $imgtmpNE = $ibid;
} 
else 
{
    $imgtmpNE = "assets/images/forum/icons/new.gif";
}

if ($Mmod)
{
    $post_aff = ' ';
}
else
{
    $post_aff = " AND post_aff='1' ";
}
   
settype($start, 'integer');
settype($posts_per_page, 'integer');
   
if (isset($start)) 
{
    if ($start == 9999)
    { 
        $start = $posts_per_page*($pages-1);
        if ($start < 0) 
        {
            $start = 0;
        } 
    }

    $sql = "SELECT * FROM ".$NPDS_Prefix."posts WHERE topic_id='$topic' AND forum_id='$forum'".$post_aff."ORDER BY post_id";
}
else 
{
    $sql = "SELECT * FROM ".$NPDS_Prefix."posts WHERE topic_id='$topic' AND forum_id='$forum'".$post_aff."ORDER BY post_id";
}

if ($allow_upload_forum) 
{
    $visible = '';
    if (!$Mmod)
    {
        $visible = " AND visible = 1";
    }
      
    $sql2 = "SELECT att_id FROM $upload_table WHERE apli='forum_npds' && topic_id = '$topic' $visible";
    $att = sql_num_rows(sql_query($sql2));
      
    if ($att > 0)
    {
        include ("modules/upload/include_forum/upload.func.forum.php");
    }
}

echo maketree(0, $sql, 0);
   
$sql = "UPDATE ".$NPDS_Prefix."forumtopics SET topic_views = topic_views + 1 WHERE topic_id = '$topic'";
sql_query($sql);
   
echo '<br />';

if ($forum_access != 9) 
{
    // un anonyme ne peut pas mettre un topic en resolu
    if (!isset($userdata))
    { 
        $userdata[0] = 0;
    }
      
    if ((($Mmod) or ($original_poster == $userdata[0])) and (!$lock_state)) 
    {
        $sec_clef = md5($forum.$topic.md5($NPDS_Key));
         
        echo '
        <p><a href="viewforum.php?forum='.$forum.'&amp;topic_id='.$topic.'&amp;topic_title='.rawurlencode($topic_subject).'&amp;op=solved&amp;sec_clef='.$sec_clef.'"><i class="fa fa-lock fa-2x align-middle mr-1"></i>'.translate("R??solu").'</a></p>';
        unset($sec_clef);
    }
}

if ($SuperCache) 
{
    $cache_clef = "forum-jump-to";
    $CACHE_TIMINGS[$cache_clef] = 600;
    $cache_obj->startCachingBlock($cache_clef);
}

if (($cache_obj->genereting_output == 1) 
    or ($cache_obj->genereting_output == -1) 
    or (!$SuperCache)) 
{
    echo '
    <form action="viewforum.php" method="post">
        <div class="form-group row">
            <div class="col-12">
                <label class="sr-only" for="forum">'.translate("Sauter ?? : ").'</label>
                <select class="form-control custom-select" id="forum" name="forum" onchange="submit();">
                    <option value="index">'.translate("Sauter ?? : ").'</option>
                    <option value="index">'.translate("Index du forum").'</option>';
      
    $sub_sql = "SELECT forum_id, forum_name, forum_type, forum_pass FROM ".$NPDS_Prefix."forums ORDER BY cat_id,forum_index,forum_id";
      
    if ($res = sql_query($sub_sql)) 
    {
        while (list($forum_id, $forum_name, $forum_type, $forum_pass) = sql_fetch_row($res)) 
        {
            if (($forum_type != "9") or ($userdata)) 
            {
                if (($forum_type == "7") or ($forum_type == "5"))
                {
                    $ok_affich = false;
                }
                else
                {
                    $ok_affich = true;
                }
                
                if ($ok_affich) 
                {
                    echo '<option value="'.$forum_id.'">&nbsp;&nbsp;'.stripslashes($forum_name).'</option>';
                }
            }
        }
    }

    echo '
                </select>
            </div>
        </div>
        </form>
    <a name="botofpage"></a>';
}

if ($SuperCache)
{
    $cache_obj->endCachingBlock($cache_clef);
}

if ((($Mmod) and ($forum_access != 9)) or ($adminforum == 1))
{
    echo '
        <ul class="nav justify-content-center border rounded">
            <li class="nav-item ">
                <a class="nav-link disabled" href="#"><i class="fa fa-cogs fa-lg" title="'.translate("Outils administrateur").'" data-toggle="tooltip"></i><span class="d-none d-md-inline">&nbsp;</span></a>
            </li>';
      
    if ($lock_state == 0)
    {
        echo '
            <li class="nav-item">
                <a class="nav-link" role="button" href="topicadmin.php?mode=lock&amp;topic='.$topic.'&amp;forum='.$forum.'&amp;arbre=1" ><i class="fa fa-lock fa-lg d-md-none" title="'.translate("Fermer ce sujet").'" data-toggle="tooltip"></i><span class="ml-2 d-none d-md-inline">'.translate("Fermer ce sujet").'</span></a>
            </li>';
    }
    else
    {
        echo '
            <li class="nav-item">
                <a class="nav-link" role="button" href="topicadmin.php?mode=unlock&amp;topic='.$topic.'&amp;forum='.$forum.'&amp;arbre=1" ><i class ="fa fa-unlock fa-lg d-md-none" title="'.translate("Ouvrir ce sujet").'" data-toggle="tooltip"></i><span class="ml-2 d-none d-md-inline">'.translate("Ouvrir ce sujet").'</span></a>
            </li>';
    }
      
    echo '
        <li class="nav-item">
            <a class="nav-link" role="button" href="topicadmin.php?mode=move&amp;topic='.$topic.'&amp;forum='.$forum.'&amp;arbre=1"><i class="fa fa-share fa-lg d-md-none" title="'.translate("D??placer ce sujet").'" data-toggle="tooltip" ></i><span class="ml-2 d-none d-md-inline">'.translate("D??placer ce sujet").'</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" role="button" href="topicadmin.php?mode=first&amp;topic='.$topic.'&amp;forum='.$forum.'&amp;arbre=1"><i class="fas fa-level-up-alt fa-lg d-md-none"  title="'.translate("Mettre ce sujet en premier").'" data-toggle="tooltip"></i><span class="ml-2 d-none d-md-inline">'.translate("Mettre ce sujet en premier").'</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" role="button" href="topicadmin.php?mode=del&amp;topic='.$topic.'&amp;forum='.$forum.'&amp;arbre=1"><i class="fas fa-times fa-lg d-md-none"  title="'.translate("Effacer ce sujet").'" data-toggle="tooltip"></i><span class="ml-2 d-none d-md-inline">'.translate("Effacer ce sujet").'</span></a>
        </li>
        </ul>';
}
   
if (!$SuperCache)
{
    include("footer.php");
}

