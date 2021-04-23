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
use npds\error\access;


if (method_exists(access::class, 'error')) 
{
    die();
}

if (!strstr($_SERVER['PHP_SELF'], 'admin.php')) 
{
    die();
}

include ('modules/'.$ModPath.'/lang/twi.lang-'.$language.'.php');

$f_meta_nom = 'npds_twi';
$f_titre = 'npds_twi';

admindroits($aid, $f_meta_nom);

global $adminimg;
   
//en attente implémentation pour notice php généré
settype($tbox_width, 'integer');
settype($tbox_height, 'integer');
settype($class_sty_2, 'string');
settype($class_sty_1, 'integer');
settype($npds_twi_post, 'integer');
   
//
settype($npds_twi_urshort, 'integer');
settype($npds_twi_arti, 'integer');
settype($consumer_key, 'string');
settype($consumer_secret, 'string');
settype($oauth_token_secret, 'string');
settype($oauth_token, 'string');

/**
 * [Configuretwi description]
 * @param [type] $subop              [description]
 * @param [type] $ModPath            [description]
 * @param [type] $ModStart           [description]
 * @param [type] $class_sty_2        [description]
 * @param [type] $npds_twi_arti      [description]
 * @param [type] $npds_twi_urshort   [description]
 * @param [type] $npds_twi_post      [description]
 * @param [type] $consumer_key       [description]
 * @param [type] $consumer_secret    [description]
 * @param [type] $oauth_token        [description]
 * @param [type] $oauth_token_secret [description]
 * @param [type] $tbox_width         [description]
 * @param [type] $tbox_height        [description]
 */
