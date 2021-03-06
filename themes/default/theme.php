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
use npds\language\metalang;
use npds\language\language;
use npds\time\time;
use npds\views\theme;
use npds\auth\auth;
use npds\utility\spam;


/**
 * [local_var description]
 * @param  [type] $Xcontent [description]
 * @return [type]           [description]
 */
function local_var($Xcontent) 
{
   	if (strstr($Xcontent, "!var!")) 
   	{
      	$deb = strpos($Xcontent, "!var!", 0)+5;
      	$fin = strpos($Xcontent, ' ', $deb);
      
      	if ($fin) 
      	{
         	$H_var = substr($Xcontent, $deb, $fin-$deb);
      	}
      	else 
      	{
         	$H_var = substr($Xcontent, $deb);
      	}
      
     	return $H_var;
   	}
}

/**
 * [themeindex description]
 * @param  [type] $aid        [description]
 * @param  [type] $informant  [description]
 * @param  [type] $time       [description]
 * @param  [type] $title      [description]
 * @param  [type] $counter    [description]
 * @param  [type] $topic      [description]
 * @param  [type] $thetext    [description]
 * @param  [type] $notes      [description]
 * @param  [type] $morelink   [description]
 * @param  [type] $topicname  [description]
 * @param  [type] $topicimage [description]
 * @param  [type] $topictext  [description]
 * @param  [type] $id         [description]
 * @return [type]             [description]
 */
function themeindex ($aid, $informant, $time, $title, $counter, $topic, $thetext, $notes, $morelink, $topicname, $topicimage, $topictext, $id) 
{
   	global $tipath, $theme, $nuke_url;
   
   	$inclusion = false;
   
   	if (file_exists("themes/".$theme."/views/index-news.html")) 
   	{
   		$inclusion = "themes/".$theme."/views/index-news.html";
   	}
   	elseif (file_exists("themes/default/views/index-news.html")) 
   	{
   		$inclusion = "themes/default/views/index-news.html";
   	}
   	else 
   	{
      	echo 'index-news.html manquant / not find !<br />';
      	die();
   	}

   	$H_var = local_var($thetext);
   
   	if ($H_var != '') 
   	{
      	${$H_var} = true;
      	$thetext = str_replace("!var!$H_var", "", $thetext);
   	}

   	if ($notes != '') 
   	{
   		$notes = '<div class="note">'.translate("Note").' : '.$notes.'</div>';
   	}
      	
    ob_start();
      	include($inclusion);
      	$Xcontent = ob_get_contents();
   	ob_end_clean();

   	$lire_la_suite = '';
   
   	if ($morelink[0]) 
   	{
   		$lire_la_suite = $morelink[1].' '.$morelink[0].' | ';
   	}
   
   	$commentaire = '';
   
   	if ($morelink[2])
   	{
      	$commentaire = $morelink[2].' '.$morelink[3].' | ';
   	}
   	else
   	{
      	$commentaire = $morelink[3].' | ';
   	}
   
   	$categorie = '';
   
   	if ($morelink[6]) 
   	{
   		$categorie = ' : '.$morelink[6];
   	}
   
   	$morel = $lire_la_suite.$commentaire.$morelink[4].' '.$morelink[5].$categorie;

   	if (!$imgtmp = theme::theme_image('topics/'.$topicimage)) 
   	{
   		$imgtmp = $tipath.$topicimage;
   	}
   
   	$timage = $imgtmp;

   	$npds_METALANG_words = array(
   		"'!N_publicateur!'i"    => $aid,
   		"'!N_emetteur!'i"       => userpopover($informant, 40).'<a href="user.php?op=userinfo&amp;uname='.$informant.'">'.$informant.'</a>',

   		"'!N_date!'i"           => time::formatTimestamp($time),
   		"'!N_date_y!'i"         => substr($time, 0, 4),
   		"'!N_date_m!'i"         => strftime("%B", mktime(0, 0, 0, substr($time, 5, 2), 1, 2000)),
   		"'!N_date_d!'i"         => substr($time, 8, 2),
   		"'!N_date_h!'i"         => substr($time, 11),
   		"'!N_print!'i"          => $morelink[4],
   		"'!N_friend!'i"         => $morelink[5],

   		"'!N_nb_carac!'i"       => $morelink[0],
   		"'!N_read_more!'i"      => $morelink[1],
   		"'!N_nb_comment!'i"     => $morelink[2],
   		"'!N_link_comment!'i"   => $morelink[3],
   		"'!N_categorie!'i"      => $morelink[6],

   		"'!N_titre!'i"          => $title,
   		"'!N_texte!'i"          => $thetext,
   		"'!N_id!'i"             => $id,
   		"'!N_sujet!'i"          => '<a href="search.php?query=&amp;topic='.$topic.'"><img class="img-fluid" src="'.$timage.'" alt="'.translate("Rechercher dans").'&nbsp;'.$topictext.'" /></a>',
   		"'!N_note!'i"           => $notes,
   		"'!N_nb_lecture!'i"     => $counter,
   		"'!N_suite!'i"          => $morel
   	);
   
   	echo metalang::meta_lang(language::aff_langue(preg_replace(array_keys($npds_METALANG_words),array_values($npds_METALANG_words), $Xcontent)));
}

