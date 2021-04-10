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
use npds\views\theme;
use npds\security\hack;
use npds\utility\spam;
use npds\mailler\mailler;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

// Make Member_list Private or not
if (!auth::AutoReg()) 
{
    unset($user);
}

if (($member_list == 1) and !isset($user) and !isset($admin))
{
    Header("Location: user.php");
}

if (isset($gr_from_ws) and ($gr_from_ws != 0)) 
{
    settype($gr_from_ws, 'integer');
   
    $uid_from_ws = "^(";
    $re = sql_query("SELECT uid, groupe FROM ".$NPDS_Prefix."users_status WHERE groupe regexp '[[:<:]]".$gr_from_ws."[[:>:]]'");
   
    while (list($ws_uid) = sql_fetch_row($re)) 
    {
        $uid_from_ws .= $ws_uid."|";
    }

    $uid_from_ws = substr($uid_from_ws, 0, -1).")\$";
} 
else
{
    $uid_from_ws = '';
}

/**
 * [alpha description]
 * @return [type] [description]
 */
function alpha() 
{
    global $sortby, $list, $gr_from_ws, $uid_from_ws;

    $alphabet = array(translate("Tous"), "A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",translate("Autres"));
    $num = count($alphabet) - 1;
    $counter = 0;
       
    foreach($alphabet as $ltr) 
    {
        echo '<a href="memberslist.php?letter='.$ltr.'&amp;sortby='.$sortby.'&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.$ltr.'</a>';
          
        if ( $counter != $num )
        {
            echo ' | ';
        }
          
        $counter++;
    }

    echo '
    <br />
    <form action="memberslist.php" method="post">
    <div class="form-group row">
        <label class="col-form-label col-sm-3" for="mblst_search">'.translate("Recherche").'</label>
        <div class="col-sm-9">
            <input class="form-control" type="input" id="mblst_search" name="letter" />
            <input type="hidden" name="list" value="'.urldecode($list).'" />
            <input type="hidden" name="gr_from_ws" value="'.$gr_from_ws.'" />
        </div>
    </div>
    </form>';
}

/**
 * [unique description]
 * @param  [type] $ibid [description]
 * @return [type]       [description]
 */
function unique($ibid) 
{
    foreach ($ibid as $to_user) 
    {
        settype($Xto_user, 'array');
        if (!array_key_exists($to_user, $Xto_user))
        {
            $Xto_user[$to_user] = $to_user;
        }
    }

    return ($Xto_user);
}

/**
 * [SortLinks description]
 * @param [type] $letter [description]
 */
function SortLinks($letter) 
{
    global $sortby, $list, $admin, $gr_from_ws;

    if ($letter == 'front')
    {
        $letter = translate("Tous");
    }
       
    $sort = false;
    echo '
    <p class="">';
    echo translate("Classé par ordre de : ")." ";
       
    if ($sortby == "uname ASC" OR !$sortby) 
    {
        echo translate("identifiant").' | ';
        $sort = true;
    } 
    else 
    {
        echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=uname%20ASC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("identifiant").'</a> | ';
    }

    if ($sortby == 'name ASC')
    {
        echo translate("vrai nom").' | ';
        $sort = true;
    } 
    else 
    {
        echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=name%20ASC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("vrai nom").'</a> | ';
    }

    if ($sortby == 'user_avatar ASC') 
    {
        echo translate("Avatar").' | ';
        $sort = true;
    } 
    else 
    {
        echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=user_avatar%20ASC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("Avatar").'</a> | ';
    }

    if (($sortby == 'femail ASC') or ($sortby == 'email ASC')) 
    {
        echo translate("Email").' | ';
        $sort = true;
    } 
    else 
    {
        if ($admin) 
        {
            echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=email%20ASC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("Email").'</a> | ';
        } 
        else 
        {
            echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=femail%20ASC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("Email").'</a> | ';
        }
    }

    if ($sortby == 'user_from ASC') 
    {
        echo translate("Localisation").' | ';
        $sort = true;
    } 
    else
    {
        echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=user_from%20ASC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("Localisation").'</a> | ';
    }
       
    if ($sortby == 'url DESC') 
    {
        echo translate("Url").' | ';
        $sort = true;
    } 
    else
    {
        echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=url%20DESC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("Url").'</a> | ';
    }

    if ($sortby == 'mns DESC') 
    {
        echo translate("MiniSite").' | ';
        $sort = true;
    } 
    else
    {
        echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=mns%20DESC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">'.translate("MiniSite").'</a> | ';
    }
       
    if ($sortby == 'uid DESC') 
    {
        echo "I.D";
        $sort = true;
    } 
    else 
    {
        echo '<a href="memberslist.php?letter='.$letter.'&amp;sortby=uid%20DESC&amp;list='.$list.'&amp;gr_from_ws='.$gr_from_ws.'">I.D</a>';
    }

    if (!$sort) 
    {
        $sortby = 'uname ASC';
    }
    echo '</p>';
}

