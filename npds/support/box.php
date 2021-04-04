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

use npds\utility\str;
 

/**
 * Construit le bloc sondage
 * syntaxe : pollMain($pollID,$pollClose)
 * @param  [type] $pollID    [description]
 * @param  [type] $pollClose [description]
 * @return [type]            [description]
 */
function pollMain($pollID,$pollClose) 
{
    global $NPDS_Prefix, $maxOptions, $boxTitle, $boxContent, $userimg, $language, $pollcomm, $cookie;
    
    if (!isset($pollID))
    {
        $pollID = 1;
    }
       
    if (!isset($url))
    {
        $url = sprintf("pollBooth.php?op=results&amp;pollID=%d", $pollID);
    }
       
    $boxContent = '
        <form action="pollBooth.php" method="post">
        <input type="hidden" name="pollID" value="'.$pollID.'" />
        <input type="hidden" name="forwarder" value="'.$url.'" />';
       
    $result = sql_query("SELECT pollTitle, voters FROM ".$NPDS_Prefix."poll_desc WHERE pollID='$pollID'");
    list($pollTitle, $voters) = sql_fetch_row($result);
       
    global $block_title;
    if ($block_title == '')
    {
        $boxTitle = translate("Sondage");
    }
    else
    {
        $boxTitle = $block_title;
    }
       
    $boxContent .= '<legend>'.aff_langue($pollTitle).'</legend>';
    
    $result = sql_query("SELECT pollID, optionText, optionCount, voteID FROM ".$NPDS_Prefix."poll_data WHERE (pollID='$pollID' AND optionText<>'') ORDER BY voteID");
    $sum = 0; 
    $j = 0;
       
    if (!$pollClose) 
    {
        $boxContent .= '
        <div class="mb-3">';
          
        while($object = sql_fetch_assoc($result)) 
        {
            $boxContent .= '
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="voteID'.$j.'" name="voteID" value="'.$object['voteID'].'" />
                <label class="custom-control-label d-block" for="voteID'.$j.'" >'.aff_langue($object['optionText']).'</label>
            </div>';
            
            $sum = $sum + $object['optionCount'];
            $j++; 
        }

        $boxContent .= '
        </div>';
    } 
    else 
    {
        while($object = sql_fetch_assoc($result)) 
        {
            $boxContent .= '&nbsp;'.aff_langue($object['optionText']).'<br />';
            $sum = $sum + $object['optionCount'];
        }
    }
    
    settype($inputvote, 'string');
       
    if (!$pollClose) 
    {
        $inputvote = '<button class="btn btn-outline-primary btn-sm btn-block" type="submit" value="'.translate("Voter").'" title="'.translate("Voter").'" ><i class="fa fa-check fa-lg"></i> '.translate("Voter").'</button>';
    }
       
    $boxContent .= '
    <div class="form-group">'.$inputvote.'</div>
        </form>
        <a href="pollBooth.php?op=results&amp;pollID='.$pollID.'" title="'.translate("Résultats").'">'.translate("Résultats").'</a>&nbsp;&nbsp;<a href="pollBooth.php">'.translate("Anciens sondages").'</a>';
        
    if ($pollcomm) 
    {
        if (file_exists("modules/comments/pollBoth.conf.php")) 
        {
            include ("modules/comments/pollBoth.conf.php");
        }

        list($numcom) = sql_fetch_row(sql_query("select count(*) from ".$NPDS_Prefix."posts where forum_id='$forum' and topic_id='$pollID' and post_aff='1'"));
            
        $boxContent .= '
        <ul>
            <li>'.translate("Votes : ").' <span class="badge badge-pill badge-secondary float-right">'.$sum.'</span></li>
            <li>'.translate("Commentaire(s) : ").' <span class="badge badge-pill badge-secondary float-right">'.$numcom.'</span></li>
        </ul>';
    } 
    else 
    {
        $boxContent .= '
        <ul>
            <li>'.translate("Votes : ").' <span class="badge badge-pill badge-secondary float-right">'.$sum.'</span></li>
        <ul>';
    }
        
    themesidebox($boxTitle, $boxContent);
}
 
/**
 * Bloc activité du site 
 * syntaxe : function#Site_Activ
 * <span class="text-success">BLOCS NPDS</span>:
 */
