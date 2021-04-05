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

use npds\error\error;


/*
 * forumposts
 */
class forumposts {


    /**
     * Retourne une chaine des id des contributeurs du sujet
     * @param  [type] $fid [description]
     * @param  [type] $tid [description]
     * @return [type]      [description]
     */
    function get_contributeurs($fid, $tid) 
    {
        global $NPDS_Prefix;
           
        $rowQ1 = Q_Select("SELECT DISTINCT poster_id FROM ".$NPDS_Prefix."posts WHERE topic_id='$tid' AND forum_id='$fid'",2);
        
        $posterids = '';
        
        foreach($rowQ1 as $contribs) 
        {
            foreach($contribs as $contrib) 
            {
                $posterids .= $contrib.' ';
            }
        }
           
        return chop($posterids);
    }

    /**
     * [get_total_posts description]
     * @param  [type] $fid  [description]
     * @param  [type] $tid  [description]
     * @param  [type] $type [description]
     * @param  [type] $Mmod [description]
     * @return [type]       [description]
     */
    public static function get_total_posts($fid, $tid, $type, $Mmod) 
    {
        global $NPDS_Prefix;
        
        if ($Mmod)
        {
               $post_aff = '';
        }
        else
        {
            $post_aff = " AND post_aff='1'";
        }
        
        switch($type) 
        {
            case 'forum':
                $sql = "SELECT COUNT(*) AS total FROM ".$NPDS_Prefix."posts WHERE forum_id='$fid'$post_aff";
            break;

            case 'topic':
                $sql = "SELECT COUNT(*) AS total FROM ".$NPDS_Prefix."posts WHERE topic_id='$tid' AND forum_id='$fid' $post_aff";
            break;

            case 'user':
                error::forumerror('0031');
            break;
        }

        if (!$result = sql_query($sql))
        {
               return "ERROR";
        }

        if (!$myrow = sql_fetch_assoc($result))
        {
            return "0";
        }

        sql_free_result($result);
        
        return $myrow['total'];
    }

}
