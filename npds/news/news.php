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
namespace npds\news;

use npds\auth\auth;
use npds\language\language;
use npds\language\metalang;
use npds\subscribe\subscribe;
use npds\utility\str;
use npds\logs\logs;
use npds\utility\code;
use npds\edito\edito;
use npds\cache\cache;


/*
 * news
 */
class news {


    /**
     * Une des fonctions fondamentales de Npds Two
     * assure la gestion de la selection des News en fonctions des critères de publication
     * @param  [type] $type_req [description]
     * @param  [type] $sel      [description]
     * @param  [type] $storynum [description]
     * @param  [type] $oldnum   [description]
     * @return [type]           [description]
     */
    public static function news_aff($type_req, $sel, $storynum, $oldnum) 
    {
        global $NPDS_Prefix;
           
        // Astuce pour afficher le nb de News correct même si certaines News 
        // ne sont pas visibles (membres, groupe de membres)
        // En fait on * le Nb de News par le Nb de groupes
        $row_Q2 = cache::Q_select("SELECT COUNT(groupe_id) AS total FROM ".$NPDS_Prefix."groupes", 86400);
        
        // list(,$NumG)=each($row_Q2);
        $NumG = $row_Q2[0];

        if ($NumG['total'] < 2)
        { 
            $coef = 2;
        } 
        else 
        {
            $coef = $NumG['total'];
        }
        
        settype($storynum, "integer");
           
        if ($type_req == 'index') 
        {
            $Xstorynum = $storynum*$coef;
            $result = cache::Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel ORDER BY sid DESC LIMIT $Xstorynum", 3600);
            $Znum = $storynum;
        }
           
        if ($type_req == 'old_news') 
        {
            $Xstorynum = $oldnum*$coef;
            $result = cache::Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel ORDER BY time DESC LIMIT $storynum,$Xstorynum", 3600);
            $Znum = $oldnum;
        }

        if (($type_req == 'big_story') or ($type_req == 'big_topic')) 
        {
            $Xstorynum = $oldnum*$coef;
            $result = cache::Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel ORDER BY counter DESC LIMIT $storynum,$Xstorynum", 3600);
            $Znum = $oldnum;
        }

        if ($type_req == 'libre') 
        {
            $Xstorynum = $oldnum*$coef;
            $result = cache::Q_select("SELECT sid, catid, ihome, time FROM ".$NPDS_Prefix."stories $sel", 3600);
            $Znum = $oldnum;
        }

        if ($type_req == 'archive') 
        {
            $Xstorynum = $oldnum*$coef;
            $result = cache::Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel", 3600);
            $Znum = $oldnum;
        }

        $ibid = 0; 
        settype($tab, 'array');

        foreach($result as $myrow) 
        {
            $s_sid = $myrow['sid'];
            $catid = $myrow['catid'];
            $ihome = $myrow['ihome'];
            
            if(array_key_exists('time', $myrow))
            {
                $time = $myrow['time'];
            }
              
            if ($ibid == $Znum) 
            {
                break;
            }
              
            if ($type_req == "libre") 
            {
                $catid = 0;
            }
              
            if ($type_req == "archive") 
            {
                $ihome = 0;
            }
              
            if (auth::ctrl_aff($ihome, $catid)) 
            {
                if (($type_req == "index") or ($type_req == "libre"))
                {
                    $result2 = sql_query("SELECT sid, catid, aid, title, time, hometext, bodytext, comments, counter, topic, informant, notes FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='0'");
                }
                
                if ($type_req == "archive")
                {
                    $result2 = sql_query("SELECT sid, catid, aid, title, time, hometext, bodytext, comments, counter, topic, informant, notes FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='1'");
                }
                
                if ($type_req == "old_news")
                {
                    $result2 = sql_query("SELECT sid, title, time, comments, counter FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='0'");
                }
                
                if (($type_req == "big_story") or ($type_req == "big_topic"))
                {
                    $result2 = sql_query("SELECT sid, title FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='0'");
                }

                $tab[$ibid] = sql_fetch_row($result2);
                
                if (is_array($tab[$ibid])) 
                {
                    $ibid++;
                }
            }
        }
        @sql_free_result($result);
        
        return $tab;
    }

