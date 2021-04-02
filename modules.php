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

include ("lib/grab_globals.php");

function Access_Error () {
  include("admin/die.php");
}
function filtre_module($strtmp) {
   if (strstr($strtmp,'..') || stristr($strtmp,'script') || stristr($strtmp,'cookie') || stristr($strtmp,'iframe') || stristr($strtmp,'applet') || stristr($strtmp,'object'))
      Access_Error();
   else {
      if ($strtmp!='')
         return (true);
      else
         return (false);
   }
}
if (filtre_module($ModPath) and filtre_module($ModStart)) {
   if (!function_exists("Mysql_Connexion"))
      include ("mainfile.php");
   if (file_exists("modules/$ModPath/$ModStart.php")) {
      include("modules/$ModPath/$ModStart.php");
      die();
   } else
      Access_Error();
} elseif (filtre_module($name) and filtre_module($file)) {
   // phpnuke compatibility
   if (!function_exists("Mysql_Connexion"))
      include ("mainfile.php");
   if (file_exists("modules/$name/$file.php")) {
      include("modules/$name/$file.php");
      die();
   } else
      Access_Error();
} else
   Access_Error();
?>