function Site_Activ() 
{
    global $startdate, $top;
       
    list($membres, $totala, $totalb, $totalc, $totald, $totalz) = req_stat();
    
    $who_online = '
    <p class="text-center">'.translate("Pages vues depuis").' '.$startdate.' : '.str::wrh($totalz).'</p>
        <ul class="list-group mb-3" id="site_active">
            <li class="my-1">'.translate("Nb. de membres").' <span class="badge badge-pill badge-secondary float-right">'.str::wrh(($membres-1)).'</span></li>
            <li class="my-1">'.translate("Nb. d'articles").' <span class="badge badge-pill badge-secondary float-right">'.str::wrh($totala).'</span></li>
            <li class="my-1">'.translate("Nb. de forums").' <span class="badge badge-pill badge-secondary float-right">'.str::wrh($totalc).'</span></li>
            <li class="my-1">'.translate("Nb. de sujets").' <span class="badge badge-pill badge-secondary float-right">'.str::wrh($totald).'</span></li>
            <li class="my-1">'.translate("Nb. de critiques").' <span class="badge badge-pill badge-secondary float-right">'.str::wrh($totalb).'</span></li>
        </ul>';
       
    if ($ibid = theme_image("box/top.gif")) 
    {
        $imgtmp = $ibid;
    } 
    else 
    {
        $imgtmp = false;
    }
    
    if ($imgtmp) 
    {
        $who_online .= '
        <p class="text-center"><a href="top.php"><img src="'.$imgtmp.'" alt="'.translate("Top").' '.$top.'" /></a>&nbsp;&nbsp;';
          
        if ($ibid = theme_image("box/stat.gif")) 
        {
            $imgtmp = $ibid;
        } 
        else 
        {
            $imgtmp = false;
        }
          
        $who_online .= '<a href="stats.php"><img src="'.$imgtmp.'" alt="'.translate("Statistiques").'" /></a></p>';
    } 
    else
    {
        $who_online .= '
        <p class="text-center"><a href="top.php">'.translate("Top").' '.$top.'</a>&nbsp;&nbsp;<a href="stats.php" >'.translate("Statistiques").'</a></p>';
    }
       
    global $block_title;
    if ($block_title == '')
    {
        $title = translate("Activité du site");
    }
    else
    {
        $title = $block_title;
    }
    
    themesidebox($title, $who_online);
}
 
/**
 * Bloc Online (Who_Online) 
 * syntaxe : function#online
 * @return [type] [description]
 */
function online() 
{
    global $NPDS_Prefix, $user, $cookie;
       
    $ip = getip();
    $username = $cookie[1];
       
    if (!isset($username)) 
    {
        $username = $ip;
        $guest = 1;
    }
    else
    {
        $guest = 0;
    }

    $past = time()-300;
       
    sql_query("DELETE FROM ".$NPDS_Prefix."session WHERE time < '$past'");
    $result = sql_query("SELECT time FROM ".$NPDS_Prefix."session WHERE username='$username'");
       
    $ctime = time();
    
    if ($row = sql_fetch_row($result))
    {
        sql_query("UPDATE ".$NPDS_Prefix."session SET username='$username', time='$ctime', host_addr='$ip', guest='$guest' WHERE username='$username'");
    }
    else
    {
        sql_query("INSERT INTO ".$NPDS_Prefix."session (username, time, host_addr, guest) VALUES ('$username', '$ctime', '$ip', '$guest')");
    }

    $result = sql_query("SELECT username FROM ".$NPDS_Prefix."session WHERE guest=1");
    $guest_online_num = sql_num_rows($result);
       
    $result = sql_query("SELECT username FROM ".$NPDS_Prefix."session WHERE guest=0");
    $member_online_num = sql_num_rows($result);
       
    $who_online_num = $guest_online_num + $member_online_num;
    
    $who_online = '<p class="text-center">'.translate("Il y a actuellement").' <span class="badge badge-secondary">'.$guest_online_num.'</span> '.translate("visiteur(s) et").' <span class="badge badge-secondary">'.$member_online_num.' </span> '.translate("membre(s) en ligne.").'<br />';
       
    $content = $who_online;
       
    if ($user) 
    {
        $content .= '<br />'.translate("Vous êtes connecté en tant que").' <strong>'.$username.'</strong>.<br />';
        
        $result = Q_select("SELECT uid FROM ".$NPDS_Prefix."users WHERE uname='$username'", 86400);
        $uid = $result[0];
          
        $result2 = sql_query("SELECT to_userid FROM ".$NPDS_Prefix."priv_msgs WHERE to_userid='".$uid['uid']."' AND type_msg='0'");
        $numrow = sql_num_rows($result2);
          
        $content .= translate("Vous avez").' <a href="viewpmsg.php"><span class="badge badge-primary">'.$numrow.'</span></a> '.translate("message(s) personnel(s).").'</p>';
    } 
    else
    {
        $content .= '<br />'.translate("Devenez membre privilégié en cliquant").' <a href="user.php?op=only_newuser">'.translate("ici").'</a></p>';}
       
    global $block_title;
    if ($block_title == '')
    {
        $title = translate("Qui est en ligne ?");
    }
    else
    {
        $title = $block_title;
    }
    
    themesidebox($title, $content);
}
 
/**
 * Bloc Little News-Letter 
 * syntaxe : function#lnlbox
 * @return [type] [description]
 */
function lnlbox() 
{
    global $block_title;
    
    if ($block_title == '')
    {
        $title = translate("La lettre");
    }
    else
    {
        $title = $block_title;
    }
       
    /*
    $arg1 = '
        var formulid = ["lnlblock"]';
    */
       
    $boxstuff = '
    <form id="lnlblock" action="lnl.php" method="get">
        <div class="form-group">
            <select name="op" class=" custom-select form-control">
                <option value="subscribe">'.translate("Abonnement").'</option>
                <option value="unsubscribe">'.translate("Désabonnement").'</option>
            </select>
        </div>
        <div class="form-group">
            <label for="email_block">'.translate("Votre adresse Email").'</label>
            <input type="email" id="email_block" name="email" maxlength="60" class="form-control" />
        </div>
        <p><span class="help-block">'.translate("Recevez par mail les nouveautés du site.").'</span></p>
        <div class="form-group row">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-outline-primary btn-block btn-sm"><i class ="fa fa-check fa-lg"></i>&nbsp;'.translate("Valider").'</button>
            </div>
        </div>
    </form>'
    .adminfoot('', '', '', '0');
    
    themesidebox($title, $boxstuff);
}
 
