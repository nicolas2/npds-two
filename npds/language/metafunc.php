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
namespace npds\language;

use npds\language\metalang;
use npds\language\language;
use npds\views\theme;
use npds\utility\spam;
use npds\cache\cache;
use npds\mailler\mailler;
use npds\users\online;
use npds\blocks\block;
use npds\news\news;
use npds\edito\edito;
use npds\groupes\groupe;
use npds\forum\forum;
use npds\forum\forumbox;
use npds\utility\str;


/*
 * metafunc 
 */
class metafunc {


    /**
     * [__construct description]
     * @param Container $container [description]
     */
    public function __construct()
    {
    }

	/**
	 * [MM_img description]
	 * @param [type] $ibid [description]
	 */
	public function MM_img($ibid) 
	{
	    $ibid = metalang::arg_filter($ibid);
	    $ibidX = theme::theme_image($ibid);
	    
	    if ($ibidX)
	    {
	        $ret = "<img src=\"$ibidX\" border=\"0\" alt=\"\" />";
	    }
	    else 
	    {
	        if (@file_exists("assets/$ibid"))
	        {
	            $ret = "<img src=\"assets/images/$ibid\" border=\"0\" alt=\"\" />";
	        }
	        else
	        {
	            $ret = false;
	        }
	    }

	    return $ret;
	}

	/**
	 * [MM_Scalcul description]
	 * @param [type] $opex     [description]
	 * @param [type] $premier  [description]
	 * @param [type] $deuxieme [description]
	 */
	public function MM_Scalcul($opex, $premier, $deuxieme) 
	{
		if ($opex == "+") 
		{
			$tmp = $premier+$deuxieme;
		}

		if ($opex == "-") 
		{
			$tmp = $premier-$deuxieme;
		}

		if ($opex == "*") 
		{
			$tmp = $premier*$deuxieme;
		}

		if ($opex == "/") 
		{      
			if ($deuxieme == 0) 
			{         
				$tmp = "Division by zero !";      
			} 
			else 
			{        
				$tmp = $premier/$deuxieme;     
			}  
		} 

		return $tmp;
	}

	/**
	 * [MM_anti_spam description]
	 * @param [type] $arg [description]
	 */
	public function MM_anti_spam($arg) 
	{  
		return('<a href=\"mailto:'.spam::anti_spam($arg, 1).' class="noir" target="_blank">'.spam::anti_spam($arg, 0).'</a>');
	}

	/**
	 * [MM_msg_foot description]
	 */
	public function MM_msg_foot() 
	{  
		global $foot1, $foot2, $foot3, $foot4;
		
		if ($foot1)
		{ 
			$MT_foot = stripslashes($foot1)."<br />";
		}

		if ($foot2) 
		{
			$MT_foot .= stripslashes($foot2)."<br />";
		}

		if ($foot3) 
		{
			$MT_foot .= stripslashes($foot3)."<br />";
		}

		if ($foot4) 
		{
			$MT_foot .= stripslashes($foot4);
		}
		
		return language::aff_langue($MT_foot);
	}

	/**
	 * [MM_date description]
	 */
	public function MM_date() 
	{
		global $locale, $gmt;
		
		setlocale(LC_TIME, language::aff_langue($locale));
		$MT_date = strftime(translate("daydate"), time()+((integer)$gmt*3600));
		
		return $MT_date;
	}

	/**
	 * [MM_banner description]
	 */
	public function MM_banner() 
	{
		global $banners, $hlpfile;

		if (($banners) and (!$hlpfile)) 
		{   
			ob_start();   
			    include("banners.php");   
			    $MT_banner = ob_get_contents();   
			ob_end_clean();
		} 
		else 
		{   
			$MT_banner = "";
		}

		return $MT_banner;
	}

	/**
	 * [MM_search_topics description]
	 */
	public function MM_search_topics() 
	{
		global $NPDS_Prefix;

		$MT_search_topics = "<form action=\"search.php\" method=\"post\"><label class=\"col-form-label\">".translate("Sujets")." </label>";
		$MT_search_topics .= "<select class=\"custom-select\" name=\"topic\"onChange=''submit()''>" ;
		$MT_search_topics .= "<option value=\"\">".translate("Tous les sujets")."</option>";
		
		$rowQ = cache::Q_select("select topicid, topictext from ".$NPDS_Prefix."topics order by topictext", 86400);
		
		foreach($rowQ as $myrow) 
		{  
			$MT_search_topics .= "<option value=\"".$myrow['topicid']."\">".language::aff_langue($myrow['topictext'])."</option>";
		}

		$MT_search_topics .= "</select></form>";
		
		return $MT_search_topics;
	}

	/**
	 * [MM_search description]
	 */
	public function MM_search() 
	{
		$MT_search = "<form action=\"search.php\" method=\"post\"><label>".translate("Recherche")."</label><input class=\"form-control\" type=\"text\" name=\"query\" size=\"10\"></form>";
		
		return $MT_search;
	}

	/**
	 * [MM_member description]
	 */
	public function MM_member() 
	{
		global $cookie, $anonymous;

		$username = $cookie[1];
		if ($username == "") 
		{
			$username = $anonymous;
		}

		ob_start();
		    mailler::Mess_Check_Mail($username);   
		    $MT_member = ob_get_contents();
		ob_end_clean();

		return $MT_member;
	}

	/**
	 * [MM_nb_online description]
	 */
	public function MM_nb_online() 
	{
		list($MT_nb_online, $MT_whoim) = online::Who_Online();
		
		return $MT_nb_online;
	}

	/**
	 * [MM_whoim description]
	 */
	public function MM_whoim() 
	{
		list($MT_nb_online, $MT_whoim) = online::Who_Online();
		
		return $MT_whoim;
	}

	/**
	 * [MM_membre_nom description]
	 */
	public function MM_membre_nom() 
	{
		global $NPDS_Prefix, $cookie;
		
		if(isset($cookie[1])) 
		{  
			$uname = metalang::arg_filter($cookie[1]);   
			$MT_name = "";   
			
			$rowQ = cache::Q_select("SELECT name FROM ".$NPDS_Prefix."users WHERE uname=''$uname''", 3600);   
			$myrow = $rowQ[0];   
			$MT_name = $myrow['name'];   
			
			return $MT_name;
		}
	}

