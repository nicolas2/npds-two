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
namespace npds\groupes;

use npds\language\language;
use npds\utility\spam;
use npds\utility\str;
use npds\utility\crypt;
use npds\auth\auth;
use npds\views\theme;
use npds\cache\cache;
use npds\users\online;
use npds\assets\java;


/*
 * groupe
 */
class groupe {


    /**
     * Retourne un tableau contenant la liste des groupes d'appartenance d'un membre
     * @param  [type] $xuser [description]
     * @return [type]        [description]
     */
    public static function valid_group($xuser) 
    {
        global $NPDS_Prefix;
           
        if ($xuser) 
        {    
            $userdata = explode(':', base64_decode($xuser));
            $user_temp = cache::Q_select("SELECT groupe FROM ".$NPDS_Prefix."users_status WHERE uid='$userdata[0]'",3600);
            
            $groupe = $user_temp[0];
            $tab_groupe = explode(',', $groupe['groupe']);
        } 
        else
        {
            $tab_groupe = '';
        }
           
        return $tab_groupe;
    }

    /**
     * Retourne une liste des groupes disponibles dans un tableau
     * @return [type] [description]
     */
    public static function liste_group() 
    {
        global $NPDS_Prefix;
           
        $r = sql_query("SELECT groupe_id, groupe_name FROM ".$NPDS_Prefix."groupes ORDER BY groupe_id ASC");
        
        $tmp_groupe[0] = '-> '.adm_translate("Supprimer").'/'.adm_translate("Choisir un groupe").' <-';
           
        while($mX = sql_fetch_assoc($r)) 
        {
            $tmp_groupe[$mX['groupe_id']] = language::aff_langue($mX['groupe_name']);
        }
        sql_free_result($r);
        
        return $tmp_groupe;
    }

    /**
     * Retourne true ou false en fonction de l'autorisation d'un membre 
     * sur 1 (ou x) forum de type groupe
     * @param  [type] $forum_groupeX [description]
     * @param  [type] $tab_groupeX   [description]
     * @return [type]                [description]
     */
    public static function groupe_forum($forum_groupeX, $tab_groupeX) 
    {
        $ok_affich = static::groupe_autorisation($forum_groupeX, $tab_groupeX);
        
        return $ok_affich;
    }

    /**
     * Retourne true ou false en fonction de l'autorisation 
     * d'un membre sur 1 (ou x) groupe
     * @param  [type] $groupeX     [description]
     * @param  [type] $tab_groupeX [description]
     * @return [type]              [description]
     */
    public static function groupe_autorisation($groupeX, $tab_groupeX) 
    {
        $tab_groupe = explode(',', $groupeX);
        $ok = false;
        
        if ($tab_groupeX) 
        {
            foreach($tab_groupe as $groupe) 
            {
                foreach($tab_groupeX as $groupevalue) 
                {
                    if ($groupe == $groupevalue) 
                    {
                       $ok = true;
                       break;
                    }
                }

                if ($ok) 
                {
                    break;
                }
            }
        }
        
        return $ok;
    }