/**
 * Bloc Search-engine 
 * syntaxe : function#searchbox
 * @return [type] [description]
 */
function searchbox() 
{
    global $block_title;
       
    if ($block_title == '')
    {
        $title = translate("Recherche");
    }
    else
    {
        $title = $block_title;
    }
    
    $content = '
    <form id="searchblock" action="search.php" method="get">
        <input class="form-control" type="text" name="query" />
    </form>';
       
    themesidebox($title, $content);
}
 
/**
 * Bloc principal 
 * syntaxe : function#mainblock
 * @return [type] [description]
 */
function mainblock() 
{
    global $NPDS_Prefix;
       
    $result = sql_query("SELECT title, content FROM ".$NPDS_Prefix."block WHERE id=1");
    list($title, $content) = sql_fetch_row($result);
       
    global $block_title;
    if ($title == '') 
    {
        $title = $block_title;
    }
    
    // must work from php 4 to 7 !..?..
    themesidebox(
        aff_langue($title), 
        aff_langue(
            preg_replace_callback('#<a href=[^>]*(&)[^>]*>#',
                [str::class, 'changetoamp'],
                $content)
        )
    );
}
 
/**
 * Bloc Admin 
 * syntaxe : function#adminblock
 * @return [type] [description]
 */
function adminblock() 
{  
    global $NPDS_Prefix, $admin, $aid, $admingraphic, $adminimg, $admf_ext, $Version_Sub, $Version_Num, $nuke_url;
       
    $bloc_foncts_A = '';

    if ($admin) 
    {
        $Q = sql_fetch_assoc(sql_query("SELECT * FROM ".$NPDS_Prefix."authors WHERE aid='$aid' LIMIT 1"));
        if ($Q['radminsuper'] == 1)
        {
            $R = sql_query("SELECT * FROM ".$NPDS_Prefix."fonctions f WHERE f.finterface =1 AND f.fetat != '0' ORDER BY f.fcategorie");
        }
        else
        {
            $R = sql_query("SELECT * FROM ".$NPDS_Prefix."fonctions f LEFT JOIN droits d ON f.fdroits1 = d.d_fon_fid LEFT JOIN authors a ON d.d_aut_aid =a.aid WHERE f.finterface =1 AND fetat!=0 AND d.d_aut_aid='$aid' AND d.d_droits REGEXP'^1' ORDER BY f.fcategorie");
        }

        while($SAQ = sql_fetch_assoc($R)) 
        {
            $arraylecture = explode('|', $SAQ['fdroits1_descr']);
            $cat[] = $SAQ['fcategorie'];
            $cat_n[] = $SAQ['fcategorie_nom'];
            $fid_ar[] = $SAQ['fid'];

            if($SAQ['fcategorie'] == 9)
            {
                $adminico = $adminimg.$SAQ['ficone'].'.'.$admf_ext;
            }

            if ($SAQ['fcategorie'] == 9 and strstr($SAQ['furlscript'], "op=Extend-Admin-SubModule"))
            {
                if (file_exists('modules/'.$SAQ['fnom'].'/'.$SAQ['fnom'].'.'.$admf_ext))
                { 
                    $adminico = 'modules/'.$SAQ['fnom'].'/'.$SAQ['fnom'].'.'.$admf_ext;
                } 
                else 
                {
                    $adminico = $adminimg.'module.'.$admf_ext;
                }
            }

            if ($SAQ['fcategorie'] == 9) 
            {
                if(preg_match('#messageModal#', $SAQ['furlscript'])) 
                {
                    $furlscript = 'data-toggle="modal" data-target="#bl_messageModal"';
                }

                if(preg_match('#mes_npds_\d#', $SAQ['fnom'])) 
                {
                    if(!in_array(strtolower($aid), $arraylecture, true))
                    {
                        $bloc_foncts_A .= '
                        <a class=" btn btn-outline-primary btn-sm mr-2 my-1 tooltipbyclass" title="'.$SAQ['fretour_h'].'" data-id="'.$SAQ['fid'].'" data-html="true" '.$furlscript.' >
                            <img class="adm_img" src="'.$adminico.'" alt="icon_'.$SAQ['fnom_affich'].'" />
                            <span class="badge badge-danger ml-1">'.$SAQ['fretour'].'</span>
                        </a>';
                    } 
                } 
                else 
                {
                    if(preg_match('#versusModal#', $SAQ['furlscript'])) 
                    {
                        $furlscript = 'data-toggle="modal" data-target="#bl_versusModal"';
                    } 
                    else 
                    {
                        $furlscript = $SAQ['furlscript'];
                    }

                    if(preg_match('#NPDS#', $SAQ['fretour_h'])) 
                    {
                        $SAQ['fretour_h'] = str_replace('NPDS', 'NPDS^', $SAQ['fretour_h']);
                    }

                    $bloc_foncts_A .= '
                    <a class=" btn btn-outline-primary btn-sm mr-2 my-1 tooltipbyclass" title="'.$SAQ['fretour_h'].'" data-id="'.$SAQ['fid'].'" data-html="true" '.$furlscript.' >
                        <img class="adm_img" src="'.$adminico.'" alt="icon_'.$SAQ['fnom_affich'].'" />
                        <span class="badge badge-danger ml-1">'.$SAQ['fretour'].'</span>
                    </a>';
                }
            }
        }
             
        $result = sql_query("SELECT title, content FROM ".$NPDS_Prefix."block WHERE id=2");
        list($title, $content) = sql_fetch_row($result);
        
        global $block_title;
        if ($title == '') 
        {
            $title = $block_title;
        }
        else 
        {
            $title = aff_langue($title);
        }
        
        $content = aff_langue(
                        preg_replace_callback(
                            '#<a href=[^>]*(&)[^>]*>#',
                            [str::class, 'changetoampadm'], 
                            $content
                        )
                    );
       
        // recuperation
        $messagerie_npds = file_get_contents('https://raw.githubusercontent.com/nicolas2/npds-two/master/versus.txt');
        
        $messages_npds = explode("\n", $messagerie_npds);
        array_pop($messages_npds);
        
        // traitement specifique car fonction permanente versus
        $versus_info = explode('|', $messages_npds[0]);
        
        if($versus_info[1] == $Version_Sub and $versus_info[2] == $Version_Num)
        {
            sql_query("UPDATE ".$NPDS_Prefix."fonctions SET fetat='1', fretour='', fretour_h='Version NPDS ".$Version_Sub." ".$Version_Num."', furlscript='' WHERE fid='36'");
        }
        else
        {
            sql_query("UPDATE ".$NPDS_Prefix."fonctions SET fetat='1', fretour='N', furlscript='data-toggle=\"modal\" data-target=\"#versusModal\"', fretour_h='Une nouvelle version NPDS est disponible !<br />".$versus_info[1]." ".$versus_info[2]."<br />Cliquez pour télécharger.' WHERE fid='36'"); 
        }
        
        $mess = array_slice($messages_npds, 1);
        
        // traitement specifique car fonction permanente versus
        $versus_info = explode('|', $messages_npds[0]);

        $content .= '
        <div class="d-flex justify-content-start flex-wrap" id="adm_block">
            '.$bloc_foncts_A.'<a class="btn btn-outline-primary btn-sm mr-2 my-1" title="'.translate("Vider la table chatBox").'" data-toggle="tooltip" href="powerpack.php?op=admin_chatbox_write&amp;chatbox_clearDB=OK" ><img src="assets/images/admin/chat.png" class="adm_img" />&nbsp;<span class="badge badge-danger ml-1">X</span></a>
        </div>
        <div class="mt-3">
            <small class="text-muted"><i class="fas fa-user-cog fa-2x align-middle"></i> '.$aid.'</small>
        </div>
        <div class="modal fade" id="bl_versusModal" tabindex="-1" aria-labelledby="bl_versusModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bl_versusModalLabel"><img class="adm_img mr-2" src="assets/images/admin/message_npds.png" alt="icon_" />'.translate("Version").' NPDS^</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>'.translate("Vous utilisez NPDS^").' '.$Version_Sub.' '.$Version_Num.'</p>
                        <p>'.translate("Une nouvelle version de NPDS^ est disponible !").'</p>
                        <p class="lead mt-3">'.$versus_info[1].' '.$versus_info[2].'</p>
                        <p class="my-3">
                            <a class="mr-3" href="https://github.com/npds/npds_dune/archive/refs/tags/'.$versus_info[2].'.zip" target="_blank" title="" data-toggle="tooltip" data-original-title="Charger maintenant"><i class="fa fa-download fa-2x mr-1"></i>.zip</a>
                            <a class="mx-3" href="https://github.com/npds/npds_dune/archive/refs/tags/'.$versus_info[2].'.tar.gz" target="_blank" title="" data-toggle="tooltip" data-original-title="Charger maintenant"><i class="fa fa-download fa-2x mr-1"></i>.tar.gz</a>
                        </p>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="bl_messageModal" tabindex="-1" aria-labelledby="bl_messageModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id=""><span id="bl_messageModalIcon" class="mr-2"></span><span id="bl_messageModalLabel"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="bl_messageModalContent"></p>
                        <form class="mt-3" id="bl_messageModalForm" action="" method="POST">
                            <input type="hidden" name="id" id="bl_messageModalId" value="0" />
                            <button type="submit" class="btn btn btn-primary btn-sm">'.translate("Confirmation lecture").'</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <span class="small text-muted">Information de npds.org</span><img class="adm_img mr-2" src="assets/images/admin/message_npds.png" alt="icon_" />
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function () {
                $("#bl_messageModal").on("show.bs.modal", function (event) {
                    var button = $(event.relatedTarget); 
                    var id = button.data("id");
                    $("#bl_messageModalId").val(id);
                    $("#bl_messageModalForm").attr("action", "'.$nuke_url.'/npds_api.php?op=alerte_update");
                    $.ajax({
                        url:"'.$nuke_url.'/npds_api.php?op=alerte_api",
                        method: "POST",
                        data:{id:id},
                        dataType:"JSON",
                        success:function(data) {
                            var fnom_affich = JSON.stringify(data["fnom_affich"]),
                                fretour_h = JSON.stringify(data["fretour_h"]),
                                ficone = JSON.stringify(data["ficone"]);
                            $("#bl_messageModalLabel").html(JSON.parse(fretour_h));
                            $("#bl_messageModalContent").html(JSON.parse(fnom_affich));
                            $("#bl_messageModalIcon").html("<img src=\"'.$nuke_url.'/assets/images/admin/"+JSON.parse(ficone)+".png\" />");
                        }
                    });
                });
            });
        </script>';
       
        themesidebox($title, $content);
    }
}
 
