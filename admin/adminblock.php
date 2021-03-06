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
use npds\assets\css;
use npds\utility\str;
use npds\logs\logs;
use npds\error\access;


if (!stristr($_SERVER['PHP_SELF'], 'admin.php')) 
{
    access::error();
}

$f_meta_nom = 'ablock';
$f_titre = adm_translate("Bloc Administration");

//==> controle droit
admindroits($aid, $f_meta_nom);
//<== controle droit
//
global $language;
$hlpfile = "admin/manuels/$language/adminblock.html";

/**
 * [ablock description]
 * @return [type] [description]
 */
function ablock() 
{
    global $hlpfile, $NPDS_Prefix, $f_meta_nom, $f_titre, $adminimg;
   
    include("header.php");
   
    GraphicAdmin($hlpfile);
    adminhead($f_meta_nom, $f_titre, $adminimg);
   
    echo '
        <hr />
        <h3>'.adm_translate("Editer le Bloc Administration").'</h3>';
   
    $result = sql_query("SELECT title, content FROM ".$NPDS_Prefix."block WHERE id=2");
   
    if (sql_num_rows($result) > 0) 
    {
        while (list($title, $content) = sql_fetch_row($result)) 
        {
            echo '
            <form id="adminblock" action="admin.php" method="post">
                <div class="form-group row">
                    <label class="col-form-label col-12" for="title">'.adm_translate("Titre").'</label>
                    <div class="col-12">
                        <textarea class="form-control" type="text" name="title" id="title" maxlength="1000">'.$title.'</textarea>
                        <span class="help-block text-right"><span id="countcar_title"></span></span>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-12" for="content">'.adm_translate("Contenu").'</label>
                    <div class="col-12">
                        <textarea class="form-control" type="text" rows="25" name="content" id="content">'.$content.'</textarea>
                    </div>
                </div>
                <input type="hidden" name="op" value="changeablock" />
                <div class="form-group row">
                    <div class="col-12">
                        <button class="btn btn-outline-primary btn-block col-12" type="submit"><i class ="fa fa-check fa-lg"></i>&nbsp;'.adm_translate("Valider").'</button>
                    </div>
                </div>
            </form>';
          
            $arg1 = '
                var formulid = ["adminblock"];
                inpandfieldlen("title",1000);';
        }
    }

    css::adminfoot('fv', '', $arg1, '');
}

/**
 * [changeablock description]
 * @param  [type] $title   [description]
 * @param  [type] $content [description]
 * @return [type]          [description]
 */
function changeablock($title, $content) 
{
    global $NPDS_Prefix;

    $title = stripslashes(str::FixQuotes($title));
    $content = stripslashes(str::FixQuotes($content));
       
    sql_query("UPDATE ".$NPDS_Prefix."block SET title='$title', content='$content' WHERE id='2'");
       
    global $aid; 
    logs::Ecr_Log('security', "ChangeAdminBlock() by AID : $aid", '');

    Header("Location: admin.php?op=adminMain");
}

switch ($op) {

    case 'ablock':
        ablock();
    break;
   
    case 'changeablock':
        changeablock($title, $content);
    break;
}