    /**
     * [aff_news description]
     * @param  [type] $op      [description]
     * @param  [type] $catid   [description]
     * @param  [type] $marqeur [description]
     * @return [type]          [description]
     */
    public static function aff_news($op, $catid, $marqeur) 
    {
        $url = $op;
        
        if ($op == 'edito-newindex') 
        {
            if ($marqeur == 0)
            { 
                edito::aff_edito();
            }
            
            $op = 'news';
        }
        
        if ($op == "newindex") 
        {
            if ($catid == '')
            {
                $op = 'news';
            }
            else 
            {
                $op = 'categories';
            }
        }
        
        if ($op == 'newtopic')
        {
             $op = 'topics';
        }

        
        if ($op == 'newcategory')
        {
            $op = 'categories';
        }
        
        $news_tab = static::prepa_aff_news($op, $catid, $marqeur);
        $story_limit = 0;
       
        // si le tableau $news_tab est vide alors return 
        if(is_null($news_tab)) 
        {
            return;
        }
       
        $newscount = sizeof($news_tab);
        while ($story_limit < $newscount) 
        {
            $story_limit++;
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
            
            themeindex($aid, $informant, $datetime, $title, $counter, $topic, $hometext, $notes, $morelink, $topicname, $topicimage, $topictext, $s_id);
        }

        $transl1 = translate("Page suivante");
        $transl2 = translate("Home");
        
        global $storyhome, $cookie;
        if (isset($cookie[3]))
        {
            $storynum = $cookie[3];
        }
        else
        {
            $storynum = $storyhome;
        }

        if ($op == 'categories') 
        {
            if (sizeof($news_tab) == $storynum) 
            {
                $marqeur = $marqeur+sizeof($news_tab);
                echo '
                <div class="text-right"><a href="index.php?op='.$url.'&amp;catid='.$catid.'&amp;marqeur='.$marqeur.'" class="page_suivante" >'.$transl1.'<i class="fa fa-chevron-right fa-lg ml-2" title="'.$transl1.'" data-toggle="tooltip"></i></a></div>';
            } 
            else 
            {
                if ($marqeur >= $storynum)
                {
                    echo '
                    <div class="text-right"><a href="index.php?op='.$url.'&amp;catid='.$catid.'&amp;marqeur=0" class="page_suivante" title="'.$transl2.'">'.$transl2.'</a></div>';
                }
            }
        }
           
        if ($op == 'news') 
        {
            if (sizeof($news_tab) == $storynum) 
            {
                $marqeur = $marqeur+sizeof($news_tab);
                echo '
                    <div class="text-right"><a href="index.php?op='.$url.'&amp;catid='.$catid.'&amp;marqeur='.$marqeur.'" class="page_suivante" >'.$transl1.'<i class="fa fa-chevron-right fa-lg ml-2" title="'.$transl1.'" data-toggle="tooltip"></i></a></div>';
            } 
            else 
            {
                if ($marqeur >= $storynum)
                {
                    echo '
                    <div class="text-right"><a href="index.php?op='.$url.'&amp;catid='.$catid.'&amp;marqeur=0" class="page_suivante" title="'.$transl2.'">'.$transl2.'</a></div>';
                }
            }
        }
           
        if ($op == 'topics') 
        {
            if (sizeof($news_tab) == $storynum) 
            {
                $marqeur = $marqeur+sizeof($news_tab);
                echo '
                    <div align="right"><a href="index.php?op=newtopic&amp;topic='.$topic.'&amp;marqeur='.$marqeur.'" class="page_suivante" >'.$transl1.'<i class="fa fa-chevron-right fa-lg ml-2" title="'.$transl1.'" data-toggle="tooltip"></i></a></div>';
            } 
            else 
            {
                if ($marqeur >= $storynum)
                {
                    echo '
                    <div class="text-right"><a href="index.php?op=newtopic&amp;topic='.$topic.'&amp;marqeur=0" class="page_suivante" title="'.$transl2.'">'.$transl2.'</a></div>';
                }
            }
        }
    }

