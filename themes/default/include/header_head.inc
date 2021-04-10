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
use npds\language\language;


define('CITRON', 'tarteaucitron');

// since NPDS Rev 16 this ressources are required dont remove //
echo '
<link rel="stylesheet" href="assets/shared/font-awesome/css/all.min.css" />'; // web font V5
echo '
<link id="bsth" rel="stylesheet" href="assets/shared/bootstrap/dist/css/bootstrap.min.css" />'; // framework
echo '
<link id="bsthxtra" rel="stylesheet" href="assets/shared/bootstrap/dist/css/extra.css" />'; // developpement
echo '
<link rel="stylesheet" href="assets/shared/formvalidation/dist/css/formValidation.min.css" />'; // form control
echo '
<link rel="stylesheet" href="assets/css/jquery-ui.min.css" />';//interface
echo '
<link rel="stylesheet" href="assets/shared/bootstrap-table/dist/bootstrap-table.min.css" />'; // table
echo '
<link rel="stylesheet" href="assets/css/prism.css" />
<script type="text/javascript" src="assets/js/jquery.min.js"></script>
';

if(defined('CITRON'))
{

    if (method_exists(language::class, 'language_iso'))
        echo '
        <script type="text/javascript"> var tarteaucitronForceLanguage = "'.language::language_iso(1, '', '').'"; </script>
        <script type="text/javascript" src="assets/shared/tarteaucitron/tarteaucitron.js"></script>
        <script type="text/javascript">
            //<![CDATA[
                tarteaucitron.init({
                    /* Privacy policy url */
                    "privacyUrl": "", 
                    
                    /* Ouverture automatique du panel avec le hashtag */
                    "hashtag": "#tarteaucitron", 
                    
                    /* Cookie name */
                    "cookieName": "tarteaucitron", 
                    
                    /* le bandeau doit être en haut (top) ou en bas (bottom) ? */
                    "orientation": "top", 
                    
                    /* afficher le petit bandeau en bas à droite ? */
                    "showAlertSmall": true,

                    /* Afficher la liste des cookies installés ? */
                    "cookieslist": true, 

                    /* Show cookie icon to manage cookies */
                    "showIcon": false, 

                    /* BottomRight, BottomLeft, TopRight and TopLeft */
                    "iconPosition": "BottomRight", 

                    /* Afficher un message si un adblocker est détecté */
                    "adblocker": false, 

                    /* Show the accept all button when highPrivacy on */
                    "AcceptAllCta" : true, 

                    /* désactiver le consentement implicite (en naviguant) ? */
                    "highPrivacy": false, 

                    /* If Do Not Track == 1, disallow all */
                    "handleBrowserDNTRequest": false, 

                    /* supprimer le lien vers la source ? */
                    "removeCredit": true, 

                    /* Show more info link */
                    "moreInfoLink": true, 

                    /* If false, the tarteaucitron.css file will be loaded */
                    "useExternalCss": false, 

                    /* Nom de domaine sur lequel sera posé le cookie - pour les multisites / sous-domaines - Facultatif */
                    "cookieDomain": "", 

                    /* Change the default readmore link */
                    "readmoreLink": "static.php?op=politiqueconf.html&npds=1&metalang=1", 
                    
                    /* Show a message about mandatory cookies */
                    "mandatory": true,
                });
            //]]
        </script>'; //RGPD tool
}
