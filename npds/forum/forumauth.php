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

use npds\views\theme;
use npds\language\language;


/*
 * forumauth
 */
class forumauth {


	/**
	 * [member_qualif description]
	 * @param  [type] $poster [description]
	 * @param  [type] $posts  [description]
	 * @param  [type] $rank   [description]
	 * @return [type]         [description]
	 */
	public static function member_qualif($poster, $posts, $rank) 
	{
		global $anonymous;
		   
		$tmp = '';
		   
		if ($ibid = theme::theme_image('forum/rank/post.gif')) 
		{
			$imgtmpP = $ibid;
		} 
		else 
		{
			$imgtmpP = 'assets/images/forum/rank/post.gif';
		}
		
		$tmp = '<img class="n-smil" src="'.$imgtmpP.'" alt="" />'.$posts.'&nbsp;';
		   
		if ($poster != $anonymous) 
		{
		    $nux = 0;
		    
		    if ($posts >= 10 and $posts < 30) 
		    {
		    	$nux = 1;
		    }
		    
		    if ($posts >= 30 and $posts < 100) 
		    {
		    	$nux = 2;
		    }
		    
		    if ($posts >= 100 and $posts < 300) 
		    {
		    	$nux = 3;
		    }
		    
		    if ($posts >= 300 and $posts < 1000) 
		    {
		    	$nux = 4;
		    }
		    
		    if ($posts >= 1000) 
		    {
		    	$nux = 5;
		    }
		    
		    for ($i=0; $i<$nux; $i++) 
		    {
		        $tmp.='<i class="fa fa-star-o text-success mr-1"></i>';
		    }
		    
		    if ($rank) 
		    {
		        if ($ibid = theme::theme_image("forum/rank/".$rank.".gif") 
		        	or $ibid = theme::theme_image("forum/rank/".$rank.".png")) 
		        {
		        	$imgtmpA = $ibid;
		        } 
		        else 
		        {
		        	$imgtmpA = "assets/images/forum/rank/".$rank.".png";
		        }

		        $rank = 'rank'.$rank;
		        
		        global $$rank;
		        $tmp .= '<div class="my-2"><img class="n-smil" src="'.$imgtmpA.'" alt="logo rôle" />&nbsp;'.language::aff_langue($$rank).'</div>';
		    }
		}
		
		return $tmp;
	}

	/**
	 * [autorize description]
	 * @return [type] [description]
	 */
	public static function autorize() 
	{
		global $apli, $IdPost, $IdTopic, $IdForum, $user, $NPDS_Prefix;
		   
		list($poster_id) = sql_fetch_row(sql_query("SELECT poster_id FROM ".$NPDS_Prefix."posts WHERE post_id='$IdPost' AND topic_id='$IdTopic'"));
		
		$Mmod = false;
		   
		if ($poster_id) 
		{
		    $myrow = sql_fetch_assoc(sql_query("SELECT forum_moderator FROM ".$NPDS_Prefix."forums WHERE (forum_id='$IdForum')"));
		    
		    if ($myrow) 
		    {
		        $moderator = static::get_moderator($myrow['forum_moderator']);
		        $moderator = explode(' ', $moderator);
		        
		        if (isset($user)) 
		        {
		            $userX = base64_decode($user);
		            $userdata = explode(":", $userX);
		            
		            for ($i = 0; $i < count($moderator); $i++) 
		            {
		                if (($userdata[1] == $moderator[$i])) 
		                { 
		               	    $Mmod = true; 
		               	    break;
		                }
		            }

		            if ($userdata[0] == $poster_id)
		            {
		            	$Mmod = true;
		            }
		        }
		    }
		}
		
		return $Mmod;
	}

    /**
     * [user_is_moderator description]
     * @param  [type] $uidX          [description]
     * @param  [type] $passwordX     [description]
     * @param  [type] $forum_accessX [description]
     * @return [type]                [description]
     */
    public static function user_is_moderator($uidX, $passwordX, $forum_accessX) 
    {
        global $NPDS_Prefix;
        
        $result1 = sql_query("SELECT pass FROM ".$NPDS_Prefix."users WHERE uid='$uidX'");
        $result2 = sql_query("SELECT level FROM ".$NPDS_Prefix."users_status WHERE uid='$uidX'");
        
        $userX = sql_fetch_assoc($result1);
        $password = $userX['pass'];
        $userX = sql_fetch_assoc($result2);
        
        if ((md5($password) == $passwordX) 
            and ($forum_accessX <= $userX['level']) 
            and ($userX['level'] > 1))
        {
            return $userX['level'];
        }
        else
        {
            return false;
        }
    }

    /**
     * [get_moderator description]
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public static function get_moderator($user_id) 
    {
        global $NPDS_Prefix;
        
        $user_id = str_replace(",", "' or uid='", $user_id);
        
        if ($user_id == 0)
        {
            return("None");
        }
        
        $rowQ1 = Q_Select("SELECT uname FROM ".$NPDS_Prefix."users WHERE uid='$user_id'", 3600);
        $modslist = '';
        
        foreach($rowQ1 as $modnames) 
        {
            foreach($modnames as $modname) 
            {
                $modslist.= $modname.' ';
            }
        }
        
        return chop($modslist);
    }

}
