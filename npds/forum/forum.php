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
namespace npds\forum;

use npds\groupes\groupe;
use npds\views\theme;
use npds\utility\str;
use npds\cache\cache;
use npds\mailler\mailler;
use npds\language\metalang;


/*
 * forum
 */
class forum {


    /**
     * [forum description]
     * @param  [type] $rowQ1 [description]
     * @return [type]        [description]
     */
    public static function forum($rowQ1) 
    {
        global $user, $subscribe, $theme, $NPDS_Prefix, $admin, $adminforum;

        // droits des admin sur les forums (superadmin et admin avec droit gestion forum)
        $adminforum = false;
        
        if ($admin) 
        {
            $adminX = base64_decode($admin);
            $adminR = explode(':', $adminX);
            
            $Q = sql_fetch_assoc(sql_query("SELECT * FROM ".$NPDS_Prefix."authors WHERE aid='$adminR[0]' LIMIT 1"));
            
            if ($Q['radminsuper'] == 1) 
            {
                $adminforum = 1;
            } 
            else 
            {
                $R = sql_query("SELECT fnom, fid, radminsuper FROM ".$NPDS_Prefix."authors a LEFT JOIN ".$NPDS_Prefix."droits d ON a.aid = d.d_aut_aid LEFT JOIN ".$NPDS_Prefix."fonctions f ON d.d_fon_fid = f.fid WHERE a.aid='$adminR[0]' AND f.fid BETWEEN 13 AND 15");
               
                if (sql_num_rows($R) >= 1) 
                {
                    $adminforum = 1;
                }
            }
        }
        // droits des admin sur les forums (superadmin et admin avec droit gestion forum)

        if ($user) 
        {
            $userX = base64_decode($user);
            $userR = explode(':', $userX);
            $tab_groupe = groupe::valid_group($user);
        }

        if ($ibid = theme::theme_image("forum/icons/red_folder.gif")) 
        {
            $imgtmpR = $ibid;
        } 
        else 
        {
            $imgtmpR = "assets/images/forum/icons/red_folder.gif";
        }
        
        if ($ibid = theme::theme_image("forum/icons/folder.gif")) 
        {
            $imgtmp = $ibid;
        } 
        else 
        {
            $imgtmp = "assets/images/forum/icons/folder.gif";
        }

        // preparation de la gestion des folders
        $result = sql_query("SELECT forum_id, COUNT(topic_id) AS total FROM ".$NPDS_Prefix."forumtopics GROUP BY (forum_id)");
        
        while (list($forumid, $total) = sql_fetch_row($result)) 
        {
            // Topic
            $tab_folder[$forumid][0] = $total; 
        }

        $result = sql_query("SELECT forum_id, COUNT(DISTINCT topicid) AS total FROM ".$NPDS_Prefix."forum_read WHERE uid='$userR[0]' AND topicid>'0' AND status!='0' GROUP BY (forum_id)");
        
        while (list($forumid, $total) = sql_fetch_row($result)) 
        {
            // Folder
            $tab_folder[$forumid][1] = $total; 
        }

        // préparation de la gestion des abonnements
        $result = sql_query("SELECT forumid FROM ".$NPDS_Prefix."subscribe WHERE uid='$userR[0]'");
        while (list($forumid) = sql_fetch_row($result)) 
        {
            $tab_subscribe[$forumid] = true;
        }
        
        // preparation du compteur total_post
        $rowQ0 = cache::Q_Select ("SELECT forum_id, COUNT(post_aff) AS total FROM ".$NPDS_Prefix."posts GROUP BY forum_id", 600);
        
        foreach($rowQ0 as $row0) 
        {
            $tab_total_post[$row0['forum_id']] = $row0['total'];
        }

        $ibid = '';
        
        if ($rowQ1) 
        {
            foreach($rowQ1 as $row) 
            {
                $title_aff = true;
                
                $rowQ2 = cache::Q_Select ("SELECT * FROM ".$NPDS_Prefix."forums WHERE cat_id = '".$row['cat_id']."' AND SUBSTRING(forum_name,1,3)!='<!>' ORDER BY forum_index,forum_id", 21600);
                
                if ($rowQ2) 
                {
                    foreach($rowQ2 as $myrow) 
                    {
                        // Gestion des Forums Cachés aux non-membres
                        if (($myrow['forum_type'] != "9") or ($userR)) 
                        {
                            // Gestion des Forums réservés à un groupe de membre
                            if (($myrow['forum_type'] == "7") or ($myrow['forum_type'] == "5"))
                            {
                                $ok_affich = groupe::groupe_forum($myrow['forum_pass'], $tab_groupe);
                               
                                if ((isset($admin)) and ($adminforum == 1)) 
                                {
                                   // to see when admin mais pas assez precis
                                   $ok_affich = true;
                                }
                            } 
                            else
                            {
                                $ok_affich = true;
                            }
                            
                            if ($ok_affich) 
                            {
                                if ($title_aff) 
                                {
                                    $title = stripslashes($row['cat_title']);
                                    
                                    if ((file_exists("themes/$theme/views/forum-cat".$row['cat_id'].".html")) 
                                        OR (file_exists("themes/default/views/forum-cat".$row['cat_id'].".html")))
                                    {
                                        
                                        $ibid .= '
                                        
                                        <div class=" mt-3" id="catfo_'.$row['cat_id'].'" >
                                            
                                            <a class="list-group-item list-group-item-action active" href="forum.php?catid='.$row['cat_id'].'"><h5>'.$title.'</h5></a>';
                                    }
                                    else
                                    {
                                        $ibid .= '
                                        <div class=" mt-3" id="catfo_'.$row['cat_id'].'">
                                            <div class="list-group-item list-group-item-action active"><h5>'.$title.'</h5></div>';
                                    }

                                    $title_aff = false;
                                }
                                
                                $forum_moderator = explode(' ', forumauth::get_moderator($myrow['forum_moderator']));
                                $Mmod = false;
                                
                                for ($i = 0; $i < count($forum_moderator); $i++) 
                                {
                                    if (($userR[1] == $forum_moderator[$i])) 
                                    {
                                        $Mmod = true;
                                    }
                                }

                                $last_post = forumtopics::get_last_post($myrow['forum_id'], "forum", "infos", $Mmod);
                                
                                $ibid .= '
                                <p class="mb-0 list-group-item list-group-item-action flex-column align-items-start">
                                    <span class="d-flex w-100 mt-1">';
                               
                                if (($tab_folder[$myrow['forum_id']][0]-$tab_folder[$myrow['forum_id']][1])>0)
                                {
                                    $ibid .= '<i class="fa fa-folder text-primary fa-lg mr-2 mt-1" title="'.translate("Les nouvelles contributions depuis votre dernière visite.").'" data-toggle="tooltip" data-placement="right"></i>';
                                }
                                else
                                {
                                    $ibid .= '<i class="far fa-folder text-primary fa-lg mr-2 mt-1" title="'.translate("Aucune nouvelle contribution depuis votre dernière visite.").'" data-toggle="tooltip" data-placement="right"></i>';
                                }
                               
                                $name = stripslashes($myrow['forum_name']);
                                $redirect = false;
                                
                                if (strstr(strtoupper($name), "<a HREF"))
                                {
                                    $redirect = true;
                                }
                                else
                                {
                                    $ibid .= '
                                    <a href="viewforum.php?forum='.$myrow['forum_id'].'" >'.$name.'</a>';
                                }
                               
                                if (!$redirect)
                                {
                                    $ibid .= '
                                    <span class="ml-auto"> 
                                        <span class="badge badge-secondary ml-1" title="'.translate("Contributions").'" data-toggle="tooltip">'.$tab_total_post[$myrow['forum_id']].'</span>
                                        <span class="badge badge-secondary ml-1" title="'.translate("Sujets").'" data-toggle="tooltip">'.$tab_folder[$myrow['forum_id']][0].'</span>
                                        </span>
                                    </span>';
                                }

                                $desc = stripslashes(metalang::meta_lang($myrow['forum_desc']));
                                
                                if($desc != '')
                                {
                                    $ibid .= '<span class="d-flex w-100 mt-1">'.$desc.'</span>';
                                }

                                if (!$redirect) 
                                {
                                    $ibid .= '<span class="d-flex w-100 mt-1"> [ ';
                                      
                                    if ($myrow['forum_access'] == "0" && $myrow['forum_type'] == "0")
                                    {
                                        $ibid .= translate("Accessible à tous");
                                    }

                                    if ($myrow['forum_type'] == "1")
                                    {
                                        $ibid .= translate("Privé");
                                    }
                                    
                                    if ($myrow['forum_type'] == "5")
                                    {
                                        $ibid .= "PHP Script + ".translate("Groupe");
                                    }
                                    
                                    if ($myrow['forum_type'] == "6")
                                    {
                                        $ibid .= "PHP Script";
                                    }
                                    
                                    if ($myrow['forum_type'] == "7")
                                    {
                                        $ibid .= translate("Groupe");
                                    }
                                    
                                    if ($myrow['forum_type'] == "8")
                                    {
                                        $ibid .= translate("Texte étendu");
                                    }
                                    
                                    if ($myrow['forum_type'] == "9")
                                    {
                                        $ibid .= translate("Caché");
                                    }
                                    
                                    if ($myrow['forum_access'] == "1" && $myrow['forum_type'] == "0")
                                    {
                                        $ibid .= translate("Utilisateur enregistré");
                                    }
                                   
                                    if ($myrow['forum_access'] == "2" && $myrow['forum_type'] == "0")
                                    {
                                        $ibid .= translate("Modérateur");
                                    }

                                    if ($myrow['forum_access'] == "9")
                                    {
                                        $ibid .= '<span class="text-danger mx-2"><i class="fa fa-lock mr-2"></i>'.translate("Fermé").'</span>';
                                    }

                                    $ibid .= ' ] </span>';
                                    
                                    // Subscribe
                                    if (($subscribe) and ($user)) 
                                    {
                                        if (!$redirect) 
                                        {
                                            //proto
                                            if(mailler::isbadmailuser($userR[0]) === false) 
                                            {
                                                $ibid .= '
                                                <span class="d-flex w-100 mt-1" >
                                                    <span class="custom-control custom-checkbox">';
                                                
                                                if ($tab_subscribe[$myrow['forum_id']]) 
                                                {
                                                   $ibid .= ' 
                                                   <input class="custom-control-input n-ckbf" type="checkbox" id="subforumid'.$myrow['forum_id'].'" name="Subforumid['.$myrow['forum_id'].']" checked="checked" />';
                                                }
                                                else 
                                                {
                                                    $ibid .= ' <input class="custom-control-input n-ckbf" type="checkbox" id="subforumid'.$myrow['forum_id'].'" name="Subforumid['.$myrow['forum_id'].']" />';
                                                }
                                              
                                                $ibid .= '
                                                        <label class="custom-control-label" for="subforumid'.$myrow['forum_id'].'" title="'.translate("Cochez et cliquez sur le bouton OK pour recevoir un Email lors d'une nouvelle soumission dans ce forum.").'" data-toggle="tooltip" data-placement="right">&nbsp;&nbsp;</label>
                                                    </span>
                                                </span>';
                                            }
                                        }
                                    }
                                    
                                    $ibid .= '<span class="d-flex w-100 justify-content-end"><span class="small">'.translate("Dernière contribution").' : '.$last_post.'</span></span>';
                                } 
                                else
                                {
                                    $ibid .= '';
                                }
                            }
                        }
                    }
                    
                    if(($ok_affich == false 
                        and $title_aff == false) 
                        or $ok_affich == true)
                    {
                        $ibid .= '
                            </p>
                        </div>';
                    }
                }
            }
        }

        if (($subscribe) and ($user) and ($ok_affich)) 
        {
            //proto
            if(mailler::isbadmailuser($userR[0]) === false) 
            {
                $ibid .= '
                <div class="custom-control custom-checkbox mt-1">
                    <input class="custom-control-input" type="checkbox" id="ckball_f" />
                    <label class="custom-control-label text-muted" for="ckball_f" id="ckb_status_f">Tout cocher</label>
                </div>';
            }
        }
        
        return $ibid;
    }
 
