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
use npds\assets\css;
use npds\forum\forumauth;
use npds\views\theme;
use npds\lnguage\language;
use npds\pixels\pixel;
use npds\media\video;
use npds\error\error;


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

/**
 * [cache_ctrl description]
 * @return [type] [description]
 */
function cache_ctrl() 
{
    global $cache_verif;

    if ($cache_verif) 
    {
        header("Expires: Sun, 01 Jul 1990 00:00:00 GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Cache-Control: no-cache, must revalidate");
        header("Pragma: no-cache");
    }
}

/**
 * [show_imm description]
 * @param  [type] $op [description]
 * @return [type]     [description]
 */
function show_imm($op) 
{
    global $smilies, $user, $allow_bbcode, $language, $Default_Theme, $theme, $site_font, $short_user, $Titlesitename, $NPDS_Prefix;
    
    if (!$user)
    {
        Header("Location: user.php");
    }
    else 
    {
        $userX = base64_decode($user);
        $userdata = explode(':', $userX);
        
        if ($userdata[9] != '') 
        {
            if (!$file = @opendir("themes/$userdata[9]")) 
            {
                $theme = $Default_Theme;
            } 
            else 
            {
                $theme = $userdata[9];
            }
        } 
        else
        {
            $theme = $Default_Theme;
        }

        include("themes/$theme/theme.php");
        
        $userdata = auth::get_userdata($userdata[1]);

        if ($op != 'new_msg') 
        {
            $sql = "SELECT * FROM ".$NPDS_Prefix."priv_msgs WHERE to_userid = '".$userdata['uid']."' AND read_msg='1' AND type_msg='0' AND dossier='...' ORDER BY msg_id DESC";
        } 
        else 
        {
            $sql = "SELECT * FROM ".$NPDS_Prefix."priv_msgs WHERE to_userid = '".$userdata['uid']."' AND read_msg='0' AND type_msg='0' ORDER BY msg_id ASC";
        }

        $result = sql_query($sql);
        $pasfin = false;
         
        while ($myrow = sql_fetch_assoc($result)) 
        {
            if ($pasfin == false) 
            {
                $pasfin = true;
                cache_ctrl();

                include("config/meta.php");
                include("theme/default/include/header_head.inc");
                
                echo css::import_css($theme, $language, $site_font, '', '');

                echo '
                    </head>
                    <body>
                    <div class="card card-body">';
            }

            $posterdata = auth::get_userdata_from_id($myrow['from_userid']);
            
            echo '
                <div class="card mb-3">
                    <div class="card-body">
                    <h3>'.translate("Message personnel").' '.translate("de");

            if ($posterdata['uid'] == 1) 
            {
                global $sitename;
                echo ' <span class="text-muted">'.$sitename.'</span></h3>';
            }

            if ($posterdata['uid'] <> 1) 
            {
                echo ' <span class="text-muted">'.$posterdata['uname'].'</span></h3>';
            }

            $myrow['subject'] = strip_tags($myrow['subject']);

            $posts = $posterdata['posts'];
            
            if ($posterdata['uid'] <> 1) 
            {
                echo forumauth::member_qualif($posterdata['uname'], $posts, $posterdata['rang']);
            }

            echo '<br /><br />';
            
            if ($smilies) 
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

                    echo '<img class="btn-secondary img-thumbnail img-fluid n-ava" src="'.$imgtmp.'" alt="'.$posterdata['uname'].'" />';
                }
            }

            if ($smilies) 
            {
                if ($myrow['msg_image'] != '') 
                {
                    if ($ibid = theme::theme_image("forum/subject/".$myrow['msg_image'])) 
                    {
                        $imgtmp = $ibid;
                    } 
                    else 
                    {
                        $imgtmp = "assets/images/forum/subject/".$myrow['msg_image'];
                    }
                    
                    echo '<img class="n-smil" src="'.$imgtmp.'"  alt="" />&nbsp;';
                }
            }

            echo translate("Envoyé").' : '.$myrow['msg_time'].'&nbsp;&nbsp;&nbsp';
            echo '<h4>'.language::aff_langue($myrow['subject']).'</h4>';
            
            $message = stripslashes($myrow['msg_text']);

            if ($allow_bbcode) 
            {
                $message = pixel::smilie($message);
                $message = video::aff_video_yt($message);
            }

            $message = str_replace("[addsig]", "<br /><br />" . nl2br($posterdata['user_sig']), language::aff_langue($message));
            
            echo $message.'<br />';

            if ($posterdata['uid'] <> 1) 
            {
                if (!$short_user) {}
            }

            echo '
            </div>
            <div class="card-footer">';
            
            if ($posterdata['uid'] <> 1)
            {
                echo '
                <a class="mr-3" href="readpmsg_imm.php?op=read_msg&amp;msg_id='.$myrow['msg_id'].'&amp;op_orig='.$op.'&amp;sub_op=reply" title="'.translate("Répondre").'" data-toggle="tooltip"><i class="fa fa-reply fa-lg mr-1"></i>'.translate("Répondre").'</a>';
            }

            echo '
                <a class="mr-3" href="readpmsg_imm.php?op=read_msg&amp;msg_id='.$myrow['msg_id'].'&amp;op_orig='.$op.'&amp;sub_op=read" title="'.translate("Lu").'" data-toggle="tooltip"><i class="far fa-check-square fa-lg"></i></a>
                <a class="mr-3" href="readpmsg_imm.php?op=delete&amp;msg_id='.$myrow['msg_id'].'&amp;op_orig='.$op.'" title="'.translate("Effacer").'" data-toggle="tooltip"><i class="far fa-trash-alt fa-lg text-danger"></i></a>
                </div>
                </div>';

        }

        if ($pasfin != true) 
        {
            cache_ctrl();
            echo '<body onload="self.close();">';
        }
    }

    echo '
            </div>
        </body>
    </html>';
}