/**
 * Bloc ephemerid
 * syntaxe : function#ephemblock
 * @return [type] [description]
 */
function ephemblock() 
{
    global $NPDS_Prefix, $gmt;
       
    $cnt = 0;
    $eday = date("d", time()+((integer)$gmt*3600));
    $emonth =date("m", time()+((integer)$gmt*3600));
    
    $result = sql_query("SELECT yid, content FROM ".$NPDS_Prefix."ephem WHERE did='$eday' AND mid='$emonth' ORDER BY yid ASC");
    $boxstuff = '<div>'.translate("En ce jour...").'</div>';
       
    while (list($yid, $content) = sql_fetch_row($result)) 
    {
        if ($cnt == 1)
        {
            $boxstuff .= "\n<br />\n";
        }

        $boxstuff .= "<b>$yid</b>\n<br />\n";
        $boxstuff .= aff_langue($content);
        $cnt = 1;
    }

    $boxstuff .= "<br />\n";
    
    global $block_title;
    if ($block_title == '')
    {
        $title = translate("Ephémérides");
    }
    else
    {
        $title = $block_title;
    }

    themesidebox($title, $boxstuff);
}
 
/**
 * Bloc Login 
 * syntaxe : function#loginbox
 * @return [type] [description]
 */
function loginbox() 
{
    global $user;
       
    $boxstuff = '';
       
    if (!$user) 
    {
        $boxstuff = '
        <form action="user.php" method="post">
            <div class="form-group">
                <label for="uname">'.translate("Identifiant").'</label>
                <input class="form-control" type="text" name="uname" maxlength="25" />
            </div>
            <div class="form-group">
                <label for="pass">'.translate("Mot de passe").'</label>
                <input class="form-control" type="password" name="pass" maxlength="20" />
            </div>
            <div class="form-group">
                <input type="hidden" name="op" value="login" />
                <button class="btn btn-primary" type="submit">'.translate("Valider").'</button>
            </div>
            <div class="help-block">
                '.translate("Vous n'avez pas encore de compte personnel ? Vous devriez").' <a href="user.php">'.translate("en créer un").'</a>. '.translate("Une fois enregistré").' '.translate("vous aurez certains avantages, comme pouvoir modifier l'aspect du site,").' '.translate("ou poster des commentaires signés...").'
            </div>
        </form>';
          
        global $block_title;
        if ($block_title == '')
        {
            $title = translate("Se connecter");
        }
        else
        {
            $title = $block_title;
        }
        
        themesidebox($title, $boxstuff);
    }
}

