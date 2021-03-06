<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/* ===========================                                          */
/*                                                                      */
/* NPDS Copyright (c) 2002-2019 by Philippe Brunier                     */
/* IZ-Xinstall version : 1.2                                            */
/*                                                                      */
/* Auteurs : v.0.1.0 EBH (plan.net@free.fr)                             */
/*         : v.1.1.1 jpb, phr                                           */
/*         : v.1.1.2 jpb, phr, dev, boris                               */
/*         : v.1.1.3 dev - 2013                                         */
/*         : v.1.2 phr, jpb - 2017                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
if (!stristr($_SERVER['PHP_SELF'],'install.php')) die();

function etape_7() {
   include_once ('config/config.php');
   global $langue, $stage, $minpass, $NPDS_Prefix, $qi;
   $stage = 7;
   echo '
               <h3 class="mb-3">'.ins_translate('Compte Admin').'</h3>
               <div class="col-sm-12">
                  <form id="admin_password" name="admin_password" method="post" action="install.php">
                     <div class="form-group row">
                        <label class="col-form-label" for="adminlogin">'.ins_translate('Identifiant').'</label>
                        <input class="form-control" type="text" name="adminlogin" id="adminlogin" maxlength="40" value="Root" required="required" />
                        <div class="d-flex justify-content-end w-100 small text-help py-1" id="countcar_adminlogin"></div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label" for="adminpass1">'.ins_translate('Mot de passe').'</label>
                        <input class="form-control" type="password" name="adminpass1" id="adminpass1" minlength="'.$minpass.'" maxlength="20" required="required" />
                        <div class="w-100 mt-2">
                           <div class="progress" style="height: 10px;">
                              <div id="passwordMeter_cont" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                           </div>
                        </div>
                        <div class="d-flex justify-content-start w-100 small text-help py-1"><div>'.ins_translate('Remarque').' : '.$minpass.' '.ins_translate('caract??res minimum').'</div><div class="ml-auto" id="countcar_adminpass1"></div></div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label" for="adminpass2">'.ins_translate("Une seconde fois").'</label>
                        <input class="form-control" type="password" name="adminpass2" id="adminpass2" minlength="'.$minpass.'" maxlength="20" required="required" />
                        <div class="d-flex justify-content-start w-100 small text-help py-1"><div>'.ins_translate('Remarque').' : '.$minpass.' '.ins_translate('caract??res minimum').'</div><div class="ml-auto" id="countcar_adminpass2"></div></div>
                     </div>
                     <div class="form-group row">
                        <input type="hidden" name="langue" value="'.$langue.'" />
                        <input type="hidden" name="stage" value="'.$stage.'" />
                        <input type="hidden" name="op" value="write_users" />
                        <input type="hidden" name="qi" value="'.$qi.'" />
                        <button type="submit" class="btn btn-success">'.ins_translate('Cr??er').'</button>
                     </div>
                  </form>
            </div>';
   $parametres='
         adminpass1: {
            validators: {
               checkPassword: {
                  message: "The password is too weak"
               },
            }
         },
         adminpass2: {
            validators: {
                identical: {
                  compare: function() {
                 return admin_password.querySelector(\'[name="adminpass1"]\').value;
                },
               message: "Les deux mots de passe ne sont pas identiques."
                }
            }

         }
         !###!
         admin_password.querySelector(\'[name="adminpass1"]\').addEventListener("input", function() {
            fvitem.revalidateField("adminpass2");
         });
';

   $arg1 = '
   var formulid = ["admin_password"]
   inpandfieldlen("adminlogin",40);
   inpandfieldlen("adminpass1",20);
   inpandfieldlen("adminpass2",20);';
   formval('fv',$parametres,$arg1,'1');
}
?>