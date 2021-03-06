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
use npds\utility\str;
use npds\logs\logs;


if (!stristr($_SERVER['PHP_SELF'], 'admin.php')) 
    access::error();

$f_meta_nom = 'blocks';// à voir si on réimplémente les droits spécifique droit et gauche

//==> controle droit
admindroits($aid, $f_meta_nom);
//<== controle droit

global $language;
$hlpfile = "admin/manuels/$language/rightblocks.html";

function makerblock($title, $content, $members, $Mmember, $Rindex, $Scache, $BRaide, $SHTML, $css) 
{
       global $NPDS_Prefix;

       if (is_array($Mmember) and ($members == 1)) {
          $members = implode(',', $Mmember);
          if ($members == 0) 
             $members = 1;
       }

       if (empty($Rindex)) 
          $Rindex = 0;
       
       $title = stripslashes(str::FixQuotes($title));
       $content = stripslashes(str::FixQuotes($content));
       
       if ($SHTML != 'ON')
          $content = strip_tags(str_replace('<br />', "\n", $content));
       
       sql_query("INSERT INTO ".$NPDS_Prefix."rblocks VALUES (NULL,'$title','$content', '$members', '$Rindex', '$Scache', '1', '$css', '$BRaide')");

       global $aid; 
       logs::Ecr_Log('security', "MakeRightBlock($title) by AID : $aid", '');
       
       Header("Location: admin.php?op=blocks");
}

function changerblock($id, $title, $content, $members, $Mmember, $Rindex, $Scache, $Sactif, $BRaide, $css) 
{
       global $NPDS_Prefix;

       if (is_array($Mmember) and ($members == 1)) {
          $members = implode(',', $Mmember);
          if ($members == 0) 
             $members = 1;
       }

       if (empty($Rindex)) 
          $Rindex = 0;

       $title = stripslashes(str::FixQuotes($title));
       
       if ($Sactif == 'ON') 
          $Sactif = 1; 
       else 
          $Sactif = 0;

       $content = stripslashes(str::FixQuotes($content));
       sql_query("UPDATE ".$NPDS_Prefix."rblocks SET title='$title', content='$content', member='$members', Rindex='$Rindex', cache='$Scache', actif='$Sactif', css='$css', aide='$BRaide' WHERE id='$id'");

       global $aid; 
       logs::Ecr_Log('security', "ChangeRightBlock($title - $id) by AID : $aid", '');
       
       Header("Location: admin.php?op=blocks");
}

function changegaucherblock($id, $title, $content, $members, $Mmember, $Rindex, $Scache, $Sactif, $BRaide, $css) 
{
       global $NPDS_Prefix;

       if (is_array($Mmember) and ($members == 1)) {
          $members = implode(',',$Mmember);
          if ($members == 0) 
             $members = 1;
       }

       if (empty($Rindex)) 
          $Rindex = 0;

       $title = stripslashes(str::FixQuotes($title));
       if ($Sactif == 'ON') 
          $Sactif = 1;
       else 
          $Sactif = 0;

       $content = stripslashes(str::FixQuotes($content));
       
       sql_query("INSERT INTO ".$NPDS_Prefix."lblocks VALUES (NULL,'$title','$content','$members', '$Rindex', '$Scache', '$Sactif', '$css', '$BRaide')");
       sql_query("DELETE FROM ".$NPDS_Prefix."rblocks WHERE id='$id'");

       global $aid; 
       logs::Ecr_Log('security', "MoveRightBlockToLeft($title - $id) by AID : $aid", '');
       
       Header("Location: admin.php?op=blocks");
}

function deleterblock($id) 
{
       global $NPDS_Prefix;

       sql_query("DELETE FROM ".$NPDS_Prefix."rblocks WHERE id='$id'");
       
       global $aid; 
       logs::Ecr_Log('security', "DeleteRightBlock($id) by AID : $aid", '');
       
       Header("Location: admin.php?op=blocks");
}

settype($css, 'integer');
//settype($Mmember, 'string');
settype($Sactif, 'string');
settype($SHTML, 'string');

switch ($op) {
       case 'makerblock':
          makerblock($title, $xtext, $members, $Mmember, $index, $Scache, $Baide, $SHTML, $css);
       break;

       case 'deleterblock':
          deleterblock($id);
       break;

       case 'changerblock':
          changerblock($id, $title, $content, $members, $Mmember, $Rindex, $Scache, $Sactif, $BRaide, $css);
       break;

       case 'gaucherblock':
          changegaucherblock($id, $title, $content, $members, $Mmember, $Rindex, $Scache, $Sactif, $BRaide, $css);
       break;
}