/**
 * Bloc membre 
 * syntaxe : function#userblock
 * @return [type] [description]
 */
function userblock() 
{
    global $NPDS_Prefix, $user, $cookie;
       
    if (($user) AND ($cookie[8])) 
    {
        $getblock = Q_select("SELECT ublock FROM ".$NPDS_Prefix."users WHERE uid='$cookie[0]'",86400);
        $ublock = $getblock[0];
        
        global $block_title;
        if ($block_title == '')
        {
            $title = translate("Menu de").' '.$cookie[1];
        }
        else
        {
            $title = $block_title;
        }

        themesidebox($title, $ublock['ublock']);
    }
}

/**
 * Bloc topdownload 
 * syntaxe : function#topdownload
 * @return [type] [description]
 */
function topdownload() 
{
    global $block_title;

    if ($block_title == '')
    {
        $title = translate("Les plus téléchargés");
    }
    else
    {
        $title = $block_title;
    }
        
    $boxstuff = '<ul>';
    $boxstuff .= topdownload_data('short', 'dcounter');
    $boxstuff .= '</ul>';
    
    if ($boxstuff == '<ul></ul>') 
    {
        $boxstuff = '';
    }
    
    themesidebox($title, $boxstuff);
}

/**
 * Bloc lastdownload 
 * syntaxe : function#lastdownload
 * @return [type] [description]
 */
function lastdownload() 
{
    global $block_title;
    
    if ($block_title == '')
    {
        $title = translate("Fichiers les + récents");
    }
    else
    {
        $title = $block_title;
    }
    
    $boxstuff = '<ul>';
    $boxstuff .= topdownload_data('short', 'ddate');
    $boxstuff .= '</ul>';
    
    if ($boxstuff == '<ul></ul>') 
    {
        $boxstuff = '';
    }
    
    themesidebox($title, $boxstuff);
}

/**
 * Bloc Anciennes News 
 * syntaxe : function#oldNews
 *           params#$storynum, lecture (affiche le NB de lecture) - facultatif
 * @param  [type] $storynum [description]
 * @param  string $typ_aff  [description]
 * @return [type]           [description]
 */