/**
 * [themearticle description]
 * @param  [type] $aid          [description]
 * @param  [type] $informant    [description]
 * @param  [type] $time         [description]
 * @param  [type] $title        [description]
 * @param  [type] $thetext      [description]
 * @param  [type] $topic        [description]
 * @param  [type] $topicname    [description]
 * @param  [type] $topicimage   [description]
 * @param  [type] $topictext    [description]
 * @param  [type] $id           [description]
 * @param  [type] $previous_sid [description]
 * @param  [type] $next_sid     [description]
 * @param  [type] $archive      [description]
 * @return [type]               [description]
 */
function themearticle ($aid, $informant, $time, $title, $thetext, $topic, $topicname, $topicimage, $topictext, $id, $previous_sid, $next_sid, $archive) 
{
   	global $tipath, $theme, $nuke_url, $counter;
   	global $boxtitle, $boxstuff, $short_user,$user;
   
   	$inclusion = false;
   
   	if (file_exists("themes/".$theme."/views/detail-news.html")) 
   	{
   		$inclusion = "themes/".$theme."/views/detail-news.html";
   	}
   	elseif (file_exists("themes/default/views/detail-news.html")) 
   	{
   		$inclusion = "themes/default/views/detail-news.html";
   	}
   	else 
   	{
      	echo 'detail-news.html manquant / not find !<br />';
      	die();
   	}

   	$H_var = local_var($thetext);
   
   	if ($H_var != '') 
   	{
      	${$H_var} = true;
      	$thetext = str_replace("!var!$H_var", '', $thetext);
   	}

   	ob_start();
   		include($inclusion);
   		$Xcontent = ob_get_contents();
   	ob_end_clean();
   
   	if ($previous_sid)
   	{
      	$prevArt = '<a href="article.php?sid='.$previous_sid.'&amp;archive='.$archive.'" ><i class="fa fa-chevron-left fa-lg mr-2" title="'.translate("Pr??c??dent").'" data-toggle="tooltip"></i><span class="d-none d-sm-inline">'.translate("Pr??c??dent").'</span></a>';
   	}
   	else 
   	{
   		$prevArt = '';
   	}
   
   	if ($next_sid) 
   	{
   		$nextArt = '<a href="article.php?sid='.$next_sid.'&amp;archive='.$archive.'" ><span class="d-none d-sm-inline">'.translate("Suivant").'</span><i class="fa fa-chevron-right fa-lg ml-2" title="'.translate("Suivant").'" data-toggle="tooltip"></i></a>';
   	}
   	else 
   	{
   		$nextArt = '';
   	}

   	$printP = '<a href="print.php?sid='.$id.'" title="'.translate("Page sp??ciale pour impression").'" data-toggle="tooltip"><i class="fa fa-2x fa-print"></i></a>';
   	$sendF = '<a href="friend.php?op=FriendSend&amp;sid='.$id.'" title="'.translate("Envoyer cet article ?? un ami").'" data-toggle="tooltip"><i class="fa fa-2x fa-at"></i></a>';

   	if (!$imgtmp = theme::theme_image('topics/'.$topicimage)) 
   	{ 
   		$imgtmp = $tipath.$topicimage;
   	}
   
   	$timage = $imgtmp;

   	$npds_METALANG_words = array(
   		"'!N_publicateur!'i"       => $aid,
   		"'!N_emetteur!'i"          => userpopover($informant, 40).'<a href="user.php?op=userinfo&amp;uname='.$informant.'"><span class="">'.$informant.'</span></a>',
   		"'!N_date!'i"              => time::formatTimestamp($time),
   		"'!N_date_y!'i"            => substr($time, 0, 4),
   		"'!N_date_m!'i"            => strftime("%B", mktime(0, 0, 0, substr($time, 5, 2), 1, 2000)),
   		"'!N_date_d!'i"            => substr($time, 8, 2),
   		"'!N_date_h!'i"            => substr($time, 11),
   		"'!N_print!'i"             => $printP,
   		"'!N_friend!'i"            => $sendF,
   		"'!N_boxrel_title!'i"      => $boxtitle,
   		"'!N_boxrel_stuff!'i"      => $boxstuff,
   		"'!N_titre!'i"             => $title,
   		"'!N_id!'i"                => $id,
   		"'!N_previous_article!'i"  => $prevArt,
   		"'!N_next_article!'i"      => $nextArt,
   		"'!N_sujet!'i"             =>'<a href="search.php?query=&amp;topic='.$topic.'"><img class="img-fluid" src="'.$timage.'" alt="'.translate("Rechercher dans").'&nbsp;'.$topictext.'" /></a>',
   		"'!N_texte!'i"             => $thetext,
   		"'!N_nb_lecture!'i"        => $counter
   	);
   
   	echo metalang::meta_lang(language::aff_langue(preg_replace(array_keys($npds_METALANG_words), array_values($npds_METALANG_words), $Xcontent)));
}