/**
 * [avatar description]
 * @param  [type] $user_avatar [description]
 * @return [type]              [description]
 */
function avatar($user_avatar) 
{
    if (!$user_avatar)
    {
        $imgtmp = "assets/images/forum/avatar/blank.gif";
    }
    else if (stristr($user_avatar, "users_private"))
    {
        $imgtmp = $user_avatar;
    }
    else 
    {
        if ($ibid = theme::theme_image("forum/avatar/$user_avatar"))
        {
            $imgtmp = $ibid;
        } 
        else 
        {
            $imgtmp = "assets/images/forum/avatar/$user_avatar";
        }

        if (!file_exists($imgtmp)) 
        {
            $imgtmp = "assets/images/forum/avatar/blank.gif";
        }
    }

    return $imgtmp;
}

include("header.php");
$pagesize = $show_user;

if (!isset($letter) or ($letter == '')) 
{
    $letter = translate("Tous");
}

$letter = hack::remove(stripslashes(htmlspecialchars($letter, ENT_QUOTES, cur_charset)));

if (!isset($sortby)) 
{
    $sortby = 'uid DESC';
}

$sortby = hack::remove($sortby);

if (!isset($page))
{
    $page = 1;
} 

if (isset($list)) 
{
    $tempo = unique(explode(',', $list));
    $list = urlencode(implode(',', $tempo));
}

$result = sql_query("SELECT u.uname, u.user_avatar FROM ".$NPDS_Prefix."users AS u LEFT JOIN users_status AS us ON u.uid = us.uid where us.open='1' ORDER BY u.uid DESC LIMIT 0,1");
list($lastuser, $lastava) = sql_fetch_row($result);

echo '
    <h2><img src="assets/images/admin/users.png" alt="'.translate("Liste des membres").'" />'.translate("Liste des membres");

if (isset ($uid_from_ws) and ($uid_from_ws != ''))
{
    echo '<span class="text-muted"> '.translate("pour le groupe").' #'.$gr_from_ws.'</span>';
} 

echo '</h2>
    <hr />';

if (!isset($gr_from_ws)) 
{
    echo '
    <div class="media">';
       
    if ($ibid_avatar = avatar($lastava))
    {
        echo '
        <div class="media-left media-middle">
            <img src="'.$ibid_avatar.'" class=" media-object n-ava img-thumbnail" alt="avatar" />
        </div>';
    }
       
    echo '
        <div class="media-body ml-2">
            '.translate("Bienvenue au dernier membre affilié : ").' <br /><h4 class="media-heading"><a href="user.php?op=userinfo&amp;uname='.$lastuser.'">'.$lastuser.'</a></h4>
        </div>
    </div>
    <hr />';
}

echo '
<div class="card card-body mb-3">
    <p>';

alpha();
echo '</p>';
SortLinks($letter);
echo '
    </div>';

$min = $pagesize * ($page - 1);
$max = $pagesize;
$ws_req = '';

if (isset($uid_from_ws) and ($uid_from_ws != '')) 
{
    $ws_req = 'WHERE uid REGEXP \''.$uid_from_ws.'\' ';
}

$count = "SELECT COUNT(uid) AS total FROM ".$NPDS_Prefix."users ";
$select = "SELECT uid, name, uname, femail, url, user_regdate, user_from, email, is_visible, user_viewemail, user_avatar, mns, user_lastvisit FROM ".$NPDS_Prefix."users ";

if (($letter != translate("Autres")) AND ($letter != translate("Tous"))) 
{
    if ($admin and (preg_match('#^[_\.0-9a-z-]+@[0-9a-z-\.]+\.+[a-z]{2,4}$#i', $letter))) 
    {
        $where = "WHERE uname LIKE '".$letter."%' OR email LIKE '%".strtolower($letter)."%'".str_replace ('WHERE', ' AND', $ws_req);
    }
    else
    {
        $where = "WHERE uname LIKE '".$letter."%'".str_replace ('WHERE', ' AND', $ws_req );
    }
}
else if (($letter == translate("Autres")) AND ($letter != translate("Tous")))
{
    $where = "WHERE uname REGEXP \"^\[1-9]\" ".str_replace ( 'WHERE',' AND', $ws_req );
}
else
{
    $where = $ws_req;
}