    /**
     * Prépare, serialize et stock dans un tableau les news répondant aux critères
     * $op="" ET $catid="" : les news 
     * $op="categories" ET $catid="catid" : les news de la catégorie catid 
     * $op="article" ET $catid=ID_X : l'article d'ID X 
     * 
     * Les news des sujets : 
     * $op="topics" ET $catid="topic"
     * @param  [type] $op      [description]
     * @param  [type] $catid   [description]
     * @param  [type] $marqeur [description]
     * @return [type]          [description]
     */
    public static function prepa_aff_news($op, $catid, $marqeur) 
    {
        global $NPDS_Prefix, $storyhome, $topicname, $topicimage, $topictext, $datetime, $cookie;
           
        if (isset($cookie[3]))
        {
            $storynum = $cookie[3];
        }
        else
        {
            $storynum = $storyhome;
        }
           
        if ($op == "categories") 
        {
            sql_query("UPDATE ".$NPDS_Prefix."stories_cat SET counter=counter+1 WHERE catid='$catid'");
            
            settype($marqeur, "integer");
            
            if (!isset($marqeur)) 
            {
                $marqeur = 0;
            }
            
            $xtab = static::news_aff("libre", "WHERE catid='$catid' AND archive='0' ORDER BY sid DESC LIMIT $marqeur,$storynum", "", "-1");
            $storynum = sizeof($xtab);
        } 
        elseif ($op == "topics") 
        {
            settype($marqeur, "integer");
            
            if (!isset($marqeur)) 
            {
                $marqeur = 0;
            }
            
            $xtab = static::news_aff("libre", "WHERE topic='$catid' AND archive='0' ORDER BY sid DESC LIMIT $marqeur,$storynum", "", "-1");
            $storynum = sizeof($xtab);
        } 
        elseif ($op == "news") 
        {
            settype($marqeur, "integer");
            
            if (!isset($marqeur)) 
            {
                $marqeur = 0;
            }
            
            $xtab = static::news_aff("libre", "WHERE ihome!='1' AND archive='0' ORDER BY sid DESC LIMIT $marqeur,$storynum", "", "-1");
            $storynum = sizeof($xtab);
        } 
        elseif ($op == "article") 
        {
            $xtab = static::news_aff("index", "WHERE ihome!='1' AND sid='$catid'", 1, "");
        } 
        else 
        {
            $xtab = static::news_aff("index", "WHERE ihome!='1' AND archive='0'", $storynum, "");
        }
           
        $story_limit = 0;
           
        while (($story_limit < $storynum) and ($story_limit < sizeof($xtab))) 
        {
            list($s_sid, $catid, $aid, $title, $time, $hometext, $bodytext, $comments, $counter, $topic, $informant, $notes) = $xtab[$story_limit];
              
            $story_limit++;
            
            $printP = '<a href="print.php?sid='.$s_sid.'" class="mr-3" title="'.translate("Page spéciale pour impression").'" data-toggle="tooltip" ><i class="fa fa-lg fa-print"></i></a>&nbsp;';
            
            $sendF = '<a href="friend.php?op=FriendSend&amp;sid='.$s_sid.'" class="mr-3" title="'.translate("Envoyer cet article à un ami").'" data-toggle="tooltip" ><i class="fa fa-lg fa-at"></i></a>';
            
            topics::getTopics($s_sid);
            
            $title = language::aff_langue(stripslashes($title));
            $hometext = language::aff_langue(stripslashes($hometext));
            $notes = language::aff_langue(stripslashes($notes));
            $bodycount = strlen(strip_tags(language::aff_langue($bodytext), '<img>'));
            
            if ($bodycount > 0) 
            {
                $bodycount = strlen(strip_tags(language::aff_langue($bodytext)));
                
                if ($bodycount > 0 )
                {
                    $morelink[0] = str::wrh($bodycount).' '.translate("caractères de plus");
                }
                else
                {
                    $morelink[0] = ' ';
                }

                $morelink[1] = ' <a href="article.php?sid='.$s_sid.'" >'.translate("Lire la suite...").'</a>';
            } 
            else 
            {
                $morelink[0] = '';
                $morelink[1] = '';
            }

            if ($comments == 0) 
            {
                $morelink[2] = 0;
                $morelink[3] = '<a href="article.php?sid='.$s_sid.'" class="mr-3"><i class="far fa-comment fa-lg" title="'.translate("Commentaires ?").'" data-toggle="tooltip"></i></a>';
            } 
            elseif ($comments == 1) 
            {
                $morelink[2] = $comments;
                $morelink[3] = '<a href="article.php?sid='.$s_sid.'" class="mr-3"><i class="far fa-comment fa-lg" title="'.translate("Commentaire").'" data-toggle="tooltip"></i></a>';
            } 
            else 
            {
                $morelink[2] = $comments;
                $morelink[3] = '<a href="article.php?sid='.$s_sid.'" class="mr-3" ><i class="far fa-comment fa-lg" title="'.translate("Commentaires").'" data-toggle="tooltip"></i></a>';
            }

            $morelink[4] = $printP;
            $morelink[5] = $sendF;
            $sid = $s_sid;
            
            if ($catid != 0) 
            {
                $resultm = sql_query("SELECT title FROM ".$NPDS_Prefix."stories_cat WHERE catid='$catid'");
                list($title1) = sql_fetch_row($resultm);
                
                $title = $title;
                
                // Attention à cela aussi
                $morelink[6] = ' <a href="index.php?op=newcategory&amp;catid='.$catid.'">&#x200b;'.language::aff_langue($title1).'</a>';
            } 
            else
            {
                $morelink[6] = '';
            }
               
            $news_tab[$story_limit]['aid'] = serialize($aid);
            $news_tab[$story_limit]['informant'] = serialize($informant);
            $news_tab[$story_limit]['datetime'] = serialize($time);
            $news_tab[$story_limit]['title'] = serialize($title);
            $news_tab[$story_limit]['counter'] = serialize($counter);
            $news_tab[$story_limit]['topic'] = serialize($topic);
            $news_tab[$story_limit]['hometext'] = serialize(metalang::meta_lang(code::aff_code($hometext)));
            $news_tab[$story_limit]['notes'] = serialize(metalang::meta_lang(code::aff_code($notes)));
            $news_tab[$story_limit]['morelink'] = serialize($morelink);
            $news_tab[$story_limit]['topicname'] = serialize($topicname);
            $news_tab[$story_limit]['topicimage'] = serialize($topicimage);
            $news_tab[$story_limit]['topictext'] = serialize($topictext);
            $news_tab[$story_limit]['id'] = serialize($s_sid);
        } 

        if (isset($news_tab))
        {      
            return $news_tab;
        }
    }