/**
 * [themesidebox description]
 * @param  [type] $title   [description]
 * @param  [type] $content [description]
 * @return [type]          [description]
 */
function themesidebox($title, $content) 
{
   	global $theme, $B_class_title, $B_class_content, $bloc_side, $htvar;
  
   	$inclusion = false;
   
   	if (file_exists("themes/".$theme."/views/bloc-right.html") and ($bloc_side == "RIGHT")) 
   	{
   		$inclusion = 'themes/'.$theme.'/views/bloc-right.html';
   	}
   
   	if (file_exists("themes/".$theme."/views/bloc-left.html") and ($bloc_side == "LEFT")) 
   	{
   		$inclusion = 'themes/'.$theme.'/views/bloc-left.html';
   	}
   
   	if (!$inclusion) 
   	{
      	if (file_exists("themes/".$theme."/views/bloc.html")) 
      	{
      		$inclusion = 'themes/'.$theme.'/views/bloc.html';
      	}
      	elseif (file_exists("themes/default/views/footer.html")) 
      	{
      		$inclusion = 'themes/default/views/bloc.html';
      	}
      	else 
      	{
         	echo 'bloc.html manquant / not find !<br />';
         	die();
      	}
   	}

   	ob_start();
   		include($inclusion);
   		$Xcontent = ob_get_contents();
   	ob_end_clean();
   
   	if ($title == 'no-title') 
   	{
      	$Xcontent = str_replace('<div class="LB_title">!B_title!</div>', '', $Xcontent);
      	$title = '';
   	}

   	$npds_METALANG_words = array(
   		"'!B_title!'i"         => $title,
   		"'!B_class_title!'i"   => $B_class_title,
   		"'!B_class_content!'i" => $B_class_content,
   		"'!B_content!'i"       => $content
   	);
   
   	echo $htvar;// modif ji fant??me block
   	echo metalang::meta_lang(preg_replace(array_keys($npds_METALANG_words), array_values($npds_METALANG_words), $Xcontent));
   	echo '
            </div>';// modif ji fant??me block
}

/**
 * [themedito description]
 * @param  [type] $content [description]
 * @return [type]          [description]
 */
function themedito($content) 
{
   	global $theme;
   	$inclusion = false;
   
   	if (file_exists("themes/".$theme."/views/editorial.html")) 
   	{
   		$inclusion = "themes/".$theme."/views/editorial.html";
   	}
   
   	if ($inclusion) 
   	{
      	ob_start();
      		include($inclusion);
      		$Xcontent = ob_get_contents();
      	ob_end_clean();
      	
      	$npds_METALANG_words = array(
      		"'!editorial_content!'i" =>  $content
      	);
     	
     	echo metalang::meta_lang(language::aff_langue(preg_replace(array_keys($npds_METALANG_words), array_values($npds_METALANG_words), $Xcontent)));
   	}

   	return $inclusion;
}

/**
 * [userpopover description]
 * @param  [type] $who [description]
 * @param  [type] $dim [description]
 * @return [type]      [description]
 */
