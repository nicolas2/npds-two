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

if (!function_exists("Mysql_Connexion"))
   include ("mainfile.php");

   settype($op,'string');
   if ($op!="maj_subscribe") {
      include("header.php");
      $inclusion=false;
      if (file_exists("themes/$theme/html/topics.html"))
         $inclusion="themes/$theme/html/topics.html";
      elseif (file_exists("themes/default/html/topics.html"))
         $inclusion="themes/default/html/topics.html";
      else
         echo 'html/topics.html / not find !<br />';
      if ($inclusion) {
         ob_start();
         include($inclusion);
         $Xcontent=ob_get_contents();
         ob_end_clean();
         echo meta_lang(aff_langue($Xcontent));
      }
      include("footer.php");
   } else {
      if ($subscribe) {
         if ($user) {
            $result = sql_query("DELETE FROM ".$NPDS_Prefix."subscribe WHERE uid='$cookie[0]' AND topicid!='NULL'");
            $result = sql_query("SELECT topicid FROM ".$NPDS_Prefix."topics ORDER BY topicid");
            while(list($topicid) = sql_fetch_row($result)) {
               if (isset($Subtopicid)) {
                  if (array_key_exists($topicid,$Subtopicid)) {
                     if ($Subtopicid[$topicid]=="on") {
                        $resultX = sql_query("INSERT INTO ".$NPDS_Prefix."subscribe (topicid, uid) VALUES ('$topicid','$cookie[0]')");
                     }
                  }
               }
            }
            redirect_url("topics.php");
         }
      }
   }
?>