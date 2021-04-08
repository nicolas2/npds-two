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
namespace npds\editeur;

use npds\language\language;


/*
 * tiny mce
 */
class tiny {


    /**
     * Charge l'éditeur ... ou non : $Xzone = nom du textarea
     * $Xactiv = deprecated 
     * si $Xzone="custom" on utilise $Xactiv pour passer des paramètres spécifiques
     * @param  [type] $Xzone  [description]
     * @param  [type] $Xactiv [description]
     * @return [type]         [description]
     */
    public static function aff_editeur($Xzone, $Xactiv) 
    {
        global $language, $tmp_theme, $tiny_mce, $tiny_mce_theme, $tiny_mce_relurl;
        
        $tmp = '';
        
        if ($tiny_mce) 
        {
            static $tmp_Xzone;
            
            if ($Xzone == 'tiny_mce') 
            {
                if ($Xactiv == 'end') 
                {
                    if (substr($tmp_Xzone, -1) == ',')
                    {
                        $tmp_Xzone = substr_replace($tmp_Xzone, '', -1);
                    }

                    if ($tmp_Xzone) 
                    {
                        $tmp = "
                        <script type=\"text/javascript\">
                            //<![CDATA[
                                $(document).ready(function() {
                                    tinymce.init({
                                        selector: 'textarea.tin',
                                        branding:false,
                                        height: 300,
                                        theme : 'silver',
                                        mobile: { theme: 'mobile' },
                                        language : '".language::language_iso(1, '', '')."',";
                        
                        include ("assets/shared/editeur/tinymce/themes/advanced/npds.conf.php");
                   
                        $tmp .= '
                                    });
                                });
                            //]]>
                        </script>';
                    }
                 } 
                 else
                 {
                     $tmp .= '<script type="text/javascript" src="assets/shared/editeur/tinymce/tinymce.min.js"></script>';
                 }
              } 
              else 
              {
                if ($Xzone != 'custom')
                {
                     $tmp_Xzone .= $Xzone.',';
                 }
                else
                {
                     $tmp_Xzone .= $Xactiv.',';
                 }
            }
        } 
        else
        {
               $tmp = '';
           }

        return $tmp;
    }

}
