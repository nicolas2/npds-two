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

global $ModPath, $ModStart;
$pos = strpos($ModPath, '/admin');
if ($pos>0) $ModPathX=substr($ModPath,0,$pos); else $ModPathX=$ModPath;
global $sform_path;
$sform_path='lib/sform/';
include_once($sform_path."sform.php");
//********************
global $m;
$m=new form_handler();
//********************
$m->add_form_title($ModPathX);
$m->add_form_method("get");
$m->add_form_check("true");
$m->add_field($ModPathX."_id", $ModPathX."_id","$browse_key",'text',true,11,"","a-9");
$m->add_key($ModPathX."_id");
$m->add_url("modules.php");
$m->add_submit_value("modifylinkrequest_adv_infos");
$m->add_field("ModStart","",$ModStart,'hidden',false);
$m->add_field("ModPath","",$ModPath,'hidden',false);
if (isset($author)) $m->add_field("author","",$author,'hidden',false);
$m->add_field("op","","modifylinkrequest",'hidden',false);
$m->add_field("lid","",$browse_key,'hidden',false);

/************************************************/
include_once($sform_path.$ModPathX."/formulaire.php");
/************************************************/

// Fabrique le formulaire et assure sa gestion
function interface_function($browse_key) {
   global $m;
   if ($m->sform_read_mysql($browse_key)) {
      $m->add_field('','',translate("Mise à jour"),'submit',false);
      $m->add_extra(' - ');
      $m->add_field('','',translate("Effacer"),'submit',false);
   } else
      $m->add_field('','',translate("Ajouter"),'submit',false);
   $m->key_lock('close');
   echo $m->print_form('class="ligna"');
}

function Supprimer_function($browse_key) {
   global $m;
   $m->sform_read_mysql($browse_key);
   $m->form_key_value=$browse_key;
   $m->sform_delete_mysql();
}

switch($modifylinkrequest_adv_infos) {
   case translate("Ajouter"):
      $m->make_response();
      $m->sform_insert_mysql($m->answer);
      interface_function($browse_key);
   break;
   case translate("Effacer"):
      $m->make_response();
      $m->sform_delete_mysql();
      interface_function($browse_key);
   break;
   case 'Supprimer_MySql':
      // C'est normal que ce case soit vide !
   break;
   case translate("Mise à jour"):
      $m->make_response();
      $m->sform_modify_mysql($m->answer);
      interface_function($browse_key);
   break;
   default:
      interface_function($browse_key);
   break;
}
?>