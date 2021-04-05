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


/*
 * forumupload
 */
class forumupload {


    /**
     * [control_efface_post description]
     * @param  [type] $apli     [description]
     * @param  [type] $post_id  [description]
     * @param  [type] $topic_id [description]
     * @param  [type] $IdForum  [description]
     * @return [type]           [description]
     */
    public static function control_efface_post($apli, $post_id, $topic_id, $IdForum) 
    {
        global $upload_table, $NPDS_Prefix;
        
        include ("modules/upload/include_forum/upload.conf.forum.php");
        
        $sql1 = "SELECT att_id, att_name, att_path FROM ".$NPDS_Prefix."$upload_table WHERE apli='$apli' AND";
        $sql2 = "DELETE FROM ".$NPDS_Prefix."$upload_table WHERE apli='$apli' AND";
        
        if ($IdForum != '') 
        {
            $sql1 .= " forum_id = '$IdForum'";
            $sql2 .= " forum_id = '$IdForum'";
        } 
        elseif ($post_id != '') 
        {
            $sql1 .= " post_id = '$post_id'";
            $sql2 .= " post_id = '$post_id'";
        } 
        elseif ($topic_id != '') 
        {
            $sql1 .= " topic_id = '$topic_id'";
            $sql2 .= " topic_id = '$topic_id'";
        }

        $result = sql_query($sql1);
        
        while(list($att_id, $att_name, $att_path) = sql_fetch_row($result))
        {
            $fic = $DOCUMENTROOT.$att_path.$att_id.".".$apli.".".$att_name;
            @unlink($fic);
        }

        @sql_query($sql2);
    }

}