    /**
     * fab_espace_groupe 
     * @param  [type] $gr   [description]
     * @param  [type] $t_gr [description]
     * @param  [type] $i_gr [description]
     * @return [type]       [description]
     */
    public static function fab_espace_groupe($gr, $t_gr, $i_gr) 
    {
        global $NPDS_Prefix, $short_user;

        $rsql = sql_fetch_assoc(sql_query("SELECT groupe_id, groupe_name, groupe_description, groupe_forum, groupe_mns, groupe_chat, groupe_blocnote, groupe_pad FROM ".$NPDS_Prefix."groupes WHERE groupe_id='$gr'"));

        $content = '
        <script type="text/javascript">
            //<![CDATA[
                //==> chargement css
                if (!document.getElementById(\'bloc_ws_css\')) {
                    var l_css = document.createElement(\'link\');
                    l_css.href = "modules/groupe/assets/css/bloc_ws.css";
                    l_css.rel = "stylesheet";
                    l_css.id = "bloc_ws_css";
                    l_css.type = "text/css";
                    document.getElementsByTagName("head")[0].appendChild(l_css);
                }
            //]]>
        </script>';

        $content .= '
        <div id="bloc_ws_'.$gr.'" class="">'."\n";
           
        if ($t_gr == 1) 
        {
            $content .= '<span style="font-size: 120%; font-weight:bolder;">'.language::aff_langue($rsql['groupe_name']).'</span>'."\n";
        }
           
        $content .= '<p>'.language::aff_langue($rsql['groupe_description']).'</p>'."\n";
           
        if (file_exists('storage/users_private/groupe/'.$gr.'/groupe.png') and ($i_gr == 1)) 
        {
            $content .= '<img src="storage/users_private/groupe/'.$gr.'/groupe.png" class="img-fluid mx-auto d-block rounded" alt="'.translate("Groupe").'" />';
        }

        // liste des membres
        $li_mb = ''; 
        $li_ic = '';
        
        $result = sql_query("SELECT uid, groupe FROM ".$NPDS_Prefix."users_status WHERE groupe REGEXP '[[:<:]]".$gr."[[:>:]]' ORDER BY uid ASC");
        $nb_mb = sql_num_rows ($result);
        
        $count = 0;
        $li_mb .= '
        <div class="my-4">
            <a data-toggle="collapse" data-target="#lst_mb_ws_'.$gr.'" class="text-primary" id="show_lst_mb_ws_'.$gr.'" title="'.translate("Déplier la liste").'"><i id="i_lst_mb_ws_'.$gr.'" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a><i class="fa fa-users fa-2x text-muted ml-3 align-middle" title="'.translate("Liste des membres du groupe.").'" data-toggle="tooltip"></i>&nbsp;<a href="memberslist.php?gr_from_ws='.$gr.'" class="text-uppercase">'.translate("Membres").'</a><span class="badge badge-secondary float-right">'.$nb_mb.'</span>';
           
        $tab = online::online_members();
        $li_mb .= '
        <ul id="lst_mb_ws_'.$gr.'" class=" ul_bloc_ws collapse ">';
           
        while(list($uid, $groupe) = sql_fetch_row($result)) 
        {
            $socialnetworks = array(); 
            $posterdata_extend = array();
            $res_id = array();
            $my_rs = '';
            
            if (!$short_user) 
            {       
                $posterdata_extend = auth::get_userdata_extend_from_id($uid);
                
                include('modules/reseaux-sociaux/reseaux-sociaux.conf.php');
                
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
                                 break;
                            } 
                            else 
                            {
                                $my_rs .= '';
                            }
                        }
                    }

                    $my_rsos[] = $my_rs;
                }
                else 
                {
                    $my_rsos[] = '';
                }
            }
           
            list($uname, $user_avatar, $mns, $url, $femail) = sql_fetch_row(sql_query("SELECT uname, user_avatar, mns, url, femail FROM ".$NPDS_Prefix."users WHERE uid='$uid'"));

            include('modules/geoloc/config/geoloc.php');
            
            settype($ch_lat, 'string');
            
            $useroutils = '';
            
            if ($uid != 1 and $uid != '')
            {
                $useroutils .= '<a class="list-group-item text-primary" href="user.php?op=userinfo&amp;uname='.$uname.'" target="_blank" title="'.translate("Profil").'" data-toggle="tooltip"><i class="fa fa-2x fa-user align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Profil").'</span></a>';
            }
            
            if ($uid != 1)
            {
                $useroutils .= '<a class="list-group-item text-primary" href="two_api.php?op=instant_message&amp;to_userid='.$uname.'" title="'.translate("Envoyer un message interne").'" data-toggle="tooltip"><i class="far fa-2x fa-envelope align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Message").'</span></a>';
            }
              
            if ($femail != '')
            {
                $useroutils .= '<a class="list-group-item text-primary" href="mailto:'.spam::anti_spam($femail,1).'" target="_blank" title="'.translate("Email").'" data-toggle="tooltip"><i class="fas fa-at fa-2x align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Email").'</span></a>';
            }
            
            if ($url != '')
            {
                $useroutils .= '<a class="list-group-item text-primary" href="'.$url.'" target="_blank" title="'.translate("Visiter ce site web").'" data-toggle="tooltip"><i class="fas fa-2x fa-external-link-alt align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Visiter ce site web").'</span></a>';
            }
              
            if ($mns)
            {
                $useroutils .= '<a class="list-group-item text-primary" href="minisite.php?op='.$uname.'" target="_blank" target="_blank" title="'.translate("Visitez le minisite").'" data-toggle="tooltip"><i class="fa fa-2x fa-desktop align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Visitez le minisite").'</span></a>';
            }
              
            if (!$short_user)
            {
                if ($posterdata_extend[$ch_lat] != '')
                {
                    $useroutils .= '<a class="list-group-item text-primary" href="modules.php?ModPath=geoloc&amp;ModStart=geoloc&op=u'.$uid.'" title="'.translate("Localisation").'" ><i class="fas fa-map-marker-alt fa-2x align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Localisation").'</span></a>';
                }
            }

            $conn = '<i class="fa fa-plug text-muted" title="'.$uname.' '.translate("n'est pas connecté").'" data-toggle="tooltip" ></i>';
            