	/**
	 * [MM_membre_pseudo description]
	 */
	public function MM_membre_pseudo() 
	{
		global $cookie;
		
		return ($cookie[1]);
	}

	/**
	 * [MM_blocID description]
	 * @param [type] $arg [description]
	 */
	public function MM_blocID($arg) 
	{
		return(@block::oneblock(substr($arg, 1), substr($arg, 0, 1)."B"));
	}

	/**
	 * [MM_block description]
	 * @param [type] $arg [description]
	 */
	public function MM_block($arg) 
	{
		return metalang::meta_lang("blocID($arg)");
	}

	/**
	 * [MM_leftblocs description]
	 */
	public function MM_leftblocs() 
	{
		ob_start();   
		    block::leftblocks();   
		    $M_Lblocs = ob_get_contents();
		ob_end_clean();
		
		return $M_Lblocs;
	}

	/**
	 * [MM_rightblocs description]
	 */
	public function MM_rightblocs() 
	{
		ob_start();   
		    block::rightblocks();   
		    $M_Lblocs = ob_get_contents();
		ob_end_clean();
		
		return $M_Lblocs;
	}

	/**
	 * [MM_articleID description]
	 * @param [type] $arg [description]
	 */
	public function MM_articleID($arg) 
	{
		global $NPDS_Prefix, $nuke_url;
		
		$arg = metalang::arg_filter($arg);
		$rowQ = cache::Q_select("SELECT title FROM ".$NPDS_Prefix."stories WHERE sid=''$arg''", 3600);
		$myrow = $rowQ[0];
		
		return "<a href=\"$nuke_url/article.php?sid=$arg\">".$myrow['title']."</a>";
	}

	/**
	 * [MM_article description]
	 * @param [type] $arg [description]
	 */
	public function MM_article($arg) 
	{
		return metalang::meta_lang("articleID($arg)");
	}

	/**
	 * [MM_article_completID description]
	 * @param [type] $arg [description]
	 */
	public function MM_article_completID($arg) 
	{
		if ($arg > 0) 
		{   
			$story_limit = 1;   
			$news_tab = news::prepa_aff_news("article",$arg,"");
		} 
		else 
		{   
			$news_tab = news::prepa_aff_news("index","","");  
		    $story_limit = abs($arg)+1;
		}

		$aid = unserialize($news_tab[$story_limit]['aid']);
		$informant = unserialize($news_tab[$story_limit]['informant']);
		$datetime = unserialize($news_tab[$story_limit]['datetime']);
		$title = unserialize($news_tab[$story_limit]['title']);
		$counter = unserialize($news_tab[$story_limit]['counter']);
		$topic = unserialize($news_tab[$story_limit]['topic']);
		$hometext = unserialize($news_tab[$story_limit]['hometext']);
		$notes = unserialize($news_tab[$story_limit]['notes']);
		$morelink = unserialize($news_tab[$story_limit]['morelink']);
		$topicname = unserialize($news_tab[$story_limit]['topicname']);
		$topicimage = unserialize($news_tab[$story_limit]['topicimage']);
		$topictext = unserialize($news_tab[$story_limit]['topictext']);
		$s_id = unserialize($news_tab[$story_limit]['id']);
		
		if ($aid) 
		{   
			ob_start();      
			    themeindex($aid, $informant, $datetime, $title, $counter, $topic, $hometext, $notes, $morelink, $topicname, $topicimage, $topictext, $s_id);      
			    $remp = ob_get_contents();   
			ob_end_clean();
		} 
		else 
		{   
			$remp = "";
		}

		return $remp;
	}

	/**
	 * [MM_article_complet description]
	 * @param [type] $arg [description]
	 */
	public function MM_article_complet($arg) 
	{
		return metalang::meta_lang("article_completID($arg)");
	}

	/**
	 * [MM_headlineID description]
	 * @param [type] $arg [description]
	 */
	public function MM_headlineID($arg) 
	{
		return (@headlines($arg, ""));
	}

	/**
	 * [MM_headline description]
	 * @param [type] $arg [description]
	 */
	public function MM_headline($arg) 
	{
		return metalang::meta_lang("headlineID($arg)");
	}

	/**
	 * [MM_list_mns description]
	 */
	public function MM_list_mns() 
	{
		global $NPDS_Prefix;
		
		$query = sql_query("SELECT uname FROM ".$NPDS_Prefix."users WHERE mns='1");
		$MT_mns = "<table width=\"100%\">";
		
		while (list($uname) = sql_fetch_row($query)) 
		{   
			$rowcolor = theme::tablos();   
			$MT_mns .= "<tr $rowcolor><td><a href=\"minisite.php?op=$uname\" target=\"_blank\" class=\"noir\">$uname</a></td></tr>";
		}
		$MT_mns .= "</table>";
		
		return $MT_mns;
	}

	/**
	 * [MM_LastMember description]
	 */
	public function MM_LastMember() 
	{
		global $NPDS_Prefix;
		
		$query = sql_query("SELECT uname FROM ".$NPDS_Prefix."users ORDER BY uid DESC LIMIT 0,1");
		$result = sql_fetch_row($query);
		
		return $result[0];
	}

	/**
	 * [MM_edito description]
	 */
	public function MM_edito() 
	{
		list($affich, $M_edito) = edito::fab_edito();
		
		if ((!$affich) or ($M_edito == "")) 
		{   
			$M_edito = "";
		}
		
		return $M_edito;
	}

	/**
	 * [MM_groupe_text description]
	 * @param [type] $arg [description]
	 */
	public function MM_groupe_text($arg) 
	{
		global $user;
		
		$affich = false;
		$remp = "";
		
		if ($arg != "") 
		{   
			if (groupe::groupe_autorisation($arg, groupe::valid_group($user))) 
			{
				$affich = true;
			}     
		} 
		else 
		{   
			if ($user)
			{
				$affich = true;
			}
		}

		if (!$affich) 
		{ 
			$remp = "!delete!"; 
		}

		return $remp;
	}

