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
namespace npds\views;

use npds\language\metalang;


/*
 * theme
 */
class theme {


    /**
     * Permet d'alterner entre les CLASS (CSS) LIGNA et LIGNB
     * @return [type] [description]
     */
    public static function tablos() 
    {
        static $colorvalue;
           
        if ($colorvalue == "class=\"ligna\"") 
        {
            $colorvalue = "class=\"lignb\"";
        } 
        else 
        {
            $colorvalue = "class=\"ligna\"";
        }
           
        return $colorvalue;
    }

    /**
     * Retourne le chemin complet si l'image est trouvée dans le répertoire image du thème sinon false
     * @param  [type] $theme_img [description]
     * @return [type]            [description]
     */
    public static function theme_image($theme_img) 
    {
        global $theme;
            
        if (@file_exists("themes/$theme/images/$theme_img")) 
        {
            return "themes/$theme/images/$theme_img";
        } 
        else 
        {
            return false;
        }
    }

    /**
     * Permet de prévisualiser la présentation d'un NEW
     * @param  [type] $title    [description]
     * @param  [type] $hometext [description]
     * @param  string $bodytext [description]
     * @param  string $notes    [description]
     * @return [type]           [description]
     */
    public static function themepreview($title, $hometext, $bodytext='', $notes='') 
    {
        echo "<span class=\"titrea\">$title</span><br />".metalang::meta_lang($hometext)."<br />".metalang::meta_lang($bodytext)."<br />".meta_lang($notes);
    }

    /**
     * 
     */
    public static function getUsetOrDefaultThemeAndSkin()
    {
        global $Default_Theme, $Default_Skin, $user;
           
        if (isset($user) and $user != '') 
        {
            global $cookie;
            if($cookie[9] != '') 
            {
                $ibix = explode('+', urldecode($cookie[9]));
                
                if (array_key_exists(0, $ibix))
                { 
                    $theme = $ibix[0]; 
                }
                else 
                {
                    $theme = $Default_Theme;
                }
                
                if (array_key_exists(1, $ibix))
                {
                    $skin = $ibix[1]; 
                }
                else 
                {
                    $skin = $Default_Skin;  
                }
                
                $tmp_theme = $theme;
                
                if (!$file = @opendir("themes/$theme"))
                {
                    $tmp_theme = $Default_Theme;
                } 
            } 
            else 
            {
                $tmp_theme = $Default_Theme;
            }
        } 
        else 
        {
            $theme = $Default_Theme;
            $skin = $Default_Skin;
            $tmp_theme = $theme;
        }

        return [$theme, $skin, $tmp_theme];
    }


}
