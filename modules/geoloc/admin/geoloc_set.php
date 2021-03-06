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
use modules\geoloc\support\font;


if (!strstr($_SERVER['PHP_SELF'], 'admin.php')) 
{
    access::error();
}

if (strstr($ModPath, '..') 
    || strstr($ModStart, '..') 
    || stristr($ModPath, 'script') 
    || stristr($ModPath, 'cookie') 
    || stristr($ModPath, 'iframe') 
    || stristr($ModPath, 'applet') 
    || stristr($ModPath, 'object') 
    || stristr($ModPath, 'meta') 
    || stristr($ModStart, 'script') 
    || stristr($ModStart, 'cookie') 
    || stristr($ModStart, 'iframe') 
    || stristr($ModStart, 'applet') 
    || stristr($ModStart, 'object') 
    || stristr($ModStart, 'meta')) 
{
    die();
}

define('GEO_AD', true);

$f_meta_nom = 'geoloc';

admindroits($aid, $f_meta_nom);

include ('modules/'.$ModPath.'/lang/geoloc.lang-'.$language.'.php');

$f_titre = geoloc_translate("Configuration du module Geoloc");

settype($subop, 'string');
settype($geo_ip, 'integer');
settype($cartyp, 'string');
settype($ch_lat, 'string');
settype($ch_lon, 'string');
settype($api_key_ipdata, 'string');

/**
 * [vidip description]
 * @return [type] [description]
 */
function vidip()
{
    global $NPDS_Prefix;
      
    $sql = "DELETE FROM ".$NPDS_Prefix."ip_loc WHERE ip_id >=1";
    if ($result = sql_query($sql)) 
    {
        sql_query( "ALTER TABLE ".$NPDS_Prefix."ip_loc AUTO_INCREMENT = 0;");
    }
}

/**
 * [Configuregeoloc description]
 * @param [type] $subop          [description]
 * @param [type] $ModPath        [description]
 * @param [type] $ModStart       [description]
 * @param [type] $ch_lat         [description]
 * @param [type] $ch_lon         [description]
 * @param [type] $cartyp         [description]
 * @param [type] $geo_ip         [description]
 * @param [type] $api_key_ipdata [description]
 */