	/**
	 * [MM_no_groupe_text description]
	 * @param [type] $arg [description]
	 */
	public function MM_no_groupe_text($arg) 
	{
		global $user;
		
		$affich = true;
		$remp = "";
		
		if ($arg != "") 
		{   
			if (groupe::groupe_autorisation($arg, groupe::valid_group($user))) 
			{     
				$affich = false;   
			}

			if (!$user)
			{      
			    $affich = false;
			}
		} 
		else 
		{   
			if ($user) 
			{    
				$affich = false;
			} 
		}
		
		if (!$affich) 
		{ 
			$remp = "!delete!"; 
		}
		
		return $remp;
	}

	/**
	 * [MM_note description]
	 */
	public function MM_note() 
	{
		return "!delete!";
	}

	/**
	 * [MM_note_admin description]
	 */
	public function MM_note_admin() 
	{
		global $admin;
		
		if (!$admin)
		{
			return "!delete!";
		}
		else 
		{
		    return "<b>nota</b> : ";
		}
	}

	/**
	 * [MM_debugON description]
	 */
	public function MM_debugON() 
	{
		global $NPDS_debug, $NPDS_debug_str, $NPDS_debug_time, $NPDS_debug_cycle;
		
		$NPDS_debug_cycle = 1;
		$NPDS_debug = true;
		$NPDS_debug_str = "<br />";
		$NPDS_debug_time = getmicrotime();
		
		return "";
	}

	/**
	 * [MM_debugOFF description]
	 */
	public function MM_debugOFF() 
	{
		global $NPDS_debug, $NPDS_debug_str, $NPDS_debug_time, $NPDS_debug_cycle;
		
		$time_end = getmicrotime();
		$NPDS_debug_str .= "=> !DebugOFF!<br /><b>=> exec time for meta-lang : ".round($time_end - $NPDS_debug_time, 4)." / cycle(s) : $NPDS_debug_cycle</b><br />";
		$NPDS_debug = false;
		
		echo $NPDS_debug_str;
		
		return "";
	}

	/**
	 * [MM_forum_all description]
	 */
	public function MM_forum_all() 
	{
		global $NPDS_Prefix;	

		$rowQ1 = cache::Q_Select("SELECT * FROM ".$NPDS_Prefix."catagories ORDER BY cat_id", 3600);
		$Xcontent = @forum::forum($rowQ1);
		
		return $Xcontent;
	}

	/**
	 * [MM_forum_categorie description]
	 * @param [type] $arg [description]
	 */
	public function MM_forum_categorie($arg) 
	{
		global $NPDS_Prefix;

		$arg = metalang::arg_filter($arg);
		$bid_tab = explode(",", $arg); 
		$sql = "";
		
		foreach($bid_tab as $cat) 
		{   
			$sql .= "cat_id=''$cat'' OR ";
		}

		$sql = substr($sql,0,-4);
		$rowQ1 = cache::Q_Select ("SELECT * FROM ".$NPDS_Prefix."catagories WHERE $sql", 3600);
		$Xcontent = @forum::forum($rowQ1);
		
		return $Xcontent;
	}

	/**
	 * [MM_forum_message description]
	 */
	public function MM_forum_message() 
	{
		global $subscribe, $user;
		
		$ibid = "";
		
		if (!$user) 
		{   
			$ibid = translate("Devenez membre et vous disposerez de fonctions spécifiques : abonnements, forums spéciaux (cachés, membres, ..), statut de lecture, ...");
		}

		if (($subscribe) and ($user)) 
		{   
			$ibid = translate("Cochez un forum et cliquez sur le bouton pour recevoir un Email lors d''une nouvelle soumission dans celui-ci.");
		}

		return $ibid;
	}

	/**
	 * [MM_forum_recherche description]
	 */
	public function MM_forum_recherche() 
	{
		$Xcontent = @forumbox::searchblock();
		
		return $Xcontent;
	}

	/**
	 * [MM_forum_icones description]
	 */
	public function MM_forum_icones() 
	{
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

		$ibid = "<img src=\"$imgtmpR\" border=\"\" alt=\"\" /> = ".translate("Les nouvelles contributions depuis votre dernière visite.")."<br />";
		$ibid .= "<img src=\"$imgtmp\" border=\"\" alt=\"\" /> = ".translate("Aucune nouvelle contribution depuis votre dernière visite.");
		
		return $ibid;
	}

	/**
	 * [MM_forum_subscribeON description]
	 */
	public function MM_forum_subscribeON() 
	{
		global $subscribe, $user;
		
		$ibid = "";
		if (($subscribe) and ($user)) 
		{   
			$userX = base64_decode($user);
			$userR = explode(':', $userX);
			
			if(mailler::isbadmailuser($userR[0]) === false) 
			{
				$ibid = "<form action=\"forum.php\" method=\"post\">   
				<input type=\"hidden\" name=\"op\" value=\"maj_subscribe\" />";   
			}
		}

		return $ibid;
	}

	/**
	 * [MM_forum_bouton_subscribe description]
	 */
	public function MM_forum_bouton_subscribe() 
	{
		global $subscribe, $user;
		
		if (($subscribe) and ($user)) 
		{   
			$userX = base64_decode($user);   
			$userR = explode(':', $userX);
			
			if(mailler::isbadmailuser($userR[0]) === false) 
			{  
				return '<input class="btn btn-secondary" type="submit" name="Xsub" value="'.translate("OK").'" />';
			}
		} 
		else 
		{  
			return '';
		}
	}

	/**
	 * [MM_forum_subscribeOFF description]
	 */
	public function MM_forum_subscribeOFF() 
	{
		global $subscribe, $user;
		
		$ibid = "";
		if (($subscribe) and ($user)) 
		{   
			$userX = base64_decode($user);   
			$userR = explode(':', $userX);  
			
			if(mailler::isbadmailuser($userR[0]) === false) 
			{     
				$ibid = "</form>";   
			}
		}

		return $ibid;
	}

	/**
	 * [MM_forum_subfolder description]
	 * @param [type] $arg [description]
	 */
	public function MM_forum_subfolder($arg) 
	{
		$forum = metalang::arg_filter($arg);
		$content = forum::sub_forum_folder($forum);
		
		return $content;
	}