function oldNews($storynum, $typ_aff='') 
{
    global $locale, $oldnum, $storyhome, $categories, $cat, $user, $cookie;
       
    $boxstuff = '<ul class="list-group">';
    if (isset($cookie[3])) 
    {
        $storynum = $cookie[3];
    } 
    else 
    {
        $storynum = $storyhome;
    }

    if (($categories == 1) and ($cat != '')) 
    {
        if ($user) 
        { 
            $sel = "WHERE catid='$cat'"; 
        }
        else 
        { 
            $sel = "WHERE catid='$cat' AND ihome=0"; 
        }
    } 
    else 
    {
        if ($user) 
        { 
            $sel = ''; 
        }
        else 
        { 
            $sel = "WHERE ihome=0"; 
        }
    }
       
    $vari = 0;
    $xtab = news_aff('old_news', $sel, $storynum, $oldnum);
    $story_limit = 0; 
    $time2 = 0; 
    $a = 0;
       
    while (($story_limit < $oldnum) and ($story_limit < sizeof($xtab))) 
    {
        list($sid, $title, $time, $comments, $counter) = $xtab[$story_limit];
          
        $story_limit++;
          
        setlocale(LC_TIME, aff_langue($locale));
          
        preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $time, $datetime2);
          
        $datetime2 = strftime("".translate("datestring2")."", @mktime($datetime2[4], $datetime2[5], $datetime2[6], $datetime2[2], $datetime2[3], $datetime2[1]));
          
        if (cur_charset != "utf-8") 
        {
            $datetime2 = ucfirst($datetime2);
        }

        if ($typ_aff == 'lecture') 
        {
            $comments = '<span class="badge badge-pill badge-secondary" title="'.translate("Lu").'" data-toggle="tooltip">'.$counter.'</span>';
        } 
        else 
        {
            $comments = '';
        }

        if ($time2 == $datetime2)
        {
            $boxstuff .= '
            <li class="list-group-item list-group-item-action d-inline-flex justify-content-between align-items-center"><a class="n-ellipses" href="article.php?sid='.$sid.'">'.aff_langue($title).'</a>'.$comments.'</li>';
        } 
        else 
        {
            if ($a == 0) 
            {
                $boxstuff .= "<strong>$datetime2</strong><br /><li><a href=\"article.php?sid=$sid\">".aff_langue($title)."</a> $comments</li>\n";
                $time2 = $datetime2;
                $a = 1;
            } 
            else 
            {
                $boxstuff .= "<br /><strong>$datetime2</strong><br /><li><a href=\"article.php?sid=$sid\">".aff_langue($title)."</a> $comments </li>\n";
                $time2 = $datetime2;
            }
        }

        $vari++;
        
        if ($vari == $oldnum) 
        {
            if (isset($cookie[3])) 
            {
                $storynum = $cookie[3];
            } 
            else 
            {
                $storynum = $storyhome;
            }

            $min = $oldnum + $storynum;
            $boxstuff .= "<li class=\"text-center mt-3\" ><a href=\"search.php?min=$min&amp;type=stories&amp;category=$cat\"><strong>".translate("Articles plus anciens")."</strong></a></li>\n";
        }
    }

    $boxstuff .= '</ul>';
    
    if ($boxstuff == '<ul></ul>') 
    {
        $boxstuff = '';
    }
    
    global $block_title;
    if ($block_title == '')
    {
        $boxTitle = translate("Anciens articles");
    }
    else
    {
        $boxTitle = $block_title;
    }
    
    themesidebox($boxTitle, $boxstuff);
}

/**
 * Bloc BigStory
 * syntaxe : function#bigstory
 * @return [type] [description]
 */
function bigstory() 
{
    global $cookie;
       
    $today = getdate();
    $day = $today['mday'];
       
    if ($day < 10)
    {
        $day = "0$day";
    }

    $month = $today['mon'];
       
    if ($month < 10)
    {
        $month = "0$month";
    }
       
    $year = $today['year'];
    $tdate = "$year-$month-$day";
    $xtab = news_aff("big_story","WHERE (time LIKE '%$tdate%')", 0, 1);
       
    if (sizeof($xtab)) 
    {
        list($fsid, $ftitle) = $xtab[0];
    } 
    else 
    {
        $fsid = ''; 
        $ftitle = '';
    }
       
    if ((!$fsid) AND (!$ftitle)) 
    {
        $content = translate("Il n'y a pas encore d'article du jour.");
    } 
    else 
    {
        $content = translate("L'article le plus consulté aujourd'hui est :")."<br /><br />";
        $content .= "<a href=\"article.php?sid=$fsid\">".aff_langue($ftitle)."</a>";
    }
       
    global $block_title;
    if ($block_title == '')
    {
        $boxtitle = translate("Article du Jour");
    }
    else
    {
        $boxtitle = $block_title;
    }
    
    themesidebox($boxtitle, $content);
}
 
/**
 * Bloc de gestion des catégories
 * syntaxe : function#category
 * @return [type] [description]
 */
function category() 
{
    global $NPDS_Prefix, $cat, $language;
       
    $result = sql_query("SELECT catid, title FROM ".$NPDS_Prefix."stories_cat ORDER BY title");
    $numrows = sql_num_rows($result);
       
    if ($numrows == 0) 
    {
        return;
    } 
    else 
    {
        $boxstuff = '<ul>';
        while (list($catid, $title) = sql_fetch_row($result)) 
        {
            $result2 = sql_query("SELECT sid FROM ".$NPDS_Prefix."stories WHERE catid='$catid' LIMIT 0,1");
            $numrows = sql_num_rows($result2);
             
            if ($numrows > 0) 
            {
                $res = sql_query("SELECT time FROM ".$NPDS_Prefix."stories WHERE catid='$catid' ORDER BY sid DESC LIMIT 0,1");
                list($time) = sql_fetch_row($res);
                
                if ($cat == $catid)
                {
                    $boxstuff .= '<li><strong>'.aff_langue($title).'</strong></li>';
                }
                else 
                {
                    $boxstuff .= '<li class="list-group-item list-group-item-action hyphenate"><a href="index.php?op=newcategory&amp;catid='.$catid.'" data-html="true" data-toggle="tooltip" data-placement="right" title="'.translate("Dernière contribution").' <br />'.formatTimestamp($time).' ">'.aff_langue($title).'</a></li>';
                }
            }
        }
        
        $boxstuff .= '</ul>';
          
        global $block_title;
        if ($block_title == '')
        {
            $title = translate("Catégories");
        }
        else
        {
            $title = $block_title;
        }

        themesidebox($title, $boxstuff);
    }
}

