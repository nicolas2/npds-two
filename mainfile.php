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

include("vendor/autoload.php");

use npds\session\session;
use npds\cache\cacheManager;
use npds\cache\cacheEmpty;
use npds\cookie\cookie;
use npds\utility\str;
use npds\time\time;

include("lib/grab_globals.php");
include("config/config.php");

include("lib/multi-langue.php");
include("language/$language/lang-$language.php");

include('npds/database/connexion.php');

Mysql_Connexion();

require_once("admin/auth.inc.php");

if (isset($user)) 
{
   $cookie = cookie::decode($user);
}

session::manage();

$tab_langue = make_tab_langue();

include("lib/metalang/metalang.php");

global $meta_glossaire;
$meta_glossaire = charg_metalang();

if (function_exists("date_default_timezone_set")) 
{
   date_default_timezone_set("Europe/Paris");
}







#autodoc Who_Online() : Qui est en ligne ? + message de bienvenue
function Who_Online() {
   list($content1, $content2)=Who_Online_Sub();
   return array($content1, $content2);
}

#autodoc Who_Online() : Qui est en ligne ? + message de bienvenue / SOUS-Fonction / Utilise Site_Load
function Who_Online_Sub() {
   global $user, $cookie;
   list($member_online_num, $guest_online_num)=site_load();
   $content1 = "$guest_online_num ".translate("visiteur(s) et")." $member_online_num ".translate("membre(s) en ligne.");
   if ($user) {
      $content2 = translate("Vous êtes connecté en tant que")." <b>".$cookie[1]."</b>";
   } else {
      $content2 = translate("Devenez membre privilégié en cliquant")." <a href=\"user.php?op=only_newuser\">".translate("ici")."</a>";
   }
   return array($content1, $content2);
}




#autodoc Site_Load() : Maintient les informations de NB connexion (membre, anonyme) - globalise la variable $who_online_num et maintient le fichier storage/cache/site_load.log &agrave; jour<br />Indispensable pour la gestion de la 'clean_limit' de SuperCache
function Site_Load() {
   global $NPDS_Prefix;
   global $SuperCache;
   // globalise la variable
   global $who_online_num;
   $guest_online_num = 0;
   $member_online_num = 0;
   $result = sql_query("SELECT COUNT(username) AS TheCount, guest FROM ".$NPDS_Prefix."session GROUP BY guest");
   while ($TheResult = sql_fetch_assoc($result)) {
      if ($TheResult['guest']==0)
         $member_online_num = $TheResult['TheCount'];
      else
         $guest_online_num = $TheResult['TheCount'];
   }
   $who_online_num = $guest_online_num + $member_online_num;
   if ($SuperCache) {
      $file=fopen("storage/cache/site_load.log", "w");
         fwrite($file, $who_online_num);
      fclose($file);
   }
   return array($member_online_num, $guest_online_num);
}

#autodoc req_stat() : Retourne un tableau contenant les nombres pour les statistiques du site (stats.php)
function req_stat() {
   global $NPDS_Prefix;
   // Les membres
   $result = sql_query("SELECT uid FROM ".$NPDS_Prefix."users");
   if ($result) {$xtab[0]=sql_num_rows($result);} else {$xtab[0]="0";}
   // Les Nouvelles (News)
   $result = sql_query("SELECT sid FROM ".$NPDS_Prefix."stories");
   if ($result) {$xtab[1]=sql_num_rows($result);} else {$xtab[1]="0";}
   // Les Critiques (Reviews))
   $result = sql_query("SELECT id FROM ".$NPDS_Prefix."reviews");
   if ($result) {$xtab[2]=sql_num_rows($result);} else {$xtab[2]="0";}
   // Les Forums
   $result = sql_query("SELECT forum_id FROM ".$NPDS_Prefix."forums");
   if ($result) {$xtab[3]=sql_num_rows($result);} else {$xtab[3]="0";}
   // Les Sujets (topics)
   $result = sql_query("SELECT topicid FROM ".$NPDS_Prefix."topics");
   if ($result) {$xtab[4]=sql_num_rows($result);} else {$xtab[4]="0";}
   // Nombre de pages vues
   $result = sql_query("SELECT count FROM ".$NPDS_Prefix."counter WHERE type='total'");
   if ($result) {list($totalz)=sql_fetch_row($result);}
   $totalz++;
   $xtab[5]=$totalz++;
   sql_free_result($result);
   return($xtab);
}



#autodoc secur_static($sec_type) : Pour savoir si le visiteur est un : membre ou admin (static.php et banners.php par exemple)
function secur_static($sec_type) {
   global $user, $admin;
   switch ($sec_type) {
      case 'member':
         if (isset($user)) {
            return true;
         } else {
            return false;
         }
      break;
      case 'admin':
         if (isset($admin)) {
            return true;
         } else {
            return false;
         }
      break;
   }
}










// Opentable - closetable
#autodoc ultramode() : Génération des fichiers ultramode.txt et net2zone.txt dans /cache
function ultramode() {
   global $NPDS_Prefix;
   global $nuke_url, $storyhome;
   $ultra = "storage/cache/ultramode.txt";
   $netTOzone = "storage/cache/net2zone.txt";
   $file = fopen("$ultra", "w");
   $file2 = fopen("$netTOzone", "w");
   fwrite($file, "General purpose self-explanatory file with news headlines\n");
   $storynum = $storyhome;
   $xtab=news_aff('index',"WHERE ihome='0' AND archive='0'",$storyhome,'');
   $story_limit=0;
   while (($story_limit<$storynum) and ($story_limit<sizeof($xtab))) {
      list($sid, $catid, $aid, $title, $time, $hometext, $bodytext, $comments, $counter, $topic, $informant, $notes) = $xtab[$story_limit];
      $story_limit++;
      $rfile2=sql_query("SELECT topictext, topicimage FROM ".$NPDS_Prefix."topics WHERE topicid='$topic'");
      list($topictext, $topicimage) = sql_fetch_row($rfile2);
      $hometext=meta_lang(strip_tags($hometext));
      fwrite($file, "%%\n$title\n$nuke_url/article.php?sid=$sid\n$time\n$aid\n$topictext\n$hometext\n$topicimage\n");
      fwrite($file2, "<NEWS>\n<NBX>$topictext</NBX>\n<TITLE>".stripslashes($title)."</TITLE>\n<SUMMARY>$hometext</SUMMARY>\n<URL>$nuke_url/article.php?sid=$sid</URL>\n<AUTHOR>".$aid."</AUTHOR>\n</NEWS>\n\n");
   }
   fclose($file);
   fclose($file2);
}















