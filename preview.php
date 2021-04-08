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
use npds\utility\code;
use npds\pixels\pixel;
use npds\media\video;
use npds\security\hack;
use npds\forum\forumaddon;
use npds\forum\forumauth;
use npds\auth\auth;
use npds\error\error; 
use npds\views\theme;


$userdatat = $userdata;
$messageP = $message;
$time = date(translate("dateinternal"), time()+((integer)$gmt*3600));
   
switch ($acc) 
{
    case "newtopic":
        $forum_type = $myrow['forum_type'];
        if ($forum_type == 8) 
        {
            $formulaire = $myrow['forum_pass'];
            
            include ("modules/sform/forum/forum_extender.php");
        }

        /*
        if ($allow_html == 0 || isset($html))
        {
            $messageP = htmlspecialchars($messageP, ENT_COMPAT|ENT_HTML401, cur_charset);
        }
        */

        if (isset($sig) && $userdata['0'] != 1 && $myrow['forum_type'] != 6 && $myrow['forum_type'] != 5)
        {
            $messageP .= ' [addsig]';
        }
        
        if (($forum_type != 6) and ($forum_type != 5)) 
        {
            $messageP = code::af_cod($messageP);
            $messageP = str_replace("\n", '<br />', $messageP);
        }

        if (($allow_bbcode) and ($forum_type != 6) and ($forum_type != 5))
        {
            $messageP = pixel::smile($messageP);
        }
         
        if (($forum_type != 6) and ($forum_type != 5)) 
        {
            $messageP = forumaddon::make_clickable($messageP);
            $messageP = hack::remove($messageP);
            
            if ($allow_bbcode) 
            {
                $messageP = video::aff_video_yt($messageP);
            }
        }

        if (!isset($Mmod))
        {
            $subject = hack::remove(strip_tags($subject));
        }

        $subject = htmlspecialchars($subject, ENT_COMPAT|ENT_HTML401, cur_charset);
    break;

    case 'reply':
        //if (array_key_exists(1,$userdata))
        // why make an error ? 
        // car le tableau est déclaré après le array key exist ...
        $userdata = auth::get_userdata($userdata[1]);
         
        if ($allow_html == 0 || isset($html)) 
        {
            $messageP = htmlspecialchars($messageP, ENT_COMPAT|ENT_HTML401, cur_charset);
        }
        
        if (isset($sig) && $userdata['uid'] != 1) 
        {
            $messageP .= " [addsig]";
        }
        
        if (($forum_type != '6') and ($forum_type != '5')) 
        {
            $messageP = code::af_cod($messageP);
            $messageP = str_replace("\n", '<br />', $messageP);
        }
        
        if (($allow_bbcode) and ($forum_type != '6') and ($forum_type != '5'))
        {
            $messageP = pixel::smile($messageP);
        }
         
        if (($forum_type != 6) and ($forum_type != 5))
        {
            $messageP = forumaddon::make_clickable($messageP);
            $messageP = hack::remove($messageP);
            
            if ($allow_bbcode) 
            {
                $messageP = video::aff_video_yt($messageP);
            }
        }

        $messageP = addslashes($messageP);
    break;

    case 'editpost' :
        $userdata = auth::get_userdata($userdata[1]);
         
        settype($post_id, "integer");
         
        $sql = "SELECT poster_id, topic_id FROM ".$NPDS_Prefix."posts WHERE (post_id = '$post_id')";
        $result = sql_query($sql);
         
        if (!$result)
        {
            error::forumerror('0022');
        }
         
        $row2 = sql_fetch_assoc($result);

        $userdata['uid'] = $row2['poster_id'];
         
        // IF we made it this far we are allowed to edit this message
        settype($forum,"integer");
         
        $myrow2 = sql_fetch_assoc(sql_query("SELECT forum_type FROM ".$NPDS_Prefix."forums WHERE (forum_id = '$forum')"));
        $forum_type = $myrow2['forum_type'];

        if ($allow_html == 0 || isset($html))
        {
            $messageP = htmlspecialchars($messageP, ENT_COMPAT|ENT_HTML401, cur_charset);
        }
         
        if (($allow_bbcode) and ($forum_type != 6) and ($forum_type != 5))
        {
            $messageP = pixel::smile($messageP);
        }

        if (($forum_type != 6) and ($forum_type != 5)) 
        {
            $messageP = code::af_cod($messageP);
            $messageP = str_replace("\n", '<br />', hack::remove($messageP));
            $messageP .= '<br /><div class=" text-muted text-right small"><i class="fa fa-edit"></i> '.translate("Message édité par").' : '.$userdata['uname'].'</div';
            
            if ($allow_bbcode)
            { 
                $messageP = video::aff_video_yt($messageP);
            }
        } 
        else
        {
            $messageP .= "\n\n".translate("Message édité par").' : '.$userdata['uname'];
        }
         
        $messageP = addslashes($messageP);
    break;
}