/**
 * Bloc HeadLines
 * syntaxe : function#headlines
 *           params#ID_du_canal
 * @param  string  $hid   [description]
 * @param  boolean $block [description]
 * @return [type]         [description]
 */
function headlines($hid='', $block=true) 
{
    global $NPDS_Prefix, $Version_Num, $Version_Id, $rss_host_verif, $long_chain;

    if (file_exists("proxy.conf.php"))
    {
        include("proxy.conf.php");
    }
       
    if ($hid == '')
    {
        $result = sql_query("SELECT sitename, url, headlinesurl, hid FROM ".$NPDS_Prefix."headlines WHERE status=1");
    }
    else
    {
        $result = sql_query("SELECT sitename, url, headlinesurl, hid FROM ".$NPDS_Prefix."headlines WHERE hid='$hid' AND status=1");
    }

    while (list($sitename, $url, $headlinesurl, $hid) = sql_fetch_row($result)) 
    {
        $boxtitle = $sitename;
        
        $cache_file = 'storage/cache/'.preg_replace('[^a-z0-9]', '', strtolower($sitename)).'_'.$hid.'.cache';
        
        $cache_time = 1200;//3600 origine
        $items = 0;
        $max_items = 6;
        $rss_timeout = 15;
        $rss_font = '<span class="small">';

        if ( (!(file_exists($cache_file))) 
            or (filemtime($cache_file)<(time()-$cache_time)) 
            or (!(filesize($cache_file))) ) 
        {
            $rss = parse_url($url);
            
            if ($rss_host_verif == true) 
            {
                $verif = fsockopen($rss['host'], 80, $errno, $errstr, $rss_timeout);
                
                if ($verif) 
                {
                    fclose($verif);
                    $verif = true;
                }
            } 
            else 
            {
                $verif = true;
            }

            if (!$verif) 
            {
                $cache_file_sec = $cache_file.".security";
                
                if (file_exists($cache_file)) 
                {
                    $ibid = rename($cache_file, $cache_file_sec);
                }

                themesidebox($boxtitle, "Security Error");
                return;
            } 
            else 
            {
                /*
                if (isset($proxy_url[$hid])) 
                {
                    $fpread=fsockopen($proxy_url[$hid], $proxy_port[$hid], $errno, $errstr, $rss_timeout);
                    fputs($fpread, "GET $headlinesurl/ HTTP/1.0\n\n");
                } 
                else 
                {
                    $fpread = fopen($headlinesurl, 'r');
                }
                */
                if (!$long_chain) 
                {
                    $long_chain = 15;
                }


                //if ($fpread) 
                //{
                    $fpwrite = fopen($cache_file, 'w');
                    if ($fpwrite) 
                    {
                        fputs($fpwrite, "<ul>\n");
                        /*while (!feof($fpread)) 
                        {
                            $buffer = ltrim(Chop(fgets($fpread, 512)));

                            if (($buffer == "<item>") and ($items < $max_items)) 
                            {
                                $title = ltrim(Chop(fgets($fpread, 256)));
                                $link = ltrim(Chop(fgets($fpread, 256)));
                                $title = str_replace( "<title>", "", $title );
                                $title = str_replace( "</title>", "", $title );
                                $link = str_replace( "<link>", "", $link );
                                $link = str_replace( "</link>", "", $link );

                                if (function_exists("mb_detect_encoding")) 
                                {
                                    $encoding = mb_detect_encoding($title);
                                } 
                                else 
                                {
                                    $encoding = "UTF-8";
                                }

                                $title = $look_title=iconv($encoding, cur_charset."//TRANSLIT",  $title);
                                
                                if ($block) 
                                {
                                    if (strlen($look_title) > $long_chain) 
                                    {
                                        $title = (substr($look_title, 0, $long_chain))." ...";
                                    }
                                }

                                fputs($fpwrite, "<li><a href=\"$link\" alt=\"$look_title\" title=\"$look_title\" target=\"_blank\">$title</a></li>\n");
                                $items++;
                           }
                        }
                        */

                        // this will not work with PHP < 5 mais si quelqu'un veut coder pour inf à 5 welcome ! à peaufiner ...
                        $flux = simplexml_load_file($headlinesurl, 'SimpleXMLElement',  LIBXML_NOCDATA);
                        $namespaces = $flux->getNamespaces(true); // get namespaces
                        $ic = '';
                        
                        //ATOM//
                        if($flux->entry) 
                        {
                            $j = 0;
                            $cont = '';
                            foreach ($flux->entry as $entry) 
                            {
                                if($entry->content) 
                                {
                                    $cont = (string) $entry->content;
                                }
                                fputs($fpwrite, '<li><a href="'.(string)$entry->link['href'].'" target="_blank" >'.(string) $entry->title.'</a><br />'.$cont.'</li>');
                                
                                if($j == $max_items) 
                                {
                                    break;
                                }
                               
                                $j++;
                            }
                        }
                       
                        if($flux->{'item'}) 
                        {
                            $j = 0;
                            $cont = '';
                            foreach ($flux->item as $item) 
                            {
                                if($item->description) 
                                {
                                    $cont = (string) $item->description;
                                }
                                 
                                fputs($fpwrite, '<li><a href="'.(string)$item->link['href'].'"  target="_blank" >'.(string) $item->title.'</a><br /></li>');
                                
                                if($j == $max_items) 
                                {
                                    break;
                                }
                                $j++;
                            }
                        }

                        //RSS
                        if($flux->{'channel'}) 
                        {
                            $j = 0;
                            $cont = '';
                            foreach ($flux->channel->item as $item) 
                            {
                                if($item->description) 
                                {
                                    $cont = (string) $item->description;
                                }

                                fputs($fpwrite, '<li><a href="'.(string)$item->link.'"  target="_blank" >'.(string) $item->title.'</a><br />'.$cont.'</li>');
                                
                                if($j == $max_items) 
                                {
                                    break;
                                }
                                $j++;
                            }
                        }
                       
                        $j = 0;
                        if($flux->image)
                        { 
                            $ico = '<img class="img-fluid" src="'.$flux->image->url.'" />&nbsp;';
                        } 

                        foreach ($flux->item as $item) 
                        {
                            fputs($fpwrite, '<li>'.$ico.'<a href="'.(string) $item->link.'" target="_blank" >'.(string) $item->title.'</a></li>');
                            
                            if($j == $max_items) 
                            {
                                break;
                            }
                            $j++;
                        }

                        fputs($fpwrite, "\n".'</ul>');
                        fclose($fpwrite);
                    }
                    //fclose($fpread);
                //}
            }
        }

        if (file_exists($cache_file)) 
        {
            ob_start();
                $ibid = readfile($cache_file);
                $boxstuff = $rss_font.ob_get_contents().'</span>';
            ob_end_clean();
        }

        $boxstuff .= '
        <div class="text-right"><a href="'.$url.'" target="_blank">'.translate("Lire la suite...").'</a></div>';
          
        if ($block) 
        {
            themesidebox($boxtitle, $boxstuff);
            $boxstuff = '';
        } 
        else
        {
            return $boxstuff;
        }
    }
}
 
