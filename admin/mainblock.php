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
use npds\utility\str;
use npds\logs\logs;


if (!stristr($_SERVER['PHP_SELF'], 'admin.php')) 
    access::error();

$f_meta_nom = 'mblock';
$f_titre = adm_translate("Bloc Principal");

//==> controle droit
admindroits($aid, $f_meta_nom);
//<== controle droit
//
global $language;
$hlpfile = "admin/manuels/$language/mainblock.html";

function mblock() 
{
       global $hlpfile, $NPDS_Prefix, $f_meta_nom, $f_titre, $adminimg;

       include("header.php");

       GraphicAdmin($hlpfile);
       adminhead ($f_meta_nom, $f_titre, $adminimg);

       echo '
       <hr />
       <h3>'.adm_translate("Edition du Bloc Principal").'</h3>';
       
       $result = sql_query("SELECT title, content FROM ".$NPDS_Prefix."block WHERE id=1");
       if (sql_num_rows($result) > 0) {
          while(list($title, $content) = sql_fetch_row($result)) {
             echo '
             <form id="fad_mblock" action="admin.php" method="post">
                <div class="form-group row">
                   <label class="col-form-label col-12" for="title">'.adm_translate("Titre").'</label>
                   <div class="col-12">
                      <textarea class="form-control" type="text" id="title" name="title" maxlength="255" placeholder="'.adm_translate("Titre :").'">'.$title.'</textarea>
                      <span class="help-block text-right"><span id="countcar_title"></span></span>
                   </div>
                </div>
                <div class="form-group row">
                   <label class="col-form-label col-12" for="content">'.adm_translate("Contenu").'</label>
                   <div class="col-12">
                      <textarea class="form-control" rows="25" id="content" name="content">'.$content.'</textarea>
                   </div>
                </div>
                <input type="hidden" name="op" value="changemblock" />
                <div class="form-group row">
                   <div class="col-12">
                      <button class="btn btn-outline-primary btn-block" type="submit"><i class ="fa fa-check fa-lg"></i>&nbsp;'.adm_translate("Valider").'</button>
                   </div>
                </div>
             </form>
             <script type="text/javascript">
             //<![CDATA[
                $(document).ready(function() {
                   inpandfieldlen("title",255);
                });
             //]]>
             </script>';
          }
       }

       css::adminfoot('fv', '', '', '');
}

function changemblock($title, $content) 
{
       global $NPDS_Prefix;
       
       $title = stripslashes(str::FixQuotes($title));
       $content = stripslashes(str::FixQuotes($content));
       
       sql_query("UPDATE ".$NPDS_Prefix."block SET title='$title', content='$content' WHERE id='1'");
       
       global $aid; 
       logs::Ecr_Log('security', "ChangeMainBlock($title) by AID : $aid", '');
       
       Header("Location: admin.php?op=adminMain");
}

switch ($op) {

       case 'mblock':
          mblock();
       break;

       case 'changemblock':
          changemblock($title, $content);
       break;
}
