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
namespace npds\utility;


/*
 * code
 */
class code {


    /**
     * Analyse le contenu d'une chaîne et converti les pseudo-balises 
     * [code]...[/code] et leur contenu en html
     * @param  [type] $r [description]
     * @return [type]    [description]
     */
    public static function change_cod($r) 
    {
        return '<'.$r[2].' class="language-'.$r[3].'">'.htmlentities($r[5], ENT_COMPAT|ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML401, cur_charset).'</'.$r[2].'>';
    }

    /**
     * [af_cod description]
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function af_cod($ibid) 
    {
        $pat = '#(\[)(\w+)\s+([^\]]*)(\])(.*?)\1/\2\4#s';
        $ibid = preg_replace_callback($pat, [code::class, 'change_cod'], $ibid, -1, $nb);
        
        return $ibid;
    }
 
    /**
     * Analyse le contenu d'une chaîne et converti les balises html 
     * <code>...</code> en pseudo-balises [code]...[/code]
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function desaf_cod($ibid) 
    {
        $pat = '#(<)(\w+)\s+(class="language-)([^">]*)(">)(.*?)\1/\2>#';
        
        $ibid = preg_replace_callback($pat, [code::class, 'rechange_cod'], $ibid, -1);
        
        return $ibid;
    }
 
    /**
     * [rechange_cod description]
     * @param  [type] $r [description]
     * @return [type]    [description]
     */
    public static function rechange_cod($r) 
    {
        return '['.$r[2].' '.$r[4].']'.$r[6].'[/'.$r[2].']';
    }

    /**
     * Analyse le contenu d'une chaîne et converti les balises [code]...[/code]
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function aff_code($ibid) 
    {
        $pasfin = true;
        while ($pasfin) 
        {
            $pos_deb = strpos($ibid, "[code]", 0);
            $pos_fin = strpos($ibid, "[/code]", 0);
              
            // ne pas confondre la position ZERO et NON TROUVE !
            if ($pos_deb === false) 
            {
                $pos_deb = -1;
            }

            if ($pos_fin === false) 
            {
                $pos_fin = -1;
            }

            if (($pos_deb >= 0) and ($pos_fin >= 0)) 
            {
                ob_start();
                    highlight_string(substr($ibid, $pos_deb+6, ($pos_fin-$pos_deb-6)));
                    $fragment = ob_get_contents();
                ob_end_clean();
                
                $ibid = str_replace(substr($ibid, $pos_deb, ($pos_fin-$pos_deb+7)), $fragment, $ibid);
            } 
            else 
            {
                $pasfin = false;
            }
        }

        return $ibid;
    }

}
