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
namespace npds\assets;

use npds\language\language;


/**
 * css
 */
class css {


    /**
     * Charge la CSS complémentaire
     * le HTML ne contient que de simple quote pour être compatible avec javascript
     * @param  [type] $tmp_theme     [description]
     * @param  [type] $language      [description]
     * @param  [type] $fw_css        [description]
     * @param  string $css_pages_ref [description]
     * @param  string $css           [description]
     * @return [type]                [description]
     */
    public static function import_css_javascript($tmp_theme, $language, $fw_css, $css_pages_ref='', $css='') 
    {
        $tmp = '';

        // CSS framework
        if (file_exists("themes/_skins/$fw_css/bootstrap.min.css"))
        {
            $tmp .= "<link href='themes/_skins/$fw_css/bootstrap.min.css' rel='stylesheet' type='text/css' media='all' />\n";
        }
           
        // CSS standard 
        if (file_exists("themes/$tmp_theme/style/$language-style.css")) 
        {
            $tmp .= "<link href='themes/$tmp_theme/style/$language-style.css' title='default' rel='stylesheet' type='text/css' media='all' />\n";
              
            if (file_exists("themes/$tmp_theme/style/$language-style-AA.css"))
            {
                $tmp .= "<link href='themes/$tmp_theme/style/$language-style-AA.css' title='alternate stylesheet' rel='alternate stylesheet' type='text/css' media='all' />\n";
            }
              
            if (file_exists("themes/$tmp_theme/style/$language-print.css"))
            {
                $tmp .= "<link href='themes/$tmp_theme/style/$language-print.css' rel='stylesheet' type='text/css' media='print' />\n";
            }
        } 
        else if (file_exists("themes/$tmp_theme/style/style.css")) 
        {
            $tmp .= "<link href='themes/$tmp_theme/style/style.css' title='default' rel='stylesheet' type='text/css' media='all' />\n";
              
            if (file_exists("themes/$tmp_theme/style/style-AA.css"))
            {
                $tmp .= "<link href='themes/$tmp_theme/style/style-AA.css' title='alternate stylesheet' rel='alternate stylesheet' type='text/css' media='all' />\n";
            }
              
            if (file_exists("themes/$tmp_theme/style/print.css"))
            {
                $tmp .= "<link href='themes/$tmp_theme/style/print.css' rel='stylesheet' type='text/css' media='print' />\n";
            }
        } 
        else 
        {
            $tmp .= "<link href='themes/default/style/style.css' title='default' rel='stylesheet' type='text/css' media='all' />\n";
        }
           
        // Chargeur CSS spécifique
        if ($css_pages_ref) 
        {
            include ("themes/pages.php");
            
            if (is_array($PAGES[$css_pages_ref]['css'])) 
            {
                foreach ($PAGES[$css_pages_ref]['css'] as $tab_css) 
                {
                    $admtmp = '';
                    $op = substr($tab_css, -1);
                    
                    if ($op == '+' or $op == '-')
                    {
                        $tab_css = substr($tab_css, 0, -1);
                    }
                    
                    if (stristr($tab_css, 'http://') || stristr($tab_css, 'https://')) 
                    {
                       $admtmp = "<link href='$tab_css' rel='stylesheet' type='text/css' media='all' />\n";
                    } 
                    else 
                    {
                        if (file_exists("themes/$tmp_theme/style/$tab_css") and ($tab_css != '')) 
                        {
                            $admtmp = "<link href='themes/$tmp_theme/style/$tab_css' rel='stylesheet' type='text/css' media='all' />\n";
                        } 
                        elseif (file_exists("$tab_css") and ($tab_css != '')) 
                        {
                            $admtmp = "<link href='$tab_css' rel='stylesheet' type='text/css' media='all' />\n";
                        }
                    }
                    
                    if ($op == '-')
                    {
                        $tmp = $admtmp;
                    }
                    else
                    {
                        $tmp .= $admtmp;
                    }
                }
            }
            else
            {
                $oups = $PAGES[$css_pages_ref]['css'];
                
                settype($oups, 'string');
                
                $op = substr($oups, -1);
                $css = substr($oups, 0, -1);
                
                if (($css != '') and (file_exists("themes/$tmp_theme/style/$css"))) 
                {
                    if ($op == '-')
                    {
                        $tmp = "<link href='themes/$tmp_theme/style/$css' rel='stylesheet' type='text/css' media='all' />\n";
                    }
                    else
                    {
                        $tmp .= "<link href='themes/$tmp_theme/style/$css' rel='stylesheet' type='text/css' media='all' />\n";
                    }
                }
            }
        }
        
        return $tmp;
    }