/**
 * [sup_imm description]
 * @param  [type] $msg_id [description]
 * @return [type]         [description]
 */
function sup_imm($msg_id) 
{
    global $cookie, $NPDS_Prefix;
    
    if (!$cookie)
    {
        Header("Location: user.php");
    }
    else 
    {
        $sql = "DELETE FROM ".$NPDS_Prefix."priv_msgs WHERE msg_id='$msg_id' AND to_userid='$cookie[0]'";
        
        if (!sql_query($sql))
        {
            error::forumerror('0021');
        }
    }
}

/**
 * [read_imm description]
 * @param  [type] $msg_id [description]
 * @param  [type] $sub_op [description]
 * @return [type]         [description]
 */
function read_imm($msg_id, $sub_op) 
{
    global $cookie, $NPDS_Prefix;

    if (!$cookie)
    {
        Header("Location: user.php");
    }
    else 
    {
        $sql = "UPDATE ".$NPDS_Prefix."priv_msgs SET read_msg='1' WHERE msg_id='$msg_id' AND to_userid='$cookie[0]'";
        
        if (!sql_query($sql))
        {
            error::forumerror('0021');
        }
        
        if ($sub_op == 'reply') 
        {
            echo "<script type=\"text/javascript\">
                    //<![CDATA[
                        window.location='replypmsg.php?reply=1&msg_id=$msg_id&userid=$cookie[0]&full_interface=short';
                    //]]>
                </script>";
                die();
        }

        echo '<script type="text/javascript">
                //<![CDATA[
                    window.location="readpmsg_imm.php?op=new_msg";
                //]]>
            </script>';
        die();
   }
}

settype($op, 'string');

switch ($op) 
{
    case 'new_msg':
        show_imm($op);
    break;

    case 'read_msg':
        read_imm($msg_id, $sub_op);
    break;

    case 'delete':
        sup_imm($msg_id);
        show_imm($op_orig);
    break;

    default:
        show_imm($op);
    break;
}