	/**
	 * [MM_insert_flash description]
	 * @param [type] $name   [description]
	 * @param [type] $width  [description]
	 * @param [type] $height [description]
	 * @param [type] $bgcol  [description]
	 */
	public function MM_insert_flash($name, $width, $height, $bgcol) 
	{   
		
		$object = '<object codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflas" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" h.cab#version="6,0,0,0" width="'.$width.'" height="'.$height.'" id="'.$name.'" align="middle">
		<param name="allowScriptAccess" value="sameDomain" />
		<param name="movie" value="flash/'.$name.'" />
		<param name="quality" value="high" />   
		<param name="bgcolor" value="'.$bgcol.'" />   
		<embed src="flash/'.$name.'" quality="high" bgcolor="'.$bgcol.'" width="'.$width.'" height='.$height.'" name="'.$name.'" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />   
	    </object>';

		return $object;
	}

	/**
	 * [MM_login description]
	 */
	public function MM_login() 
	{
		$boxstuff = '<div class="card card-body m-3">   
		<h3><a href="user.php?op=only_newuser" role="button" title="'.translate("Nouveau membre").'"><i class="fa fa-user-plus"></i>&nbsp;'.translate("Nouveau membre").'</a></h3>
		</div>
		<div class="card card-body m-3">   
		<h3><i class="fas fa-sign-in-alt fa-lg"></i>&nbsp;'.translate("Connexion").'</h3>   
		<form action="user.php" method="post" name="userlogin">
		<div class="form-group row">
		<label for="inputuser" class="form-control-label col-sm-4">'.translate("Identifiant").'</label>
		<div class="col-sm-8">
		<input type="text" class="form-control" name="uname" id="inputuser" placeholder="'.translate("Identifiant").'">
		</div>
		</div>
		<div class="form-group row">
		<label for="inputPassuser" class="form-control-label col-sm-4">'.translate("Mot de passe").'</label>
		<div class="col-sm-8">
		<input type="password" class="form-control" name="pass" id="inputPassuser" placeholder="'.translate("Mot de passe").'"> 
		<span class="help-block small"><a href="user.php?op=forgetpassword" role="button" title="'.translate("Vous avez perdu votre mot de passe ?").'">'.translate("Vous avez perdu votre mot de passe ?").'</a></span>
		</div>
		</div>
		<input type="hidden" name="op" value="login" />
		<div class="form-group row">
		<div class="col-sm-8 ml-sm-auto">
		<button class="btn btn-primary" type="submit" title="'.translate("Valider").'">'.translate("Valider").'</button>
		</div>
		</div>
		</form>
		</div>';

		return $boxstuff;
	}

	/**
	 * [MM_administration description]
	 */
	public function MM_administration() 
	{
		global $admin;
		
		if ($admin) 
		{   
			return "<a href=\"admin.php\">".translate("Outils administrateur")."</a>";
		} 
		else 
		{   
			return "";
		}
	}

	/**
	 * Note : a revoir each
	 * [MM_admin_infos description]
	 * @param [type] $arg [description]
	 */
	public function MM_admin_infos($arg) 
	{
		global $NPDS_Prefix;
		
		$arg = metalang::arg_filter($arg);
		$rowQ1 = cache::Q_select ("SELECT url, email FROM ".$NPDS_Prefix."authors WHERE aid='$arg'", 86400);
		
		//list(, $myrow) = each($rowQ1);
		foreach ($rowQ1 as $myrow)
		{
			if (isset($myrow['url'])) 
			{   
				$auteur = "<a href=\"".$myrow['url']."\">$arg</a>";
			} 
			elseif (isset($myrow['email'])) 
			{   
				$auteur = "<a href=\"mailto:".$myrow['email']."\">$arg</a>";
			} 
			else 
			{   
				$auteur = $arg;
			}
		}
		
		return $auteur;
	}

	/**
	 * [MM_theme_img description]
	 * @param [type] $arg [description]
	 */
	public function MM_theme_img($arg) 
	{
		return MM_img($arg);
	}

	/**
	 * [MM_rotate_img description]
	 * @param [type] $arg [description]
	 */
	public function MM_rotate_img($arg) 
	{
		mt_srand((double)microtime()*1000000);
		
		$arg = metalang::arg_filter($arg);
		$tab_img = explode(",", $arg);
		
		if (count($tab_img) > 1) 
		{
			$imgnum = mt_rand(0, count($tab_img)-1);
		} 
		else if (count($tab_img) == 1) 
		{
			$imgnum = 0;
		} 
		else 
		{
			$imgnum = -1;
		}

		if ($imgnum != -1) 
		{
			$Xcontent = "<img src=\"".$tab_img[$imgnum]."\" border=\"0\" alt=\"".$tab_img[$imgnum]."\" title=\"".$tab_img[$imgnum]."\" />";
		}

		return $Xcontent;
	}

	/**
	 * [MM_sql_nbREQ description]
	 */
	public function MM_sql_nbREQ() 
	{
		global $sql_nbREQ;
		
		return "SQL REQ : $sql_nbREQ";
	}