/**
 * Bloc langue 
 * syntaxe : function#bloc_langue
 * @return [type] [description]
 */
function bloc_langue() 
{
    global $block_title;
    
    if ($block_title == '')
    {
        $title = translate("Choisir une langue");
    }
    else
    {
        $title = $block_title;
    }
    
    themesidebox($title, aff_local_langue('' , "index.php", "choice_user_language"));
}

/**
 * Bloc des Rubriques 
 * syntaxe : function#bloc_rubrique
 * @return [type] [description]
 */
function bloc_rubrique() 
{
    global $NPDS_Prefix, $language, $user;
       
    $result = sql_query("SELECT rubid, rubname FROM ".$NPDS_Prefix."rubriques WHERE enligne='1' AND rubname<>'divers' ORDER BY ordre");
    
    $boxstuff = '<ul>';
    while (list($rubid, $rubname) = sql_fetch_row($result)) 
    {
        $title = aff_langue($rubname);
        $result2 = sql_query("SELECT secid, secname, userlevel FROM ".$NPDS_Prefix."sections WHERE rubid='$rubid' ORDER BY ordre");
        
        $boxstuff .= '<li><strong>'.$title.'</strong></li>';
        
        while (list($secid, $secname, $userlevel) = sql_fetch_row($result2)) 
        {
            $query3 = "SELECT artid FROM ".$NPDS_Prefix."seccont WHERE secid='$secid'";
            $result3 = sql_query($query3);
            $nb_article = sql_num_rows($result3);
            
            if ($nb_article > 0) 
            {
                $boxstuff .= '<ul>';
                $tmp_auto = explode(',', $userlevel);
                
                foreach($tmp_auto as $userlevel) 
                {
                    $okprintLV1 = autorisation($userlevel);
                    
                    if ($okprintLV1) 
                    {
                        break;
                    }
                }

                if ($okprintLV1) 
                {
                    $sec = aff_langue($secname);
                    $boxstuff .= '<li><a href="sections.php?op=listarticles&amp;secid='.$secid.'">'.$sec.'</a></li>';
                }

                $boxstuff .= '</ul>';
            }
        }
    }
    $boxstuff .= '</ul>';
    
    global $block_title;
    if ($block_title == '')
    {
        $title = translate("Rubriques");
    }
    else
    {  
        $title = $block_title;
    }

    themesidebox($title, $boxstuff);
}

/**
 * Bloc du WorkSpace
 * syntaxe : function#bloc_espace_groupe
 *           params#ID_du_groupe, Aff_img_groupe(0 ou 1) 
 * Si le bloc n'a pas de titre, Le nom du groupe sera utilisé
 * @param  [type] $gr   [description]
 * @param  [type] $i_gr [description]
 * @return [type]       [description]
 */
function bloc_espace_groupe($gr, $i_gr) 
{
    global $NPDS_Prefix, $block_title;
       
    if ($block_title == '') 
    {
        $rsql = sql_fetch_assoc(sql_query("SELECT groupe_name FROM ".$NPDS_Prefix."groupes WHERE groupe_id='$gr'"));
        
        $title = $rsql['groupe_name'];
    } 
    else
    {
        $title = $block_title;
    }

    themesidebox($title, fab_espace_groupe($gr, "0", $i_gr));
}
