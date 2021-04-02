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

include("powerpack_f.php");
global $powerpack; $powerpack=true;

settype($op,'string');
switch ($op) {
   // Instant Members Message
   case 'instant_message':
      Form_instant_message($to_userid);
   break;
   case 'write_instant_message':
      settype($copie,'string');
      settype($messages,'string');
         if ($user) {
            $rowQ1=Q_Select("SELECT uid FROM ".$NPDS_Prefix."users WHERE uname='$cookie[1]'", 3600);
            list(,$uid)=each($rowQ1);
            $from_userid=$uid['uid'];
            if (($subject!='') or ($message!='')) {
               $subject=FixQuotes($subject).'';
               $messages=FixQuotes($messages).'';
               writeDB_private_message($to_userid,'',$subject,$from_userid,$message,$copie);
            }
         }
      Header("Location: index.php");
   break;
   // Instant Members Message
   // Purge Chat Box
   case 'admin_chatbox_write':
      if ($admin) {
         if ($chatbox_clearDB=='OK') {
            sql_query("DELETE FROM ".$NPDS_Prefix."chatbox WHERE date <= ".(time()-(60*5))."");
         }
      }
      Header("Location: index.php");
   break;
   // Purge Chat Box
}
?>