            if (!$user_avatar)
            {
                $imgtmp = "images/forum/avatar/blank.gif";
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
              
            $timex = false;
            
            for ($i = 1; $i <= $tab[0]; $i++) 
            {
                if ($tab[$i]['username'] == $uname)
                {
                    $timex = time()-$tab[$i]['time'];
                }
            }
              
            if (($timex !== false) and ($timex < 60))
            {
                $conn = '<i class="fa fa-plug faa-flash animated text-primary" title="'.$uname.' '.translate("est connecté").'" data-toggle="tooltip" ></i>';
            }
              
            $li_ic .= '<img class="n-smil" src="'.$imgtmp.'" alt="avatar" />';
            $li_mb .= '
            <li class="list-group-item list-group-item-action d-flex flex-row p-2">
                <div id="li_mb_'.$uname.'_'.$gr.'" class="n-ellipses">
                '.$conn.'<a class="ml-2" tabindex="0" data-title="'.$uname.'" data-toggle="popover" data-trigger="focus" data-html="true" data-content=\'<div class="list-group mb-3">'.$useroutils.'</div><div class="mx-auto text-center" style="max-width:170px;">';
              
            if (!$short_user)
            {
                $li_mb .= $my_rsos[$count];
            }
              
            $li_mb .= '</div>\'><img class=" btn-outline-primary img-thumbnail img-fluid n-ava-small " src="'.$imgtmp.'" alt="avatar" title="'.$uname.'" /></a><span class="ml-2">'.$uname.'</span>
                       </div>
                    </li>';
            $count++;
        }
           
        $li_mb .= '
                <li style="clear:left;line-height:6px; background:none;">&nbsp;</li>
                <li class="list-group-item" style="clear:left;line-height:24px;padding:6px; margin-top:0px;">'.$li_ic.'</li>
           </ul>
        </div>';
           
        $content .= $li_mb;
        // liste des membres

        // Forum
        $lst_for = '';
        $lst_for_tog = '';
        $nb_for_gr = '';
           
        if ($rsql['groupe_forum'] == 1) 
        {
            $res_forum = sql_query("SELECT forum_id, forum_name FROM ".$NPDS_Prefix."forums WHERE forum_pass REGEXP '$gr'");
            $nb_foru = sql_num_rows ($res_forum);
            
            if ($nb_foru >= 1) 
            {
                $lst_for_tog = '<a data-toggle="collapse" data-target="#lst_for_gr_'.$gr.'" class="text-primary" id="show_lst_for_'.$gr.'" title="'.translate("Déplier la liste").'" ><i id="i_lst_for_gr_'.$gr.'" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a>';
                
                $lst_for .= '<ul id="lst_for_gr_'.$gr.'" class="ul_bloc_ws collapse" style ="list-style-type:none;">';
                
                $nb_for_gr = '  <span class="badge badge-secondary float-right">'.$nb_foru.'</span>';
                
                while(list($id_fo,$fo_name) = sql_fetch_row($res_forum)) 
                {
                    $lst_for .= '
                    <li class="list-group-item list-group-item-action"><a href="viewforum.php?forum='.$id_fo.'">'.$fo_name.'</a></li>';
                }
                 
                $lst_for .= '</ul>';
            }

            $content .= '
            <hr /><div class="">'.$lst_for_tog.'<i class="fa fa-list-alt fa-2x text-muted ml-3 align-middle" title="'.translate("Groupe").'('.$gr.'): '.translate("forum").'." data-toggle="tooltip" ></i>&nbsp;<a class="text-uppercase" href="forum.php">'.translate("Forum").'</a>'.$nb_for_gr.$lst_for.'</div>'."\n";
        }

