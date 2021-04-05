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

use npds\mailler\mailler;
use npds\error\error;
use npds\logs\logs;


/*
 * forumaddon
 */
class forumaddon {


	/**
	 * [HTML_Add description]
	 */
	public static function HTML_Add() 
	{
	    $affich = '
	    <div class="mt-2">
	        <a href="javascript: addText(\'&lt;b&gt;\',\'&lt;/b&gt;\');" title="'.translate("Gras").'" data-toggle="tooltip" ><i class="fa fa-bold fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;i&gt;\',\'&lt;/i&gt;\');" title="'.translate("Italique").'" data-toggle="tooltip" ><i class="fa fa-italic fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;u&gt;\',\'&lt;/u&gt;\');" title="'.translate("Souligné").'" data-toggle="tooltip" ><i class="fa fa-underline fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;span style=\\\'text-decoration:line-through;\\\'&gt;\',\'&lt;/span&gt;\');" title="" data-toggle="tooltip" ><i class="fa fa-strikethrough fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;p class=\\\'text-left\\\'&gt;\',\'&lt;/p&gt;\');" title="'.translate("Texte aligné à gauche").'" data-toggle="tooltip" ><i class="fa fa-align-left fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;p class=\\\'text-center\\\'&gt;\',\'&lt;/p&gt;\');" title="'.translate("Texte centré").'" data-toggle="tooltip" ><i class="fa fa-align-center fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;p class=\\\'text-right\\\'&gt;\',\'&lt;/p&gt;\');" title="'.translate("Texte aligné à droite").'" data-toggle="tooltip" ><i class="fa fa-align-right fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;p align=\\\'justify\\\'&gt;\',\'&lt;/p&gt;\');" title="'.translate("Texte justifié").'" data-toggle="tooltip" ><i class="fa fa-align-justify fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;ul&gt;&lt;li&gt;\',\'&lt;/li&gt;&lt;/ul&gt;\');" title="'.translate("Liste non ordonnnée").'" data-toggle="tooltip" ><i class="fa fa-list-ul fa-lg mr-2 mb-3"></i></a>
	        <a href="javascript: addText(\'&lt;ol&gt;&lt;li&gt;\',\'&lt;/li&gt;&lt;/ol&gt;\');" title="'.translate("Liste ordonnnée").'" data-toggle="tooltip" ><i class="fa fa-list-ol fa-lg mr-2 mb-3"></i></a>
	        <div class="dropdown d-inline mr-2 mb-3" title="'.translate("Lien web").'" data-toggle="tooltip" data-placement="left">
	            <a class=" dropdown-toggle" href="#" role="button" id="protocoletype" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-link fa-lg"></i></a>
	            <div class="dropdown-menu" aria-labelledby="protocoletype">
	                <a class="dropdown-item" href="javascript: addText(\' http://\',\'\');">http</a>
	                <a class="dropdown-item" href="javascript: addText(\' https://\',\'\');">https</a>
	                <a class="dropdown-item" href="javascript: addText(\' ftp://\',\'\');">ftp</a>
	                <a class="dropdown-item" href="javascript: addText(\' sftp://\',\'\');">sftp</a>
	            </div>
	        </div>
	        <a href="javascript: addText(\'&lt;table class=\\\'table table-bordered table-striped table-sm\\\'&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;&lt;/th&gt;&lt;th&gt;&lt;/th&gt;&lt;th&gt;&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;&lt;/td&gt;&lt;td&gt;&lt;/td&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&lt;/td&gt;&lt;td&gt;&lt;/td&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&lt;/td&gt;&lt;td&gt;&lt;/td&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;\',\'\'); " title="'.translate("Tableau").'" data-toggle="tooltip"><i class="fa fa-table fa-lg mr-2 mb-3"></i></a>
	        <div class="dropdown d-inline mr-2 mb-3" title="'.translate("Code").'" data-toggle="tooltip" data-placement="left">
	            <a class=" dropdown-toggle" href="#" role="button" id="codeclasslanguage" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-code fa-lg"></i></a>
	            <div class="dropdown-menu" aria-labelledby="codeclasslanguage">
	                <h6 class="dropdown-header">Languages</h6>
	                <div class="dropdown-divider"></div>
	                <a class="dropdown-item" href="javascript: addText(\'&lt;pre&gt;[code markup]\',\'[/code]&lt;/pre&gt;\');">Markup</a>
	                <a class="dropdown-item" href="javascript: addText(\'&lt;pre&gt;[code php]\',\'[/code]&lt;/pre&gt;\');">Php</a>
	                <a class="dropdown-item" href="javascript: addText(\'&lt;pre&gt;[code css]\',\'[/code]&lt;/pre&gt;\');">Css</a>
	                <a class="dropdown-item" href="javascript: addText(\'&lt;pre&gt;[code js]\',\'[/code]&lt;/pre&gt;\');">js</a>
	                <a class="dropdown-item" href="javascript: addText(\'&lt;pre&gt;[code sql]\',\'[/code]&lt;/pre&gt;\');">SQL</a>
	            </div>
	        </div>
	        <div class="dropdown d-inline mr-2 mb-3" title="'.translate("Vidéos").'" data-toggle="tooltip" data-placement="left">
	            <a class=" dropdown-toggle" href="#" role="button" id="typevideo" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-film fa-lg"></i></a>
	            <div class="dropdown-menu" aria-labelledby="typevideo">
	                <p class="dropdown-header">'.translate("Coller l'ID de votre vidéo entre les deux balises").' : <br />[video_yt]xxxx[/video_yt]<br />[video_vm]xxxx[/video_vm]<br />[video_dm]xxxx[/video_dm]</p>
	                    <div class="dropdown-divider"></div>
	                <a class="dropdown-item" href="javascript: addText(\'[video_yt]\',\'[/video_yt]\');"><i class="fab fa-youtube fa-lg fa-fw mr-1"></i>Youtube</a>
	                <a class="dropdown-item" href="javascript: addText(\'[video_vm]\',\'[/video_vm]\');"><i class="fab fa-vimeo fa-lg fa-fw mr-1"></i>Vimeo</a>
	                <a class="dropdown-item" href="javascript: addText(\'[video_dm]\',\'[/video_dm]\');"><i class="fas fa-video fa-fw fa-lg mr-1"></i>Dailymotion</a>
	            </div>
	        </div>
	    </div>';
	    
	    return $affich;
	}