    /**
     * [automatednews description]
     * @return [type] [description]
     */
    public static function automatednews() 
    {
        global $gmt, $NPDS_Prefix;

        $today = getdate(time()+((integer)$gmt*3600));
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
        $hour = $today['hours'];
        $min = $today['minutes'];
           
        $result = sql_query("SELECT anid, date_debval FROM ".$NPDS_Prefix."autonews WHERE date_debval LIKE '$year-$month%'");
          
        while(list($anid, $date_debval) = sql_fetch_row($result)) 
        {
            preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $date_debval, $date);
              
            if (($date[1] <= $year) 
                AND ($date[2] <= $month) 
                AND ($date[3] <= $day)) 
            {
                if (($date[4] < $hour) 
                    AND ($date[5] >= $min) 
                    OR ($date[4] <= $hour) 
                    AND ($date[5] <= $min) 
                    OR (($day-$date[3]) >= 1)) 
                {
                    $result2 = sql_query("SELECT catid, aid, title, hometext, bodytext, topic, informant, notes, ihome, date_finval, auto_epur FROM ".$NPDS_Prefix."autonews WHERE anid='$anid'");
                    
                    while (list($catid, $aid, $title, $hometext, $bodytext, $topic, $author, $notes, $ihome, $date_finval, $epur) = sql_fetch_row($result2)) 
                    {
                        $subject = stripslashes(str::FixQuotes($title));
                        $hometext = stripslashes(str::FixQuotes($hometext));
                        $bodytext = stripslashes(str::FixQuotes($bodytext));
                        $notes = stripslashes(str::FixQuotes($notes));
                       
                        sql_query("INSERT INTO ".$NPDS_Prefix."stories VALUES (NULL, '$catid', '$aid', '$subject', now(), '$hometext', '$bodytext', '0', '0', '$topic', '$author', '$notes', '$ihome', '0', '$date_finval', '$epur')");
                        sql_query("DELETE FROM ".$NPDS_Prefix."autonews WHERE anid='$anid'");
                        
                        global $subscribe;
                        if ($subscribe)
                        {
                            subscribe::subscribe_mail('topic', $topic, '', $subject, '');
                        }

                        // Réseaux sociaux
                        if (file_exists('modules/npds_twi/npds_to_twi.php')) 
                        {
                            include ('modules/npds_twi/npds_to_twi.php');
                        }
                        if (file_exists('modules/npds_fbk/npds_to_fbk.php')) 
                        {
                            include ('modules/npds_twi/npds_to_fbk.php');
                        }
                        // Réseaux sociaux
                    }
                }
            }
        }
           