    /**
     * fonction appelée par le meta-mot forum_subfolder()
     * @param  [type] $forum [description]
     * @return [type]        [description]
     */
    public static function sub_forum_folder($forum) 
    {
        global $user, $NPDS_Prefix;

        if ($user) 
        {
            $userX = base64_decode($user);
            $userR = explode(':', $userX);
        }

        $result = sql_query("SELECT COUNT(topic_id) AS total FROM ".$NPDS_Prefix."forumtopics WHERE forum_id='$forum'");
        list($totalT) = sql_fetch_row($result);

        $result = sql_query("SELECT COUNT(DISTINCT topicid) AS total FROM ".$NPDS_Prefix."forum_read WHERE uid='$userR[0]' AND topicid>'0' AND status!='0' AND forum_id='$forum'");
        list($totalF) = sql_fetch_row($result);

        if ($ibid = theme::theme_image("forum/icons/red_sub_folder.gif")) 
        {
            $imgtmpR = $ibid;
        } 
        else 
        {
            $imgtmpR = "assets/images/forum/icons/red_sub_folder.gif";
        }
        
        if ($ibid = theme::theme_image("forum/icons/sub_folder.gif")) 
        {
            $imgtmp = $ibid;
        } 
        else 
        {
            $imgtmp = "assets/images/forum/icons/sub_folder.gif";
        }

        if (($totalT-$totalF) > 0)
        {
            $ibid = '<img src="'.$imgtmpR.'" alt="" />';
        }
        else
        {
            $ibid = '<img src="'.$imgtmp.'" alt="" />';
        }
        
        return $ibid;
    }