function Configuretwi($subop, $ModPath, $ModStart, $class_sty_2, $npds_twi_arti, $npds_twi_urshort, $npds_twi_post, $consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret, $tbox_width, $tbox_height) 
{
   
    if (file_exists('modules/'.$ModPath.'/config/twi.php'))
    {
        include ('modules/'.$ModPath.'/config/twi.php');
    }
      
    $hlpfile = 'modules/'.$ModPath.'/views/doc/aide_admtwi.html';

    global $f_meta_nom, $f_titre, $adminimg, $npds_twi;
   
    $checkarti_y = '';
    $checkarti_n = '';
    $checkpost_y = '';
    $checkpost_n = '';
    $urshort_mr = '';
    $urshort_ft = '';
    $urshort_c = '';
   
    if ($npds_twi_arti === 1) 
    {
        $checkarti_y = 'checked="checked"';
    } 
    else 
    {
        $checkarti_n = 'checked="checked"';
    }
   
    if ($npds_twi_post === 1) 
    {
        $checkpost_y = 'checked="checked"'; 
    }
    else 
    {
        $checkpost_n = 'checked="checked"';
    }
   
    if ($npds_twi_urshort === 1) 
    {
        $urshort_mr = 'checked="checked"';
    }
   
    if ($npds_twi_urshort === 2) 
    {
        $urshort_ft = 'checked="checked"';
    }
   
    if ($npds_twi_urshort === 3) 
    {
        $urshort_c = 'checked="checked"';
    }
    else 
    {
        $checkpost_n = 'checked="checked"';
    }
   
    //en attente implémentation pour notice
    settype($tbox_width, 'integer');
    settype($tbox_height, 'integer');

    GraphicAdmin($hlpfile);
    adminhead($f_meta_nom, $f_titre, $adminimg);
    
    echo '<hr />';
   
    if ($npds_twi !== 1) 
    {
        echo "
        <div class=\"alert alert-danger\">La publication de vos news sur twitter n'est pas autorisée vous devez l'activer <a class=\"alert-link\" href=\"admin.php?op=Configure\">Ici</a></div>";
    }
    else 
    {
        echo'
        <div class="alert alert-success">La publication de vos news sur twitter est autorisée. Vous pouvez révoquer cette autorisation <a class="alert-link" href="admin.php?op=Configure">Ici</a></div>';
    }
   
    echo '
    <h3 class="mb-3">'.twi_trad('Configuration du module npds_twi').'</h3>
    <span class="text-danger">*</span> '.twi_trad('requis').'
    <form id="twitterset" action="admin.php" method="post">
        <div class="form-group row">
            <label class="col-form-label col-sm-6" for="npds_twi_arti">'.twi_trad('Activation de la publication auto des articles').'</label>
            <div class="col-sm-6 my-2">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="npds_twi_arti_y" name="npds_twi_arti" value="1" '.$checkarti_y.' />
                    <label class="custom-control-label" for="npds_twi_arti_y">'.twi_trad('Oui').'</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="npds_twi_arti_n" name="npds_twi_arti" value="0" '.$checkarti_n.' />
                    <label class="custom-control-label" for="npds_twi_arti_n">'.twi_trad('Non').'</label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-control-label col-sm-6" for="npds_twi_urshort">'.twi_trad("Méthode pour le raccourciceur d'URL").'</label>
            <div class="col-sm-6">
                <div class="custom-controls-stacked">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="npds_twi_mod" name="npds_twi_urshort" value="1" '.$urshort_mr.' />
                        <label class="custom-control-label" for="npds_twi_mod">'.twi_trad("Réécriture d'url avec mod_rewrite").'</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="npds_twi_force" name="npds_twi_urshort" value="2" '.$urshort_ft.' />
                        <label class="custom-control-label" for="npds_twi_force">'.twi_trad("Réécriture d'url avec ForceType").'</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="npds_twi_npd" name="npds_twi_urshort" value="3" '.$urshort_c.' />
                        <label class="custom-control-label" for="npds_twi_npd">'.twi_trad("Réécriture d'url avec contrôleur Npds").'</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-12" for="consumer_key">'.twi_trad('Votre clef de consommateur').'&nbsp;<span class="text-danger">*</span></label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="consumer_key" name="consumer_key" value="'.$consumer_key.'" required="required" />
                <span class="help-block small">'.$consumer_key.'</span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-12" for="consumer_secret">'.twi_trad('Votre clef secrète de consommateur').'&nbsp;<span class="text-danger">*</span></label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="consumer_secret" name="consumer_secret" value="'.$consumer_secret.'" required="required" />
                <span class="help-block small">'.$consumer_secret.'</span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-12" for="oauth_token" >'.twi_trad("Jeton d'accès pour Open Authentification (oauth_token)").'&nbsp;<span class="text-danger">*</span></label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="oauth_token" name="oauth_token" value="'.$oauth_token.'" required="required" />
                <span class="help-block small">'.$oauth_token.'</span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-12" for="oauth_token_secret" >'.twi_trad("Jeton d'accès secret pour Open Authentification (oauth_token_secret)").' <span class="text-danger">*</span></label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="oauth_token_secret" name="oauth_token_secret" value="'.$oauth_token_secret.'" />
                <span class="help-block small">'.$oauth_token_secret.'</span>
            </div>
        </div>
        <!--
        <tr>
            <td colspan="2"><strong>'.twi_trad('Interface bloc').'</strong></td>
        </tr>
            <td width="30%">
                '.twi_trad('Largeur de la tweet box').' <span class="text-danger">*</span> : '.$tbox_width.'
            </td>
            <td>
                <input type="text" " size="25" maxlength="3" name="tbox_width" value="'.$tbox_width.'" />
            </td>
        </tr>
        <tr>
            <td width="30%">
                '.twi_trad('Hauteur de la tweet box').'</span>  <span class="text-danger">*</span> : '.$tbox_height.'
            </td>
            <td>
                <input type="text" " size="25" maxlength="3" name="tbox_height" value="'.$tbox_height.'" />
            </td>
        </tr>
        <tr>
            <td colspan="2"><strong>Styles</strong></td>
        </tr>
        <tr>
            <td width="30%">
                <span class="'.$class_sty_2.'">'.twi_trad('Classe de style titre').'</span> </td><td><input type="text" size="25" maxlength="255" name="class_sty_1" value="'.$class_sty_1.'">
            </td>
        </tr>
        <tr>
            <td width="30%">
                <span class="'.$class_sty_2.'">'.twi_trad("Classe de style sous-titre").'</span>
            </td>
            <td>
                <input type="text" size="25" maxlength="255" name="class_sty_2" value="'.$class_sty_2.'" />
            </td>
        </tr>
        -->';
   
    echo '
        <div class="form-group row">
            <div class="col-sm-12">
                <input class="btn btn-primary" type="submit" value="'.twi_trad('Enregistrez').'" />
                <input type="hidden" name="op" value="Extend-Admin-SubModule" />
                <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                <input type="hidden" name="subop" value="SaveSettwi" />
            </div>
        </div>
        </form>
        <div class="text-right">Version : '.$npds_twi_versus.'</div>';
    
    $arg1 = '
        var formulid = ["twitterset"];';
   
    css::adminfoot('fv', '', $arg1, '');
}

/**
 * [SaveSettwi description]
 * @param [type] $npds_twi_arti      [description]
 * @param [type] $npds_twi_urshort   [description]
 * @param [type] $npds_twi_post      [description]
 * @param [type] $consumer_key       [description]
 * @param [type] $consumer_secret    [description]
 * @param [type] $oauth_token        [description]
 * @param [type] $oauth_token_secret [description]
 * @param [type] $tbox_width         [description]
 * @param [type] $tbox_height        [description]
 * @param [type] $class_sty_1        [description]
 * @param [type] $class_sty_2        [description]
 * @param [type] $ModPath            [description]
 * @param [type] $ModStart           [description]
 */
