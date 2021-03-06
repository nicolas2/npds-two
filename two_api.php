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
use npds\messenger\messenger;
use npds\cache\cache;
use npds\utility\str;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

/**
 * [stripslashes_deep description]
 * @param  [type] $value [description]
 * @return [type]        [description]
 */
function stripslashes_deep($value) 
{
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    
    return $value;
}

/**
 * [api_getusers description]
 * @return [type] [description]
 */
function api_getusers() 
{
    global $una;

    if ($una == 'Tous') 
    {
        $where = '';
    } 
    else
    {
        $where = " WHERE uname='".$una."'"; 
    }
    
    settype($ap_users, 'array');
    
    $res = sql_query("SELECT uid, uname, name, user_avatar, user_regdate FROM ".$NPDS_Prefix."users ".$where);
    
    while($ap_user = sql_fetch_assoc($res))
    {
        $ap_users[] = $ap_user;
    }
    
    print '{"user":'.json_encode( $ap_users ).'}';
    echo $_GET['jsoncallback'].'('.$ap_users.')';
}

/**
 * [api_getgroups description]
 * @return [type] [description]
 */
function api_getgroups() 
{
    global $gna;
    
    if ($gna == 'Tous') 
    {
        $where = '';
    } 
    else
    {
        $where = " where groupe_name='".$gna."'"; 
    }
    
    settype($ap_groups,'array');
    
    $res = sql_query("SELECT groupe_id, groupe_name, groupe_description FROM ".$NPDS_Prefix."groupes ".$where);
    
    while($ap_group = sql_fetch_assoc($res))
    {
        $ap_groups[] = $ap_group;
    }
    
    //$ap_users = json_encode($ap_users);
    print json_encode($ap_groups);
    //echo $_GET['jsoncallback'].'('.$ap_groups.')';
}

/**
 * [api_getusers_post description]
 * @return [type] [description]
 */
function api_getusers_post() 
{
    global $upa;

    $res = sql_query("SELECT count(*) as total FROM ".$NPDS_Prefix."forumtopics WHERE topic_poster = $upa ");
    $ap_user_post = sql_fetch_assoc($res);

    $res_1 = sql_query("SELECT * FROM ".$NPDS_Prefix."forumtopics WHERE topic_poster = $upa ");
    
    while($ap_top = sql_fetch_assoc($res_1))
    {
        $ap_tops[] = $ap_top;
        $ap_fo = $ap_top['topic_id'];

        $res_2 = sql_query("select * from forumtopics ft LEFT JOIN posts ON ft.topic_id = posts.topic_id LEFT JOIN forums ON ft.forum_id = forums.forum_id WHERE ft.topic_id=$ap_fo GROUP BY ft.topic_id");
        
        while($ap_for = sql_fetch_assoc($res_2))
        {
            $ap_fors[] = $ap_for;
        }
    }

    print '[ {"user_post":'.json_encode( $ap_user_post['total'] ).'}'.',{"user_top":'.json_encode( $ap_tops).'},{"topic_detail":'.json_encode($ap_fors).'} ]';
}

/**
 * data (*) de download categorie x ou tout
 * @return [type] [description]
 */
function api_getdownload() 
{
    global $dna;
    
    if ($dna == 'Tous') 
    {
        $where = '';
    } 
    else
    {
        $where = " where dcategory='".$dna."'"; 
    }
    
    settype($ap_dows, 'array');
    
    $res = sql_query("select * from ".$NPDS_Prefix."downloads ".$where);
    
    while($ap_dow = sql_fetch_assoc($res))
    {
        $ap_dows[] = $ap_dow;
    }

    $ap_dows = stripslashes_deep($ap_dows);
    
    print json_encode($ap_dows);
}

/**
 * [alerte_api description]
 * @return [type] [description]
 */
function alerte_api()
{
    global $NPDS_Prefix;
      
    if (isset($_POST['id'])) 
    {
        $id = $_POST['id'];

        $result = sql_query("SELECT * FROM ".$NPDS_Prefix."fonctions WHERE fid='$id'");
         
        if(isset($result))
        {
            $row = sql_fetch_assoc($result);
            if (count($row) > 0) 
            {
                $data = $row;
            }
        }
        echo json_encode($data);
    }  
}

/**
 * [alerte_update description]
 * @return [type] [description]
 */
function alerte_update() 
{  
    global $NPDS_Prefix, $admin;

    $Xadmin = base64_decode($admin);
    $Xadmin = explode(':', $Xadmin);
    $aid = urlencode($Xadmin[0]);

    if (isset($_POST['id']) and  isset($aid)) 
    {
        $id = $_POST['id'];

        $result = sql_query("SELECT * FROM ".$NPDS_Prefix."fonctions WHERE fid=".$id."");
        $row = sql_fetch_assoc($result);
        $newlecture = strtolower($aid).'|'.$row['fdroits1_descr'];
        
        sql_query("UPDATE ".$NPDS_Prefix."fonctions SET fdroits1_descr='".$newlecture."' WHERE fid=".$id."");
    }

    header('Location: '.$_SERVER['HTTP_REFERER']);
}


switch ($op) 
{
    case "api_getusers":
        api_getusers();
    break;

    case "api_getgroups":
        api_getgroups();
    break;

    case "api_getusers_post":
        api_getusers_post();
    break;

    case "api_getdownload":
        api_getdownload();
    break;

    case "alerte_api":
        alerte_api();
    break; 

    case "alerte_update":
        alerte_update();
    break;

    // Instant Members Message
    case 'instant_message':
        messenger::Form_instant_message($to_userid);
    break;

    case 'write_instant_message':
        settype($copie, 'string');
        settype($messages, 'string');
        
        if ($user) 
        {
            $rowQ1 = cache::Q_Select("SELECT uid FROM ".$NPDS_Prefix."users WHERE uname='$cookie[1]'", 3600);
            //list(, $uid) = each($rowQ1); // Note : each
            $uid = $rowQ1[0];
            
            $from_userid = $uid['uid'];
            
            if (($subject != '') or ($message != '')) 
            {
                $subject = str::FixQuotes($subject).'';
                $messages = str::FixQuotes($messages).'';
               
                messenger::writeDB_private_message($to_userid, '', $subject, $from_userid, $message, $copie);
            }
        }

        Header("Location: index.php");
    break;
   
    // Instant Members Message
    // Purge Chat Box
    case 'admin_chatbox_write':
        if ($admin) 
        {
            if ($chatbox_clearDB == 'OK') 
            {
                sql_query("DELETE FROM ".$NPDS_Prefix."chatbox WHERE date <= ".(time()-(60*5))."");
            }
        }

        Header("Location: index.php");
    break;
    // Purge Chat Box             
}
