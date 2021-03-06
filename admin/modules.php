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
use npds\error\access;
use npds\assets\css;


if (!stristr($_SERVER['PHP_SELF'], 'admin.php')) 
    access::error();

$f_meta_nom = 'modules';
$f_titre = adm_translate("Gestion, Installation Modules");

//==> controle droit
admindroits($aid, $f_meta_nom);
//<== controle droit
//
$hlpfile = "admin/manuels/$language/modules.html";

global $language, $adminimg, $admf_ext;

   include("header.php");
   GraphicAdmin($hlpfile);

   $handle = opendir('modules');
   $modlist = '';
   while (false !== ($file = readdir($handle))) {
      if (!@file_exists("modules/$file/kernel")) {
        if (is_dir("modules/$file") and ($file != '.') and ($file != '..'))
           $modlist .= "$file ";
      }
   }
   closedir($handle);
   $modlist = explode(' ', rtrim($modlist));

   $whatondb = sql_query("SELECT mnom FROM ".$NPDS_Prefix."modules" );
   while ($row = sql_fetch_row($whatondb)) {
      if(!in_array($row[0], $modlist)) sql_query("DELETE FROM ".$NPDS_Prefix."modules WHERE mnom='".$row[0]."'");
   }

   foreach ($modlist as $value) {
      $queryexiste = sql_query("SELECT mnom FROM ".$NPDS_Prefix."modules WHERE mnom='".$value."'");
      $moexiste = sql_num_rows($queryexiste);
      if ($moexiste !== 1)
         sql_query("INSERT INTO ".$NPDS_Prefix."modules VALUES (NULL, '".$value."', '0')");
   }

   adminhead($f_meta_nom, $f_titre, $adminimg);
   
   echo '
   <hr />
   <h3>'.adm_translate("Les modules").'</h3>
   <table id="tad_modu" data-toggle="table" data-striped="false" data-show-toggle="true" data-mobile-responsive="true" data-buttons-class="outline-secondary" data-icons="icons" data-icons-prefix="fa">
      <thead>
         <tr>
            <th data-align="center" class="n-t-col-xs-1"><img class="adm_img" src="assets/images/admin/module.png" alt="icon_module" /></th>
            <th data-sortable="true">'.adm_translate('Nom').'</th>
            <th data-align="center" class="n-t-col-xs-2" >'.adm_translate('Fonctions').'</th>
         </tr>
      </thead>
      <tbody>';

   $result = sql_query("SELECT * FROM ".$NPDS_Prefix."modules ORDER BY mid");
   while ($row = sql_fetch_assoc($result)) {
      $icomod = ''; 
      $clatd = '';
      
      if (file_exists("modules/".$row["mnom"]."/".$row["mnom"].".png"))
         $icomod = '<img class="adm_img" src="modules/'.$row["mnom"].'/'.$row["mnom"].'.png" alt="icon_'.$row["mnom"].'" title="" />';
      else
         $icomod = '<img class="adm_img" src="assets/images/admin/module.png" alt="icon_module" title="" />';

      if ($row["minstall"] == 0) {
         if (file_exists("modules/".$row["mnom"]."/install.conf.php"))
            $status_chngac = '<a href="admin.php?op=Module-Install&amp;ModInstall='.$row["mnom"].'" ><i class="fa fa-compress fa-lg text-success"></i><i class="fa fa-puzzle-piece fa-2x fa-rotate-90 text-success" title="'.adm_translate("Installer le module").'" data-toggle="tooltip"></i></a>';
         else
            $status_chngac = '<a href="admin.php?op=Module-Install&amp;ModInstall='.$row["mnom"].'&amp;subop=install"><i class="far fa-check-square fa-2x" title="'.adm_translate("Pas d'installeur disponible").' '.adm_translate("Marquer le module comme install??").'" data-toggle="tooltip"></i></a>';
         
         $clatd = 'table-danger';
      }
      else {
         $status_chngac = '<a href="admin.php?op=Module-Install&amp;ModDesinstall='.$row["mnom"].'" ><i class="fa fa-expand fa-lg text-danger"></i><i class="fa fa fa-puzzle-piece fa-2x fa-rotate-90 text-danger" title="'.adm_translate("D??sinstaller le module").'" data-toggle="tooltip"></i></a>';
         $clatd = 'table-success';
      }

      echo '
          <tr>
             <td class="'.$clatd.'">'.$icomod.'</td>
             <td class="'.$clatd.'">'.$row["mnom"].'</td>
             <td class="'.$clatd.'">'.$status_chngac.'</td>
          </tr>';
   }

   echo '
      </tbody>
   </table>';
   
   css::adminfoot('', '', '', '');