function SaveSettwi($npds_twi_arti, $npds_twi_urshort, $npds_twi_post, $consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret, $tbox_width, $tbox_height, $class_sty_1, $class_sty_2, $ModPath, $ModStart) 
{

    //==> modifie le fichier de configuration
    $file_conf = fopen("modules/$ModPath/config/twi.php", "w+");
    $content = "<?php \n";
    $content .= "/**\n";
    $content .= " * Npds Two\n";
    $content .= " *\n";
    $content .= " * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier\n";
    $content .= " * \n";
    $content .= " * @author Nicolas2\n";
    $content .= " * @version 1.0\n";
    $content .= " * @date 02/04/2021\n";
    $content .= " */\n\n";
   
    if (!$npds_twi_arti) 
    {
        $npds_twi_arti = 0;
    }

    $content .= "// activation publication auto des news sur twitter\n";
    $content .= "\$npds_twi_arti = $npds_twi_arti;\n";
   
    if (!$npds_twi_post) 
    {
        $npds_twi_post = 0;
    }

    $content .= "\// activation publication auto des posts sur twitter\n";
    $content .= "\$npds_twi_post = $npds_twi_post;\n";
   
    if (!$npds_twi_urshort) 
    {
        $npds_twi_urshort = 0;
    }

    $content .= "// activation du raccourciceur d'url\n";
    $content .= "\$npds_twi_urshort = $npds_twi_urshort;\n";
    $content .= "\// \n";
    $content .= "\$consumer_key = \"$consumer_key\"; //\n";
    $content .= "\// \n";
    $content .= "\$consumer_secret = \"$consumer_secret\"; //\n";
    $content .= "\// \n";
    $content .= "\$oauth_token = \"$oauth_token\"; //\n";
    $content .= "\// \n";
    $content .= "\$oauth_token_secret = \"$oauth_token_secret\"; //\n";
    $content .= "// interface bloc \n\n";
    $content .= "\// largeur de la tweet box\n";
    $content .= "\$tbox_width = \"$tbox_width\";\n";
    $content .= "\// hauteur de la tweet box\n";
    $content .= "\$tbox_height = \"$tbox_height\";\n";
    $content .= "// style \n\n";
    $content .= "\// titre de la page\n";
    $content .= "\$class_sty_1 = \"$class_sty_1\";\n";
    $content .= "\// sous-titre de la page\n";
    $content .= "\$class_sty_2 = \"$class_sty_2\";\n";
    $content .= "\// \n";
    $content .= "\$npds_twi_versus = \"v.1.0\";\n";
    $content .= "?>";
    fwrite($file_conf, $content);
    fclose($file_conf);
    //<== modifie le fichier de configuration

    //==> modifie le fichier controleur
    $file_controleur = '';
    if ($npds_twi_urshort <> 1) 
    {
        $file_controleur = fopen("s.php", "w+");
        $content = "<?php \n";
        $content .= "/**\n";
        $content .= " * Npds Two\n";
        $content .= " *\n";
        $content .= " * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier\n";
        $content .= " * \n";
        $content .= " * @author Nicolas2\n";
        $content .= " * @version 1.0\n";
        $content .= " * @date 02/04/2021\n";
        $content .= " */\n\n";
        $content .= "\n";
        $content .= "\$fol=preg_replace ('#s\.php/(\d+)\$#','',\$_SERVER['PHP_SELF']);\n";
        $content .= "preg_match ('#/s\.php/(\d+)\$#', \$_SERVER['PHP_SELF'],\$res);\n";
        $content .= "header('Location: http://'.\$_SERVER['HTTP_HOST'].\$fol.'article.php?sid='.\$res[1]);\n";
        $content .= "?>";
        fwrite($file_controleur, $content);
        fclose($file_controleur);

        $file_controleur = fopen("s", "w+");
        $content = "<?php \n";
        $content .= "/**\n";
        $content .= " * Npds Two\n";
        $content .= " *\n";
        $content .= " * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier\n";
        $content .= " * \n";
        $content .= " * @author Nicolas2\n";
        $content .= " * @version 1.0\n";
        $content .= " * @date 02/04/2021\n";
        $content .= " */\n\n";
        $content .= "\n";
        $content .= "\$fol=preg_replace ('#s/(\d+)\$#','',\$_SERVER['PHP_SELF']);\n";
        $content .= "preg_match ('#/s/(\d+)\$#', \$_SERVER['PHP_SELF'],\$res);\n";
        $content .= "header('Location: http://'.\$_SERVER['HTTP_HOST'].\$fol.'article.php?sid='.\$res[1]);\n";
        $content .= "?>";
        fwrite($file_controleur, $content);
        fclose($file_controleur);
    }
    //<== modifie le fichier controleur
}

settype($subop, 'string');

if ($admin) 
{

    switch ($subop) 
    {
        case "SaveSettwi":
            SaveSettwi($npds_twi_arti, $npds_twi_urshort, $npds_twi_post, $consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret, $tbox_width, $tbox_height, $class_sty_1, $class_sty_2, $ModPath, $ModStart);
        break;

        default:
            Configuretwi($subop, $ModPath, $ModStart, $class_sty_2, $npds_twi_arti, $npds_twi_urshort, $npds_twi_post, $consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret, $tbox_width, $tbox_height);
        break;
    }
}