	/**
	 * [MM_comment_system description]
	 * @param [type] $file_name [description]
	 * @param [type] $topic     [description]
	 */
	public function MM_comment_system($file_name, $topic) 
	{
		global $NPDS_Prefix, $anonpost, $moderate, $admin, $user;
		
		ob_start();
		    if (file_exists("modules/comments/$file_name.conf.php")) 
		    {
			    include ("modules/comments/$file_name.conf.php");
			    include ("modules/comments/comments.php");
		    }
		    $output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	/**
	 * [MM_top_stories description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_stories($arg) 
	{
		$content = '';
		$arg = metalang::arg_filter($arg);
		$xtab = news::news_aff("libre", "ORDER BY counter DESC LIMIT 0, ".$arg*2, 0, $arg*2);
		$story_limit = 0;
		
		while (($story_limit < $arg) and ($story_limit < sizeof($xtab))) 
		{
			list($sid, $catid, $aid, $title, $time, $hometext, $bodytext, $comments, $counter) = $xtab[$story_limit];
			$story_limit++;
			
			if($counter > 0) 
			{
				$content .= '<li class="ml-4 my-1"><a href="article.php?sid='.$sid.'" >'.language::aff_langue($title).'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($counter).' '.translate("Fois").'</span></li>';  
			}
		}

		return $content;
	}

	/**
	 * [MM_top_commented_stories description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_commented_stories($arg) 
	{
		$content = '';
		$arg = metalang::arg_filter($arg);
		$xtab = news::news_aff("libre", "ORDER BY comments DESC  LIMIT 0, ".$arg*2, 0, $arg*2);
		$story_limit = 0;
		
		while (($story_limit < $arg) and ($story_limit < sizeof($xtab))) 
		{
			list($sid, $catid, $aid, $title, $time, $hometext, $bodytext, $comments) = $xtab[$story_limit];
			$story_limit++;
			
			if($comments > 0) 
			{ 
				$content .= '<li class="ml-4 my-1"><a href="article.php?sid='.$sid.'" >'.language::aff_langue($title).'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($comments).'</span></li>';  
			}
		}

		return $content;
	}

	/**
	 * [MM_top_categories description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_categories($arg) 
	{
		global $NPDS_Prefix;

		$content = '';
		$arg = metalang::arg_filter($arg);
		$result = sql_query("select catid, title, counter from ".$NPDS_Prefix."stories_cat order by counter DESC limit 0,$arg");
		
		while (list($catid, $title, $counter) = sql_fetch_row($result)) 
		{
			if ($counter > 0) 
			{
				$content .= '<li class="ml-4 my-1"><a href="index.php?op=newindex&amp;catid='.$catid.'" >'.language::aff_langue($title).'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($counter).'</span></li>';  
			}
		}
		sql_free_result($result);
		
		return $content;
	}

	/**
	 * [MM_top_sections description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_sections($arg) 
	{
		global $NPDS_Prefix;
		
		$content = '';
		$arg = metalang::arg_filter($arg);
		$result = sql_query("SELECT artid, title, counter FROM ".$NPDS_Prefix."seccont ORDER BY counter DESC LIMIT 0,$arg");
		
		while (list($artid, $title, $counter) = sql_fetch_row($result)) 
		{
			$content .= '<li class="ml-4 my-1"><a href="sections.php?op=viewarticle&amp;artid='.$artid.'" >'.language::aff_langue($title).'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($counter).' '.translate("Fois").'</span></li>';
		}
		sql_free_result($result);
		
		return $content;
	}

	/**
	 * [MM_top_reviews description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_reviews($arg) 
	{
		global $NPDS_Prefix;
		
		$content = '';
		$arg = metalang::arg_filter($arg);
		$result = sql_query("SELECT id, title, hits FROM reviews ORDER BY hits DESC LIMIT 0,$arg");
		
		while (list($id, $title, $hits) = sql_fetch_row($result)) 
		{
			if ($hits > 0) 
			{
				$content .= '<li class="ml-4 my-1"><a href="reviews.php?op=showcontent&amp;id='.$id.'" >'.$title.'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($hits).' '.translate("Fois").'</span></li>';   
			}
		}
		sql_free_result($result);
		
		return $content;
	}

	/**
	 * [MM_top_authors description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_authors($arg) 
	{
		global $NPDS_Prefix;
		
		$content = '';
		$arg = metalang::arg_filter($arg);
		$result = sql_query("SELECT aid, counter FROM authors ORDER BY counter DESC LIMIT 0,$arg");
		while (list($aid, $counter) = sql_fetch_row($result)) 
		{   
			if ($counter > 0) 
			{     
				$content .= '<li class="ml-4 my-1"><a href="search.php?query=&amp;author='.$aid.'" >'.$aid.'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($counter).'</span></li>';   
			}
		}
		sql_free_result($result);
		
		return $content;
	}

	/**
	 * [MM_top_polls description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_polls($arg) 
	{
		global $NPDS_Prefix;
		
		$content = '';
		$arg = metalang::arg_filter($arg);
		$result = sql_query("SELECT pollID, pollTitle, voters FROM poll_desc ORDER BY voters DESC LIMIT 0,$arg");
		
		while (list($pollID, $pollTitle, $voters) = sql_fetch_row($result)) 
		{
			if ($voters > 0) 
			{
				$content.='<li class="ml-4 my-1"><a href="pollBooth.php?op=results&amp;pollID='.$pollID.'" >'.language::aff_langue($pollTitle).'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($voters).'</span></li>';   
			}
		}
		sql_free_result($result);
		
		return $content;
	}

	/**
	 * [MM_top_storie_authors description]
	 * @param [type] $arg [description]
	 */
	public function MM_top_storie_authors($arg) 
	{
		global $NPDS_Prefix;
		
		$content = '';
		$arg = metalang::arg_filter($arg);
		$result = sql_query("SELECT uname, counter FROM users ORDER BY counter DESC LIMIT 0,$arg");
		
		while (list($uname, $counter) = sql_fetch_row($result)) 
		{  
			if ($counter > 0) 
			{
				$content .= '<li class="ml-4 my-1"><a href="user.php?op=userinfo&amp;uname='.$uname.'" >'.$uname.'</a>&nbsp;<span class="badge badge-secondary float-right">'.str::wrh($counter).'</span></li>';   
			}
		}
		sql_free_result($result);
		
		return $content;
	}

	/**
	 * [MM_topic_all description]
	 */
	public function MM_topic_all() 
	{
		global $NPDS_Prefix, $tipath;
		
		$aff = '';
		$aff = '<div class="card-columns">';
		$result = sql_query("SELECT topicid, topicname, topicimage, topictext FROM ".$NPDS_Prefix."topics ORDER BY topicname");
		
		while(list($topicid, $topicname, $topicimage, $topictext) = sql_fetch_row($result)) 
		{
			$resultn = sql_query("SELECT COUNT(*) AS total FROM ".$NPDS_Prefix."stories WHERE topic='$topicid'");
			$total_news = sql_fetch_assoc($resultn);
			
			$aff .= '   <div class="card">';
			
			if ((($topicimage) or ($topicimage != '')) and (file_exists("$tipath$topicimage")))
			{
				$aff .= '      <img class="mt-3 ml-3 n-sujetsize" src="'.$tipath.$topicimage.'" alt="topic_icon" />';
			}  
			    
			$aff .= '      <div class="card-body">';   

			if($total_news['total'] != '0')      
			{
			    $aff .= '      <a href="index.php?op=newtopic&amp;topic='.$topicid.'"><h4 class="card-title">'.language::aff_langue($topicname).'</h4></a>'; 
			}  
			else 
			{
			    $aff .= '      <h4 class="card-title">'.language::aff_langue($topicname).'</h4>'; 
			}  
			    
			$aff .= '      <p class="card-text">'.language::aff_langue($topictext).'</p>      <p class="card-text text-right"><span class="small">'.translate("Nb. d''articles").'</span> <span class="badge badge-secondary">'.$total_news['total'].'</span></p>     </div>';  
			$aff .= '     </div>';
		}
		$aff .= '  </div>';
		sql_free_result($result);
		
		return $aff;
	}

	/**
	 * [MM_topic_subscribeOFF description]
	 */
	public function MM_topic_subscribeOFF() 
	{
		$aff = '<div class="form-group row"><input type="hidden" name="op" value="maj_subscribe" />';
		$aff .= '<button class="btn btn-primary ml-3" type="submit" name="ok">'.translate("Valider").'</button>';
		$aff .= '</div></fieldset></form>';
		
		return $aff;
	}

	/**
	 * [MM_topic_subscribeON description]
	 */
	public function MM_topic_subscribeON() 
	{
		global $subscribe, $user, $cookie;
		
		if ($subscribe and $user) 
		{   
			if(mailler::isbadmailuser($cookie[0]) === false) 
			{      
				return '<form action="topics.php" method="post"><fieldset>';   
			}
		}
	}

	/**
	 * [MM_topic_subscribe description]
	 * @param [type] $arg [description]
	 */
	public function MM_topic_subscribe($arg) 
	{
		global $NPDS_Prefix, $subscribe, $user, $cookie;
		
		$segment = metalang::arg_filter($arg);
		$aff = '';
		if ($subscribe) 
		{
			if ($user) 
			{  
				$aff = '      <div class="form-group row">';      
				$result = sql_query("SELECT topicid, topictext, topicname FROM ".$NPDS_Prefix."topics ORDER BY topicname");    
			    
			    while(list($topicid, $topictext, $topicname) = sql_fetch_row($result)) 
			    {
			    	$resultX = sql_query("SELECT topicid FROM ".$NPDS_Prefix."subscribe WHERE uid='$cookie[0]' AND topicid='$topicid'");         
			    	
			    	if (sql_num_rows($resultX) == "1")
			    	{            
			    		$checked = 'checked';
			    	}       
			    	else 
			    	{
			    		$checked = '';
			    	}  

			    	$aff .= '            
			    	<div class="'.$segment.'">               
			    	<div class="custom-control custom-checkbox">
			    	<input type="checkbox" class="custom-control-input" name="Subtopicid['.$topicid.']" id="subtopicid'.$topicid.'" '.$checked.' />                  
			    	<label class="custom-control-label" for="subtopicid'.$topicid.'">'.language::aff_langue($topicname).'</label>
			    	</div>
			    	</div>';      
			    }

			    $aff .= '</div>';
			    sql_free_result($result);
			}
		}

		return $aff;
	}

	/**
	 * [MM_yt_video description]
	 * @param [type] $id_yt_video [description]
	 */
	public function MM_yt_video($id_yt_video) 
	{
		$content = '';
		$id_yt_video = metalang::arg_filter($id_yt_video);
		
		if(!defined('CITRON'))
		{
			$content .= '   <div class="embed-responsive embed-responsive-16by9">      
		    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/'.$id_yt_video.'" allowfullscreen="" frameborder="0"></iframe>
		    </div>';
		}
		else 
		{
			$content .= '<div class="youtube_player" videoID="'.$id_yt_video.'"></div>';
		}
		
		return $content;
	}

	/**
	 * [MM_espace_groupe description]
	 * @param [type] $gr   [description]
	 * @param [type] $t_gr [description]
	 * @param [type] $i_gr [description]
	 */
	public function MM_espace_groupe($gr, $t_gr, $i_gr) 
	{
		$gr = metalang::arg_filter($gr);
		$t_gr = metalang::arg_filter($t_gr);
		$i_gr = metalang::arg_filter($i_gr);
		
		return groupe::fab_espace_groupe($gr, $t_gr, $i_gr);
	}

	/**
	 * [MM_blocnote description]
	 * @param [type] $arg [description]
	 */
	public function MM_blocnote($arg) 
	{
		global $REQUEST_URI;
		
		if (!stristr($REQUEST_URI, "admin.php")) 
		{
			return(@block::oneblock($arg, "RB"));
		} 
		else 
		{
			return ""; 
		}
	}

	/**
	 * [MM_forumP description]
	 */
	public function MM_forumP() 
	{
		global $NPDS_Prefix, $cookie, $user;
		
		/*Sujet chaud*/	
		$hot_threshold = 10;
		/*Nbre posts a afficher*/
		$maxcount = "15";
		
		$MM_forumP = '<table cellspacing="3" cellpadding="3" width="top" border="0">'	
		    .'<tr align="center" class="ligna">'	
		    .'<th width="5%">'.language::aff_langue('[french]Etat[/french][english]State[/english]').'</th>'	
		    .'<th width="20%">'.language::aff_langue('[french]Forum[/french][english]Forum[/english]').'</th>'	
		    .'<th width="30%">'.language::aff_langue('[french]Sujet[/french][english]Topic[/english]').'</th>'
		    .'<th width="5%">'.language::aff_langue('[french]RÃ©ponse[/french][english]Replie[/english]').'</th>'	
		    .'<th width="20%">'.language::aff_langue('[french]Dernier Auteur[/french][english]Last author[/english]').'</th>'
		   	.'<th width="20%">'.language::aff_langue('[french]Date[/french][english]Date[/english]').'</th>'
		   	.'</tr>';
		
		/*Requete liste dernier post*/
		$result = sql_query("SELECT MAX(post_id) FROM ".$NPDS_Prefix."posts WHERE forum_id > 0 GROUP BY topic_id ORDER BY MAX(post_id) DESC LIMIT 0,$maxcount");	
		while (list($post_id) = sql_fetch_row($result))	
		{
		    
		    /*Requete detail dernier post*/
		   	$res = sql_query("SELECT us.topic_id, us.forum_id, us.poster_id, us.post_time, uv.topic_title, ug.forum_name, ug.forum_type, ug.forum_pass, ut.uname FROM ".$NPDS_Prefix."posts us, ".$NPDS_Prefix."forumtopics uv, ".$NPDS_Prefix."forums ug, ".$NPDS_Prefix."users ut WHERE us.post_id = $post_id AND uv.topic_id = us.topic_id AND uv.forum_id = ug.forum_id AND ut.uid = us.poster_id LIMIT 1");		

		   	list($topic_id, $forum_id, $poster_id, $post_time, $topic_title, $forum_name, $forum_type, $forum_pass, $uname) = sql_fetch_row($res);
		   	
		   	if (($forum_type == "5") or ($forum_type == "7")) 
		   	{
		   		$ok_affich = false;
		   		$tab_groupe = groupe::valid_group($user);
		   		$ok_affich = groupe::groupe_forum($forum_pass, $tab_groupe);
		   	} 
		   	else 
		   	{
		   		$ok_affich = true;
		   	}

		   	if ($ok_affich) 
		   	{	
		   		/*Nbre de postes par sujet*/	
		   		$TableRep = sql_query("SELECT * FROM ".$NPDS_Prefix."posts WHERE forum_id > 0 AND topic_id = '$topic_id'");
		   		$replys = sql_num_rows($TableRep)-1;
		   		
		   		/*Gestion lu / non lu*/
		   		$sqlR = "SELECT rid FROM ".$NPDS_Prefix."forum_read WHERE topicid = '$topic_id' AND uid = '$cookie[0]' AND status != '0'";
		   		
		   		if ($ibid = theme::theme_image("forum/icons/hot_red_folder.gif"))
		   		{
		   			$imgtmpHR = $ibid;
		   		}
		   		else
		   		{
		   			$imgtmpHR = "assets/images/forum/icons/hot_red_folder.gif";
		   		}

		   		if ($ibid = theme::theme_image("forum/icons/hot_folder.gif"))
		   		{
		   			$imgtmpH = $ibid;
		   		}
		   		else
		   		{
		   			$imgtmpH = "assets/images/forum/icons/hot_folder.gif";
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
		   			$imgtmpF = $ibid;
		   		}
		   		else
		   		{
		   			$imgtmpF = "assets/images/forum/icons/folder.gif";
		   		}

		   		if ($ibid = theme::theme_image("forum/icons/lock.gif"))
		   		{
		   			$imgtmpL = $ibid;
		   		}
		   		else
		   		{
		   			$imgtmpL = "assets/images/forum/icons/lock.gif";
		   		}

		   		if ($replys >= $hot_threshold) 
		   		{
		   			if (sql_num_rows(sql_query($sqlR)) == 0)
		   			{
		   				$image = $imgtmpHR;
		   			}
		   			else
		   			{
		   				$image = $imgtmpH;
		   			}
		   		} 
		   		else 
		   		{
		   			if (sql_num_rows(sql_query($sqlR)) == 0)
		   			{	
		   				$image = $imgtmpR;
		   			}
		   			else
		   			{
		   				$image = $imgtmpF;
		   			}
		   		}

		   		if ($myrow['topic_status'] != 0)
		   		{
		   		    $image = $imgtmpL;
		   		}

		   		$MM_forumP .= '<tr class="lignb">'			
		   		.'<td align="center"><img src="'.$image.'"></td>'			
		   		.'<td><a href="viewforum.php?forum='.$forum_id.'">'.$forum_name.'</a></td>'
		   		.'<td><a href="viewtopic.php?topic='.$topic_id.'&forum='.$forum_id.'">'.$topic_title.'</a></td>'
		   		.'<td align="center">'.$replys.'</td>'			
		   		.'<td><a href="user.php?op=userinfo&uname='.$uname.'">'.$uname.'</a></td>'
		   		.'<td align="center">'.$post_time.'</td>'
		   		.'</tr>';
		   	}
		}
		$MM_forumP .= '</table>';
		
		return $MM_forumP;
	}

	/**
	 * [MM_np_twi_Us description]
	 * @param [type] $twi_Us_us    [description]
	 * @param [type] $twi_Us_nb    [description]
	 * @param [type] $twi_Us_time  [description]
	 * @param [type] $twi_Us_color [description]
	 * @param [type] $twi_Us_dim   [description]
	 * @param [type] $twi_Us_other [description]
	 */
	public function MM_np_twi_Us($twi_Us_us, $twi_Us_nb, $twi_Us_time, $twi_Us_color, $twi_Us_dim, $twi_Us_other)
	{ 
		$twi_Us_us = metalang::arg_filter($twi_Us_us);
		$twi_Us_nb = metalang::arg_filter($twi_Us_nb);
		$twi_Us_time = metalang::arg_filter($twi_Us_time);
		$twi_Us_color = metalang::arg_filter($twi_Us_color);
		$twi_Us_dim = metalang::arg_filter($twi_Us_dim);
		$twi_Us_other = metalang::arg_filter($twi_Us_other);
		
		global $language;
		
		if (file_exists("modules/npds_twi/lang/twi.lang-$language.php")) 
		{
			include_once ("modules/npds_twi/lang/twi.lang-$language.php");
		}

		$dim = explode('|', $twi_Us_dim);
		$col = explode('|', $twi_Us_color);
		$twi_Us_other = str_replace('1', 'true', $twi_Us_other);
		$twi_Us_other = str_replace('0', 'false', $twi_Us_other);
		$oth = explode('|', $twi_Us_other);
		
		$content = '';
		$content .= '   
		    <a class="twitter-timeline" href="https://twitter.com/labonpds" data-widget-id="694244113345044482">Tweets de @labonpds</a>
		    <script>
		        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
		    </script>';
		
		return $content;
	}

	/**
	 * [MM_forumL description]
	 */
	public function MM_forumL()
	{
		global $NPDS_Prefix, $cookie, $user;
		
		/*Sujet chaud*/
		$hot_threshold = 10;
		
		/*Nbre posts a afficher*/
		$maxcount = "10";
		$MM_forumL = '<table cellspacing="3" cellpadding="3" width="top" border="0">'
		.'<tr align="center" class="ligna">'
		.'<td width="8%">'.language::aff_langue('[french]Etat[/french][english]State[/english]').'</td>'
		.'<td width="35%">'.language::aff_langue('[french]Forum[/french][english]Forum[/english]').'</td>'
		.'<td width="50%">'.language::aff_langue('[french]Sujet[/french][english]Topic[/english]').'</td>'
		.'<td width="7%">'.language::aff_langue('[french]RÃ©ponses[/french][english]Replies[/english]').'</td>'
		.'</tr>';
		
		/*Requete liste dernier post*/
		$result = sql_query("SELECT MAX(post_id) FROM ".$NPDS_Prefix."posts WHERE forum_id > 0 GROUP BY topic_id ORDER BY MAX(post_id) DESC LIMIT 0,$maxcount");
		while (list($post_id) = sql_fetch_row($result)) 
		{
			/*Requete detail dernier post*/
			$res = sql_query("SELECT us.topic_id, us.forum_id, us.poster_id, uv.topic_title, ug.forum_name, ug.forum_type, ug.forum_pass FROM ".$NPDS_Prefix."posts us, ".$NPDS_Prefix."forumtopics uv, ".$NPDS_Prefix."forums ug WHERE us.post_id = $post_id AND uv.topic_id = us.topic_id AND uv.forum_id = ug.forum_id LIMIT 1");		
			list($topic_id, $forum_id, $poster_id, $topic_title, $forum_name, $forum_type, $forum_pass) = sql_fetch_row($res);
			
			if (($forum_type == "5") or ($forum_type == "7")) 
			{
				$ok_affich = false;
				$tab_groupe = groupe::valid_group($user);
				$ok_affich = groupe::groupe_forum($forum_pass, $tab_groupe);
			} 
			else 
			{
				$ok_affich = true;
			}

			if ($ok_affich) 
			{
				/*Nbre de postes par sujet*/
				$TableRep = sql_query("SELECT * FROM ".$NPDS_Prefix."posts WHERE forum_id > 0 AND topic_id = '$topic_id'");	
				$replys = sql_num_rows($TableRep)-1;
				
				/*Gestion lu / non lu*/
				$sqlR = "SELECT rid FROM ".$NPDS_Prefix."forum_read WHERE topicid = '$topic_id' AND uid = '$cookie[0]' AND status != '0'";
				
				if ($ibid = theme::theme_image("forum/icons/hot_red_folder.gif"))
				{
					$imgtmpHR = $ibid;
				}
				else
				{
					$imgtmpHR = "assets/images/forum/icons/hot_red_folder.gif";
				}

				if ($ibid = theme::theme_image("forum/icons/hot_folder.gif"))
				{
					$imgtmpH = $ibid;
				}
				else
				{
					$imgtmpH = "assets/images/forum/icons/hot_folder.gif";
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
					$imgtmpF = $ibid;
				}
				else
				{
					$imgtmpF = "assets/images/forum/icons/folder.gif";
				}

				if ($ibid = theme::theme_image("forum/icons/lock.gif"))
				{
					$imgtmpL = $ibid;
				}
				else
				{
					$imgtmpL = "assets/images/forum/icons/lock.gif";
				}

				if ($replys >= $hot_threshold) 
				{
					if (sql_num_rows(sql_query($sqlR)) == 0)
					{		
						$image = $imgtmpHR;
				    }
				    else
				    {
				    	$image = $imgtmpH;
				    }
				} 
				else 
				{
					if (sql_num_rows(sql_query($sqlR)) == 0)
					{
						$image = $imgtmpR;
					}
					else
					{
						$image = $imgtmpF;
					}
				}

				if ($myrow['topic_status'] != 0)
				{
					$image = $imgtmpL;
				}

				$MM_forumL .= '<tr class="lignb">'
				.'<td align="center"><img src="'.$image.'"></td>'
				.'<td><a href="viewforum.php?forum='.$forum_id.'">'.$forum_name.'</a></td>'
				.'<td><a href="viewtopic.php?topic='.$topic_id.'&forum='.$forum_id.'">'.$topic_title.'</a></td>'
				.'<td align="center">'.$replys.'</td>'
				.'</tr>';
			}
		}
		$MM_forumL .= '</table>';
		
		return $MM_forumL;
	}

	/**
	 * [MM_vm_video description]
	 * @param [type] $id_vm_video [description]
	 */
	public function MM_vm_video($id_vm_video) 
	{
	    $content = '';
	    $id_vm_video = metalang::arg_filter($id_vm_video);
	    
	    if(!defined('CITRON'))
	    {
	        $content .= '
	            <div class="embed-responsive embed-responsive-16by9">
	            <iframe class="embed-responsive-item" src="https://player.vimeo.com/video/'.$id_vm_video.'" allowfullscreen="" frameborder="0"></iframe>
	            </div>';
	    }
	    else 
	    {
	        $content .= '<div class="vimeo_player" videoID="'.$id_vm_video.'"></div>';
	    }

	    return $content;
	}

	/**
	 * [MM_dm_video description]
	 * @param [type] $id_dm_video [description]
	 */
	public function MM_dm_video($id_dm_video) 
	{
	    $content = '';
	    $id_dm_video = metalang::arg_filter($id_dm_video);
	    
	    if(!defined('CITRON'))
	    {
	        $content .= '
	        <div class="embed-responsive embed-responsive-16by9">
	        <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/'.$id_dm_video.'" allowfullscreen="" frameborder="0"></iframe>
	        </div>';
	    }
	    else
	    {
	        $content .= '<div class="dailymotion_player" videoID="'.$id_dm_video.'"></div>';
	    }
	    
	    return $content;
	}

	/**
	 * [MM_noforbadmail description]
	 */
	public function MM_noforbadmail() 
	{
		global $subscribe, $user, $cookie;

		$remp = '';
		if ($subscribe and $user) 
		{
			if(mailler::isbadmailuser($cookie[0]) === true)
			{
				$remp = '!delete!';
			}
		}
		
		return $remp;
	}

}