        // Purge automatique
        $result = sql_query("SELECT sid, date_finval, auto_epur FROM ".$NPDS_Prefix."stories WHERE date_finval LIKE '$year-$month%'");
        
        while(list($sid, $date_finval, $epur) = sql_fetch_row($result)) 
        {
            preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $date_finval, $date);
              
            if (($date[1] <= $year) 
                AND ($date[2] <= $month) 
                AND ($date[3] <= $day)) 
            {
                if (($date[4] < $hour) 
                    AND ($date[5] >= $min) 
                    OR ($date[4] <= $hour) 
                    AND ($date[5] <= $min)) 
                {
                    if ($epur == 1) 
                    {
                        sql_query("DELETE FROM ".$NPDS_Prefix."stories WHERE sid='$sid'");
                        
                        if (file_exists("modules/comments/config/article.php")) 
                        {
                            include ("modules/comments/config/article.php");
                        
                            sql_query("DELETE FROM ".$NPDS_Prefix."posts WHERE forum_id='$forum' AND topic_id='$topic'");
                        }
                    
                        logs::Ecr_Log('security', "removeStory ($sid, epur) by automated epur : system", '');
                    }
                    else 
                    {
                        sql_query("UPDATE ".$NPDS_Prefix."stories SET archive='1' WHERE sid='$sid'");
                    }
                }
            }
        }
    }


    /**
     * Génération des fichiers ultramode.txt et net2zone.txt dans /cache
     * @return [type] [description]
     */
    public static function ultramode() 
    {
        global $NPDS_Prefix;
        global $nuke_url, $storyhome;
           
        $ultra = "storage/cache/ultramode.txt";
        $netTOzone = "storage/cache/net2zone.txt";
        
        $file = fopen("$ultra", "w");
        $file2 = fopen("$netTOzone", "w");
        
        fwrite($file, "General purpose self-explanatory file with news headlines\n");
        
        $storynum = $storyhome;
        $xtab = static::news_aff('index', "WHERE ihome='0' AND archive='0'", $storyhome, '');
        $story_limit = 0;
        
        while (($story_limit < $storynum) and ($story_limit < sizeof($xtab))) 
        {
            list($sid, $catid, $aid, $title, $time, $hometext, $bodytext, $comments, $counter, $topic, $informant, $notes) = $xtab[$story_limit];
              
            $story_limit++;
            $rfile2 = sql_query("SELECT topictext, topicimage FROM ".$NPDS_Prefix."topics WHERE topicid='$topic'");
            list($topictext, $topicimage) = sql_fetch_row($rfile2);
              
            $hometext = metalang::meta_lang(strip_tags($hometext));
              
            fwrite($file, "%%\n$title\n$nuke_url/article.php?sid=$sid\n$time\n$aid\n$topictext\n$hometext\n$topicimage\n");
            
            fwrite($file2, "<NEWS>\n<NBX>$topictext</NBX>\n<TITLE>".stripslashes($title)."</TITLE>\n<SUMMARY>$hometext</SUMMARY>\n<URL>$nuke_url/article.php?sid=$sid</URL>\n<AUTHOR>".$aid."</AUTHOR>\n</NEWS>\n\n");
        }

        fclose($file);
        fclose($file2);
    }

}