#autodoc formatTimestamp($time) : Formate un timestamp en fonction de la valeur de $locale (config/config.php) / si "nogmt" est concaténé devant la valeur de $time, le décalage gmt n'est pas appliqué
function formatTimestamp($time) {
   global $datetime, $locale, $gmt;
   $local_gmt=$gmt;
   setlocale (LC_TIME, aff_langue($locale));
   if (substr($time,0,5)=='nogmt') {
      $time=substr($time,5);
      $local_gmt=0;
   }
   preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$#', $time, $datetime);
   $datetime = strftime(translate("datestring"), mktime($datetime[4]+(integer)$local_gmt,$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
   return (ucfirst(htmlentities($datetime,ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML401,cur_charset)));
}

#autodoc formatAidHeader($aid) : Affiche URL et Email d'un auteur
function formatAidHeader($aid) {
   global $NPDS_Prefix;
   $holder = sql_query("SELECT url, email FROM ".$NPDS_Prefix."authors WHERE aid='$aid'");
   if ($holder) {
      list($url, $email) = sql_fetch_row($holder);
      if (isset($url)) {
         echo '<a href="'.$url.'" >'.$aid.'</a>';
      } elseif (isset($email)) {
         echo '<a href="mailto:'.$email.'" >'.$aid.'</a>';
      } else {
         echo $aid;
      }
   }
}



#autodoc ctrl_aff($ihome, $catid) : Gestion + fine des destinataires (-1, 0, 1, 2 -> 127, -127)
function ctrl_aff($ihome, $catid=0) {
   global $user;
   $affich=false;
   if ($ihome==-1 and (!$user)) {
      $affich=true;
   } elseif ($ihome==0) {
      $affich=true;
   } elseif ($ihome==1) {
      if ($catid>0) {
         $affich=false;
      } else {
         $affich=true;
      }
   } elseif (($ihome>1) and ($ihome<=127)) {
      $tab_groupe=valid_group($user);
      if ($tab_groupe) {
         foreach($tab_groupe as $groupevalue) {
            if ($groupevalue==$ihome) {
               $affich=true;
               break;
            }
         }
      }
   } else {
      if ($user) $affich=true;
   }
   return ($affich);
}



#autodoc news_aff($type_req, $sel, $storynum, $oldnum) : Une des fonctions fondamentales de NPDS / assure la gestion de la selection des News en fonctions des critères de publication
function news_aff($type_req, $sel, $storynum, $oldnum) {
   global $NPDS_Prefix;
   // Astuce pour afficher le nb de News correct même si certaines News ne sont pas visibles (membres, groupe de membres)
   // En fait on * le Nb de News par le Nb de groupes
   $row_Q2 = Q_select("SELECT COUNT(groupe_id) AS total FROM ".$NPDS_Prefix."groupes",86400);
   //   list(,$NumG)=each($row_Q2);
   $NumG=$row_Q2[0];

   if ($NumG['total']<2) $coef=2; else $coef=$NumG['total'];
   settype($storynum,"integer");
   if ($type_req=='index') {
      $Xstorynum=$storynum*$coef;
      $result = Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel ORDER BY sid DESC LIMIT $Xstorynum",3600);
      $Znum=$storynum;
   }
   if ($type_req=='old_news') {
      $Xstorynum=$oldnum*$coef;
      $result = Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel ORDER BY time DESC LIMIT $storynum,$Xstorynum",3600);
      $Znum=$oldnum;
   }
   if (($type_req=='big_story') or ($type_req=='big_topic')) {
      $Xstorynum=$oldnum*$coef;
      $result = Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel ORDER BY counter DESC LIMIT $storynum,$Xstorynum",3600);
      $Znum=$oldnum;
   }
   if ($type_req=='libre') {
      $Xstorynum=$oldnum*$coef;
      $result=Q_select("SELECT sid, catid, ihome, time FROM ".$NPDS_Prefix."stories $sel",3600);
      $Znum=$oldnum;
   }
   if ($type_req=='archive') {
      $Xstorynum=$oldnum*$coef;
      $result=Q_select("SELECT sid, catid, ihome FROM ".$NPDS_Prefix."stories $sel",3600);
      $Znum=$oldnum;
   }
   $ibid=0; settype($tab,'array');

  foreach($result as $myrow) {
   //   while(list(,$myrow) = each($result)) {
      $s_sid=$myrow['sid'];
      $catid=$myrow['catid'];
      $ihome=$myrow['ihome'];
      if(array_key_exists('time', $myrow))
         $time=$myrow['time'];
      if ($ibid==$Znum) {break;}
      if ($type_req=="libre") $catid=0;
      if ($type_req=="archive") $ihome=0;
      if (ctrl_aff($ihome, $catid)) {
         if (($type_req=="index") or ($type_req=="libre"))
            $result2 = sql_query("SELECT sid, catid, aid, title, time, hometext, bodytext, comments, counter, topic, informant, notes FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='0'");
         if ($type_req=="archive")
            $result2 = sql_query("SELECT sid, catid, aid, title, time, hometext, bodytext, comments, counter, topic, informant, notes FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='1'");
         if ($type_req=="old_news")
            $result2 = sql_query("SELECT sid, title, time, comments, counter FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='0'");
         if (($type_req=="big_story") or ($type_req=="big_topic"))
            $result2 = sql_query("SELECT sid, title FROM ".$NPDS_Prefix."stories WHERE sid='$s_sid' AND archive='0'");

         $tab[$ibid]=sql_fetch_row($result2);
         if (is_array($tab[$ibid])) {
            $ibid++;
        }
      }
   }
   @sql_free_result($result);
   return ($tab);
}

#autodoc prepa_aff_news($op,$catid) : Prépare, serialize et stock dans un tableau les news répondant aux critères<br />$op="" ET $catid="" : les news // $op="categories" ET $catid="catid" : les news de la catégorie catid //  $op="article" ET $catid=ID_X : l'article d'ID X // Les news des sujets : $op="topics" ET $catid="topic"
function prepa_aff_news($op,$catid,$marqeur) {
   global $NPDS_Prefix, $storyhome, $topicname, $topicimage, $topictext, $datetime, $cookie;
   if (isset($cookie[3]))
       $storynum = $cookie[3];
   else
       $storynum = $storyhome;
   if ($op=="categories") {
      sql_query("UPDATE ".$NPDS_Prefix."stories_cat SET counter=counter+1 WHERE catid='$catid'");
      settype($marqeur, "integer");
      if (!isset($marqeur)) {$marqeur=0;}
      $xtab=news_aff("libre","WHERE catid='$catid' AND archive='0' ORDER BY sid DESC LIMIT $marqeur,$storynum","","-1");
      $storynum=sizeof($xtab);
   } elseif ($op=="topics") {
      settype($marqeur, "integer");
      if (!isset($marqeur)) {$marqeur=0;}
      $xtab=news_aff("libre","WHERE topic='$catid' AND archive='0' ORDER BY sid DESC LIMIT $marqeur,$storynum","","-1");
      $storynum=sizeof($xtab);
   } elseif ($op=="news") {
      settype($marqeur, "integer");
      if (!isset($marqeur)) {$marqeur=0;}
      $xtab=news_aff("libre","WHERE ihome!='1' AND archive='0' ORDER BY sid DESC LIMIT $marqeur,$storynum","","-1");
      $storynum=sizeof($xtab);
   } elseif ($op=="article") {
      $xtab=news_aff("index","WHERE ihome!='1' AND sid='$catid'",1,"");
   } else {
      $xtab=news_aff("index","WHERE ihome!='1' AND archive='0'",$storynum,"");
   }
   $story_limit=0;
   while (($story_limit<$storynum) and ($story_limit<sizeof($xtab))) {
      list($s_sid, $catid, $aid, $title, $time, $hometext, $bodytext, $comments, $counter, $topic, $informant, $notes) = $xtab[$story_limit];
      $story_limit++;
      $printP = '<a href="print.php?sid='.$s_sid.'" class="mr-3" title="'.translate("Page spéciale pour impression").'" data-toggle="tooltip" ><i class="fa fa-lg fa-print"></i></a>&nbsp;';
      $sendF = '<a href="friend.php?op=FriendSend&amp;sid='.$s_sid.'" class="mr-3" title="'.translate("Envoyer cet article à un ami").'" data-toggle="tooltip" ><i class="fa fa-lg fa-at"></i></a>';
      getTopics($s_sid);
      $title = aff_langue(stripslashes($title));
      $hometext = aff_langue(stripslashes($hometext));
      $notes = aff_langue(stripslashes($notes));
      $bodycount = strlen(strip_tags(aff_langue($bodytext),'<img>'));
      if ($bodycount > 0) {
         $bodycount = strlen(strip_tags(aff_langue($bodytext)));
         if ($bodycount > 0 )
            $morelink[0]=str::wrh($bodycount).' '.translate("caractères de plus");
         else
            $morelink[0]=' ';
         $morelink[1]=' <a href="article.php?sid='.$s_sid.'" >'.translate("Lire la suite...").'</a>';
      } else {
         $morelink[0]='';
         $morelink[1]='';
      }
      if ($comments==0) {
         $morelink[2]=0;
         $morelink[3]='<a href="article.php?sid='.$s_sid.'" class="mr-3"><i class="far fa-comment fa-lg" title="'.translate("Commentaires ?").'" data-toggle="tooltip"></i></a>';
       } elseif ($comments==1) {
         $morelink[2]=$comments;
         $morelink[3]='<a href="article.php?sid='.$s_sid.'" class="mr-3"><i class="far fa-comment fa-lg" title="'.translate("Commentaire").'" data-toggle="tooltip"></i></a>';
       } else {
         $morelink[2]=$comments;
         $morelink[3]='<a href="article.php?sid='.$s_sid.'" class="mr-3" ><i class="far fa-comment fa-lg" title="'.translate("Commentaires").'" data-toggle="tooltip"></i></a>';
       }
       $morelink[4]=$printP;
       $morelink[5]=$sendF;
       $sid = $s_sid;
         if ($catid != 0) {
          $resultm = sql_query("SELECT title FROM ".$NPDS_Prefix."stories_cat WHERE catid='$catid'");
          list($title1) = sql_fetch_row($resultm);
         $title= $title;
          // Attention à cela aussi
          $morelink[6]=' <a href="index.php?op=newcategory&amp;catid='.$catid.'">&#x200b;'.aff_langue($title1).'</a>';
       } else
          $morelink[6]='';
       $news_tab[$story_limit]['aid']=serialize($aid);
       $news_tab[$story_limit]['informant']=serialize($informant);
       $news_tab[$story_limit]['datetime']=serialize($time);
       $news_tab[$story_limit]['title']=serialize($title);
       $news_tab[$story_limit]['counter']=serialize($counter);
       $news_tab[$story_limit]['topic']=serialize($topic);
       $news_tab[$story_limit]['hometext']=serialize(meta_lang(aff_code($hometext)));
       $news_tab[$story_limit]['notes']=serialize(meta_lang(aff_code($notes)));
       $news_tab[$story_limit]['morelink']=serialize($morelink);
       $news_tab[$story_limit]['topicname']=serialize($topicname);
       $news_tab[$story_limit]['topicimage']=serialize($topicimage);
       $news_tab[$story_limit]['topictext']=serialize($topictext);
       $news_tab[$story_limit]['id']=serialize($s_sid);
   } if (isset($news_tab))
   return($news_tab);
}




#autodoc valid_group($xuser) : Retourne un tableau contenant la liste des groupes d'appartenance d'un membre
function valid_group($xuser) {
   global $NPDS_Prefix;
   if ($xuser) {
      $userdata = explode(':',base64_decode($xuser));
      $user_temp=Q_select("SELECT groupe FROM ".$NPDS_Prefix."users_status WHERE uid='$userdata[0]'",3600);
      $groupe=$user_temp[0];
      $tab_groupe=explode(',',$groupe['groupe']);
   } else
      $tab_groupe='';
   return ($tab_groupe);
}

#autodoc liste_group() : Retourne une liste des groupes disponibles dans un tableau
function liste_group() {
   global $NPDS_Prefix;
   $r = sql_query("SELECT groupe_id, groupe_name FROM ".$NPDS_Prefix."groupes ORDER BY groupe_id ASC");
   $tmp_groupe[0]='-> '.adm_translate("Supprimer").'/'.adm_translate("Choisir un groupe").' <-';
   while($mX = sql_fetch_assoc($r)) {
      $tmp_groupe[$mX['groupe_id']]=aff_langue($mX['groupe_name']);
   }
   sql_free_result($r);
   return ($tmp_groupe);
}

#autodoc groupe_forum($forum_groupeX, $tab_groupeX) : Retourne true ou false en fonction de l'autorisation d'un membre sur 1 (ou x) forum de type groupe
function groupe_forum($forum_groupeX, $tab_groupeX) {
   $ok_affich=groupe_autorisation($forum_groupeX, $tab_groupeX);
   return ($ok_affich);
}

#autodoc groupe_autorisation($groupeX, $tab_groupeX) : Retourne true ou false en fonction de l'autorisation d'un membre sur 1 (ou x) groupe
function groupe_autorisation($groupeX, $tab_groupeX) {
   $tab_groupe=explode(',',$groupeX);
   $ok=false;
   if ($tab_groupeX) {
      foreach($tab_groupe as $groupe) {
         foreach($tab_groupeX as $groupevalue) {
            if ($groupe==$groupevalue) {
               $ok=true;
               break;
            }
         }
         if ($ok) break;
      }
   }
   return ($ok);
}

function fab_espace_groupe($gr, $t_gr, $i_gr) {
   global $NPDS_Prefix, $short_user;

   $rsql=sql_fetch_assoc(sql_query("SELECT groupe_id, groupe_name, groupe_description, groupe_forum, groupe_mns, groupe_chat, groupe_blocnote, groupe_pad FROM ".$NPDS_Prefix."groupes WHERE groupe_id='$gr'"));

   $content='
   <script type="text/javascript">
   //<![CDATA[
   //==> chargement css
   if (!document.getElementById(\'bloc_ws_css\')) {
      var l_css = document.createElement(\'link\');
      l_css.href = "modules/groupe/bloc_ws.css";
      l_css.rel = "stylesheet";
      l_css.id = "bloc_ws_css";
      l_css.type = "text/css";
      document.getElementsByTagName("head")[0].appendChild(l_css);
   }
   //]]>
   </script>';

   $content.='
   <div id="bloc_ws_'.$gr.'" class="">'."\n";
   if ($t_gr==1) 
      $content.= '<span style="font-size: 120%; font-weight:bolder;">'.aff_langue($rsql['groupe_name']).'</span>'."\n";
   $content.='<p>'.aff_langue($rsql['groupe_description']).'</p>'."\n";
   if (file_exists('storage/users_private/groupe/'.$gr.'/groupe.png') and ($i_gr==1)) 
      $content.='<img src="storage/users_private/groupe/'.$gr.'/groupe.png" class="img-fluid mx-auto d-block rounded" alt="'.translate("Groupe").'" />';

   //=> liste des membres
   $li_mb=''; $li_ic='';
   $result = sql_query("SELECT uid, groupe FROM ".$NPDS_Prefix."users_status WHERE groupe REGEXP '[[:<:]]".$gr."[[:>:]]' ORDER BY uid ASC");
   $nb_mb=sql_num_rows ($result);
   $count=0;
   $li_mb.='
      <div class="my-4">
      <a data-toggle="collapse" data-target="#lst_mb_ws_'.$gr.'" class="text-primary" id="show_lst_mb_ws_'.$gr.'" title="'.translate("Déplier la liste").'"><i id="i_lst_mb_ws_'.$gr.'" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a><i class="fa fa-users fa-2x text-muted ml-3 align-middle" title="'.translate("Liste des membres du groupe.").'" data-toggle="tooltip"></i>&nbsp;<a href="memberslist.php?gr_from_ws='.$gr.'" class="text-uppercase">'.translate("Membres").'</a><span class="badge badge-secondary float-right">'.$nb_mb.'</span>';
   $tab=online_members();
   $li_mb.='
         <ul id="lst_mb_ws_'.$gr.'" class=" ul_bloc_ws collapse ">';
   while(list($uid, $groupe) = sql_fetch_row($result)) {
      $socialnetworks=array(); $posterdata_extend=array();$res_id=array();$my_rs='';
      if (!$short_user) {
         include_once('functions.php');
         $posterdata_extend = get_userdata_extend_from_id($uid);
         include('modules/reseaux-sociaux/reseaux-sociaux.conf.php');
         if ($posterdata_extend['M2']!='') {
            $socialnetworks= explode(';',$posterdata_extend['M2']);
            foreach ($socialnetworks as $socialnetwork) {
               $res_id[] = explode('|',$socialnetwork);
            }
            sort($res_id);
            sort($rs);
            foreach ($rs as $v1) {
               foreach($res_id as $y1) {
                  $k = array_search( $y1[0],$v1);
                  if (false !== $k) {
                     $my_rs.='<a class="mr-2" href="';
                     if($v1[2]=='skype') $my_rs.= $v1[1].$y1[1].'?chat'; else $my_rs.= $v1[1].$y1[1];
                     $my_rs.= '" target="_blank"><i class="fab fa-'.$v1[2].' fa-lg fa-fw mb-2"></i></a> ';
                     break;
                  } 
                  else $my_rs.='';
               }
            }
            $my_rsos[]=$my_rs;
         }
         else $my_rsos[]='';
      }
   
      list($uname, $user_avatar, $mns, $url, $femail)=sql_fetch_row(sql_query("SELECT uname, user_avatar, mns, url, femail FROM ".$NPDS_Prefix."users WHERE uid='$uid'"));

      include('modules/geoloc/geoloc_conf.php');
      settype($ch_lat,'string');
      $useroutils = '';
      if ($uid!= 1 and $uid!='')
         $useroutils .= '<a class="list-group-item text-primary" href="user.php?op=userinfo&amp;uname='.$uname.'" target="_blank" title="'.translate("Profil").'" data-toggle="tooltip"><i class="fa fa-2x fa-user align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Profil").'</span></a>';
      if ($uid!= 1)
         $useroutils .= '<a class="list-group-item text-primary" href="powerpack.php?op=instant_message&amp;to_userid='.$uname.'" title="'.translate("Envoyer un message interne").'" data-toggle="tooltip"><i class="far fa-2x fa-envelope align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Message").'</span></a>';
      if ($femail!='')
         $useroutils .= '<a class="list-group-item text-primary" href="mailto:'.anti_spam($femail,1).'" target="_blank" title="'.translate("Email").'" data-toggle="tooltip"><i class="fas fa-at fa-2x align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Email").'</span></a>';
      if ($url!='')
         $useroutils .= '<a class="list-group-item text-primary" href="'.$url.'" target="_blank" title="'.translate("Visiter ce site web").'" data-toggle="tooltip"><i class="fas fa-2x fa-external-link-alt align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Visiter ce site web").'</span></a>';
      if ($mns)
         $useroutils .= '<a class="list-group-item text-primary" href="minisite.php?op='.$uname.'" target="_blank" target="_blank" title="'.translate("Visitez le minisite").'" data-toggle="tooltip"><i class="fa fa-2x fa-desktop align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Visitez le minisite").'</span></a>';
      if (!$short_user)
         if ($posterdata_extend[$ch_lat] !='')
            $useroutils .= '<a class="list-group-item text-primary" href="modules.php?ModPath=geoloc&amp;ModStart=geoloc&op=u'.$uid.'" title="'.translate("Localisation").'" ><i class="fas fa-map-marker-alt fa-2x align-middle fa-fw"></i><span class="ml-2 d-none d-sm-inline">'.translate("Localisation").'</span></a>';

      $conn= '<i class="fa fa-plug text-muted" title="'.$uname.' '.translate("n'est pas connecté").'" data-toggle="tooltip" ></i>';
      if (!$user_avatar)
         $imgtmp="images/forum/avatar/blank.gif";
      else if (stristr($user_avatar,"users_private"))
         $imgtmp=$user_avatar;
      else {
         if ($ibid=theme_image("forum/avatar/$user_avatar")) {$imgtmp=$ibid;} else {$imgtmp="assets/images/forum/avatar/$user_avatar";}
         if (!file_exists($imgtmp)) {$imgtmp="assets/images/forum/avatar/blank.gif";}
      }
      $timex=false;
      for ($i = 1; $i <= $tab[0]; $i++) {
         if ($tab[$i]['username']==$uname)
            $timex=time()-$tab[$i]['time'];
      }
      if (($timex!==false) and ($timex<60))
         $conn= '<i class="fa fa-plug faa-flash animated text-primary" title="'.$uname.' '.translate("est connecté").'" data-toggle="tooltip" ></i>';
      $li_ic.='<img class="n-smil" src="'.$imgtmp.'" alt="avatar" />';
      $li_mb.= '
            <li class="list-group-item list-group-item-action d-flex flex-row p-2">
               <div id="li_mb_'.$uname.'_'.$gr.'" class="n-ellipses">
                  '.$conn.'<a class="ml-2" tabindex="0" data-title="'.$uname.'" data-toggle="popover" data-trigger="focus" data-html="true" data-content=\'<div class="list-group mb-3">'.$useroutils.'</div><div class="mx-auto text-center" style="max-width:170px;">';
      if (!$short_user)
         $li_mb.= $my_rsos[$count];
      $li_mb.= '</div>\'><img class=" btn-outline-primary img-thumbnail img-fluid n-ava-small " src="'.$imgtmp.'" alt="avatar" title="'.$uname.'" /></a><span class="ml-2">'.$uname.'</span>
               </div>
            </li>';
   $count++;
   }
   $li_mb.='
         <li style="clear:left;line-height:6px; background:none;">&nbsp;</li>
         <li class="list-group-item" style="clear:left;line-height:24px;padding:6px; margin-top:0px;">'.$li_ic.'</li>
      </ul>
   </div>';
   $content.= $li_mb;
   //<== liste des membres

   //=> Forum
   $lst_for='';$lst_for_tog='';$nb_for_gr='';
   if ($rsql['groupe_forum'] == 1) {
      $res_forum=sql_query("SELECT forum_id, forum_name FROM ".$NPDS_Prefix."forums WHERE forum_pass REGEXP '$gr'");
      $nb_foru=sql_num_rows ($res_forum);
      if ($nb_foru >= 1) {
         $lst_for_tog='<a data-toggle="collapse" data-target="#lst_for_gr_'.$gr.'" class="text-primary" id="show_lst_for_'.$gr.'" title="'.translate("Déplier la liste").'" ><i id="i_lst_for_gr_'.$gr.'" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a>';
         $lst_for.='<ul id="lst_for_gr_'.$gr.'" class="ul_bloc_ws collapse" style ="list-style-type:none;">';
         $nb_for_gr='  <span class="badge badge-secondary float-right">'.$nb_foru.'</span>';
         while(list($id_fo,$fo_name) = sql_fetch_row($res_forum)) {
            $lst_for.='
            <li class="list-group-item list-group-item-action"><a href="viewforum.php?forum='.$id_fo.'">'.$fo_name.'</a></li>';
         }
         $lst_for.='</ul>';
      }
      $content.='
      <hr /><div class="">'.$lst_for_tog.'<i class="fa fa-list-alt fa-2x text-muted ml-3 align-middle" title="'.translate("Groupe").'('.$gr.'): '.translate("forum").'." data-toggle="tooltip" ></i>&nbsp;<a class="text-uppercase" href="forum.php">'.translate("Forum").'</a>'.$nb_for_gr.$lst_for.'</div>'."\n";
   }
   //=> wspad
   if ($rsql['groupe_pad'] == 1) {
      settype($lst_doc,'string');
      settype($nb_doc_gr,'string');
      settype($lst_doc_tog,'string');
      include("modules/wspad/config.php");
      $docs_gr=sql_query("SELECT page, editedby, modtime, ranq FROM ".$NPDS_Prefix."wspad WHERE (ws_id) IN (SELECT MAX(ws_id) FROM ".$NPDS_Prefix."wspad WHERE member='$gr' GROUP BY page) ORDER BY page ASC");
      $nb_doc=sql_num_rows ($docs_gr);
      if ($nb_doc >= 1) {
         $lst_doc_tog ='<a data-toggle="collapse" data-target="#lst_doc_gr_'.$gr.'" class="text-primary" id="show_lst_doc_'.$gr.'" title="'.translate("Déplier la liste").'"><i id="i_lst_doc_gr_'.$gr.'" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a>';
         $lst_doc.='
         <ul id="lst_doc_gr_'.$gr.'" class="ul_bloc_ws mt-3 collapse">';
         $nb_doc_gr='  <span class="badge badge-secondary float-right">'.$nb_doc.'</span>';
         while (list($p,$e,$m,$r)=sql_fetch_row($docs_gr)) {
            $surlignage=$couleur[hexfromchr($e)];
            $lst_doc.='
            <li class="list-group-item list-group-item-action" style="line-height:14px;"><div id="last_editor_'.$p.'" data-toggle="tooltip" data-placement="right" title="'.translate("Dernier éditeur").' : '.$e.' '.date (translate("dateinternal"),$m ).'" style="float:left; width:1rem; height:1rem; background-color:'.$surlignage.'"></div><i class="fa fa-edit text-muted mx-1" data-toggle="tooltip" title="'.translate("Document co-rédigé").'." ></i><a href="modules.php?ModPath=wspad&amp;ModStart=wspad&amp;op=relo&amp;page='.$p.'&amp;member='.$gr.'&amp;ranq='.$r.'">'.$p.'</a></li>';
         }
         $lst_doc.='
         </ul>';
      }
      $content.='
      <hr /><div class="">'. $lst_doc_tog.'<i class="fa fa-edit fa-2x text-muted ml-3 align-middle" title="'.translate("Co-rédaction").'" data-toggle="tooltip" data-placement="right"></i>&nbsp;<a class="text-uppercase" href="modules.php?ModPath=wspad&ModStart=wspad&member='.$gr.'" >'.translate("Co-rédaction").'</a>'.$nb_doc_gr.$lst_doc.'</div>'."\n";
   }
   //<= wspad
   
   //=> bloc-notes
   if ($rsql['groupe_blocnote'] == 1) {
      settype($lst_blocnote_tog,'string');
      settype($lst_blocnote,'string');
      include_once("modules/bloc-notes/bloc-notes.php");
      $lst_blocnote_tog ='<a data-toggle="collapse" data-target="#lst_blocnote_'.$gr.'" class="text-primary" id="show_lst_blocnote" title="'.translate("Déplier la liste").'"><i id="i_lst_blocnote" class="toggle-icon fa fa-caret-down fa-2x" >&nbsp;</i></a><i class="far fa-sticky-note fa-2x text-muted ml-3 align-middle"></i>&nbsp;<span class="text-uppercase">Bloc note</span>';
      $lst_blocnote = '
      <div id="lst_blocnote_'.$gr.'" class="mt-3 collapse">
      '.blocnotes("shared", 'WS-BN'.$gr,'','7','bg-dark text-light',false).'
      </div>';
      $content.='
      <hr />
      <div class="mb-2">'.$lst_blocnote_tog.$lst_blocnote.'</div>';
   }
   //<= bloc-notes
   
   $content.='<div class="px-1 card card-body d-flex flex-row mt-3 flex-wrap text-center">';
   //=> Filemanager
   if (file_exists('modules/f-manager/users/groupe_'.$gr.'.conf.php'))
      $content.='<a class="mx-2" href="modules.php?ModPath=f-manager&amp;ModStart=f-manager&amp;FmaRep=groupe_'.$gr.'" title="'.translate("Gestionnaire fichiers").'" data-toggle="tooltip" data-placement="right"><i class="fa fa-folder fa-2x"></i></a>'."\n";
   //=> Minisite
   if ($rsql['groupe_mns'] == 1)
      $content.='<a class="mx-2" href="minisite.php?op=groupe/'.$gr.'" target="_blank" title= "'.translate("MiniSite").'" data-toggle="tooltip" data-placement="right"><i class="fa fa-desktop fa-2x"></i></a>';
   //=> Chat
   settype($chat_img,'string');
   if ($rsql['groupe_chat'] == 1) {
      $PopUp = JavaPopUp("chat.php?id=$gr&amp;auto=".encrypt(serialize ($gr)),"chat".$gr,380,480);
      if (array_key_exists('chat_info_'.$gr, $_COOKIE))
         if ($_COOKIE['chat_info_'.$gr]) $chat_img='faa-pulse animated faa-slow';
      $content.='<a class="mx-2" href="javascript:void(0);" onclick="window.open('.$PopUp.');" title="'.translate("Ouvrir un salon de chat pour le groupe.").'" data-toggle="tooltip" data-placement="right" ><i class="fa fa-comments fa-2x '.$chat_img.'"></i></a>';
   }
   //=> admin
   if (autorisation(-127))
      $content.='<a class="mx-2" href="admin.php?op=groupes" ><i title="'.translate("Gestion des groupes.").'" data-toggle="tooltip" class="fa fa-cogs fa-2x"></i></a>';
   $content.='</div>
   </div>';
   return ($content);
}




#autodoc block_fonction($title, $contentX) : Assure la gestion des include# et function# des blocs de NPDS / le titre du bloc est exporté (global) )dans $block_title
function block_fonction($title, $contentX) {
   global $block_title;
   $block_title=$title;
   //For including PHP functions in block
   if (stristr($contentX,"function#")) {
      $contentX=str_replace('<br />','',$contentX);
      $contentX=str_replace('<BR />','',$contentX);
      $contentX=str_replace('<BR>','',$contentX);
      $contentY=trim(substr($contentX,9));
      if (stristr($contentY,"params#")) {
         $pos = strpos($contentY,"params#");
         $contentII=trim(substr($contentY,0,$pos));
         $params=substr($contentY,$pos+7);
         $prm=explode(',',$params);
         // Remplace le param "False" par la valeur false (idem pour True)
         for ($i=0; $i<=count($prm)-1; $i++) {
            if ($prm[$i]=="false") {$prm[$i]=false;}
            if ($prm[$i]=="true") {$prm[$i]=true;}
         }
         // En fonction du nombre de params de la fonction : limite actuelle : 8
         if (function_exists($contentII)) {
            switch(count($prm)) {
               case 1:
                  $contentII($prm[0]); break;
               case 2:
                  $contentII($prm[0],$prm[1]); break;
               case 3:
                  $contentII($prm[0],$prm[1],$prm[2]); break;
               case 4:
                  $contentII($prm[0],$prm[1],$prm[2],$prm[3]); break;
               case 5:
                  $contentII($prm[0],$prm[1],$prm[2],$prm[3],$prm[4]); break;
               case 6:
                  $contentII($prm[0],$prm[1],$prm[2],$prm[3],$prm[4],$prm[5]); break;
               case 7:
                  $contentII($prm[0],$prm[1],$prm[2],$prm[3],$prm[4],$prm[5],$prm[6]); break;
               case 8:
                  $contentII($prm[0],$prm[1],$prm[2],$prm[3],$prm[4],$prm[5],$prm[6],$prm[7]); break;
            }
            return (true);
         } else {
            return (false);
         }
      } else {
         if (function_exists($contentY)) {
            $contentY();
            return (true);
         } else {
            return (false);
         }
      }
   } else {
      return (false);
   }
}

#autodoc fab_block($title, $member, $content, $Xcache) : Assure la fabrication réelle et le Cache d'un bloc
function fab_block($title, $member, $content, $Xcache) {
   global $SuperCache, $CACHE_TIMINGS;
   // Multi-Langue
   $title=aff_langue($title);
   // Bloc caché
   $hidden=false;
   if (substr($content,0,7)=="hidden#") {
      $content=str_replace("hidden#",'',$content);
      $hidden=true;
   }
   // Si on cherche à charger un JS qui a déjà été chargé par pages.php alors on ne le charge pas ...
   global $pages_js;
   if ($pages_js!='') {
      preg_match('#src="([^"]*)#',$content,$jssrc);
      if (is_array($pages_js)) {
         foreach($pages_js as $jsvalue) {
            if (array_key_exists('1',$jssrc)) {
               if ($jsvalue==$jssrc[1]) {
                  $content='';
                  break;
               }
            }
         }
      } else {
         if (array_key_exists('1',$jssrc)) {
            if ($pages_js==$jssrc[1]) $content="";
         }
      }
   }
   $content=aff_langue($content);
   if (($SuperCache) and ($Xcache!=0)) {
      $cache_clef=md5($content);
      $CACHE_TIMINGS[$cache_clef]=$Xcache;
      $cache_obj = new cacheManager();
      $cache_obj->startCachingBlock($cache_clef);
   } else
      $cache_obj = new cacheEmpty();
   if (($cache_obj->genereting_output==1) or ($cache_obj->genereting_output==-1) or (!$SuperCache) or ($Xcache==0)) {
      global $user, $admin;
      // For including CLASS AND URI in Block
      global $B_class_title, $B_class_content;
      $B_class_title=''; $B_class_content=''; $R_uri='';
      if (stristr($content,'class-') or stristr($content,'uri')) {
         $tmp=explode("\n",$content);
         $content='';
         foreach($tmp as $id => $class) {
            $temp=explode("#",$class);
            if ($temp[0]=="class-title")
               $B_class_title=str_replace("\r","",$temp[1]);
            else if ($temp[0]=="class-content")
               $B_class_content=str_replace("\r","",$temp[1]);
            else if ($temp[0]=="uri")
               $R_uri=str_replace("\r",'',$temp[1]);
            else {
               if ($content!='') $content.="\n ";
               $content.=str_replace("\r",'',$class);
            }
         }
      }
      // For BLOC URIs
      if ($R_uri) {
         global $REQUEST_URI;
         $page_ref=basename($REQUEST_URI);
         $tab_uri=explode(" ",$R_uri);
         $R_content=false;
         $tab_pref=parse_url($page_ref);
         $racine_page=$tab_pref['path'];
         if(array_key_exists('query', $tab_pref))
            $tab_pref=explode('&',$tab_pref['query']);
         foreach($tab_uri as $RR_uri) {
            $tab_puri=parse_url($RR_uri);
            $racine_uri=$tab_puri['path'];
            if ($racine_page==$racine_uri) {
               if(array_key_exists('query', $tab_puri))
                  $tab_puri=explode('&',$tab_puri['query']);
               foreach($tab_puri as $idx => $RRR_uri) {
                  if (substr($RRR_uri,-1)=="*") {
                     // si le token contient *
                     if (substr($RRR_uri,0,strpos($RRR_uri,"="))==substr($tab_pref[$idx],0,strpos($tab_pref[$idx],"=")))
                        $R_content=true;
                  } else {
                     if ($RRR_uri!=$tab_pref[$idx])
                        $R_content=false;
                     else
                        $R_content=true;
                  }
               }
            }
            if ($R_content==true) break;
         }
         if (!$R_content) $content='';
      }
      // For Javascript in Block
      if (!stristr($content,'javascript'))
         $content = nl2br($content);
      // For including externale file in block / the return MUST BE in $content
      if (stristr($content,'include#')) {
         $Xcontent=false;
         // You can now, include AND cast a fonction with params in the same bloc !
         if (stristr($content,"function#")) {
            $content=str_replace('<br />','',$content);
            $content=str_replace('<BR />','',$content);
            $content=str_replace('<BR>','',$content);
            $pos = strpos($content,'function#');
            $Xcontent=substr(trim($content),$pos);
            $content=substr(trim($content),8,$pos-10);
         } else {
            $content=substr(trim($content),8);
         }
         include_once($content);
         if ($Xcontent) {$content=$Xcontent;}
      }
      if (!empty($content)) {
         if (($member==1) and (isset($user))) {
            if (!block_fonction($title,$content)) {
               if (!$hidden)
                  themesidebox($title, $content);
               else
                  echo $content;
            }
         } elseif ($member==0) {
            if (!block_fonction($title,$content)) {
               if (!$hidden)
                  themesidebox($title, $content);
               else
                  echo $content;
            }
         } elseif (($member>1) and (isset($user))) {
            $tab_groupe=valid_group($user);
            if (groupe_autorisation($member,$tab_groupe)) {
               if (!block_fonction($title,$content)) {
                  if (!$hidden)
                     themesidebox($title, $content);
                  else
                     echo $content;
               }
            }
         } elseif (($member==-1) and (!isset($user))) {
            if (!block_fonction($title,$content)) {
               if (!$hidden)
                  themesidebox($title, $content);
               else
                  echo $content;
            }
         } elseif (($member==-127) and (isset($admin)) and ($admin)) {
            if (!block_fonction($title,$content)) {
               if (!$hidden)
                  themesidebox($title, $content);
               else
                  echo $content;
            }
         }
      }
      if (($SuperCache) and ($Xcache!=0)) {
         $cache_obj->endCachingBlock($cache_clef);
      }
   }
}

#autodoc leftblocks() : Meta-Fonction / Blocs de Gauche
function leftblocks() {
   Pre_fab_block('','LB');
}

#autodoc rightblocks() : Meta-Fonction / Blocs de Droite
function rightblocks() {
   Pre_fab_block('','RB');
}

#autodoc oneblock($Xid, $Xblock) : Alias de Pre_fab_block pour meta-lang
function oneblock($Xid, $Xblock) {
   ob_start();
      Pre_fab_block($Xid, $Xblock);
      $tmp=ob_get_contents();
   ob_end_clean();
   return ($tmp);
}

#autodoc Pre_fab_block($Xid, $Xblock) : Assure la fabrication d'un ou de tous les blocs Gauche et Droite
function Pre_fab_block($Xid, $Xblock) {
    global $NPDS_Prefix, $htvar; // modif Jireck
    if ($Xid) {
      if ($Xblock=='RB')
         $result = sql_query("SELECT title, content, member, cache, actif, id, css FROM ".$NPDS_Prefix."rblocks WHERE id='$Xid'");
      else
         $result = sql_query("SELECT title, content, member, cache, actif, id, css FROM ".$NPDS_Prefix."lblocks WHERE id='$Xid'");
    } else {
      if ($Xblock=='RB')
         $result = sql_query("SELECT title, content, member, cache, actif, id, css FROM ".$NPDS_Prefix."rblocks ORDER BY Rindex ASC");
      else
         $result = sql_query("SELECT title, content, member, cache, actif, id, css FROM ".$NPDS_Prefix."lblocks ORDER BY Lindex ASC");
    }
    global $bloc_side;
    if ($Xblock=='RB')
      $bloc_side='RIGHT';
    else
      $bloc_side='LEFT';
    while (list($title, $content, $member, $cache, $actif, $id, $css)=sql_fetch_row($result)) {
      if (($actif) or ($Xid)) {
         if ($css==1){
            $htvar = '
            <div id="'.$Xblock.'_'.$id.'">'; // modif Jireck
         } else {
            $htvar = '
            <div class="card mb-3 '.strtolower($bloc_side).'bloc">'; // modif Jireck
         }
         fab_block($title, $member, $content, $cache);
         // echo "</div>"; // modif Jireck
      }
    }
    sql_free_result($result);
}

#autodoc niv_block($Xcontent) : Retourne le niveau d'autorisation d'un block (et donc de certaines fonctions) / le paramètre (une expression régulière) est le contenu du bloc (function#....)
function niv_block($Xcontent) {
   global $NPDS_Prefix;
   $result = sql_query("SELECT content, member, actif FROM ".$NPDS_Prefix."rblocks WHERE content REGEXP '$Xcontent'");
   if (sql_num_rows($result)) {
      list($content, $member, $actif) = sql_fetch_row($result);
      return ($member.','.$actif);
   }
   $result = sql_query("SELECT content, member, actif FROM ".$NPDS_Prefix."lblocks WHERE content REGEXP '$Xcontent'");
   if (sql_num_rows($result)) {
      list($content, $member, $actif) = sql_fetch_row($result);
      return ($member.','.$actif);
   }
   sql_free_result($result);
}

#autodoc autorisation_block($Xcontent) : Retourne une chaine?? // array ou vide contenant la liste des autorisations (-127,-1,0,1,2...126)) SI le bloc est actif SINON "" / le paramètre est le contenu du bloc (function#....)
function autorisation_block($Xcontent) {
   $autoX=array();//notice .... to follow
   $auto=explode(',', niv_block($Xcontent));
   // le dernier indice indique si le bloc est actif
   $actif=$auto[count($auto)-1];
   // on dépile le dernier indice
   array_pop($auto);
   foreach($auto as $autovalue) {
      if (autorisation($autovalue))
         $autoX[]=$autovalue;
   }
   if ($actif)
      return ($autoX);
   else
      return('');
}

#autodoc autorisation($auto) : Retourne true ou false en fonction des paramètres d'autorisation de NPDS (Administrateur, anonyme, Membre, Groupe de Membre, Tous)
function autorisation($auto) {
   global $user, $admin;
   $affich=false;
   if (($auto==-1) and (!$user)) $affich=true;
   if (($auto==1) and (isset($user))) $affich=true;
   if ($auto>1) {
      $tab_groupe=valid_group($user);
      if ($tab_groupe) {
         foreach($tab_groupe as $groupevalue) {
            if ($groupevalue==$auto) {
               $affich=true;
               break;
            }
         }
      }
   }
   if ($auto==0) $affich=true;
   if (($auto==-127) and ($admin)) $affich=true;
   return ($affich);
}





#autodoc getTopics($s_sid) : Retourne le nom, l'image et le texte d'un topic ou False
function getTopics($s_sid) {
   global $NPDS_Prefix;
   global $topicname, $topicimage, $topictext;
   $sid = $s_sid;
   $result=sql_query("SELECT topic FROM ".$NPDS_Prefix."stories WHERE sid='$sid'");
   if ($result) {
      list($topic) = sql_fetch_row($result);
      $result=sql_query("SELECT topicid, topicname, topicimage, topictext FROM ".$NPDS_Prefix."topics WHERE topicid='$topic'");
      if ($result) {
         list($topicid, $topicname, $topicimage, $topictext) = sql_fetch_row($result);
         return (true);
      } else {
         return (false);
      }
   } else {
      return (false);
   }
}





#autodoc subscribe_mail($Xtype, $Xtopic,$Xforum, $Xresume, $Xsauf) : Assure l'envoi d'un mail pour un abonnement
function subscribe_mail($Xtype, $Xtopic, $Xforum, $Xresume, $Xsauf) {
   // $Xtype : topic, forum ... / $Xtopic clause WHERE / $Xforum id of forum / $Xresume Text passed / $Xsauf not this userid
   global $NPDS_Prefix, $sitename, $nuke_url;
   if ($Xtype=='topic') {
      $result=sql_query("SELECT topictext FROM ".$NPDS_Prefix."topics WHERE topicid='$Xtopic'");
      list($abo)=sql_fetch_row($result);
      $result=sql_query("SELECT uid FROM ".$NPDS_Prefix."subscribe WHERE topicid='$Xtopic'");
   }
   if ($Xtype=='forum') {
      $result=sql_query("SELECT forum_name, arbre FROM ".$NPDS_Prefix."forums WHERE forum_id='$Xforum'");
      list($abo, $arbre)=sql_fetch_row($result);
      if ($arbre)
         $hrefX='viewtopicH.php';
      else
         $hrefX='viewtopic.php';
      $resultZ=sql_query("SELECT topic_title FROM ".$NPDS_Prefix."forumtopics WHERE topic_id='$Xtopic'");
      list($title_topic)=sql_fetch_row($resultZ);
      $result=sql_query("SELECT uid FROM ".$NPDS_Prefix."subscribe WHERE forumid='$Xforum'");
   }
   include_once("language/lang-multi.php");
   while(list($uid) = sql_fetch_row($result)) {
      if ($uid!=$Xsauf) {
         $resultX=sql_query("SELECT email, user_langue FROM ".$NPDS_Prefix."users WHERE uid='$uid'");
         list($email, $user_langue)=sql_fetch_row($resultX);
         if ($Xtype=='topic') {
            $entete=translate_ml($user_langue, "Vous recevez ce Mail car vous vous êtes abonné à : ").translate_ml($user_langue, "Sujet")." => ".strip_tags($abo)."\n\n";
            $resume=translate_ml($user_langue, "Le titre de la dernière publication est")." => $Xresume\n\n";
            $url=translate_ml($user_langue, "L'URL pour cet article est : ")."<a href=\"$nuke_url/search.php?query=&topic=$Xtopic\">$nuke_url/search.php?query=&topic=$Xtopic</a>\n\n";
         }
         if ($Xtype=='forum') {
            $entete=translate_ml($user_langue, "Vous recevez ce Mail car vous vous êtes abonné à : ").translate_ml($user_langue, "Forum")." => ".strip_tags($abo)."\n\n";
            $url=translate_ml($user_langue, "L'URL pour cet article est : ")."<a href=\"$nuke_url/$hrefX?topic=$Xtopic&forum=$Xforum&start=9999#lastpost\">$nuke_url/$hrefX?topic=$Xtopic&forum=$Xforum&start=9999</a>\n\n";
            $resume=translate_ml($user_langue, "Le titre de la dernière publication est")." => ";
            if ($Xresume!='') {
               $resume.=$Xresume."\n\n";
            } else {
               $resume.=$title_topic."\n\n";
            }
         }
         $subject = translate_ml($user_langue, "Abonnement")." / $sitename";
         $message = $entete;
         $message .= $resume;
         $message .= $url;
         include("signat.php");
         send_email($email, $subject, $message, '', true, 'html');
      }
   }
}

#autodoc subscribe_query($Xuser,$Xtype, $Xclef) : Retourne true si le membre est abonné; à un topic ou forum
function subscribe_query($Xuser,$Xtype, $Xclef) {
   global $NPDS_Prefix;
   if ($Xtype=='topic') {
      $result=sql_query("SELECT topicid FROM ".$NPDS_Prefix."subscribe WHERE uid='$Xuser' AND topicid='$Xclef'");
   }
   if ($Xtype=='forum') {
      $result=sql_query("SELECT forumid FROM ".$NPDS_Prefix."subscribe WHERE uid='$Xuser' AND forumid='$Xclef'");
   }
   list($Xtemp) = sql_fetch_row($result);
   if ($Xtemp!='') {
      return (true);
   } else {
      return (false);
   }
}





#autodoc pollSecur($pollID) : Assure la gestion des sondages membres
function pollSecur($pollID) {
   global $NPDS_Prefix, $user;
   $pollIDX=false;
   $result = sql_query("SELECT pollType FROM ".$NPDS_Prefix."poll_data WHERE pollID='$pollID'");
   if (sql_num_rows($result)) {
      list($pollType)=sql_fetch_row($result);
      $pollClose = (($pollType / 128) >= 1 ? 1 : 0);
      $pollType = $pollType%128;
      if (($pollType==1) and !isset($user)) {
         $pollClose=99;
      }
   }
   return ( array($pollID, $pollClose));
}




#autodoc fab_edito() : Construit l'edito
function fab_edito() {
   global $cookie;
   if (isset($cookie[3])) {
      if (file_exists("storage/static/edito_membres.txt")) {
         $fp=fopen("storage/static/edito_membres.txt","r");
         if (filesize("storage/static/edito_membres.txt")>0)
            $Xcontents=fread($fp,filesize("storage/static/edito_membres.txt"));
         fclose($fp);
      } else {
         if (file_exists("storage/static/edito.txt")) {
            $fp=fopen("storage/static/edito.txt","r");
            if (filesize("storage/static/edito.txt")>0)
               $Xcontents=fread($fp,filesize("storage/static/edito.txt"));
            fclose($fp);
         }
      }
   } else {
      if (file_exists("storage/static/edito.txt")) {
         $fp=fopen("storage/static/edito.txt","r");
         if (filesize("storage/static/edito.txt")>0)
            $Xcontents=fread($fp,filesize("storage/static/edito.txt"));
         fclose($fp);
      }
   }
   $affich=false;
   $Xibid=strstr($Xcontents,'aff_jours');
   if ($Xibid) {
      parse_str($Xibid,$Xibidout);
      if (($Xibidout['aff_date']+($Xibidout['aff_jours']*86400))-time()>0) {
         $affichJ=false; $affichN=false;
         if ((time::NightDay()=='Jour') and ($Xibidout['aff_jour']=='checked')) $affichJ=true;
         if ((time::NightDay()=='Nuit') and ($Xibidout['aff_nuit']=='checked')) $affichN=true;
      }
      $XcontentsT=substr($Xcontents,0,strpos($Xcontents,'aff_jours'));
      $contentJ=substr($XcontentsT,strpos($XcontentsT,"[jour]")+6,strpos($XcontentsT,"[/jour]")-6);
      $contentN=substr($XcontentsT,strpos($XcontentsT,"[nuit]")+6,strpos($XcontentsT,"[/nuit]")-19-strlen($contentJ));
      $Xcontents='';
      if (isset($affichJ) and $affichJ===true)
         $Xcontents=$contentJ;
      if (isset($affichN) and $affichN===true) {
         if ($contentN!='')
            $Xcontents=$contentN;
         else
            $Xcontents=$contentJ;
      }
      if ($Xcontents!='') $affich=true;
   } else
      $affich=true;
   $Xcontents=meta_lang(aff_langue($Xcontents));
   return array($affich, $Xcontents);
}











#autodoc aff_editeur($Xzone, $Xactiv) : Charge l'éditeur ... ou non : $Xzone = nom du textarea / $Xactiv = deprecated <br /> si $Xzone="custom" on utilise $Xactiv pour passer des paramètres spécifiques
function aff_editeur($Xzone, $Xactiv) {
   global $language, $tmp_theme, $tiny_mce,$tiny_mce_theme,$tiny_mce_relurl;
   $tmp='';
   if ($tiny_mce) {
      static $tmp_Xzone;
      if ($Xzone=='tiny_mce') {
         if ($Xactiv=='end') {
            if (substr($tmp_Xzone,-1)==',')
               $tmp_Xzone=substr_replace($tmp_Xzone,'',-1);
            if ($tmp_Xzone) {
               $tmp="
      <script type=\"text/javascript\">
      //<![CDATA[
      $(document).ready(function() {
         tinymce.init({
            selector: 'textarea.tin',
            branding:false,
            height: 300,
            theme : 'silver',
            mobile: { theme: 'mobile' },
            language : '".language_iso(1,'','')."',";
               include ("assets/shared/editeur/tinymce/themes/advanced/npds.conf.php");
               $tmp.='
            });
         });
      //]]>
      </script>';
            }
         } else
            $tmp.='<script type="text/javascript" src="assets/shared/editeur/tinymce/tinymce.min.js"></script>';
      } else {
         if ($Xzone!='custom')
            $tmp_Xzone.=$Xzone.',';
         else
            $tmp_Xzone.=$Xactiv.',';
      }
   } else
      $tmp='';
   return ($tmp);
}




#autodoc topdownload_data($form, $ordre) : Bloc topdownload et lastdownload / SOUS-Fonction
function topdownload_data($form, $ordre) {
   global $NPDS_Prefix, $top, $long_chain;
   if (!$long_chain) $long_chain=13;
   settype($top,'integer');
   $result = sql_query("SELECT did, dcounter, dfilename, dcategory, ddate, perms FROM ".$NPDS_Prefix."downloads ORDER BY $ordre DESC LIMIT 0,$top");
   $lugar=1; $ibid='';
   while(list($did, $dcounter, $dfilename, $dcategory, $ddate, $dperm) = sql_fetch_row($result)) {
      if ($dcounter>0) {
         $okfile=autorisation($dperm);
         if ($ordre=='dcounter') {
            $dd= wrh($dcounter);
         }
         if ($ordre=='ddate') {
            $dd=translate("dateinternal");
            $day=substr($ddate,8,2);
            $month=substr($ddate,5,2);
            $year=substr($ddate,0,4);
            $dd=str_replace('d',$day,$dd);
            $dd=str_replace('m',$month,$dd);
            $dd=str_replace('Y',$year,$dd);
            $dd=str_replace("H:i","",$dd);
         }
         $ori_dfilename=$dfilename;
         if (strlen($dfilename)>$long_chain) {
            $dfilename = (substr($dfilename, 0, $long_chain))." ...";
         }
         if ($form=='short') {
            if ($okfile) { $ibid.='<li class="list-group-item list-group-item-action d-flex justify-content-start p-2 flex-wrap">'.$lugar.' <a class="ml-2" href="download.php?op=geninfo&amp;did='.$did.'&amp;out_template=1" title="'.$ori_dfilename.' '.$dd.'" >'.$dfilename.'</a><span class="badge badge-secondary ml-auto align-self-center">'.$dd.'</span></li>';}
         } else {
            if ($okfile) { $ibid.='<li class="ml-4 my-1"><a href="download.php?op=mydown&amp;did='.$did.'" >'.$dfilename.'</a> ('.translate("Catégorie"). ' : '.aff_langue(stripslashes($dcategory)).')&nbsp;<span class="badge badge-secondary float-right align-self-center">'.wrh($dcounter).'</span></li>';}
         }
         if ($okfile)
            $lugar++;
      }
   }
   sql_free_result($result);
   return $ibid;
}



#autodoc PollNewest() : Bloc Sondage <br />=> syntaxe : <br />function#pollnewest<br />params#ID_du_sondage OU vide (dernier sondage créé)
function PollNewest($id='') {
   global $NPDS_Prefix;
   // snipe : multi-poll evolution
   if ($id!=0) {
      settype($id, "integer");
      list($ibid,$pollClose)=pollSecur($id);
      if ($ibid) {pollMain($ibid,$pollClose);}
   } elseif ($result = sql_query("SELECT pollID FROM ".$NPDS_Prefix."poll_data ORDER BY pollID DESC LIMIT 1")) {
      list($pollID)=sql_fetch_row($result);
      list($ibid,$pollClose)=pollSecur($pollID);
      if ($ibid) {pollMain($ibid,$pollClose);}
   }
}




#autodoc tablos() : Permet d'alterner entre les CLASS (CSS) LIGNA et LIGNB
function tablos() {
   static $colorvalue;
   if ($colorvalue == "class=\"ligna\"") {
      $colorvalue="class=\"lignb\"";
   } else {
      $colorvalue="class=\"ligna\"";
   }
   return ($colorvalue);
}

#autodoc theme_image($theme_img) : Retourne le chemin complet si l'image est trouvée dans le répertoire image du thème sinon false
function theme_image($theme_img) {
    global $theme;
    if (@file_exists("themes/$theme/images/$theme_img")) {
       return ("themes/$theme/images/$theme_img");
    } else {
       return (false);
    }
}

#autodoc themepreview($title, $hometext, $bodytext, $notes) : Permet de prévisualiser la présentation d'un NEW
function themepreview($title, $hometext, $bodytext='', $notes='') {
   echo "<span class=\"titrea\">$title</span><br />".meta_lang($hometext)."<br />".meta_lang($bodytext)."<br />".meta_lang($notes);
}

?>