	/**
	 * [make_clickable description]
	 * @param  [type] $text [description]
	 * @return [type]       [description]
	 */
	public static function make_clickable($text) 
	{
	    $ret = '';
	    $ret = preg_replace('#(^|\s)(http|https|ftp|sftp)(://)([^\s]*)#i',' <a href="$2$3$4" target="_blank">$2$3$4</a>', $text);
	    
	    $ret = preg_replace_callback('#([_\.0-9a-z-]+@[0-9a-z-\.]+\.+[a-z]{2,4})#i', [mailler::class, 'fakedmail'], $ret);
	   
	    return $ret;
	}

	/**
	 * [anti_flood description]
	 * @param  [type] $modoX      [description]
	 * @param  [type] $paramAFX   [description]
	 * @param  [type] $poster_ipX [description]
	 * @param  [type] $userdataX  [description]
	 * @param  [type] $gmtX       [description]
	 * @return [type]             [description]
	 */
	public static function anti_flood($modoX, $paramAFX, $poster_ipX, $userdataX, $gmtX) 
	{
	    // anti_flood : nd de post dans les 90 puis 30 dernières minutes 
	    // les modérateurs echappent à cette règle
	    // security.log est utilisée pour enregistrer les tentatives
	    global $NPDS_Prefix, $anonymous;
	    
	    if (!array_key_exists('uname', $userdataX)) 
	    {
	    	$compte = $anonymous;
	    } 
	    else 
	    {
	    	$compte = $userdataX['uname'];
	    }

	    if ((!$modoX) AND ($paramAFX > 0)) 
	    {
	        $sql = "SELECT COUNT(poster_ip) AS total FROM ".$NPDS_Prefix."posts WHERE post_time>'";
	        
	        if ($userdataX['uid'] != 1)
	        {
	      	    $sql2 = "' AND (poster_ip='$poster_ipX' OR poster_id='".$userdataX['uid']."')";
	      	}
	        else
	        {
	      	    $sql2 = "' AND poster_ip='$poster_ipX'";
	      	}

	        $timebase = date("Y-m-d H:i", time()+($gmtX*3600)-5400);
	        list($time90) = sql_fetch_row(sql_query($sql.$timebase.$sql2));
	        
	        if ($time90 > ($paramAFX*2)) 
	        {
	            logs::Ecr_Log("security", "Forum Anti-Flood : ".$compte, '');
	            error::forumerror(translate("Vous n'êtes pas autorisé à participer à ce forum"));
	        } 
	        else 
	        {
	            $timebase = date("Y-m-d H:i", time()+($gmtX*3600)-1800);
	            list($time30) = sql_fetch_row(sql_query($sql.$timebase.$sql2));
	            
	            if ($time30 > $paramAFX) 
	            {
	                logs::Ecr_Log("security", "Forum Anti-Flood : ".$compte, '');
	                error::forumerror(translate("Vous n'êtes pas autorisé à participer à ce forum"));
	            }
	        }
	    }
	}

}
