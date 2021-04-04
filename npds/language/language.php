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
namespace npds\language;


/*
 * language
 */
class language {


    /**
     * Analyse le contenu d'une chaine et converti la section correspondante 
     * ([langue] OU [!langue] ...[/langue]) 
     * &agrave; la langue / [transl] ... [/transl] 
     * permet de simuler un appel translate("xxxx")
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function aff_langue($ibid) 
    {
        global $language, $tab_langue;
        
        // copie du tableau + rajout de transl pour gestion de 
        // l'appel à translate(...); - Theme Dynamic
        $tab_llangue = $tab_langue;
        $tab_llangue[] = 'transl';
           
        reset($tab_llangue);
           
        $ok_language = false;
        $trouve_language = false;

        foreach($tab_llangue as $key => $lang) 
        {
            $pasfin = true; 
            $pos_deb = false; 
            $abs_pos_deb = false; 
            $pos_fin = false;

            while ($pasfin) 
            {
                // tags [langue] et [/langue]
                $pos_deb = strpos($ibid, "[$lang]", 0);
                $pos_fin = strpos($ibid, "[/$lang]", 0);
                
                if ($pos_deb === false) 
                {
                	$pos_deb = -1;
                }
                
                if ($pos_fin === false) 
                {
                	$pos_fin = -1;
                }
                
                // tags [!langue]
                $abs_pos_deb = strpos($ibid, "[!$lang]", 0);
                
                if ($abs_pos_deb !== false) 
                {
                    $ibid = str_replace("[!$lang]", "[$lang]", $ibid);
                    $pos_deb = $abs_pos_deb;
                    
                    if ($lang != $language) 
                    {
                    	$trouve_language = true;
                    }
                }

                $decal = strlen($lang)+2;
                 
                if (($pos_deb >= 0) and ($pos_fin >= 0)) 
                {
                    $fragment = substr($ibid, $pos_deb+$decal, ($pos_fin-$pos_deb-$decal));
                    
                    if ($trouve_language == false) 
                    {
                        if ($lang != 'transl')
                        {
                            $ibid = str_replace("[$lang]".$fragment."[/$lang]", $fragment, $ibid);
                        }
                        else
                        {
                            $ibid = str_replace("[$lang]".$fragment."[/$lang]", translate($fragment), $ibid);
                        }
                        
                        $ok_language = true;
                    } else {
                        if ($lang != 'transl')
                        {
                            $ibid = str_replace("[$lang]".$fragment."[/$lang]", "", $ibid);
                        }
                        else
                        {
                            $ibid = str_replace("[$lang]".$fragment."[/$lang]", translate($fragment), $ibid);
                        }
                    }
                } 
                else
                {
                    $pasfin = false;
                }
			}
            
            if ($ok_language)
            {
                $trouve_language = true;
            }
        }

        return $ibid;
    }

    /**
     * Charge le tableau TAB_LANGUE qui est utilisé par les fonctions multi-langue
     * @return [type] [description]
     */
    public static function make_tab_langue() 
    {
        global $language, $languageslist;
           
        $languageslocal = $language.' '.str_replace($language, '', $languageslist);
        $languageslocal = trim(str_replace('  ', ' ', $languageslocal));
        $tab_langue = explode(' ', $languageslocal);
        
        return $tab_langue;
    }

    /**
     * Charge une zone de formulaire de selection de la langue
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function aff_localzone_langue($ibid) 
    {
        global $tab_langue;
           
        //reset($tab_langue);
           
        $M_langue = '
        <div class="form-group">
            <select name="'.$ibid.'" class="custom-select form-control" onchange="this.form.submit()">
                <option value="">'.translate("Choisir une langue").'</option>';
        
        foreach($tab_langue as $bidon => $langue) 
        {
            $M_langue .= '
                <option value="'.$langue.'">'.translate("$langue").'</option>';
        }
        
        $M_langue .= '
                    <option value="">- '.translate("Aucune langue").'</option>
                </select>
            </div>
            <noscript>
            <input class="btn btn-primary" type="submit" name="local_sub" value="'.translate("Valider").'" />
            </noscript>';
        
        return $M_langue;
    }

    /**
     * Charge une FORM de selection de langue $ibid_index = URL de la Form, 
     * $ibid = nom du champ
     * @param  string $mess       [description]
     * @param  [type] $ibid_index [description]
     * @param  [type] $ibid       [description]
     * @return [type]             [description]
     */
    public static function aff_local_langue($mess='' , $ibid_index, $ibid) 
    {
        if ($ibid_index == '') 
        {
            global $REQUEST_URI;
            $ibid_index = $REQUEST_URI;
        }
           
        $M_langue = '<form action="'.$ibid_index.'" name="local_user_language" method="post">';
        $M_langue .= $mess.static::aff_localzone_langue($ibid);
        $M_langue .= '</form>';
           
        return $M_langue;
    }

    /**
     * appel la fonction aff_langue en modifiant temporairement la valeur de la langue
     * @param  [type] $local_user_language [description]
     * @param  [type] $ibid                [description]
     * @return [type]                      [description]
     */
    public static function preview_local_langue($local_user_language, $ibid) 
    {
        if ($local_user_language) 
        {
            global $language, $tab_langue;
              
            $old_langue = $language;
            $language = $local_user_language;
            $tab_langue = static::make_tab_langue();
            $ibid = static::aff_langue($ibid);
            $language = $old_langue;
        }
           
        return $ibid;
    }

    /**
     * renvoi le code language iso 639-1 et code pays ISO 3166-2 
     * $l=> 0 ou 1(requis), 
     * $s (séparateur - | _), 
     * $c=> 0 ou 1 (requis)
     * @param  [type] $l [description]
     * @param  [type] $s [description]
     * @param  [type] $c [description]
     * @return [type]    [description]
     */
    public static function language_iso($l, $s, $c) 
    {
        global $language;
            
        $iso_lang = '';
        $iso_country = '';
        $ietf = '';
            
        switch ($language) 
        {
            case "french": 
                $iso_lang = 'fr';
                $iso_country = 'FR'; 
            break;

            case "english": 
                $iso_lang = 'en';
                $iso_country = 'US'; 
            break;

            case "spanish": 
                $iso_lang = 'es';
                $iso_country = 'ES'; 
            break;

            case "german": 
                $iso_lang = 'de';
                $iso_country = 'DE'; 
            break;

            case "chinese": 
                $iso_lang = 'zh';
                $iso_country = 'CN'; 
            break;

            default:
            break;
        }
            
        if ($c !== 1) 
        {
        	$ietf = $iso_lang;
        }

        if (($l == 1) and ($c == 1)) 
        {
        	$ietf = $iso_lang.$s.$iso_country;
        }

        if (($l !== 1) and ($c == 1)) 
        {
        	$ietf = $iso_country;
        }

        if (($l !== 1) and ($c !== 1)) 
        {
        	$ietf = '';
        }

        if (($l == 1) and ($c !== 1)) 
        {
        	$ietf = $iso_lang;
        }
        
        return $ietf;
    }

}