        // wspad
        if ($rsql['groupe_pad'] == 1) 
        {
            settype($lst_doc, 'string');
            settype($nb_doc_gr, 'string');
            settype($lst_doc_tog, 'string');
           
            include("modules/wspad/config.php");
            
            $docs_gr = sql_query("SELECT page, editedby, modtime, ranq FROM ".$NPDS_Prefix."wspad WHERE (ws_id) IN (SELECT MAX(ws_id) FROM ".$NPDS_Prefix."wspad WHERE member='$gr' GROUP BY page) ORDER BY page ASC");
            $nb_doc = sql_num_rows ($docs_gr);
              
            if ($nb_doc >= 1) 
            {
                $lst_doc_tog = '<a data-toggle="collapse" data-target="#lst_doc_gr_'.$gr.'" class="text-primary" id="show_lst_doc_'.$gr.'" title="'.translate("Déplier la liste").'"><i id="i_lst_doc_gr_'.$gr.'" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a>';
                
                $lst_doc .= '
                 <ul id="lst_doc_gr_'.$gr.'" class="ul_bloc_ws mt-3 collapse">';
                
                $nb_doc_gr = '  <span class="badge badge-secondary float-right">'.$nb_doc.'</span>';
                
                while (list($p, $e, $m, $r) = sql_fetch_row($docs_gr)) 
                {
                    $surlignage = $couleur[str::hexfromchr($e)];
                    
                    $lst_doc .= '
                    <li class="list-group-item list-group-item-action" style="line-height:14px;"><div id="last_editor_'.$p.'" data-toggle="tooltip" data-placement="right" title="'.translate("Dernier éditeur").' : '.$e.' '.date(translate("dateinternal"), $m ).'" style="float:left; width:1rem; height:1rem; background-color:'.$surlignage.'"></div><i class="fa fa-edit text-muted mx-1" data-toggle="tooltip" title="'.translate("Document co-rédigé").'." ></i><a href="modules.php?ModPath=wspad&amp;ModStart=wspad&amp;op=relo&amp;page='.$p.'&amp;member='.$gr.'&amp;ranq='.$r.'">'.$p.'</a></li>';
                }

                $lst_doc .= '
                </ul>';
            }
            
            $content .= '
            <hr /><div class="">'. $lst_doc_tog.'<i class="fa fa-edit fa-2x text-muted ml-3 align-middle" title="'.translate("Co-rédaction").'" data-toggle="tooltip" data-placement="right"></i>&nbsp;<a class="text-uppercase" href="modules.php?ModPath=wspad&ModStart=wspad&member='.$gr.'" >'.translate("Co-rédaction").'</a>'.$nb_doc_gr.$lst_doc.'</div>'."\n";
        }
        // wspad
           
        // bloc-notes
        if ($rsql['groupe_blocnote'] == 1) 
        {
            settype($lst_blocnote_tog, 'string');
            settype($lst_blocnote, 'string');
            
            include_once("modules/bloc-notes/bloc-notes.php");
            
            $lst_blocnote_tog = '<a data-toggle="collapse" data-target="#lst_blocnote_'.$gr.'" class="text-primary" id="show_lst_blocnote" title="'.translate("Déplier la liste").'"><i id="i_lst_blocnote" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a><i class="far fa-sticky-note fa-2x text-muted ml-3 align-middle"></i>&nbsp;<span class="text-uppercase">Bloc note</span>';
            
            $lst_blocnote = '
            <div id="lst_blocnote_'.$gr.'" class="mt-3 collapse">
            '.blocnotes("shared", 'WS-BN'.$gr, '', '7', 'bg-dark text-light', false).'
            </div>';
            
            $content .= '
            <hr />
            <div class="mb-2">'.$lst_blocnote_tog.$lst_blocnote.'</div>';
        }
        // bloc-notes
           
        $content .= '<div class="px-1 card card-body d-flex flex-row mt-3 flex-wrap text-center">';
        
        // Filemanager
        if (file_exists('modules/f-manager/users/groupe_'.$gr.'.conf.php'))
        {
            $content .= '<a class="mx-2" href="modules.php?ModPath=f-manager&amp;ModStart=f-manager&amp;FmaRep=groupe_'.$gr.'" title="'.translate("Gestionnaire fichiers").'" data-toggle="tooltip" data-placement="right"><i class="fa fa-folder fa-2x"></i></a>'."\n";
        }
           
        // Minisite
        if ($rsql['groupe_mns'] == 1)
        {
            $content .= '<a class="mx-2" href="minisite.php?op=groupe/'.$gr.'" target="_blank" title= "'.translate("MiniSite").'" data-toggle="tooltip" data-placement="right"><i class="fa fa-desktop fa-2x"></i></a>';
        }
           
        // Chat
        settype($chat_img, 'string');
           
        if ($rsql['groupe_chat'] == 1) 
        {
            $PopUp = java::JavaPopUp("chat.php?id=$gr&amp;auto=".crypt::encrypt(serialize ($gr)), "chat".$gr, 380, 480);
            
            if (array_key_exists('chat_info_'.$gr, $_COOKIE))
            {
                if ($_COOKIE['chat_info_'.$gr]) 
                {
                    $chat_img = 'faa-pulse animated faa-slow';
                }
            }
              
            $content .= '<a class="mx-2" href="javascript:void(0);" onclick="window.open('.$PopUp.');" title="'.translate("Ouvrir un salon de chat pour le groupe.").'" data-toggle="tooltip" data-placement="right" ><i class="fa fa-comments fa-2x '.$chat_img.'"></i></a>';
        }
        
        // admin
        if (auth::autorisation(-127))
        {
            $content .= '<a class="mx-2" href="admin.php?op=groupes" ><i title="'.translate("Gestion des groupes.").'" data-toggle="tooltip" class="fa fa-cogs fa-2x"></i></a>';
        }
           
        $content .= '</div>
        </div>';
        
        return $content;
    }

}
