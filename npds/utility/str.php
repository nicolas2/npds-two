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
 * str
 */
class str {


    /**
     * [addslashes_GPC description]
     * @param  [type] &$arr [description]
     * @return [type]       [description]
     */
    public static function addslashes_GPC(&$arr) 
    {
        $arr = addslashes($arr);
    }

    /**
     * [changetoamp description]
     * @param  [type] $r [description]
     * @return [type]    [description]
     */
    public static function changetoamp($r) 
    { 
        return str_replace('&', '&amp;', $r[0]);
    }

    /**
     * [changetoampadm description]
     * @param  [type] $r [description]
     * @return [type]    [description]
     */
    public static function changetoampadm($r) 
    { 
        return static::changetoamp($r);
    }

    /**
     * Formate une chaine numérique avec un espace tous les 3 chiffres / cheekybilly 2005
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function wrh($ibid) 
    {
        $tmp = number_format($ibid, 0, ',', ' ');
        $tmp = str_replace(' ', '&nbsp;', $tmp);
        
        return $tmp;
    }

    /**
     * convertie \r \n  BR ... en br XHTML
     * @param  [type] $txt [description]
     * @return [type]      [description]
     */
    public static function conv2br($txt) 
    {
        $Xcontent = str_replace("\r\n", "<br />", $txt);
        $Xcontent = str_replace("\r", "<br />", $Xcontent);
        $Xcontent = str_replace("\n", "<br />", $Xcontent);
        $Xcontent = str_replace("<BR />" ,"<br />", $Xcontent);
        $Xcontent = str_replace("<BR>", "<br />", $Xcontent);
        
        return $Xcontent;
    }
  
    /**
     * Les 8 premiers caractères sont convertis en UNE valeur Hexa unique
     * @param  [type] $txt [description]
     * @return [type]      [description]
     */
    public static function hexfromchr($txt) 
    {
        $surlignage = substr(md5($txt), 0, 8);
        $tmp = 0;
        
        for ($ix = 0; $ix <= 5; $ix++) 
        {
            $tmp += hexdec($surlignage[$ix])+1;
        }

        return $tmp%=16;
    }

    /**
     * Découpe la chaine en morceau de $slpit longueur si celle-ci ne contient pas d'espace
     * @param  [type] $msg   [description]
     * @param  [type] $split [description]
     * @return [type]        [description]
     */
    public static function split_string_without_space($msg, $split) 
    {
        $Xmsg = explode(' ', $msg);
        array_walk($Xmsg, [str::class, 'wrapper_f'], $split);
        $Xmsg = implode(' ', $Xmsg);
        
        return $Xmsg;
    }

    /**
     * Fonction Wrapper pour split_string_without_space
     * @param  [type] &$string [description]
     * @param  [type] $key     [description]
     * @param  [type] $cols    [description]
     * @return [type]          [description]
     */
    public static function wrapper_f(&$string, $key, $cols) 
    {
        $outlines = '';
        if (strlen($string) > $cols) 
        {
            while(strlen($string) > $cols) 
            {
                $cur_pos = 0;
                for($num=0; $num < $cols-1; $num++) 
                {
                    $outlines .= $string[$num];
                    $cur_pos++;
                    
                    if ($string[$num] == "\n") 
                    {
                        $string = substr($string, $cur_pos, (strlen($string)-$cur_pos));
                        $cur_pos = 0;
                        $num = 0;
                    }
                }

                $outlines .= '<i class="fa fa-cut fa-lg"> </i>';
                $string = substr($string, $cur_pos, (strlen($string)-$cur_pos));
            }

            $string = $outlines.$string;
        }
    }

    /**
     * Quote une chaîne contenant des
     * @param string $what [description]
     */
    public static function FixQuotes($what = '') 
    {
        $what = str_replace("&#39;", "'", $what);
        $what = str_replace("'", "''", $what);
        
        while (preg_match("#\\\\'#", $what)) 
        {
            $what = preg_replace("#\\\\'#", "'", $what);
        }
           
        return $what;
    }

    /**
     * Controle de réponse 
     * note : c'est pas encore assez fin not work with https probably
     * @param  [type]  $url           [description]
     * @param  integer $response_code [description]
     * @return [type]                 [description]
     */
    function file_contents_exist($url, $response_code = 200) 
    {
        $headers = get_headers($url);
        
        if (substr($headers[0], 9, 3) == $response_code)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
