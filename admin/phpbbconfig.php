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
use npds\cache\cache;
use npds\assets\css;


if (!stristr($_SERVER['PHP_SELF'], "admin.php")) 
    access::error();

$f_meta_nom = 'ForumConfigAdmin';
$f_titre = adm_translate('Configuration des Forums');

//==> controle droit
admindroits($aid, $f_meta_nom);
//<== controle droit

global $language, $adminimg, $admf_ext;
$hlpfile = "admin/manuels/$language/forumconfig.html";

function ForumConfigAdmin() 
{
       global $hlpfile, $NPDS_Prefix, $f_meta_nom, $f_titre, $adminimg;

       include ("header.php");

       GraphicAdmin($hlpfile);
       adminhead($f_meta_nom, $f_titre, $adminimg);

       $result = sql_query("SELECT * FROM ".$NPDS_Prefix."config");
       list($allow_html, $allow_bbcode, $allow_sig, $posts_per_page, $hot_threshold, $topics_per_page, $allow_upload_forum, $allow_forum_hide, $forum_attachments, $rank1, $rank2, $rank3, $rank4, $rank5, $anti_flood, $solved) = sql_fetch_row($result);
       
       echo '
       <hr />
       <h3 class="mb-3">'.adm_translate("Configuration des Forums").'</h3>
       <form id="phpbbconfigforum" action="admin.php" method="post">
          <div class="row">
             <label class="col-form-label col-sm-5" for="allow_html">'.adm_translate("Autoriser le HTML").'</label>
             <div class="col-sm-7 my-2">';
       
       $cky = ''; 
       $ckn = '';
       
       if ($allow_html == 1) {
          $cky = 'checked="checked"'; 
          $ckn = '';
       } else {
          $cky = ''; 
          $ckn = 'checked="checked"';
       }

       echo '
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_html_y" name="allow_html" value="1" '.$cky.' />
                   <label class="custom-control-label" for="allow_html_y">'.adm_translate("Oui").'</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_html_n" name="allow_html" value="0" '.$ckn.' />
                   <label class="custom-control-label" for="allow_html_n">'.adm_translate("Non").'</label>
                </div>
             </div>
          </div>
          <div class="row">
             <label class="col-form-label col-sm-5 " for="allow_bbcode">'.adm_translate("Autoriser les Smilies").'</label>
             <div class="col-sm-7 my-2">';
       
       $cky = ''; 
       $ckn = '';
       
       if ($allow_bbcode == 1) {
          $cky = 'checked="checked"'; 
          $ckn = '';
       } else {
          $cky = ''; 
          $ckn = 'checked="checked"';
       }

       echo '
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_bbcode_y" name="allow_bbcode" value="1" '.$cky.' />
                   <label class="custom-control-label" for="allow_bbcode_y">'.adm_translate("Oui").'</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_bbcode_n" name="allow_bbcode" value="0" '.$ckn.' />
                   <label class="custom-control-label" for="allow_bbcode_n">'.adm_translate("Non").'</label>
                </div>
             </div>
          </div>
          <div class="row">
             <label class="col-form-label col-sm-5" for="allow_sig">'.adm_translate("Autoriser les Signatures").'</label>
             <div class="col-sm-7 my-2">';
       
       $cky = ''; 
       $ckn = '';

       if ($allow_sig == 1) {
          $cky = 'checked="checked"'; 
          $ckn = '';
       } else {
          $cky = ''; 
          $ckn = 'checked="checked"';
       }

          echo '
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_sig_y" name="allow_sig" value="1" '.$cky.' />
                   <label class="custom-control-label" for="allow_sig_y">'.adm_translate("Oui").'</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_sig_n" name="allow_sig" value="0" '.$ckn.' />
                   <label class="custom-control-label" for="allow_sig_n">'.adm_translate("Non").'</label>
                </div>
             </div>
          </div>
          <div class="form-group row">
             <label class="col-form-label col-sm-5" for="hot_threshold">'.adm_translate("Seuil pour les Sujet 'chauds'").'</label>
             <div class="col-sm-7">
                <input class="form-control" type="text" min="0" id="hot_threshold" name="hot_threshold" maxlength="6" value="'.$hot_threshold.'" />
                <span class="help-block text-right" id="countcar_hot_threshold"></span>
             </div>
          </div>
          <div class="form-group row">
             <label class="col-form-label col-sm-5" for="posts_per_page">'.adm_translate("Nombre de contributions par page").'</label>
             <div class="col-sm-7">
                <input class="form-control" type="text" min="0" id="posts_per_page" name="posts_per_page" maxlength="6" value="'.$posts_per_page.'" />
                <span class="help-block">'.adm_translate("(C'est le nombre de contributions affich??es pour chaque page relative ?? un Sujet)").'<span class="float-right ml-1" id="countcar_posts_per_page"></span></span>
             </div>
          </div>
          <div class="form-group row">
             <label class="col-form-label col-sm-5" for="topics_per_page">'.adm_translate("Sujets par forum").'</label>
             <div class="col-sm-7">
                <input class="form-control" type="text" min="0" id="topics_per_page" name="topics_per_page" maxlength="6" value="'.$topics_per_page.'" />
                <span class="help-block">'.adm_translate("(C'est le nombre de Sujets affich??s pour chaque page relative ?? un Forum)").'<span class="float-right ml-1" id="countcar_topics_per_page"></span></span>
             </div>
          </div>
          <div class="form-group row">
             <label class="col-form-label col-sm-5" for="anti_flood">'.adm_translate("Nombre maximum de contributions par IP et par p??riode de 30 minutes (0=syst??me inactif)").'</label>
             <div class="col-sm-7">
                <input class="form-control" type="text" min="0" id="anti_flood" name="anti_flood" maxlength="6" value="'.$anti_flood.'" />
                <span class="help-block text-right" id="countcar_anti_flood"></span>
             </div>
          </div>
          <div class="row">
             <label class="col-form-label col-sm-5" for="solved">'.adm_translate("Activer le tri des contributions 'r??solues'").'</label>
             <div class="col-sm-7 my-2">';
       
       $cky = ''; 
       $ckn = '';

       if ($solved == 1) {
          $cky = 'checked="checked"'; 
          $ckn = '';
       } else {
          $cky = ''; 
          $ckn = 'checked="checked"';
       }

          echo '
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="solved_y" name="solved" value="1" '.$cky.' />
                   <label class="custom-control-label" for="solved_y">'.adm_translate("Oui").'</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="solved_n" name="solved" value="0" '.$ckn.' />
                   <label class="custom-control-label" for="solved_n">'.adm_translate("Non").'</label>
                </div>
             </div>
          </div>
          <div class="row">
             <label class="col-form-label col-sm-5" for="allow_upload_forum">'.adm_translate("Activer l'upload dans les forums ?").'</label>
             <div class="col-sm-7 my-2">';
       
       $cky = ''; 
       $ckn = '';

       if ($allow_upload_forum == 1) {
          $cky = 'checked="checked"'; 
          $ckn = '';
       } else {
          $cky = ''; 
          $ckn = 'checked="checked"';
       }

          echo '
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_upload_forum_y" name="allow_upload_forum" value="1" '.$cky.' />
                   <label class="custom-control-label" for="allow_upload_forum_y">'.adm_translate("Oui").'</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_upload_forum_n" name="allow_upload_forum" value="0" '.$ckn.' />
                   <label class="custom-control-label" for="allow_upload_forum_n">'.adm_translate("Non").'</label>
                </div>
             </div>
          </div>
          <div class="row">
             <label class="col-form-label col-sm-5" for="allow_forum_hide">'.adm_translate("Activer les textes cach??s").'</label>
             <div class="col-sm-7 my-2">';
       
       $cky = ''; 
       $ckn = '';

       if ($allow_forum_hide == 1) {
          $cky = 'checked="checked"'; 
          $ckn = '';
       } else {
          $cky = ''; 
          $ckn = 'checked="checked"';
       }

          echo '
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_forum_hide_y" name="allow_forum_hide" value="1" '.$cky.'/>
                   <label class="custom-control-label" for="allow_forum_hide_y">'.adm_translate("Oui").'</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                   <input class="custom-control-input" type="radio" id="allow_forum_hide_n" name="allow_forum_hide" value="0" '.$ckn.' />
                   <label class="custom-control-label" for="allow_forum_hide_n">'.adm_translate("Non").'</label>
                </div>
             </div>
          </div>
          <div class="form-group">
             <label class="col-form-label" for="rank1">'.adm_translate("Texte pour le r??le").' 1 </label>
             <textarea class="form-control" id="rank1" name="rank1" rows="3" maxlength="255">'.$rank1.'</textarea>
             <span class="help-block text-right" id="countcar_rank1"></span>
          </div>
          <div class="form-group">
             <label class="col-form-label" for="rank2">'.adm_translate("Texte pour le r??le").' 2 </label>
             <textarea class="form-control" id="rank2" name="rank2" rows="3" maxlength="255">'.$rank2.'</textarea>
             <span class="help-block text-right" id="countcar_rank2"></span>
          </div>
          <div class="form-group">
             <label class="col-form-label" for="rank3">'.adm_translate("Texte pour le r??le").' 3 </label>
             <textarea class="form-control" id="rank3" name="rank3" rows="3" maxlength="255">'.$rank3.'</textarea>
             <span class="help-block text-right" id="countcar_rank3"></span>
          </div>
          <div class="form-group">
             <label class="col-form-label" for="rank4">'.adm_translate("Texte pour le r??le").' 4 </label>
             <textarea class="form-control" id="rank4" name="rank4" rows="3" maxlength="255">'.$rank4.'</textarea>
             <span class="help-block text-right" id="countcar_rank4"></span>
          </div>
          <div class="form-group">
             <label class="col-form-label" for="rank5">'.adm_translate("Texte pour le r??le").' 5 </label>
             <textarea class="form-control" id="rank5" name="rank5" rows="3" maxlength="255">'.$rank5.'</textarea>
             <span class="help-block text-right" id="countcar_rank5"></span>
          </div>
          <input type="hidden" name="op" value="ForumConfigChange" />
          <div class="form-group">
             <button class="btn btn-primary" type="submit">'.adm_translate("Changer").'</button>
          </div>
       </form>';
       
       $fv_parametres = '
          hot_threshold: {
             validators: {
                regexp: {
                   regexp:/^\d{1,6}$/,
                   message: "0-9"
                }
             }
          },
          posts_per_page: {
             validators: {
                regexp: {
                   regexp:/^\d{1,6}$/,
                   message: "0-9"
                }
             }
          },
          topics_per_page: {
             validators: {
                regexp: {
                   regexp:/^\d{1,6}$/,
                   message: "0-9"
                }
             }
          },
          anti_flood: {
             validators: {
                regexp: {
                   regexp:/^\d{1,6}$/,
                   message: "0-9"
                }
             }
          },
       ';
      
      $arg1 = '
       var formulid = ["phpbbconfigforum"];
       inpandfieldlen("posts_per_page",255);
       inpandfieldlen("hot_threshold",255);
       inpandfieldlen("topics_per_page",255);
       inpandfieldlen("anti_flood",255);
       inpandfieldlen("rank1",255);
       inpandfieldlen("rank2",255);
       inpandfieldlen("rank3",255);
       inpandfieldlen("rank4",255);
       inpandfieldlen("rank5",255);
       ';
       
       css::adminfoot('fv', $fv_parametres, $arg1, '');
}

function ForumConfigChange($allow_html, $allow_bbcode, $allow_sig, $posts_per_page, $hot_threshold, $topics_per_page, $allow_upload_forum, $allow_forum_hide, $rank1, $rank2, $rank3, $rank4, $rank5, $anti_flood, $solved) 
{
       global $NPDS_Prefix;

       sql_query("UPDATE ".$NPDS_Prefix."config SET allow_html='$allow_html', allow_bbcode='$allow_bbcode', allow_sig='$allow_sig', posts_per_page='$posts_per_page', hot_threshold='$hot_threshold', topics_per_page='$topics_per_page', allow_upload_forum='$allow_upload_forum', allow_forum_hide='$allow_forum_hide', rank1='$rank1', rank2='$rank2', rank3='$rank3', rank4='$rank4', rank5='$rank5', anti_flood='$anti_flood', solved='$solved'");
       cache::Q_Clean();

       Header("Location: admin.php?op=ForumConfigAdmin");
}