function Configuregeoloc($subop, $ModPath, $ModStart, $ch_lat, $ch_lon, $cartyp, $geo_ip, $api_key_ipdata) 
{
    global $hlpfile, $language, $f_meta_nom, $f_titre, $adminimg, $dbname, $NPDS_Prefix, $subop, $nuke_url;
    
    include ('modules/'.$ModPath.'/config/geoloc.php');
      
    $hlpfile = 'modules/'.$ModPath.'/views/doc/aide_admgeo.html';

    $result = sql_query("SELECT CONCAT(ROUND(((DATA_LENGTH + INDEX_LENGTH - DATA_FREE) / 1024 / 1024), 2), ' Mo') AS TailleMo FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = ".$NPDS_Prefix."'ip_loc'");
    $row = sql_fetch_array($result);

    $ar_fields = array('C3', 'C4', 'C5', 'C6', 'C7', 'C8');
    foreach($ar_fields as $k => $v)
    {
        $req = sql_query("SELECT $v FROM users_extend WHERE $v !=''");
        if(!sql_num_rows($req)) 
        {
            $dispofield[] = $v;
        }
    }

    GraphicAdmin($hlpfile);
    adminhead($f_meta_nom, $f_titre, $adminimg);

    echo '
    <hr />
    <a href="modules.php?ModPath=geoloc&amp;ModStart=geoloc"><i class="fa fa-globe fa-lg mr-2 "></i>'.geoloc_translate('Carte').'</a>
    <form id="geolocset" name="geoloc_set" action="admin.php" method="post">
        <h4 class="my-3">'.geoloc_translate('Param??tres syst??me').'</h4>
        <fieldset id="para_sys" class="" style="padding-top: 16px; padding-right: 3px; padding-bottom: 6px;padding-left: 3px;">
            <span class="text-danger">* '.geoloc_translate("requis").'</span>
            <div class="form-group row ">
                <label class="col-form-label col-sm-6" for="ch_lat">'.geoloc_translate('Champ de table pour latitude').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <select class="custom-select form-control" name="ch_lat" id="ch_lat">
                        <option selected="selected">'.$ch_lat.'</option>';
      
    foreach($dispofield as $ke => $va) 
    {
        echo '<option>'.$va.'</option>';
    }

    echo '
                    </select>
                </div>
            </div>
            <div class="form-group row ">
                <label class="col-form-label col-sm-6" for="ch_lon">'.geoloc_translate('Champ de table pour longitude').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <select class="custom-select form-control" name="ch_lon" id="ch_lon">
                        <option selected="selected">'.$ch_lon.'</option>';
      
    foreach($dispofield as $ke => $va) 
    {
        echo '<option>'.$va.'</option>';
    }
    
    echo '
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="ch_img">'.geoloc_translate('Chemin des images').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="ch_img" id="ch_img" placeholder="Chemin des images" value="'.$ch_img.'" required="required" />
                </div>
            </div>';

    $cky_geo = ''; 
    $ckn_geo = '';

    if ($geo_ip == 1) 
    {
        $cky_geo = 'checked="checked"'; 
    }
    else 
    {
        $ckn_geo = 'checked="checked"';
    }
    
    echo '
            <div class="form-group row">
                <label class="col-sm-6 col-form-label" for="geo_ip">'.geoloc_translate('G??olocalisation des IP').'</label>
                <div class="col-sm-6 my-2">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input class="custom-control-input" type="radio" id="geo_oui" name="geo_ip" value="1" '.$cky_geo.' />
                        <label class="custom-control-label" for="geo_oui">'.geoloc_translate('Oui').'</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input class="custom-control-input" type="radio" id="geo_no" name="geo_ip" value="0" '.$ckn_geo.' />
                        <label class="custom-control-label" for="geo_no">'.geoloc_translate('Non').'</label>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-6 col-form-label" for="provider_ip">'.geoloc_translate('Provider Ip').'</label>
                    <div class="col-sm-6">
                    <select class="custom-select form-control" name="provider_ip" id="provider_ip">';    
                        $providers = array(
                            'https://ipapi.co',
                            'https://api.ipdata.co',
                            'https://extreme-ip-lookup.com',
                            'http://ip-api.com'       
                        );
                        global $nuke_url;
                        if(strstr($nuke_url, 'https')) 
                        {
                            foreach($providers as $key => $val)
                            {
                                if( !preg_match('/https\:\/\//i', $val) ) 
                                {
                                    unset($providers[$key]);
                                }
                            }
                        }
                        else 
                        {
                            foreach($providers as $key => $val)
                            {
                                if( !preg_match('/http\:\/\//i', $val) ) 
                                {
                                    unset($providers[$key]);
                                }
                            }
                        }

                        foreach($providers as $key => $val)
                        {
                            if ($provider_select == $key)
                            {
                                $sel = 'selected="selected"'; 
                            }
                            else 
                            {
                                $sel = '';
                            }
                            echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
                        }

        echo '      </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-4" for="api_key_ipdata">'.geoloc_translate("Clef d'API").' Ipdata</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="api_key_ipdata" id="api_key_ipdata" placeholder="" value="'.$api_key_ipdata.'" />
                    <span class="help-block small muted">'.$api_key_ipdata.'</span>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12"><span class="form-control-label">'.geoloc_translate('Taille de la table').' ip_loc '.$row['TailleMo'].'</span> <span class="float-right"><a href="admin.php?op=Extend-Admin-SubModule&ModPath='.$ModPath.'&ModStart='.$ModStart.'&subop=vidip" title="'.geoloc_translate('Vider la table des IP g??or??f??renc??es').'" data-toggle="tooltip" data-placement="left"><i class="far fa-trash-alt fa-lg text-danger"></i></a></span></div>
            </div>
        </fieldset>
        <hr />
        <h4 class="my-3" >'.geoloc_translate('Interface carte').'</h4>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="api_key_bing">'.geoloc_translate("Clef d'API").' Bing maps</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" name="api_key_bing" id="api_key_bing" placeholder="" value="'.$api_key_bing.'" />
                <span class="help-block small muted">'.$api_key_bing.'</span>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-4" for="api_key_mapbox">'.geoloc_translate("Clef d'API").' Mapbox</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" name="api_key_mapbox" id="api_key_mapbox" placeholder="" value="'.$api_key_mapbox.'" />
                <span class="help-block small muted">'.$api_key_mapbox.'</span>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8">
                <fieldset id="para_car" class="" style="padding-top: 16px; padding-right: 3px; padding-bottom: 6px;padding-left: 3px;">
                    <div class="form-group row ">
                        <label class="col-form-label col-sm-6" for="cartyp">'.geoloc_translate('Type de carte').'<span class="text-danger ml-1">*</span></label>
                        <div class="col-sm-6">
                            <select class="custom-select form-control" name="cartyp" id="cartyp">';
      
    $j = 0;

    $fond_provider = font::provider();

    foreach($fond_provider as $v) 
    {
        if($v[0] == $cartyp) 
        {
            $sel = 'selected="selected"'; 
        }
        else 
        {
            $sel = '';
        }

        switch($j)
        {
            case '0': 
                echo '<optgroup label="OpenStreetMap">';
            break;

            case '1': 
                echo '<optgroup label="Stamen">';
            break;

            case '4': 
                echo '<optgroup label="Mapbox">';
            break;

            case '6': 
                if($api_key_bing == !'') 
                {
                    echo '<optgroup label="Bing maps">';
                }
            break;

            case '9': 
                echo '<optgroup label="Google">';
            break;
        }
    
        echo '<option '.$sel.' value="'.$v[0].'">'.$v[1].'</option>';

        switch($j)
        {
            case '0': 
            case '3': 
            case '5': 
            case '10': 
                echo '</optgroup>'; 
            break;
        }
        $j++;
    }
      
    echo '
                            </select>
                        </div>
                    </div>';
                  
    $s_dd = '';
    $s_dm = '';

    if($co_unit == 'dd') 
    {
        $s_dd = 'selected="selected"';
    }
    elseif($co_unit == 'dms')
    { 
        $s_dm = 'selected="selected"';
    } 
                  
    echo '
                <div class="form-group row">
                    <label class="col-form-label col-sm-6" for="co_unit">'.geoloc_translate('Unit?? des coordonn??es').'<span class="text-danger ml-1">*</span></label>
                    <div class="col-sm-6">
                        <select class="custom-select form-control" name="co_unit" id="co_unit">
                            <option '.$s_dd.'>dd</option>
                            <option '.$s_dm.'>dms</option>
                        </select>
                    </div>
                </div>';
                  
    $cky_mar = ''; 
    $ckn_mar = '';

    if ($mark_typ == 1)
    { 
        $cky_mar = 'checked="checked"'; 
    }
    else 
    {
        $ckn_mar = 'checked="checked"';
    }
                  
    echo '
                <div class="form-group row">
                    <label class="col-sm-12 col-form-label" for="mark_typ">'.geoloc_translate('Type de marqueur').'</label>
                    <div class="col-sm-12">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="img_img" name="mark_typ" value="1" '.$cky_mar.' checked="checked" />
                        <label class="custom-control-label" for="img_img">'.geoloc_translate('Marqueur images de type png, gif, jpeg.').'</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="img_svg" name="mark_typ" value="0" '.$ckn_mar.' />
                        <label class="custom-control-label" for="img_svg">'.geoloc_translate('Marqueur SVG font ou objet vectoriel.').'</label>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset id="para_ima" class="" style="padding-top: 16px; padding-right: 3px; padding-bottom: 6px;padding-left: 3px;">
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="nm_img_mbg">'.geoloc_translate('Image membre g??or??f??renc??').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span id="v_img_mbg" class="input-group-text"><img width="22" height="22" src="'.$ch_img.$nm_img_mbg.'" alt="'.geoloc_translate('Image membre g??or??f??renc??').'" /></span>
                        </div>
                        <input type="text" class="form-control input-lg" name="nm_img_mbg" id="nm_img_mbg" placeholder="'.geoloc_translate('Nom du fichier image').'" value="'.$nm_img_mbg.'" required="required" />
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="nm_img_mbcg">'.geoloc_translate('Image membre g??or??f??renc?? en ligne').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group ">
                        <div class="input-group-prepend">
                            <span id="v_img_mbcg" class="input-group-text"><img width="22" height="22" src="'.$ch_img.$nm_img_mbcg.'" alt="'.geoloc_translate('Image membre g??or??f??renc?? en ligne').'" /></span>
                        </div>
                        <input type="text" class="form-control input-lg" name="nm_img_mbcg" id="nm_img_mbcg" placeholder="'.geoloc_translate('Nom du fichier image').'" value="'.$nm_img_mbcg.'" required="required" />
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="nm_img_acg">'.geoloc_translate('Image anonyme g??or??f??renc?? en ligne').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span id="v_img_acg" class="input-group-text"><img width="22" height="22" src="'.$ch_img.$nm_img_acg.'" alt="'.geoloc_translate('Image anonyme g??or??f??renc?? en ligne').'" /></span>
                        </div>
                        <input type="text" class="form-control input-lg" name="nm_img_acg" id="nm_img_acg" placeholder="'.geoloc_translate('Nom du fichier image').'" value="'.$nm_img_acg.'" required="required" />
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="w_ico">'.geoloc_translate('Largeur ic??ne des marqueurs').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="w_ico" id="w_ico" maxlength="3" placeholder="Largeur des images" value="'.$w_ico.'" required="required" />
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="h_ico">'.geoloc_translate('Hauteur ic??ne des marqueurs').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="h_ico" id="h_ico" maxlength="3" placeholder="Hauteur des images" value="'.$h_ico.'" required="required" />
                </div>
            </div>
        </fieldset>
        <fieldset id="para_svg" class="" style="padding-top: 16px; padding-right: 3px; padding-bottom: 6px;padding-left: 3px;">
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="f_mbg">'.geoloc_translate('Marqueur font SVG').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <div class="input-group">';
    $fafont = '';

    $fonts_svg = font::svg();

    foreach($fonts_svg as $v) 
    {
        if($v[0] == $f_mbg) 
        {
            $fafont = '&#x'.substr($v[1], 1).';'; 
        }
    }
    
    echo'
                    <div id="vis_ic" class="input-group-prepend"><span class="input-group-text"><span class="fa fa-lg id="fontchoice"">'.$fafont.'</span></div>
                        <select class="custom-select form-control input-lg" name="f_mbg" id="f_mbg">';
      
    foreach($fonts_svg as $v) 
    {
        if($v[0] == $f_mbg) 
        {
            $sel = 'selected="selected"'; 
        }
        else 
        {
            $sel = '';
        }
        
        echo '<option '.$sel.' value="'.$v[0].'">'.$v[2].'</option>';
    }
    
    echo '
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-4">
                    <div><span id="f_choice_mbg" class="fa fa-2x align-center" style="color:'.$mbg_f_co.';" >'.$fafont.'</span>&nbsp;<span>'.geoloc_translate('Membre').'</span></div>
                </div>
                <div class="col-4">
                    <div><i id="f_choice_mbgc" class="fa fa-2x align-center" style="color:'.$mbgc_f_co.';" >'.$fafont.'</i>&nbsp;<span>'.geoloc_translate('Membre en ligne').'</span></div>
                </div>
                <div class="col-4">
                    <div><i id="f_choice_acg" class="fa fa-2x align-center" style="color:'.$acg_f_co.';" >'.$fafont.'</i>&nbsp;<span>'.geoloc_translate('Anonyme en ligne').'</span></div>
                </div>
            </div>
            <div class="row">
                <div class="col-4 bkmbg">
                    <label class="col-form-label" for="mbg_f_co">'.geoloc_translate('Couleur fond').'</label>
                    <div class="input-group pickcol_fmb pickol">
                        <span class="input-group-prepend">
                            <span class="input-group-text colorpicker-input-addon bg-transparent"><i></i></span>
                        </span>
                        <input type="text" class="form-control" name="mbg_f_co" id="mbg_f_co" placeholder="'.geoloc_translate('Couleur du fond').'" value="'.$mbg_f_co.'" />
                    </div>
                </div>
                <div class="col-4">
                     <label class="col-form-label" for="mbgc_f_co">'.geoloc_translate('Couleur fond').'</label>
                        <div class="input-group pickcol_fmbc pickol">
                            <span class="input-group-prepend">
                                <span class="input-group-text colorpicker-input-addon bg-transparent"><i></i></span>
                            </span>
                            <input type="text" class="form-control" name="mbgc_f_co" id="mbgc_f_co" placeholder="'.geoloc_translate('Couleur du fond').'" value="'.$mbgc_f_co.'" />
                        </div>
                    </div>
                <div class="col-4">
                    <label class="col-form-label" for="acg_f_co">'.geoloc_translate('Couleur fond').'</label>
                    <div class="input-group pickcol_fac pickol">
                        <span class="input-group-prepend">
                            <span class="input-group-text colorpicker-input-addon bg-transparent"><i></i></span>
                            </span>
                            <input type="text" class="form-control" name="acg_f_co" id="acg_f_co" placeholder="'.geoloc_translate('Couleur du fond').'" value="'.$acg_f_co.'" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 bkmbg">
                        <label class="col-form-label" for="mbg_t_co">'.geoloc_translate('Couleur du trait').'</label>
                        <div class="input-group pickcol_tmb pickol">
                            <span class="input-group-prepend">
                                <span class="input-group-text colorpicker-input-addon bg-transparent"><i></i></span>
                            </span>
                            <input type="text" class="form-control" name="mbg_t_co" id="mbg_t_co" placeholder="'.geoloc_translate('Couleur du trait').'" value="'.$mbg_t_co.'" />
                        </div>
                    </div>
                    <div class="col-4">
                        <label class="col-form-label" for="mbgc_t_co">'.geoloc_translate('Couleur du trait').'</label>
                        <div class="input-group pickcol_tmbc pickol">
                            <span class="input-group-prepend">
                                <span class="input-group-text colorpicker-input-addon bg-transparent"><i></i></span>
                            </span>
                            <input type="text" class="form-control" name="mbgc_t_co" id="mbgc_t_co" placeholder="'.geoloc_translate('Couleur du trait').'" value="'.$mbgc_t_co.'" />
                        </div>
                    </div>
                    <div class="col-4" >
                        <label class="col-form-label" for="acg_t_co">'.geoloc_translate('Couleur du trait').'</label>
                        <div class="input-group pickcol_tac pickol">
                            <span class="input-group-prepend">
                                <span class="input-group-text colorpicker-input-addon bg-transparent"><i></i></span>
                            </span>
                            <input type="text" class="form-control" name="acg_t_co" id="acg_t_co" placeholder="'.geoloc_translate('Couleur du trait').'" value="'.$acg_t_co.'" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 bkmbg">
                        <label class="col-form-label" for="mbg_f_op">'.geoloc_translate('Opacit?? du fond').'</label>
                        <input type="number" step="any" min="0" max="1" class="form-control" name="mbg_f_op" id="mbg_f_op" value="'.$mbg_f_op.'" required="required" />
                    </div>
                    <div class="col-4">
                        <label class="col-form-label" for="mbgc_f_op">'.geoloc_translate('Opacit?? du fond').'</label>
                        <input type="number" step="any" min="0" max="1" class="form-control" name="mbgc_f_op" id="mbgc_f_op" value="'.$mbgc_f_op.'" required="required" />
                    </div>
                    <div class="col-4" >
                        <label class="col-form-label" for="acg_f_op">'.geoloc_translate('Opacit?? du fond').'</label>
                        <input type="number" step="any" min="0" max="1" class="form-control" name="acg_f_op" id="acg_f_op" value="'.$acg_f_op.'" required="required" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 bkmbg">
                        <label class="col-form-label" for="mbg_t_op">'.geoloc_translate('Opacit?? du trait').'</label>
                        <input type="number" step="any" min="0" max="1" class="form-control" name="mbg_t_op" id="mbg_t_op" value="'.$mbg_t_op.'" required="required" />
                    </div>
                    <div class="col-4">
                        <label class="col-form-label" for="mbgc_t_op">'.geoloc_translate('Opacit?? du trait').'</label>
                        <input type="number" step="any" min="0" max="1" class="form-control" name="mbgc_t_op" id="mbgc_t_op" value="'.$mbgc_t_op.'" required="required" />
                    </div>
                    <div class="col-4" >
                        <label class="col-form-label" for="acg_t_op">'.geoloc_translate('Opacit?? du trait').'</label>
                        <input type="number" step="any" min="0" max="1" class="form-control" name="acg_t_op" id="acg_t_op" value="'.$acg_t_op.'" required="required" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 bkmbg">
                        <label class="col-form-label" for="mbg_t_ep">'.geoloc_translate('Epaisseur du trait').'</label>
                        <input type="number" step="any" min="0" class="form-control" name="mbg_t_ep" id="mbg_t_ep" value="'.$mbg_t_ep.'" required="required" />
                    </div>
                    <div class="col-4">
                        <label class="col-form-label" for="mbgc_t_ep">'.geoloc_translate('Epaisseur du trait').'</label>
                        <input type="number" step="any" min="0" class="form-control" name="mbgc_t_ep" id="mbgc_t_ep" value="'.$mbgc_t_ep.'" required="required" />
                    </div>
                    <div class="col-4">
                        <label class="col-form-label" for="acg_t_ep">'.geoloc_translate('Epaisseur du trait').'</label>
                        <input type="number" step="any" min="0" class="form-control" name="acg_t_ep" id="acg_t_ep" value="'.$acg_t_ep.'" required="required" />
                  </div>
                </div>
                <div class="row">
                    <div class="col-4 bkmbg">
                        <label class="col-form-label" for="mbg_sc">'.geoloc_translate('Echelle').'</label>
                        <select class="custom-select form-control" name="mbg_sc" id="mbg_sc">
                            <option>10</option>
                            <option>11</option>
                            <option>12</option>
                            <option>14</option>
                            <option>16</option>
                            <option>18</option>
                            <option>20</option>
                            <option>22</option>
                            <option>24</option>
                            <option>26</option>
                            <option>28</option>
                            <option>30</option>
                            <option>32</option>
                            <option>36</option>
                            <option>38</option>
                            <option>40</option>
                            <option selected="selected">'.$mbg_sc.'</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="col-form-label" for="mbgc_sc">'.geoloc_translate('Echelle').'</label>
                        <select class="custom-select form-control" name="mbgc_sc" id="mbgc_sc">
                            <option>10</option>
                            <option>11</option>
                            <option>12</option>
                            <option>14</option>
                            <option>16</option>
                            <option>18</option>
                            <option>20</option>
                            <option>22</option>
                            <option>24</option>
                            <option>26</option>
                            <option>28</option>
                            <option>30</option>
                            <option>32</option>
                            <option>36</option>
                            <option>38</option>
                            <option>40</option>
                            <option selected="selected">'.$mbgc_sc.'</option>
                        </select>
                    </div>
                    <div class="col-4" >
                        <label class="col-form-label" for="acg_sc">'.geoloc_translate('Echelle').'</label>
                        <select class="custom-select form-control" name="acg_sc" id="acg_sc">
                            <option>10</option>
                            <option>11</option>
                            <option>12</option>
                            <option>14</option>
                            <option>16</option>
                            <option>18</option>
                            <option>20</option>
                            <option>22</option>
                            <option>24</option>
                            <option>26</option>
                            <option>28</option>
                            <option>30</option>
                            <option>32</option>
                            <option>36</option>
                            <option>38</option>
                            <option>40</option>
                            <option selected="selected">'.$acg_sc.'</option>
                        </select>
                    </div>
                </div>
            </fieldset>
            <hr />
            <h4 class="my-3">'.geoloc_translate('Interface bloc').'</h4>
            <fieldset class="" style="padding-top: 16px; padding-right: 3px; padding-bottom: 6px;padding-left: 3px;">
            <div class="form-group row">
                <label class="col-form-label col-sm-6" for="cartyp_b">'.geoloc_translate('Type de carte').'<span class="text-danger ml-1">*</span></label>
                <div class="col-sm-6">
                    <select class="custom-select form-control" name="cartyp_b" id="cartyp_b">';
      
    $j = 0;
    foreach($fond_provider as $v) 
    {
        if($v[0] == $cartyp_b) 
        {
            $sel = 'selected="selected"'; 
        }
        else 
        {
            $sel = '';
        }
         
        switch($j)
        {
            case '0': 
                echo '<optgroup label="OpenStreetMap">';
            break;

            case '1':
                echo '<optgroup label="Stamen">';
            break;

            case '4': 
                echo '<optgroup label="Mapbox">';
            break;
            
            case '6': 
                if($api_key_bing ==! '')
                { 
                    echo '<optgroup label="Bing maps">'; 
                }
                else 
                {
                    if($api_key =! '' and $api_key_bing == '') 
                    {
                        echo '<optgroup label="Google maps">';
                    }
                } 
            break;

            case '9': 
                echo '<optgroup label="Google maps">';
            break;
        }
         
        echo '<option '.$sel.' value="'.$v[0].'">'.$v[1].'</option>';
         
        switch($j)
        {
            case '0': 
            case '3': 
            case '5': 
            case '9': 
                echo '</optgroup>'; 
                break;
        }
        $j++;
    }
      
    echo '
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-6" for="img_mbgb">'.geoloc_translate('Image membre g??or??f??renc??').'<span class="text-danger ml-1">*</span></label>
            <div class="col-sm-6">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span id="v_img_mbgb" class="input-group-text"><img src="'.$ch_img.$img_mbgb.'" /></span>
                    </div>
                    <input type="text" class="form-control" name="img_mbgb" id="img_mbgb" placeholder="Nom du fichier image" value="'.$img_mbgb.'" required="required" />
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-6" for="w_ico_b">'.geoloc_translate('Largeur ic??ne des marqueurs').'<span class="text-danger ml-1">*</span></label>
            <div class="col-sm-6">
                <input type="number" class="form-control" name="w_ico_b" id="w_ico_b" placeholder="Chemin des images" value="'.$w_ico_b.'" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-6" for="h_ico_b">'.geoloc_translate('Hauteur ic??ne des marqueurs').'<span class="text-danger ml-1">*</span></label>
            <div class="col-sm-6">
                <input type="number" class="form-control" name="h_ico_b" id="h_ico_b" placeholder="Chemin des images" value="'.$h_ico_b.'" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-6" for="h_b">'.geoloc_translate('Hauteur de la carte dans le bloc').'<span class="text-danger ml-1">*</span></label>
            <div class="col-sm-6">
                <input type="number" class="form-control" name="h_b" id="h_b" placeholder="'.geoloc_translate('Hauteur de la carte dans le bloc').'" value="'.$h_b.'" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-sm-6" for="z_b">'.geoloc_translate('Zoom').'<span class="text-danger ml-1">*</span></label>
            <div class="col-sm-6">
                <select class="custom-select form-control" name="z_b" id="z_b">
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                    <option>6</option>
                    <option>7</option>
                    <option>8</option>
                    <option>9</option>
                    <option>10</option>
                    <option>11</option>
                    <option>12</option>
                    <option>13</option>
                    <option>14</option>
                    <option>15</option>
                    <option>16</option>
                    <option>17</option>
                    <option>18</option>
                    <option>19</option>
                    <option selected="selected">'.$z_b.'</option>
                </select>
            </div>
        </div>
        </fieldset>
        <div class="form-group row">
            <div class="col-sm-6 ml-sm-auto">
                <button type="submit" class="btn btn-primary">'.geoloc_translate('Sauver').'</button>
            </div>
        </div>
        <input type="hidden" name="op" value="Extend-Admin-SubModule" />
        <input type="hidden" name="ModPath" value="'.$ModPath.'" />
        <input type="hidden" name="ModStart" value="'.$ModStart.'" />
        <input type="hidden" name="subop" value="SaveSetgeoloc" />
        <input type="hidden" name="svg_path" value="" />
    </form>
    </div>
    <div class="col-sm-4">
        <div id="map_conf"></div>
            '.geoloc_translate('Ic??nes en service').'
        </div>
    </div>';

    $source_fond = '';
    switch ($cartyp) 
    {
        case 'OSM':
            $source_fond = 'new ol.source.OSM()';
        break;

        case 'sat-google':
            $source_fond = ' new ol.source.XYZ({url: "https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}",crossOrigin: "Anonymous", attributions: " &middot; <a href=\"https://www.google.at/permissions/geoguidelines/attr-guide.html\">Map data ??2015 Google</a>"})';
        break;

        case 'Road':
        case 'Aerial':
        case 'AerialWithLabels':
            $source_fond = 'new ol.source.BingMaps({key: "'.$api_key_bing.'",imagerySet: "'.$cartyp.'"})';
        break;

        case 'natural-earth-hypso-bathy': 
        case 'geography-class':
            $source_fond = ' new ol.source.TileJSON({url: "https://api.tiles.mapbox.com/v4/mapbox.'.$cartyp_b.'.json?access_token='.$api_key_mapbox.'"})';
        break;

        case 'terrain':
        case 'toner':
        case 'watercolor':
            $source_fond = 'new ol.source.Stamen({layer:"'.$cartyp.'"})';
        break;

        default:
        $source_fond = 'new ol.source.OSM()';
    }
      
    echo '
    <script type="text/javascript">
        //<![CDATA[
            $(document).ready(function() {
                $("head").append($("<script />").attr("src","assets/shared/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"));
                
                if (typeof ol=="undefined")
                    $("head").append($("<script />").attr({"type":"text/javascript","src":"assets/shared/ol/ol.js"}));
                
                $("head").append($("<script />").attr({"type":"text/javascript","src":"modules/geoloc/assets/js/fontawesome.js"}));
                
                $("head link[rel=\'stylesheet\']").last().after("<link rel=\'stylesheet\' href=\'/assets/shared/ol/ol.css\' type=\'text/css\' media=\'screen\'>");
                
                $("head link[rel=\'stylesheet\']").last().after("<link rel=\'stylesheet\' href=\'/modules/geoloc/assets/css/geoloc_admin.css\' type=\'text/css\' media=\'screen\'>");
            });

            function geoloc_conf() {
                var
                    w_ico_size = $("#w_ico").val(),
                    h_ico_size = $("#h_ico").val();

                $(document).ready(function() {

                    /*
                    $( "#api_key_bing").change(function() {
                        if($("#api_key_bing").val()!="") {
                            console.log($("#api_key_bing").val())
                        }; 
                    });
                    */

                    if(img_svg.checked) {
                        $("#para_ima input").prop("readonly", true), $("#para_svg input").prop("readonly", false), $("#f_mbg").prop("disabled", false)
                    }
                    
                    if(img_img.checked) {
                        $("#para_svg input").prop("readonly", true), $("#f_mbg").prop("disabled", true)
                    }

                    $("#geolocset").on("submit", function() {
                        $(".pickol").colorpicker("enable");
                        $("#f_mbg").prop("disabled", false);
                    });

                    $("#img_img").on("click", function(){
                        $("#para_svg input").prop("readonly", true);
                        $("#f_mbg").prop("disabled", true);
                        $(".pickol").colorpicker("disable");
                        $("#para_ima input").prop("readonly", false);
                    });

                    $("#img_svg").on("click", function(){
                        $("#para_svg input").prop("readonly", false);
                        $("#f_mbg").prop("disabled", false);
                        $(".pickol").colorpicker("enable");
                        $("#para_ima input").prop("readonly", true);
                    });

                    $( "#w_ico, #h_ico, #ch_img, #nm_img_mbg, #nm_img_mbcg, #nm_img_acg, #f_mbg" ).change(function() {
                        w_ico_size = $("#w_ico").val();
                        h_ico_size = $("#h_ico").val();
                        i_path_mbg = $("#ch_img").val()+$("#nm_img_mbg").val();
                        i_path_mbcg = $("#ch_img").val()+$("#nm_img_mbcg").val();
                        i_path_acg = $("#ch_img").val()+$("#nm_img_acg").val();
                        f_pa = $("#f_mbg option:selected").val();
                    }).trigger("change");
                
                    $(".pickcol_fmb, .pickcol_fmbc, .pickcol_fac, .pickcol_tmb, .pickcol_tmbc, .pickcol_tac").colorpicker({format:"rgb"});
                    var 
                        map_c,
                        w_ico_size,
                        h_ico_size,
                        mark_cmbg,
                        cartyp,
                        
                        mark_cmbg = new ol.Feature({geometry: new ol.geom.Point(ol.proj.fromLonLat([12, 48]))}),
                        
                        mark_cmbgc = new ol.Feature({geometry: new ol.geom.Point(ol.proj.fromLonLat([6, 45]))}),
                        
                        mark_cacg = new ol.Feature({geometry: new ol.geom.Point(ol.proj.fromLonLat([12, 40]))}),
                        
                        mark_cmbg_svg = new ol.Feature({geometry: new ol.geom.Point(ol.proj.fromLonLat([10, 10]))}),
                        
                        mark_cmbgc_svg = new ol.Feature({geometry: new ol.geom.Point(ol.proj.fromLonLat([1, 47]))}),
                        
                        mark_acg_svg = new ol.Feature({geometry: new ol.geom.Point(ol.proj.fromLonLat([5, 60]))});

                    mark_cmbg.setStyle(new ol.style.Style({
                        image: new ol.style.Icon({
                            crossOrigin: "anonymous",
                            src: "'.$ch_img.$nm_img_mbg.'",
                            imgSize:['.$w_ico_b.','.$h_ico_b.']
                        })
                    }));
                    
                    mark_cmbgc.setStyle(new ol.style.Style({
                        image: new ol.style.Icon({
                            crossOrigin: "anonymous",
                            src: "'.$ch_img.$nm_img_mbcg.'",
                            imgSize:['.$w_ico_b.','.$h_ico_b.']
                        })
                    }));

                    mark_cacg.setStyle(new ol.style.Style({
                        image: new ol.style.Icon({
                            crossOrigin: "anonymous",
                            src: "'.$ch_img.$nm_img_acg.'",
                            imgSize:['.$w_ico_b.','.$h_ico_b.']
                        })
                    }));

                    mark_cmbg_svg.setStyle(new ol.style.Style({
                        text: new ol.style.Text({
                            text: fa("'.$f_mbg.'"),
                            font: "900 '.$mbg_sc.'px \'Font Awesome 5 Free\'",
                            bottom: "Bottom",
                            fill: new ol.style.Fill({color: "'.$mbg_f_co.'"}),
                            stroke: new ol.style.Stroke({color: "'.$mbg_t_co.'", width: '.$mbg_t_ep.'})
                        })
                    }));
                    
                    mark_cmbgc_svg.setStyle(new ol.style.Style({
                        text: new ol.style.Text({
                            text: fa("'.$f_mbg.'"),
                            font: "900 '.$mbgc_sc.'px \'Font Awesome 5 Free\'",
                            bottom: "Bottom",
                            fill: new ol.style.Fill({color: "'.$mbgc_f_co.'"}),
                            stroke: new ol.style.Stroke({color: "'.$mbgc_t_co.'", width: '.$mbgc_t_ep.'})
                        })
                    }));
                    
                    mark_acg_svg.setStyle(new ol.style.Style({
                        text: new ol.style.Text({
                            text: fa("'.$f_mbg.'"),
                            font: "900 '.$acg_sc.'px \'Font Awesome 5 Free\'",
                            bottom: "Bottom",
                            fill: new ol.style.Fill({color: "'.$acg_f_co.'"}),
                            stroke: new ol.style.Stroke({color: "'.$acg_t_co.'", width: '.$acg_t_ep.'})
                        })
                    }));

                    var src_markers = new ol.source.Vector({
                        features: [mark_cmbg, mark_cmbgc, mark_cacg, mark_cmbg_svg, mark_cmbgc_svg, mark_acg_svg]
                    });
                     
                    var les_markers = new ol.layer.Vector({source: src_markers});

                    var src_fond = '.$source_fond.';
                    var fond_carte = new ol.layer.Tile({
                        source: '.$source_fond.'
                    });

                    var attribution = new ol.control.Attribution({collapsible: true});
                    var map = new ol.Map({
                        interactions: new ol.interaction.defaults({
                            constrainResolution: true, onFocusOnly: true
                        }),
                        controls: new ol.control.defaults({attribution: false}).extend([attribution, new ol.control.FullScreen()]),
                        target: "map_conf",
                        layers: [
                            fond_carte,
                            les_markers
                        ],
                        view: new ol.View({
                            center: ol.proj.fromLonLat([0, 45]),
                            zoom: 3
                        })
                    });

                    var coul_temp;

                    /*
                    "Je suis le marker (image au format .gif .jpg .png) symbolisant un membre du site g&#xE9;or&#xE9;f&#xE9;renc&#xE9;.");
                    "Je suis le marker (image au format .gif .jpg .png) symbolisant un membre du site g&#xE9;or&#xE9;f&#xE9;renc&#xE9; actuellement connect?? sur le site.");
                    "Je suis le marker (image au format .gif .jpg .png) symbolisant un visiteur actuellement connect?? sur le site g??olocalis?? par son adresse IP");
                    "Je suis le marker (image au format SVG) symbolisant un membre du site g&#xE9;or&#xE9;f&#xE9;renc&#xE9;");
                    "Je suis le marker (image au format SVG) symbolisant un membre du site g&#xE9;or&#xE9;f&#xE9;renc&#xE9; actuellement connect?? sur le site.");
                    "Je suis le marker (image au format SVG) symbolisant un visiteur actuellement connect?? sur le site g??olocalis?? par son adresse IP.");
                    */

                    // size dont work ?? revoir
                    $( "#w_ico, #h_ico, #ch_img, #nm_img_mbg, #nm_img_mbcg, #nm_img_acg" ).change(function() {
                        w_ico_size = $("#w_ico").val();
                        h_ico_size = $("#h_ico").val();
                        mark_cmbg.setStyle(new ol.style.Style({
                            image: new ol.style.Icon({
                                crossOrigin: "anonymous",
                                src: $("#ch_img").val()+$("#nm_img_mbg").val(),
                                imgSize:[w_ico_size,h_ico_size]
                            })
                        }));

                        mark_cmbgc.setStyle(new ol.style.Style({
                            image: new ol.style.Icon({
                                crossOrigin: "anonymous",
                                src: $("#ch_img").val()+$("#nm_img_mbcg").val(),
                                imgSize:[w_ico_size,h_ico_size]
                            })
                        }));

                        mark_cacg.setStyle(new ol.style.Style({
                            image: new ol.style.Icon({
                                crossOrigin: "anonymous",
                                src: $("#ch_img").val()+$("#nm_img_acg").val(),
                                imgSize:[w_ico_size,h_ico_size]
                            })
                        }));

                        $("#v_img_mbg").html("<img width=\"22\" height=\"22\" alt=\"'.geoloc_translate('Image membre g??or??f??renc??').'\" src=\""+$("#ch_img").val()+$("#nm_img_mbg").val()+"\" />");
                        
                        $("#v_img_mbcg").html("<img width=\"22\" height=\"22\" alt=\"'.geoloc_translate('Image membre g??or??f??renc?? en ligne').'\" src=\""+$("#ch_img").val()+$("#nm_img_mbcg").val()+"\" />");
                        
                        $("#v_img_acg").html("<img width=\"22\" height=\"22\" alt=\"'.geoloc_translate('Image anonyme g??or??f??renc?? en ligne').'\" src=\""+$("#ch_img").val()+$("#nm_img_acg").val()+"\" />");
                    })

                    var changestyle = function(m,f_fa,fc,tc,sc) {
                        m.setStyle(new ol.style.Style({
                            text: new ol.style.Text({
                                text: fa(f_fa),
                                font: "900 "+sc+"px \'Font Awesome 5 Free\'",
                                bottom: "Bottom",
                                fill: new ol.style.Fill({color: fc}),
                                stroke: new ol.style.Stroke({color: tc, width: '.$mbg_t_ep.'})
                            })
                        }));
                    }

                    //==> change font on the map
                    $("#f_mbg").change(function(event) {
                        var
                            f_fa = $("#f_mbg option:selected").val(),
                            fc_m = $("#mbg_f_co").val(),
                            fc_mo = $("#mbgc_f_co").val(),
                            fc_a = $("#acg_f_co").val(),
                            tc_m = $("#mbg_t_co").val(),
                            tc_mo = $("#mbgc_t_co").val(),
                            tc_a = $("#acg_t_co").val(),
                            sc_m = $("#mbg_sc option:selected").val(),
                            sc_mo = $("#mbgc_sc option:selected").val(),
                            sc_a = $("#acg_sc option:selected").val();

                        changestyle(mark_cmbg_svg,f_fa,fc=fc_m,tc=tc_m,sc=sc_m);
                        changestyle(mark_cmbgc_svg,f_fa,fc=fc_mo,tc=tc_mo,sc=sc_mo);
                        changestyle(mark_acg_svg,f_fa,fc=fc_a,tc=tc_a,sc=sc_a);
                         
                        $("#f_choice_mbg,#f_choice_mbgc,#f_choice_acg").html(fa(f_fa));
                        $("#vis_ic").html(\'<span class="input-group-text"><span id="fontchoice" class="fa fa-lg">\'+fa(f_fa)+\'</span></span>\');
                    })


                    $("#ch_img, #img_mbgb").change(function() {
                        $("#v_img_mbgb").html("<img width=\"22\" height=\"22\" alt=\"'.geoloc_translate('Image membre g??or??f??renc??').'\" src=\""+$("#ch_img").val()+$("#img_mbgb").val()+"\" />");
                    })

                    //==> aux changements de taille
                    $("#mbg_sc").change(function() {
                        var f_fa = $("#f_mbg option:selected").val();
                        var fc = $("#mbg_f_co").val();
                        var tc = $("#mbg_t_co").val();
                        var sc = $("#mbg_sc option:selected").val();
                        changestyle(mark_cmbg_svg,f_fa,fc,tc,sc);
                    });

                    $("#mbgc_sc").change(function() {
                        var f_fa = $("#f_mbg option:selected").val();
                        var fc = $("#mbgc_f_co").val();
                        var tc = $("#mbgc_t_co").val();
                        var sc = $("#mbgc_sc option:selected").val();
                        changestyle(mark_cmbgc_svg,f_fa,fc,tc,sc);
                    });

                    $("#acg_sc").change(function() {
                        var f_fa = $("#f_mbg option:selected").val();
                        var fc = $("#acg_f_co").val();
                        var tc = $("#acg_t_co").val();
                        var sc = $("#acg_sc option:selected").val();
                        changestyle(mark_acg_svg,f_fa,fc,tc,sc);
                    });
                    //<== aux changements de taille

                    //==> aux changements de couleurs fond
                    $("#mbg_f_co").change(function(){
                        var f_fa = $("#f_mbg option:selected").val();
                        var fc = $("#mbg_f_co").val();
                        var tc = $("#mbg_t_co").val();
                        var sc = $("#mbg_sc option:selected").val();
                        changestyle(mark_cmbg_svg,f_fa,fc,tc,sc);
                        $("#f_choice_mbg").attr("style","color:"+fc);
                    });
                  
                    $("#mbgc_f_co").change(function(){
                        var f_fa = $("#f_mbg option:selected").val();
                        var fc = $("#mbgc_f_co").val();
                        var tc = $("#mbgc_t_co").val();
                        var sc = $("#mbgc_sc option:selected").val();
                        changestyle(mark_cmbgc_svg,f_fa,fc,tc,sc);
                        $("#f_choice_mbgc").attr("style","color:"+fc);
                    });
                    
                    $("#acg_f_co").change(function(){
                        var f_fa = $("#f_mbg option:selected").val();
                        var fc = $("#acg_f_co").val();
                        var tc = $("#acg_t_co").val();
                        var sc = $("#acg_sc option:selected").val();
                        changestyle(mark_acg_svg,f_fa,fc,tc,sc);
                        $("#f_choice_acg").attr("style","color:"+fc);
                    });
                    //<== aux changements de couleurs fond
                    
                    /*
                    $("#mbg_f_op").change(function() {
                        icon_mbg_svg.fillOpacity = Number($("#mbg_f_op").val());
                        mark_cmbg_svg.setIcon(icon_mbg_svg);
                    });

                    $("#mbgc_f_op").change(function() {
                        icon_cmbg_svg.fillOpacity = Number($("#mbgc_f_op").val());
                        mark_cmbgc_svg.setIcon(icon_cmbg_svg);
                    });

                    $("#acg_f_op").change(function() {
                        icon_cacg_svg.fillOpacity = Number($("#acg_f_op").val());
                        mark_acg_svg.setIcon(icon_cacg_svg);
                    });

                    $("#mbg_t_op").change(function() {
                        icon_mbg_svg.strokeOpacity = Number($("#mbg_t_op").val());
                        mark_cmbg_svg.setIcon(icon_mbg_svg);
                    });

                    $("#mbgc_t_op").change(function() {
                        icon_cmbg_svg.strokeOpacity = Number($("#mbgc_t_op").val());
                        mark_cmbgc_svg.setIcon(icon_cmbg_svg);
                    });

                    $("#acg_t_op").change(function() {
                        icon_cacg_svg.strokeOpacity = Number($("#acg_t_op").val());
                        mark_acg_svg.setIcon(icon_cacg_svg);
                    });

                    $("#mbg_t_ep").change(function() {
                        icon_mbg_svg.strokeWeight = Number($("#mbg_t_ep").val());
                        mark_cmbg_svg.setIcon(icon_mbg_svg);
                    });

                    $("#mbgc_t_ep").change(function() {
                        icon_cmbg_svg.strokeWeight = Number($("#mbgc_t_ep").val());
                        mark_cmbgc_svg.setIcon(icon_cmbg_svg);
                    });

                    $("#acg_t_ep").change(function() {
                        icon_cacg_svg.strokeWeight = Number($("#acg_t_ep").val());
                        mark_acg_svg.setIcon(icon_cacg_svg);
                    });
                    */

                    $(".pickcol_tmb").colorpicker().on("changeColor.colorpicker", function(event){
                        var coul = event.color.toHex()
                        icon_mbg_svg.strokeColor=coul;
                        mark_cmbg_svg.setIcon(icon_mbg_svg);
                    });

                    $(".pickcol_tmbc").colorpicker().on("changeColor.colorpicker", function(event){
                        var coul = event.color.toHex()
                        icon_cmbg_svg.strokeColor=coul;
                        mark_cmbgc_svg.setIcon(icon_cmbg_svg);
                    });

                    $(".pickcol_tac").colorpicker().on("changeColor.colorpicker", function(event){
                        var coul = event.color.toHex()
                        icon_cacg_svg.strokeColor=coul;
                        mark_acg_svg.setIcon(icon_cacg_svg);
                    });

                    $("#cartyp").on("change", function() {
                        cartyp = $( "#cartyp option:selected" ).val();
                     
                        switch (cartyp) {
                            case "OSM":
                                fond_carte.setSource(new ol.source.OSM());
                            break;

                            case "Road":case "Aerial":case "AerialWithLabels":
                                fond_carte.setSource(new ol.source.BingMaps({key: "'.$api_key_bing.'",imagerySet: cartyp }));
                            break;

                            case "natural-earth-hypso-bathy": case "geography-class":
                                fond_carte.setSource(new ol.source.TileJSON({url: "https://api.tiles.mapbox.com/v4/mapbox."+cartyp+".json?access_token='.$api_key_mapbox.'"}));
                            break;
                            case "terrain": case "toner": case "watercolor":
                                fond_carte.setSource(new ol.source.Stamen({layer:cartyp}));
                            break;

                            case "sat-google":
                                fond_carte.setSource(new ol.source.XYZ({url: "https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}",crossOrigin: "Anonymous", attributions: " &middot; <a href=\"https://www.google.at/permissions/geoguidelines/attr-guide.html\">Map data ??2015 Google</a>"}));
                            break;
                        }
                    });
                });
            }

            window.onload = geoloc_conf;

        //]]>
    </script>';
    
    css::adminfoot('', '', '', '');
}

/**
 * [SaveSetgeoloc description]
 * @param [type] $api_key_bing   [description]
 * @param [type] $api_key_mapbox [description]
 * @param [type] $ch_lat         [description]
 * @param [type] $ch_lon         [description]
 * @param [type] $cartyp         [description]
 * @param [type] $geo_ip         [description]
 * @param [type] $api_key_ipdata [description]
 * @param [type] $co_unit        [description]
 * @param [type] $mark_typ       [description]
 * @param [type] $ch_img         [description]
 * @param [type] $nm_img_acg     [description]
 * @param [type] $nm_img_mbcg    [description]
 * @param [type] $nm_img_mbg     [description]
 * @param [type] $w_ico          [description]
 * @param [type] $h_ico          [description]
 * @param [type] $f_mbg          [description]
 * @param [type] $mbg_sc         [description]
 * @param [type] $mbg_t_ep       [description]
 * @param [type] $mbg_t_co       [description]
 * @param [type] $mbg_t_op       [description]
 * @param [type] $mbg_f_co       [description]
 * @param [type] $mbg_f_op       [description]
 * @param [type] $mbgc_sc        [description]
 * @param [type] $mbgc_t_ep      [description]
 * @param [type] $mbgc_t_co      [description]
 * @param [type] $mbgc_t_op      [description]
 * @param [type] $mbgc_f_co      [description]
 * @param [type] $mbgc_f_op      [description]
 * @param [type] $acg_sc         [description]
 * @param [type] $acg_t_ep       [description]
 * @param [type] $acg_t_co       [description]
 * @param [type] $acg_t_op       [description]
 * @param [type] $acg_f_co       [description]
 * @param [type] $acg_f_op       [description]
 * @param [type] $cartyp_b       [description]
 * @param [type] $img_mbgb       [description]
 * @param [type] $w_ico_b        [description]
 * @param [type] $h_ico_b        [description]
 * @param [type] $h_b            [description]
 * @param [type] $z_b            [description]
 * @param [type] $ModPath        [description]
 * @param [type] $ModStart       [description]
 */
function SaveSetgeoloc($api_key_bing, $api_key_mapbox, $ch_lat, $ch_lon, $cartyp, $geo_ip, $api_key_ipdata, $co_unit, $mark_typ, $ch_img, $nm_img_acg, $nm_img_mbcg, $nm_img_mbg, $w_ico, $h_ico, $f_mbg, $mbg_sc, $mbg_t_ep, $mbg_t_co, $mbg_t_op, $mbg_f_co, $mbg_f_op, $mbgc_sc, $mbgc_t_ep, $mbgc_t_co, $mbgc_t_op, $mbgc_f_co, $mbgc_f_op, $acg_sc, $acg_t_ep, $acg_t_co, $acg_t_op, $acg_f_co, $acg_f_op, $cartyp_b, $img_mbgb, $w_ico_b, $h_ico_b, $h_b, $z_b, $ModPath, $ModStart, $provider_ip) {

    $providers = array(
        'https://ipapi.co',
        'https://api.ipdata.co',
        'https://extreme-ip-lookup.com',
        'http://ip-api.com'       
    );

    $file_conf = fopen("modules/geoloc/config/geoloc.php", "w+");
    $content = "<?php \n";
    $content .= "/**\n";
    $content .= "* Npds Two\n";
    $content .= "*\n";
    $content .= "* Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier\n";
    $content .= "*\n"; 
    $content .= "*\n"; 
    $content .= "* module geoloc version 4.0\n";
    $content .= "* geoloc.php file 2008-".date('Y')." by Jean Pierre Barbary (jpb)\n";
    $content .= "*\n";
    $content .= "* @author Nicolas2\n";
    $content .= "* @version 1.0\n";
    $content .= "* @date 02/04/2021\n";
    $content .= "*/\n\n\n";
    $content .= "// clef api bing maps\n";
    $content .= "\$api_key_bing = \"$api_key_bing\";\n\n";
    $content .= "// clef api mapbox\n";
    $content .= "\$api_key_mapbox = \"$api_key_mapbox\";\n\n";
    $content .= "// Champ lat dans sql\n";
    $content .= "\$ch_lat = \"$ch_lat\";\n\n";
    $content .= "// Champ long dans sql\n";
    $content .= "\$ch_lon = \"$ch_lon\";\n";
    $content .= "\n";
    $content .= "// interface carte\n\n";
    $content .= "// Type de carte\n";
    $content .= "\$cartyp = \"$cartyp\";\n\n";
    $content .= "// Coordinates Units\n";
    $content .= "\$co_unit = \"$co_unit\";\n\n";
    $content .= "// Chemin des images\n";
    $content .= "\$ch_img = \"$ch_img\";\n\n";
    $content .= "// Autorisation de g??olocalisation des IP\n";
    $content .= "\$geo_ip = $geo_ip;\n\n";
    $content .= "// Clef API pour provider IP\n";
    $content .= "\$api_key_ipdata = \"$api_key_ipdata\";\n\n";
    $content .= "// Nom fichier image anonyme g??or??f??renc?? en ligne\n";
    $content .= "\$nm_img_acg = \"$nm_img_acg\";\n\n";
    $content .= "// Nom fichier image membre g??or??f??renc?? en ligne\n";
    $content .= "\$nm_img_mbcg = \"$nm_img_mbcg\";\n\n";
    $content .= "// Nom fichier image membre g??or??f??renc??\n";
    $content .= "\$nm_img_mbg = \"$nm_img_mbg\";\n\n";
    $content .= "// Type de marker\n";
    $content .= "\$mark_typ = $mark_typ;\n\n";
    $content .= "// Largeur icone des markers\n";
    $content .= "\$w_ico = \"$w_ico\";\n\n";
    $content .= "// Hauteur icone des markers\n";
    $content .= "\$h_ico = \"$h_ico\";\n\n";
    $content .= "// Font SVG\n";
    $content .= "\$f_mbg = \"$f_mbg\";\n\n";
    $content .= "// Echelle du Font SVG du membre\n";
    $content .= "\$mbg_sc = \"$mbg_sc\";\n\n";
    $content .= "// Epaisseur trait Font SVG du membre\n";
    $content .= "\$mbg_t_ep = \"$mbg_t_ep\";\n\n";
    $content .= "// Couleur trait SVG du membre\n";
    $content .= "\$mbg_t_co = \"$mbg_t_co\";\n\n";
    $content .= "// Opacit?? trait SVG du membre\n";
    $content .= "\$mbg_t_op = \"$mbg_t_op\";\n\n";
    $content .= "// Couleur fond SVG du membre\n";
    $content .= "\$mbg_f_co = \"$mbg_f_co\";\n\n";
    $content .= "// Opacit?? fond SVG du membre\n";
    $content .= "\$mbg_f_op = \"$mbg_f_op\";\n\n";
    $content .= "// Echelle du Font SVG du membre g??or??f??renc??\n";
    $content .= "\$mbgc_sc = \"$mbgc_sc\";\n\n";
    $content .= "// Epaisseur trait Font SVG du membre g??or??f??renc??\n";
    $content .= "\$mbgc_t_ep = \"$mbgc_t_ep\";\n\n";
    $content .= "// Couleur trait SVG du membre g??or??f??renc??\n";
    $content .= "\$mbgc_t_co = \"$mbgc_t_co\"; \n\n";
    $content .= "// Opacit?? trait SVG du membre g??or??f??renc??\n";
    $content .= "\$mbgc_t_op = \"$mbgc_t_op\";\n\n";
    $content .= "// Couleur fond SVG du membre g??or??f??renc??\n";
    $content .= "\$mbgc_f_co = \"$mbgc_f_co\";\n\n";
    $content .= "// Opacit?? fond SVG du membre g??or??f??renc??\n";
    $content .= "\$mbgc_f_op = \"$mbgc_f_op\";\n\n";
    $content .= "// Echelle du Font SVG pour anonyme en ligne\n";
    $content .= "\$acg_sc = \"$acg_sc\";\n\n";
    $content .= "// Epaisseur trait Font SVG pour anonyme en ligne\n";
    $content .= "\$acg_t_ep = \"$acg_t_ep\";\n\n";
    $content .= "// Couleur trait SVG pour anonyme en ligne\n";
    $content .= "\$acg_t_co = \"$acg_t_co\";\n\n";
    $content .= "// Opacit?? trait SVG pour anonyme en ligne\n";
    $content .= "\$acg_t_op = \"$acg_t_op\";\n\n";
    $content .= "// Couleur fond SVG pour anonyme en ligne\n";
    $content .= "\$acg_f_co = \"$acg_f_co\";\n\n";
    $content .= "// Opacit?? fond SVG pour anonyme en ligne\n";
    $content .= "\$acg_f_op = \"$acg_f_op\";\n";
    $content .= "\n";
    $content .= "// interface bloc \n";
    $content .= "\n";
    $content .= "// Type de carte pour le bloc\n";
    $content .= "\$cartyp_b = \"$cartyp_b\";\n\n";
    $content .= "// Nom fichier image membre g??or??f??renc?? pour le bloc\n";
    $content .= "\$img_mbgb = \"$img_mbgb\";\n\n";
    $content .= "// Largeur icone marker dans le bloc\n";
    $content .= "\$w_ico_b = \"$w_ico_b\";\n\n"; 
    $content .= "// Hauteur icone marker dans le bloc\n";
    $content .= "\$h_ico_b = \"$h_ico_b\";\n\n";
    $content .= "// hauteur carte dans bloc\n";
    $content .= "\$h_b = \"$h_b\";\n\n";
    $content .= "// facteur zoom carte dans bloc\n";
    $content .= "\$z_b = \"$z_b\";\n\n";
    $content .= "// Provider Ip : $providers[$provider_ip]\n";
    $content .= "\$provider_select = \"$provider_ip\";\n";

    fwrite($file_conf, $content);
    fclose($file_conf);
}

if ($admin) 
{
    switch ($subop) 
    {
        case 'vidip':
            vidip();
            Configuregeoloc($subop, $ModPath, $ModStart, $ch_lat, $ch_lon, $cartyp, $geo_ip);
        break;

        case 'SaveSetgeoloc':
            SaveSetgeoloc($api_key_bing, $api_key_mapbox, $ch_lat, $ch_lon, $cartyp, $geo_ip, $api_key_ipdata, $co_unit, $mark_typ, $ch_img, $nm_img_acg, $nm_img_mbcg, $nm_img_mbg, $w_ico, $h_ico, $f_mbg, $mbg_sc, $mbg_t_ep, $mbg_t_co, $mbg_t_op, $mbg_f_co, $mbg_f_op, $mbgc_sc, $mbgc_t_ep, $mbgc_t_co, $mbgc_t_op, $mbgc_f_co, $mbgc_f_op, $acg_sc, $acg_t_ep, $acg_t_co, $acg_t_op, $acg_f_co, $acg_f_op, $cartyp_b, $img_mbgb, $w_ico_b, $h_ico_b, $h_b,$z_b, $ModPath, $ModStart, $provider_ip);
            Configuregeoloc($subop, $ModPath, $ModStart, $ch_lat, $ch_lon, $cartyp, $geo_ip, $api_key_ipdata);
        break;

        default:
            Configuregeoloc($subop, $ModPath, $ModStart, $ch_lat, $ch_lon, $cartyp, $geo_ip, $api_key_ipdata);
        break;
    }
}
