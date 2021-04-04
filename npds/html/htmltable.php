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
namespace npds\html;


/*
 * error
 */
class htmltable {


    /**
     * Ouverture de tableaux pour le thème : return
     * @return [type] [description]
     */
    public static function sub_opentable() 
    {
        if (function_exists("opentable_theme")) 
        {
            $content = opentable_theme();
        } 
        else 
        {
            $content = "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" class=\"ligna\"><tr><td>\n";
            $content .= "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"6\" class=\"lignb\"><tr><td>\n";
        }

        return $content;
    }

    /**
     * Ouverture de tableaux pour le thème : echo
     * @return [type] [description]
     */
    public static function opentable() 
    {
           echo static::sub_opentable();
    }

    /**
     * Fermeture de tableaux pour le thème : return
     * @return [type] [description]
     */
    public static function sub_closetable() 
    {
        if (function_exists("closetable_theme")) 
        {
            return closetable_theme();
        } 
        else 
        {
            return "</td></tr></table></td></tr></table>\n";
        }
    }

    /**
     * Fermeture de tableaux pour le thème : echo
     * @return [type] [description]
     */
    public static function closetable() 
    {
           echo static::sub_closetable();
    }

    /**
     * Ouverture de tableaux pour le thème : return
     * @return [type] [description]
     */
    public static function sub_opentable2() 
    {
        if (function_exists("opentable2_theme")) 
        {
            $content = opentable2_theme();
        } 
        else 
        {
            $content = "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\" class=\"ligna\"><tr><td>\n";
            $content .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"6\" class=\"lignb\"><tr><td>\n";
        }

        return $content;
    }

    /**
     * Ouverture de tableaux pour le thème : echo
     * @return [type] [description]
     */
    public static function opentable2() 
    {
       echo static::sub_opentable2();
    }

    /**
     * Fermeture de tableaux pour le thème : return
     * @return [type] [description]
     */
    public static function sub_closetable2() 
    {
        if (function_exists("opentable2_theme")) 
        {
            $content = closetable2_theme();
        } 
        else 
        {
            return "</td></tr></table></td></tr></table>\n";
        }

        return $content;
    }

    /**
     * Fermeture de tableaux pour le thème : echo
     * @return [type] [description]
     */
    public static function closetable2() 
    {
           echo static::sub_closetable2();
    }

}