    /**
     * Fonctionnement identique à import_css_javascript sauf que le code HTML 
     * en retour ne contient que de double quote
     * @param  [type] $tmp_theme     [description]
     * @param  [type] $language      [description]
     * @param  [type] $fw_css        [description]
     * @param  [type] $css_pages_ref [description]
     * @param  [type] $css           [description]
     * @return [type]                [description]
     */
    public static function import_css($tmp_theme, $language, $fw_css, $css_pages_ref, $css) 
    {
           return (str_replace("'", "\"", static::import_css_javascript($tmp_theme, $language, $fw_css, $css_pages_ref, $css)));
    }

    #autodoc adminfoot($fv,$fv_parametres,$arg1,$foo) : 
    /**
     * fin d'affichage avec form validateur ou pas, ses parametres (js), 
     * fermeture div admin et inclusion footer.php  
     * $fv => fv : inclusion du validateur de form
     *  
     * $fv_parametres => éléments de l'objet fields differents input 
     *     (objet js ex :   xxx: {},...) 
     *     si !###! est trouvé dans la variable la partie du code suivant sera 
     *     inclu à la fin de la fonction d'initialisation
     *  
     * $arg1 => js pur au début du script js
     *  
     * $foo =='' ==> </div> et inclusion footer.php
     *  
     * $foo =='foo' ==> inclusion footer.php
     * 
     * @param  [type] $fv            [description]
     * @param  [type] $fv_parametres [description]
     * @param  [type] $arg1          [description]
     * @param  [type] $foo           [description]
     * @return [type]                [description]
     */
    public static function adminfoot($fv, $fv_parametres, $arg1, $foo) 
    {
        global $minpass;
           
        if ($fv == 'fv') 
        {
            if($fv_parametres != '') 
            {
                $fv_parametres = explode('!###!', $fv_parametres);
            }
              
            echo '
            <script type="text/javascript" src="assets/js/es6-shim.min.js"></script>
            <script type="text/javascript" src="assets/shared/formvalidation/dist/js/FormValidation.full.min.js"></script>
            <script type="text/javascript" src="assets/shared/formvalidation/dist/js/locales/'.language::language_iso(1, "_", 1).'.min.js"></script>
            <script type="text/javascript" src="assets/shared/formvalidation/dist/js/plugins/Bootstrap.min.js"></script>
            <script type="text/javascript" src="assets/shared/formvalidation/dist/js/plugins/L10n.min.js"></script>
            <script type="text/javascript" src="assets/js/checkfieldinp.js"></script>
            <script type="text/javascript">
            //<![CDATA[
                '.$arg1.'
                var diff;
                document.addEventListener("DOMContentLoaded", function(e) {
                    // validateur pour mots de passe
                    const strongPassword = function() {
                    const bar = $("#passwordMeter_cont");
                    return {
                        validate: function(input) {
                            var score=0;
                            const value = input.value;
                            if (value === "") {
                                return {
                                    valid: true,
                                    score:null,
                                };
                            }
                            if (value === value.toLowerCase()) {
                                bar.removeClass().addClass("progress-bar bg-danger");
                                return {
                                    valid: false,
                                    message: "Le mot de passe doit contenir au moins un caractère en majuscule.",
                                };
                            }
                            if (value === value.toUpperCase()) {
                                bar.removeClass().addClass("progress-bar bg-danger");
                                return {
                                    valid: false,
                                    message: "Le mot de passe doit contenir au moins un caractère en minuscule.",
                                };
                            }
                            if (value.search(/[0-9]/) < 0) {
                                bar.removeClass().addClass("progress-bar bg-danger");
                                return {
                                    valid: false,
                                    message: "Le mot de passe doit contenir au moins un chiffre.",
                                };
                            }
                            if (value.search(/[@\+\-!#$%&^~*_]/) < 0) {
                                bar.removeClass().addClass("progress-bar bg-danger");
                                return {
                                    valid: false,
                                    message: "Le mot de passe doit contenir au moins un caractère non numérique et non alphabétique.",
                                };
                            }
                            if (value.length < 8) {
                                bar.removeClass().addClass("progress-bar bg-danger");
                                return {
                                    valid: false,
                                    message: "Le mot de passe doit contenir plus de 8 caractères.",
                                };
                            }

                            score += ((value.length >= '.$minpass.') ? 1 : -1);
                            if (/[A-Z]/.test(value)) score += 1;
                            if (/[a-z]/.test(value)) score += 1; 
                            if (/[0-9]/.test(value)) score += 1;
                            if (/[@\+\-!#$%&^~*_]/.test(value)) score += 1; 
                            return {
                                valid: true,
                                score: score,
                            };
                        },
                    };
                };
                // enregistré comme nouveau validateur nommé checkPassword
                FormValidation.validators.checkPassword = strongPassword;

                formulid.forEach(function(item, index, array) {
                    const fvitem = FormValidation.formValidation(
                        document.getElementById(item),{
                            locale: "'.language::language_iso(1, "_", 1).'",
                            localization: FormValidation.locales.'.language::language_iso(1, "_", 1).',
                            fields: {';
           
            if($fv_parametres != '')
            {
                echo '
                '.$fv_parametres[0];
            }
           
            echo '
                },
                plugins: {
                    declarative: new FormValidation.plugins.Declarative({
                       html5Input: true,
                    }),
                    trigger: new FormValidation.plugins.Trigger(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                    bootstrap: new FormValidation.plugins.Bootstrap(),
                    icon: new FormValidation.plugins.Icon({
                        valid: "fa fa-check",
                        invalid: "fa fa-times",
                        validating: "fa fa-sync",
                        onPlaced: function(e) {
                            e.iconElement.addEventListener("click", function() {
                             fvitem.resetField(e.field);
                            });
                        },
                    }),
                },
            })
            .on("core.validator.validated", function(e) {
            // voir si on a plus de champs mot de passe : changer par un array de champs ...
                if ((e.field === "add_pwd" || e.field === "chng_pwd" || e.field === "pass" || e.field === "add_pass" || e.field === "code") && e.validator === "checkPassword") {
                    score = e.result.score;
                    const bar = $("#passwordMeter_cont");
                    switch (true) {
                        case (score === null):
                            bar.css("width", "100%").removeClass().addClass("progress-bar bg-danger");
                            bar.attr("value","100");
                        break;
                        case (score > 4):
                            bar.css("width", "100%").removeClass().addClass("progress-bar bg-success");
                            bar.attr("aria-valuenow","100");
                            bar.attr("value","100").removeClass().addClass("progress-bar bg-success");
                        break;
                        default:
                        break;
                    }
                }
                if (e.field === "B1" && e.validator === "promise") {
                    //const preview = document.getElementById("avatarPreview");
                    if (e.result.valid && e.result.meta && e.result.meta.source) {
                        $("#ava_perso").removeClass("border-danger").addClass("border-success")
                    } else if (!e.result.valid) {
                       $("#ava_perso").addClass("border-danger")
                    }
                }
            });';
              
            if($fv_parametres != '')
            {
                if(array_key_exists(1, $fv_parametres))
                {
                    echo '
                    '.$fv_parametres[1];
                }
            }
           
            echo '
            })
            });
            //]]>
            </script>';
        }
           
        switch($foo) 
        {
            case '' :
                echo '
                </div>';
                include ('footer.php');
            break;
            
            case 'foo' :
                include ('footer.php');
            break;
        }
    }

}