function userpopover($who,$dim) 
{
   	global $short_user, $user, $NPDS_Prefix;
   
   	$result = sql_query("SELECT uname FROM ".$NPDS_Prefix."users WHERE uname ='$who'");

   	if (sql_num_rows($result)) 
   	{
      	$temp_user = auth::get_userdata($who);
      	$socialnetworks = array(); 
      	$posterdata_extend = array();
      	$res_id = array(); 
      	$my_rs = '';
      
      	if (!$short_user) 
      	{
         	if($temp_user['uid'] != 1) 
         	{
            	$posterdata_extend = auth::get_userdata_extend_from_id($temp_user['uid']);
            
            	include('modules/reseaux-sociaux/reseaux-sociaux.conf.php');
            	include('modules/geoloc/config/geoloc.php');
            
            	if($user or auth::autorisation(-127)) 
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
                           			$my_rs .= '<a class="mr-2 " href="';
                           			
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
               		}
           		}
         	}
      	}

   		settype($ch_lat, 'string');
   		$useroutils = '';
   
   		if($user or auth::autorisation(-127)) 
   		{
      		if ($temp_user['uid'] != 1 and $temp_user['uid'] != '') 
      		{
         		$useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="user.php?op=userinfo&amp;uname='.$temp_user['uname'].'" target="_blank" title="'.translate("Profil").'" ><i class="fa fa-2x fa-user align-middle fa-fw"></i><span class="ml-2 d-none d-md-inline">'.translate("Profil").'</span></a>';
      		}
      
      		if ($temp_user['uid'] != 1 and $temp_user['uid'] != '')
      		{
         		$useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="two_api.php?op=instant_message&amp;to_userid='.urlencode($temp_user['uname']).'" title="'.translate("Envoyer un message interne").'" ><i class="far fa-2x fa-envelope align-middle fa-fw"></i><span class="ml-2 d-none d-md-inline">'.translate("Message").'</span></a>';
      		}
      
      		if ($temp_user['femail'] != '')
      		{
         		$useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="mailto:'.spam::anti_spam($temp_user['femail'],1).'" target="_blank" title="'.translate("Email").'" ><i class="fa fa-at fa-2x align-middle fa-fw"></i><span class="ml-2 d-none d-md-inline">'.translate("Email").'</span></a>';
      		}
      
      		if ($temp_user['uid'] != 1 and array_key_exists($ch_lat, $posterdata_extend)) 
      		{
         		if ($posterdata_extend[$ch_lat] != '')
         		{
            		$useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="modules.php?ModPath=geoloc&amp;ModStart=geoloc&op=u'.$temp_user['uid'].'" title="'.translate("Localisation").'" ><i class="fas fa-map-marker-alt fa-2x align-middle fa-fw">&nbsp;</i><span class="ml-2 d-none d-md-inline">'.translate("Localisation").'</span></a>';
         		}
     	 	}
   		}

   		if ($temp_user['url'] != '')
   		{
      		$useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="'.$temp_user['url'].'" target="_blank" title="'.translate("Visiter ce site web").'"><i class="fas fa-external-link-alt fa-2x align-middle fa-fw"></i><span class="ml-2 d-none d-md-inline">'.translate("Visiter ce site web").'</span></a>';
   		}
   
   		if ($temp_user['mns'])
   		{
       		$useroutils .= '<a class="list-group-item text-primary text-center text-md-left" href="minisite.php?op='.$temp_user['uname'].'" target="_blank" target="_blank" title="'.translate("Visitez le minisite").'" ><i class="fa fa-2x fa-desktop align-middle fa-fw"></i><span class="ml-2 d-none d-md-inline">'.translate("Visitez le minisite").'</span></a>';
   		}

   		if (stristr($temp_user['user_avatar'],'users_private')) 
   		{
      		$imgtmp = $temp_user['user_avatar'];
   		}
   		else
   		{
      		if ($ibid = theme::theme_image('forum/avatar/'.$temp_user['user_avatar'])) 
      		{
      			$imgtmp = $ibid;
      		} 
      		else 
      		{
      			$imgtmp = 'assets/images/forum/avatar/'.$temp_user['user_avatar'];
      		}
   		}
   
   		$userpop = '<a tabindex="0" data-toggle="popover" data-trigger="focus" data-html="true" data-title="'.$temp_user['uname'].'" data-content=\'<div class="list-group mb-3 text-center">'.$useroutils.'</div><div class="mx-auto text-center" style="max-width:170px;">'.$my_rs.'</div>\'></i><img data-html="true" class="btn-outline-primary img-thumbnail img-fluid n-ava-'.$dim.' mr-2" src="'.$imgtmp.'" alt="'.$temp_user['uname'].'" /></a>';

   		return $userpop;
   	}
}
