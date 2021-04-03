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

use npds\security\protect;
use npds\security\extract;
use npds\error\access;
use npds\support\str;


if (!defined('NPDS_GRAB_GLOBALS_INCLUDED')) {
   define('NPDS_GRAB_GLOBALS_INCLUDED', 1);

   // Modify the report level of PHP
   // error_reporting(0);// report NO ERROR
   //error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); // Devel report
   error_reporting(E_ERROR | E_WARNING | E_PARSE); // standard ERROR report
   //error_reporting(E_ALL);

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
          access::denied();
      
      //=> nous pouvons bannir une plage d'adresse ip en V4 (dans l'admin IPban sous forme x.x.%|5 ou x.x.x.%|5)
      if($ipv == '4') {
         $ip4detail = explode('.', $ipadr);
         
         if (in_array($ip4detail[0].'.'.$ip4detail[1].'.%|5', $tab_spam))
            access::denied();
         
         if (in_array($ip4detail[0].'.'.$ip4detail[1].'.'.$ip4detail[2].'.%|5', $tab_spam))
            access::denied();
      }
      
      //=> nous pouvons bannir une plage d'adresse ip en V6 (dans l'admin IPban sous forme x:x:%|5 ou x:x:x:%|5)
      if($ipv == '6') {
         $ip6detail = explode(':', $ipadr);
         
         if (in_array($ip6detail[0].':'.$ip6detail[1].':%|5', $tab_spam))
            access::denied();
         
         if (in_array($ip6detail[0].':'.$ip6detail[1].':'.$ip6detail[2].':%|5', $tab_spam))
            access::denied();
      }
   }

   // include current charset
   if (file_exists("config/charset.php"))
      include ("config/charset.php");
   
   // Get values, slash, filter and extract
   if (!empty($_GET)) {
      array_walk_recursive($_GET, [str::class, 'addslashes_GPC']);
      reset($_GET);
      
      array_walk_recursive($_GET, [protect::class, 'url']);
      extract($_GET, EXTR_OVERWRITE);
   }

   if (!empty($_POST)) {
      array_walk_recursive($_POST, [str::class, 'addslashes_GPC']);
      reset($_POST);
      
      //array_walk_recursive($_POST, [protect::class, 'url']);
      extract($_POST, EXTR_OVERWRITE);
   }

   // Cookies - user
   $user = extract::user();
   
   // Cookies - user_language
   $user_language = extract::user_language();

   // Cookies - admin
   $admin = extract::admin();

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