$theposterdata = auth::get_userdata_from_id($userdatat[0]);
         
echo '
<div class="mb-3">
    <h4 class="mb-3">'.translate("Prévisualiser").'</h4>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">';
            
if ($smilies) 
{
    if ($theposterdata['user_avatar'] != '') 
    {
        if (stristr($theposterdata['user_avatar'],"users_private")) 
        {
            $imgtmp = $theposterdata['user_avatar'];
        } 
        else 
        {
            if ($ibid = theme::theme_image("forum/avatar/".$theposterdata['user_avatar'])) 
            {
                $imgtmp = $ibid;
            } 
            else 
            {
                $imgtmp = "assets/images/forum/avatar/".$theposterdata['user_avatar'];
            }
        }

        echo '
        <a style="position:absolute; top:1rem;" tabindex="0" data-toggle="popover" data-html="true" data-title="'.$theposterdata['uname'].'" data-content=\''.forumauth::member_qualif($theposterdata['uname'], $theposterdata['posts'], $theposterdata['rang']).'\'><img class=" btn-secondary img-thumbnail img-fluid n-ava" src="'.$imgtmp.'" alt="'.$theposterdata['uname'].'" /></a>';
    }
}

echo'
&nbsp;<span style="position:absolute; left:6rem;" class="text-muted"><strong>'.$userdatat[1].'</strong></span>
    <span class="float-right">';
         
if (isset($image_subject)) 
{
    if ($ibid = theme::theme_image("forum/subject/$image_subject")) 
    {
        $imgtmp = $ibid;
    } 
    else 
    {
        $imgtmp = "assets/images/forum/subject/$image_subject";
    }

    echo '<img class="n-smil" src="'.$imgtmp.'" alt="" />';
} 
else 
{
    if ($ibid = theme::theme_image("forum/icons/posticon.gif")) 
    {
        $imgtmpP = $ibid;
    } 
    else 
    {
        $imgtmpP = "assets/images/forum/icons/posticon.gif";
    }
            
    echo '<img class="n-smil" src="'.$imgtmpP.'" alt="" />';
}
         
echo '</span>
        </div>
        <div class="card-body">
            <span class="text-muted float-right small" style="margin-top:-1rem;">'.translate("Commentaires postés : ").$time.'</span>
            <div id="post_preview" class="card-text pt-3">';

$messageP = stripslashes($messageP);

if (($forum_type == '6') or ($forum_type == '5'))
{
    highlight_string(stripslashes($messageP));
}
else 
{
    if ($allow_bbcode) 
    {
        $messageP = pixel::smilie($messageP);
    }
            
    $messageP = str_replace('[addsig]', '<div class="n-signature">'.nl2br($theposterdata['user_sig']).'</div>', $messageP);
    
    echo $messageP.'
            </div>
        </div>';
}
         
echo '
                </div>
            </div>
        </div>
    </div>';

if ($acc == 'reply'|| $acc == 'editpost')
{
    $userdata = $userdatat;
}
