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
use npds\utility\crypt;
use npds\assets\css;


if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

switch($apli) 
{
   case 'f-manager':
      $fma = rawurldecode(crypt::decrypt($att_id));
      $fma = explode('#fma#', $fma);
      $att_id = crypt::decrypt($fma[0]);
      $att_name = crypt::decrypt($fma[1]);
   
   case 'forum_npds':
      if(isset($user)) 
      {
         $userX = base64_decode($user);
         $userdata = explode(':', $userX);
         $marqueurM = substr($userdata[2], 8, 6);
      }

   case 'minisite':
   case 'getfile':
      $att_name = StripSlashes(str_replace("\"", '', rawurldecode($att_name)));
      
      if ((preg_match('#^[a-z0-9_\.-]#i', $att_name) 
         or ($Mmod == $marqueurM)) 
         and !stristr($att_name, ".*://") 
         and !stristr($att_name, "..") 
         and !stristr($att_name, "../") 
         and !stristr($att_name, "script") 
         and !stristr($att_name, "cookie") 
         and !stristr($att_name, "iframe") 
         and !stristr($att_name, "applet") 
         and !stristr($att_name, "object")) 
      {
         if (preg_match('#^[a-z0-9_/\.-]#i', $att_id) 
            and !stristr($att_id, ".*://") 
            and !stristr($att_id, "..") 
            and !stristr($att_id, "../") 
            and !stristr($att_id, "script") 
            and !stristr($att_id, "cookie") 
            and !stristr($att_id, "iframe") 
            and !stristr($att_id, "applet") 
            and !stristr($att_id, "object")) 
         {
            $fic = '';
            switch($apli) 
            {
               // Forum
               case 'forum_npds':
                  $fic = "modules/upload/upload_forum/$att_id.$apli.$att_name";
               break;

               // MiniSite
               case 'minisite':
                  $fic = "storage/users_private/$att_id/mns/$att_name";
               break;

               // Application générique : la présence de getfile.conf.php est nécessaire
               case 'getfile':
                  if (file_exists("$att_id/getfile.conf.php") or file_exists("$att_id/.getfile.conf.php"))
                  {
                     $fic = "$att_id/$att_name";
                  }
                  else
                  {
                     header("location: index.php");
                  }
               break;

               case 'f-manager';
                  $fic = "$att_id/$att_name";
               break;
            }

            include ("modules/upload/lang/upload.lang-$language.php");
            include ("modules/upload/include/mimetypes.php");
            
            $suffix = strtoLower(substr(strrchr($att_name, '.'), 1));
            
            if (isset($type))
            {
               // strip "; name=.... " (Opera6)
               list($type, $garbage) = explode(';', $type);
            }

            if (isset($mimetypes[$suffix]))
            {
               $type = $mimetypes[$suffix];
            }
            elseif (empty($type) || ($type == 'application/octet-stream'))
            {
               $type = $mimetype_default;
            }
            
            $att_type = $type;
            $att_size = @filesize($fic);
            
            if (file_exists($fic)) 
            {
               if ($apli == 'forum_npds') 
               {
                  include ('auth.php');
                  
                  $sql = "UPDATE $upload_table SET compteur = compteur+1 WHERE att_id = '$att_id'";
                  sql_query($sql);
               }
               
               // Output file to the browser
               header('Expires: Thu, 01 Jan 1970 00:00:01 GMT');
               header('Cache-Control: max-age=60, must-revalidate');

               // work with mimetypes.php for showing source'code
               if ($att_type == 'text/source') 
               {
                  include ('config/meta.php');
                  
                  echo css::import_css($Default_Theme, $language, $site_font, '', '');
                  echo '
                  </head>
                  <body>
                     <div style="background-color:white; padding:4px;">';
                  show_source($fic);
                  echo '
                     </div>
                  </body>
                  </html>';
                  die();
               }

               if ($att_type == "application/x-shockwave-flash")
               {
                  header("Content-type: application/x-shockwave-flash");
               }
               else
               {
                  header("Content-Type: $att_type; name=\"".basename($att_name)."\"");
               }
              
               header ("Content-length: $att_size");
               header("Content-Disposition: inline; filename=\"".basename($att_name)."\"");
               readfile($fic);
            } 
            else
            {
               header("location: index.php");
            }
         } 
         else
         {
            header("location: index.php");
         }
      } 
      else
      {
         header("location: index.php");
      }
   break;

   case 'captcha':
      $mot = rawurldecode(crypt::decrypt($att_id));
      $font = 16;
      $width = imagefontwidth($font)* strlen($mot);
      $height = imagefontheight($font);
      $img = imagecreate($width+4, $height+4);
      $blanc = imagecolorallocate($img, 255, 255, 255);
      $noir = imagecolorallocate($img, 0, 0, 0);
      imagecolortransparent($img, $blanc);
      
      if (cur_charset == "utf-8")
      {
         $mot = utf8_decode($mot);
      }
      
      imagestring($img, $font, 1 , 1, $mot, $noir);
      imagepng($img);
      imagedestroy($img);
   break;
   
   default:
      header('location: index.php');
   break;
}