    /**
     * [RecentForumPosts_fab description]
     * @param [type] $title         [description]
     * @param [type] $maxforums     [description]
     * @param [type] $maxtopics     [description]
     * @param [type] $displayposter [description]
     * @param [type] $topicmaxchars [description]
     * @param [type] $hr            [description]
     * @param [type] $decoration    [description]
     */
    public static function RecentForumPosts_fab($title, $maxforums, $maxtopics, $displayposter, $topicmaxchars, $hr, $decoration) 
    {
        global $parse, $user, $NPDS_Prefix;

        $topics = 0;
        settype($maxforums, "integer");
        settype($maxtopics, "integer");

        if ($maxforums == 0)
        {
            $lim = '';
        }
        else
        {
            $lim = " LIMIT $maxforums";
        }
            
        if ($user)
        {
            $query = "SELECT * FROM ".$NPDS_Prefix."forums ORDER BY cat_id,forum_index,forum_id".$lim;
        }
        else
        {
            $query = "SELECT * FROM ".$NPDS_Prefix."forums WHERE forum_type!='9' AND forum_type!='7' AND forum_type!='5' ORDER BY cat_id,forum_index,forum_id".$lim;
        }
            
        $result = sql_query($query);

        if (!$result) 
        {
            exit();
        }

        $premier = false;
        $boxstuff = '<ul>';

        while ($row = sql_fetch_row($result)) 
        {
            if (($row[6] == "5") or ($row[6] == "7")) 
            {
                $ok_affich = false;
                $tab_groupe = groupe::valid_group($user);
                $ok_affich = groupe::groupe_forum($row[7], $tab_groupe);
            } 
            else
            {
                $ok_affich = true;
            }
               
            if ($ok_affich) 
            {
                $forumid = $row[0];
                $forumname = $row[1];
                $forum_desc = $row[2];
                  
                if ($hr)
                {
                    $boxstuff .= '<li><hr /></li>';
                }
                  
                if ($parse == 0) 
                {
                    $forumname = str::FixQuotes($forumname);
                    $forum_desc = str::FixQuotes($forum_desc);
                } 
                else 
                {
                    $forumname = stripslashes($forumname);
                    $forum_desc = stripslashes($forum_desc);
                }

                $res = sql_query("SELECT * FROM ".$NPDS_Prefix."forumtopics WHERE forum_id = '$forumid' ORDER BY topic_time DESC");
                $ibidx = sql_num_rows($res);
                
                $boxstuff .= '
                <li class="list-unstyled border-0 p-2 mt-1"><h6><a href="viewforum.php?forum='.$forumid.'" title="'.strip_tags($forum_desc).'" data-toggle="tooltip">'.$forumname.'</a><span class="float-right badge badge-secondary" title="'.translate("Sujets").'" data-toggle="tooltip">'.$ibidx.'</span></h6></li>';

                $topics = 0;
                while(($topics < $maxtopics) && ($topicrow = sql_fetch_row($res))) 
                {
                    $topicid = $topicrow[0];
                    $tt = $topictitle = $topicrow[1];
                    $date = $topicrow[3];
                    $replies = 0;
                    
                    $postquery = "SELECT COUNT(*) AS total FROM ".$NPDS_Prefix."posts WHERE topic_id = '$topicid'";
                      
                    if ($pres = sql_query($postquery)) 
                    {
                        if ($myrow = sql_fetch_assoc($pres))
                        {
                            $replies = $myrow['total'];
                        }
                    }
                      
                    if (strlen($topictitle) > $topicmaxchars) 
                    {
                        $topictitle = substr($topictitle, 0, $topicmaxchars);
                        $topictitle .= '..';
                    }

                    if ($displayposter) 
                    {
                        $posterid = $topicrow[2];
                        $RowQ1 = cache::Q_Select ("SELECT uname FROM ".$NPDS_Prefix."users WHERE uid = '$posterid'", 3600);
                        $myrow = $RowQ1[0];
                        $postername = $myrow['uname'];
                    }
                      
                    if ($parse == 0) 
                    {
                        $tt =  strip_tags(str::FixQuotes($tt));
                        $topictitle = str::FixQuotes($topictitle);
                    } 
                    else 
                    {
                        $tt =  strip_tags(stripslashes($tt));
                        $topictitle = stripslashes($topictitle);
                    }
                      
                    $boxstuff .= '<li class="list-group-item p-1 border-right-0 border-left-0 list-group-item-action"><div class="n-ellipses"><span class="badge badge-secondary mx-2" title="'.translate("Réponses").'" data-toggle="tooltip" data-placement="top">'.$replies.'</span><a href="viewtopic.php?topic='.$topicid.'&amp;forum='.$forumid.'" >'.$topictitle.'</a></div>';
                    
                    if ($displayposter) 
                    {
                        $boxstuff .= $decoration.'<span class="ml-1">'.$postername.'</span>';
                    }
                      
                    $boxstuff .= '</li>';
                    $topics++;
                }
            }
        }
            
        $boxstuff .= '
        </ul>';
            
        return $boxstuff;
    }

}
