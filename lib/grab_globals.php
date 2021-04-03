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

if (!defined('NPDS_GRAB_GLOBALS_INCLUDED')) {
   define('NPDS_GRAB_GLOBALS_INCLUDED', 1);

   // Modify the report level of PHP
   // error_reporting(0);// report NO ERROR
   //error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); // Devel report
   error_reporting(E_ERROR | E_WARNING | E_PARSE); // standard ERROR report
   //error_reporting(E_ALL);

   function access_denied() {
      include("admin/die.php");
   }

    // Boris 2012 - simulate PHP5 fonction array_walk_recursive / Mod by Dev to realy support PHP4 
    /*if (!function_exists("array_walk_recursive")) {
       function array_walk_recursive(&$tab, $callback, $userdata = null) {
          foreach($tab as $key => $dumy) {
             $value =& $tab[$key];
             if (is_array($value)) {
                if (!array_walk_recursive($value, $callback, $userdata)) {
                   return false;
                }
             } else {
                $callback($value, $key, $userdata);
             }
          }
          return true;
       }
    }*/

    // First of all : Spam from IP / |5 indicate that the same IP has passed 6 times with status KO in the anti_spambot function
   if (file_exists("storage/logs/spam.log"))
      $tab_spam = str_replace("\r\n", "", file("storage/logs/spam.log"));
   
   if (is_array($tab_spam)) {
      $ipadr = urldecode(getip());
      
      if (strstr($ipadr, ':'))
         $ipv = '6';
      else
         $ipv = '4';
      
      if (in_array($ipadr."|5", $tab_spam))
          access_denied();
      
      //=> nous pouvons bannir une plage d'adresse ip en V4 (dans l'admin IPban sous forme x.x.%|5 ou x.x.x.%|5)
      if($ipv == '4') {
         $ip4detail = explode('.', $ipadr);
         
         if (in_array($ip4detail[0].'.'.$ip4detail[1].'.%|5', $tab_spam))
            access_denied();
         
         if (in_array($ip4detail[0].'.'.$ip4detail[1].'.'.$ip4detail[2].'.%|5', $tab_spam))
            access_denied();
      }
      
      //=> nous pouvons bannir une plage d'adresse ip en V6 (dans l'admin IPban sous forme x:x:%|5 ou x:x:x:%|5)
      if($ipv == '6') {
         $ip6detail = explode(':', $ipadr);
         
         if (in_array($ip6detail[0].':'.$ip6detail[1].':%|5', $tab_spam))
            access_denied();
         
         if (in_array($ip6detail[0].':'.$ip6detail[1].':'.$ip6detail[2].':%|5', $tab_spam))
            access_denied();
      }
   }

   function addslashes_GPC(&$arr) {
      $arr = addslashes($arr);
   }

   // include current charset
   if (file_exists("config/cur_charset.php"))
      include ("config/cur_charset.php");
   
   // include url_protect Bad Words and create the filter function
   include ("lib/security/protect.php");

   // Get values, slash, filter and extract
   if (!empty($_GET)) {
      array_walk_recursive($_GET, 'addslashes_GPC');
      reset($_GET);
      
      array_walk_recursive($_GET, [new protect(), 'url']);
      extract($_GET, EXTR_OVERWRITE);
   }

   if (!empty($_POST)) {
      array_walk_recursive($_POST, 'addslashes_GPC');
      reset($_POST);
      
      //array_walk_recursive($_POST, [new protect(), 'url']);
      extract($_POST, EXTR_OVERWRITE);
   }

   // Cookies - analyse et purge - shiney 07-11-2010
   if (!empty($_COOKIE))
      extract($_COOKIE, EXTR_OVERWRITE);

   if (isset($user)) {
      $ibid = explode(':', base64_decode($user));
      array_walk($ibid, [new protect(), 'url']);
      $user = base64_encode(str_replace("%3A", ":", urlencode(base64_decode($user))));
   }

   if (isset($user_language)) {
      $ibid = explode(':', $user_language);
      array_walk($ibid, [new protect(), 'url']);
      $user_language = str_replace("%3A", ":", urlencode($user_language));
   }

   if (isset($admin)) {
      $ibid = explode(':', base64_decode($admin));
      array_walk($ibid, [new protect(), 'url']);
      $admin = base64_encode(str_replace('%3A', ':', urlencode(base64_decode($admin))));
   }
   // Cookies - analyse et purge - shiney 07-11-2010

   if (!empty($_SERVER))
      extract($_SERVER, EXTR_OVERWRITE);

   if (!empty($_ENV))
      extract($_ENV, EXTR_OVERWRITE);

   if (!empty($_FILES)) {
      foreach ($_FILES as $key => $value) {
         $$key = $value['tmp_name'];
      }
   } else if (!empty($HTTP_POST_FILES)) {
      foreach ($HTTP_POST_FILES as $key => $value) {
         $$key = $value['tmp_name'];
      }
   }

}
?>