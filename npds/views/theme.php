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
        echo "<span class=\"titrea\">$title</span><br />".meta_lang($hometext)."<br />".meta_lang($bodytext)."<br />".meta_lang($notes);
    }

}