global $member_invisible;
if ($member_invisible) 
{
    if ($admin)
    {
        $and = '';
    }
    else 
    {
        if ($where)
        {
            $and = ' AND is_visible=1 ';
        }
        else
        {
            $and = ' WHERE is_visible=1 ';
        }
    }
} 
else
{
    $and = '';
}

$sort = " ORDER BY $sortby";
$limit = ' LIMIT '.$min.', '.$max;
$count_result = sql_query($count.$where);

list($num_rows_per_order) = sql_fetch_row($count_result);
$result = sql_query($select.$where.$and.$sort.$limit);

if ( $letter != 'front' ) 
{
    echo '
    <table class="table table-no-bordered table-sm " data-toggle="table" data-search="true" data-show-toggle="true" data-mobile-responsive="true" data-buttons-class="outline-secondary" data-icons="icons" data-icons-prefix="fa" data-show-columns="true">
        <thead>
            <tr>
                <th class="n-t-col-xs-1 align-middle text-muted" data-halign="center" data-align="center"><i class="fa fa-user-o fa-lg"></i></th>
                <th class="align-middle" data-sortable="true">'.translate("Identifiant").'</th>
                <th class="align-middle" data-sortable="true">'.translate("Identité").'</th>
                ';
    
    if ($sortby != 'user_from ASC')
    {
        echo '
        <th class="align-middle " data-sortable="true" data-halign="center">'.translate("Email").'</th>';
    }
    else
    {
        echo '
        <th class="align-middle " data-sortable="true" data-halign="center" >'.translate("Localisation").'</th>';
    }
       
    echo '
    <th class="align-middle " data-halign="center">'.translate("Url").'</th>';
       
    $cols = 6;
    if ($admin) 
    {
        $cols = 7;
        echo '<th class="n-t-col-xs-2 align-middle " data-halign="center" data-align="right">'.translate("Fonctions").'</th>';
    }

    echo '
        </tr>
    </thead>
    <tbody>';
       
    $num_users = sql_num_rows($result);
       
    if ( $num_rows_per_order > 0 ) 
    {
        global $anonymous, $user;
        while($temp_user = sql_fetch_assoc($result)) 
        {
            $socialnetworks = array(); 
            $posterdata_extend = array();
            $res_id = array();
            $my_rs = '';
            
            if (!$short_user) 
            {
                $posterdata_extend = auth::get_userdata_extend_from_id($temp_user['uid']);
                
                include('modules/reseaux-sociaux/reseaux-sociaux.conf.php');
                include('modules/geoloc/geoloc_conf.php');
                
                if (array_key_exists('M2', $posterdata_extend)) 
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
                                $my_rs .= '<a class="mr-2" href="';
                            
                                if($v1[2] == 'skype') 
                                {
                                    $my_rs .= $v1[1].$y1[1].'?chat'; 
                                }
                                else 
                                {
                                    $my_rs .= $v1[1].$y1[1];
                                }
                            
                                $my_rs .= '" target="_blank"><i class="fab fa-'.$v1[2].' fa-lg fa-fw mb-2"></i></a> ';
                                // break; ?????
                            } 
                        }
                    }
                }
            }

            settype($ch_lat, 'string');
            $useroutils = '';
             
            if ($temp_user['uid'] != 1 and $temp_user['uid'] != '')
            {
                $useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="user.php?op=userinfo&amp;uname='.$temp_user['uname'].'" target="_blank" title="'.translate("Profil").'" ><i class="fa fa-user fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Profil").'</span></a>';
            }
             
            if ($temp_user['uid'] != 1 and $temp_user['uid'] != '')
            {
                $useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="two_api.php?op=instant_message&amp;to_userid='.urlencode($temp_user['uname']).'" title="'.translate("Envoyer un message interne").'" ><i class="far fa-envelope fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Message").'</span></a>';
            }
             
            if ($temp_user['femail'] != '')
            {
                $useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="mailto:'.spam::anti_spam($temp_user['femail'],1).'" target="_blank" title="'.translate("Email").'" ><i class="fa fa-at fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Email").'</span></a>';
            }
             
            if ($temp_user['url'] != '')
            {
                $useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="'.$temp_user['url'].'" target="_blank" title="'.translate("Visiter ce site web").'"><i class="fas fa-external-link-alt fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Visiter ce site web").'</span></a>';
            }
             
            if ($temp_user['mns'])
            {
                 $useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="minisite.php?op='.$temp_user['uname'].'" target="_blank" target="_blank" title="'.translate("Visitez le minisite").'" ><i class="fa fa-desktop fa-2x align-middle fa-fw"></i><span class="ml-3 d-none d-md-inline">'.translate("Visitez le minisite").'</span></a>';
            }

            if($user)
            {
                if ($temp_user['uid'] != 1)
                {
                    $useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="memberslist.php?letter='.$letter.'&amp;sortby='.$sortby.'&amp;list='.$list.urlencode($temp_user['uname']).',&amp;page='.$page.'&amp;gr_from_ws='.$gr_from_ws.'" title="'.translate("Ajouter à la liste de diffusion").'" ><i class="fa fa-plus-circle fa-2x align-middle fa-fw">&nbsp;</i><span class="ml-3 d-none d-md-inline">'.translate("Liste de diffusion").'</span></a>';
                }
            }
             
            if ($temp_user['uid'] != 1 and array_key_exists($ch_lat, $posterdata_extend)) 
            {
                if ($posterdata_extend[$ch_lat] != '')
                {
                   $useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="modules.php?ModPath=geoloc&amp;ModStart=geoloc&op=u'.$temp_user['uid'].'" title="'.translate("Localisation").'" ><i class="fas fa-map-marker-alt fa-2x align-middle fa-fw">&nbsp;</i><span class="ml-3 d-none d-md-inline">'.translate("Localisation").'</span></a>';
                }
            }

            $op_result = sql_query("SELECT open FROM ".$NPDS_Prefix."users_status WHERE uid='".$temp_user['uid']."'");
            list($open_user) = sql_fetch_row($op_result);
             
            $clconnect = '';
             
            if (($open_user == 1 and $user) || ($admin)) 
            {
                if ($open_user == 0) 
                {
                    $clconnect = 'danger';
                    echo '
                    <tr class="table-danger" title="'.translate("Connexion non autorisée").'" data-toggle="tooltip">
                    <td title="'.translate("Connexion non autorisée").'" data-toggle="tooltip">';
                }
                else  
                {
                    $clconnect = 'primary';
                    echo '
                        <tr>
                    <td>';
                }

                if ($ibid_avatar = avatar($temp_user['user_avatar']))
                {
                    echo '<a tabindex="0" data-toggle="popover" data-placement="right" data-trigger="focus" data-html="true" data-title="'.$temp_user['uname'].'" data-content=\'<div class="list-group mb-3 text-center">'.$useroutils.'</div><div class="mx-auto text-center" style="max-width:170px;">'.$my_rs.'</div>\'></i><img data-html="true" class=" btn-outline-'.$clconnect.' img-thumbnail img-fluid n-ava-40" src="'.$ibid_avatar.'" alt="'.$temp_user['uname'].'" /></a>
                        </td>
                        <td><a href="user.php?op=userinfo&amp;uname='.$temp_user['uname'].'" title="'.date(translate("dateinternal"), $temp_user['user_regdate']);
                }
             
                if ($admin and $temp_user['user_lastvisit'] != '') 
                {
                    echo ' => '.date(translate("dateinternal"), $temp_user['user_lastvisit']);
                }

                echo '" data-toggle="tooltip">'.$temp_user['uname'].'</a>
                    </td>
                    <td>'.$temp_user['name'].'</td>';
             
                if ($sortby != 'user_from ASC') 
                {
                    if ($admin) 
                    {
                        if(mailler::isbadmailuser($temp_user['uid']) === true)
                        {
                            echo '<td class="table-danger"><small>'.$temp_user['email'].'</small></td>';
                        }
                        else
                        {
                            echo '<td><small>'.spam::preg_anti_spam($temp_user['email']).'</small></td>';
                        }
                    } 
                    else 
                    {
                        if ($temp_user['user_viewemail']) 
                        {
                            echo '<td><small>'.spam::preg_anti_spam($temp_user['email']).'</small></td>';
                        } 
                        else 
                        {
                            echo '<td><small>'.substr($temp_user['femail'], 0, strpos($temp_user['femail'], "@")).'</small></td>';
                        }
                    }
                } 
                else
                {
                    echo '<td><small>'.$temp_user['user_from'].'</small></td>';
                }
             
                echo '
                <td><small>';
             
                if($temp_user['url'] != '')
                {
                    echo '<a href="'.$temp_user['url'].'" target="_blank">'.$temp_user['url'].'</a>';
                }
             
                echo '</small></td>';
             
                if ($admin) 
                {
                    echo '
                    <td>
                       <a class="mr-3" href="admin.php?chng_uid='.$temp_user['uid'].'&amp;op=modifyUser" ><i class="fa fa-edit fa-lg" title="'.translate("Editer").'" data-toggle="tooltip"></i></a> 
                       <a href="admin.php?op=delUser&amp;chng_uid='.$temp_user['uid'].'" ><i class="far fa-trash-alt fa-lg text-danger" title="'.translate("Effacer").'" data-toggle="tooltip"></i></a>';
                    
                    if (!$temp_user['is_visible'])
                    {
                        echo '<img src="assets/images/admin/ws/user_invisible.gif" alt="'.translate("Membre invisible").'" title="'.translate("Membre invisible").'" />';
                    }
                    else
                    {
                        echo '<img src="assets/images/admin/ws/blank.gif" alt="" />';
                    }
                    
                    echo '
                    </td>';
                }

                echo '
                </tr>';
            }
        }
    } 
    else 
    {
        echo '
        <tr>
            <td colspan="'.$cols.'"><strong>'.translate("Aucun membre trouvé pour").' '.$letter.'</strong></td>
        </tr>';
    }
       
    echo '
       </tbody>
    </table>';

    if ($user) 
    {
        echo '
        <div class="mt-3 card card-block-small">
            <p class=""><strong>'.translate("Liste de diffusion").' :</strong>&nbsp;';
             
        if ($list) 
        {
            echo urldecode($list);
            echo '
            <span class="float-right">
                <a href="replypmsg.php?send='.substr($list,0,strlen($list)-3).'" ><i class="far fa-envelope fa-lg" title="'.translate("Ecrire à la liste").'" data-toggle="tooltip" ></i></a>
                <a class="ml-3" href="memberslist.php?letter='.$letter.'&amp;sortby='.$sortby.'&amp;page='.$page.'&amp;gr_from_ws='.$gr_from_ws.'" ><i class="far fa-trash-alt fa-lg text-danger" title="'.translate("Raz de la liste").'" data-toggle="tooltip" ></i></a>
            </span>';
        }

        echo '</p>
        </div>';
    }

    settype($total_pages, 'integer');
          
    if ($num_rows_per_order > $pagesize ) 
    {
        echo '
        <div class="mt-3 lead align-middle">
            <span class="badge badge-secondary lead">'.$num_rows_per_order.'</span> '.translate("Utilisateurs trouvés pour").' <strong>'.$letter.'</strong> ('.$total_pages.' '.translate("pages").', '.$num_users.' '.translate("Utilisateurs montrés").').
        </div>
        <ul class="pagination pagination-sm my-3 flex-wrap">';
             
        $total_pages = ceil($num_rows_per_order / $pagesize);
        $prev_page = $page - 1;
             
        if ($prev_page > 0) 
        {
            echo '
            <li class="page-item"><a class="page-link" href="memberslist.php?letter='.$letter.'&amp;sortby='.$sortby.'&amp;list='.$list.'&amp;page='.$prev_page.'&amp;gr_from_ws='.$gr_from_ws.'"><i class="fa fa-angle-double-left fa-lg"></i></a></li>';
        }

        $next_page = $page + 1;
             
        if ($next_page <= $total_pages) 
        {
            echo '
            <li class="page-item"><a class="page-link" href="memberslist.php?letter='.$letter.'&amp;sortby='.$sortby.'&amp;list='.$list.'&amp;page='.$next_page.'&amp;gr_from_ws='.$gr_from_ws.'"><i class="fa fa-angle-double-right fa-lg"></i></a></li>';
        }

        for($n=1; $n < $total_pages; $n++) 
        {
            if ($n == $page) 
            {
                echo '
                <li class="page-item active"><a class="page-link" href="#">'.$n.'</a></li>';
            } 
            else 
            {
                echo '
                <li class="page-item"><a class="page-link" href="memberslist.php?letter='.$letter.'&amp;sortby='.$sortby.'&amp;list='.$list.'&amp;page='.$n.'&amp;gr_from_ws='.$gr_from_ws.'">'.$n.'</a></li>';
            }

            if ($n >= 22) 
            {  // if more than 20 pages are required, break it at 22.
                $break = true;
                break;
            }
        }

        if (!isset($break)) 
        {
            if ($n == $page) 
            {
                echo '
                <li class="page-item active"><a class="page-link" href="#">'.$n.'</a></li>';
            } 
            else 
            {
                echo '
                <li class="page-item"><a class="page-link" href="memberslist.php?letter='.$letter.'&amp;sortby='.$sortby.'&amp;list='.$list.'&amp;page='.$total_pages.'&amp;gr_from_ws='.$gr_from_ws.'">'.$n.'</a></li>';
            }
        }

        echo '
        </ul>';
    } 
    else 
    {
        echo '<div class="mt-3 lead align-middle"><span class="badge badge-secondary lead">'.$num_rows_per_order.'</span> '.translate("Utilisateurs trouvés").'</div>';
    }
}
       
include('footer.php');
