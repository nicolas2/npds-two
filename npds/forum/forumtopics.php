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

use npds\date\date;
use npds\cache\cache;


/*
 * forumtopics
 */
class forumtopics {


	/**
	 * [get_total_topics description]
	 * @param  [type] $forum_id [description]
	 * @return [type]           [description]
	 */
	public static function get_total_topics($forum_id) 
	{
		global $NPDS_Prefix;
		   
		$sql = "SELECT COUNT(*) AS total FROM ".$NPDS_Prefix."forumtopics WHERE forum_id='$forum_id'";
		   
		if (!$result = sql_query($sql))
		{
		    return "ERROR";
		}
		   
		if (!$myrow = sql_fetch_assoc($result))
		{
		   	return "ERROR";
		}

		sql_free_result($result);
		
		return $myrow['total'];
	}

	/**
	 * Note : a revoir 
	 * [get_last_post description]
	 * @param  [type] $id   [description]
	 * @param  [type] $type [description]
	 * @param  [type] $cmd  [description]
	 * @param  [type] $Mmod [description]
	 * @return [type]       [description]
	 */
	public static function get_last_post($id, $type, $cmd, $Mmod) 
	{
		global $NPDS_Prefix;
		   
		// $Mmod ne sert plus - maintenu pour compatibilité
		switch($type) 
		{
		    case 'forum':
		        $sql1 = "SELECT topic_time, current_poster FROM ".$NPDS_Prefix."forumtopics WHERE forum_id = '$id' ORDER BY topic_time DESC LIMIT 0,1";
		        $sql2 = "SELECT uname FROM ".$NPDS_Prefix."users WHERE uid=";
		    break;

		    case 'topic':
		        $sql1 = "SELECT topic_time, current_poster FROM ".$NPDS_Prefix."forumtopics WHERE topic_id = '$id'";
		        $sql2 = "SELECT uname FROM ".$NPDS_Prefix."users WHERE uid=";
		    break;
		}
		   
		if (!$result = sql_query($sql1))
		{
		    return "ERROR";
		}

		if ($cmd == 'infos') 
		{
		    if (!$myrow = sql_fetch_row($result))
		    {
		      	$val = translate("Rien");
		    }
		    else 
		    {
		        $rowQ1 = cache::Q_Select($sql2."'".$myrow[1]."'", 3600);
		        $val = date::convertdate($myrow[0]).' '.userpopover($rowQ1[0]['uname'], 40);
		    }
		}
		sql_free_result($result);
		
		return $val;
	}

	/**
	 * [is_locked description]
	 * @param  [type]  $topic [description]
	 * @return boolean        [description]
	 */
	public static function is_locked($topic) 
	{
		global $NPDS_Prefix;
		
		$sql = "SELECT topic_status FROM ".$NPDS_Prefix."forumtopics WHERE topic_id='$topic'";
		
		if (!$r = sql_query($sql))
		{
			return true;
		}
		
		if (!$m = sql_fetch_assoc($r))
		{
			return false;
		}
		
		if (($m['topic_status'] == 1) or ($m['topic_status'] == 2))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * [does_exists description]
	 * @param  [type] $id   [description]
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public static function does_exists($id, $type) 
	{
	   	global $NPDS_Prefix;

	   	switch($type) 
	   	{
	      	case 'forum':
	           	$sql = "SELECT forum_id FROM ".$NPDS_Prefix."forums WHERE forum_id = '$id'";
	        break;
	      	case 'topic':
	           	$sql = "SELECT topic_id FROM ".$NPDS_Prefix."forumtopics WHERE topic_id = '$id'";
	        break;
	   	}

	   	if (!$result = sql_query($sql))
	   	{
	      	return(0);
	   	}

	   	if (!$myrow = sql_fetch_row($result))
	   	{
	      	return(0);
	   	}

	   	return(1);
	}

}
