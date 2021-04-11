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
namespace modules\fmanager\support;

use npds\security\hack;
use npds\groupes\groupe;


/*
 * picmanager
 */
class picmanager {

 
    /*Gestion Ascii étendue*
     * 
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function extend_ascii($ibid) 
    {
        $tmp = urlencode($ibid);
        $tmp = str_replace("%82", "È", $tmp);
        $tmp = str_replace("%85", "‡", $tmp);
        $tmp = str_replace("%87", "Á", $tmp);
        $tmp = str_replace("%88", "Í", $tmp);
        $tmp = str_replace("%97", "˘", $tmp);
        $tmp = str_replace("%8A", "Ë", $tmp);
        $tmp = urldecode($tmp);
       
        return $tmp;
    }
 
    /**
     * Gestion des fichiers autorisés
     * @param  [type] $type      [description]
     * @param  [type] $filename  [description]
     * @param  [type] $Extension [description]
     * @return [type]            [description]
     */
    public static function fma_filter($type, $filename, $Extension) 
    {
        $autorise = false;
        $error = '';
        
        if ($type == 'f') 
        {
            $filename = hack::remove($filename);
        }

        $filename = preg_replace('#[/\\\:\*\?"<>|]#i', '', rawurldecode($filename));
        $filename = str_replace('..', '', $filename);

        // Liste des extensions autorisées
        $suffix = strtoLower(substr(strrchr( $filename, '.' ), 1 ));
        if (($suffix != '') or ($type == 'd')) 
        {
            if ((in_array($suffix, $Extension)) or ($Extension[0] == '*') or $type == 'd') 
            {
                // Fichiers interdits en fonction de qui est connecté
                if (static::fma_autorise($type, $filename)) 
                {
                    $autorise = true;
                } 
                else 
                {
                    $error = fma_translate("Fichier interdit");
                }
            } 
            else 
            {
                $error = fma_translate("Type de fichier interdit");
            }
        } 
        else 
        {
            $error = fma_translate("Fichier interdit");
        }

        $tab[] = $autorise;
        $tab[] = $error;
        $tab[] = $filename;
       
        return $tab;
    }
 
    /**
     * Gestion des autorisations sur les répertoires et les fichiers
     * @param  [type] $type [description]
     * @param  [type] $dir  [description]
     * @return [type]       [description]
     */
    public static function fma_autorise($type, $dir)  
    {
        global $user, $admin, $dirlimit_fma, $ficlimit_fma, $access_fma, $dir_minuscptr, $fic_minuscptr;
        /* ==> 
        was noticeSss on if($type f) in some case ...Coriace ..... 
        add set type de la variable $autorise_arbo=false;
        controle de l'index dans le array and array_key_exists($dir, $ficlimit_fma))
        et if(isset($autorise_arbo)) au lieu de if($autorise_arbo)
        cohérence de la correction et de ses implications encore incertaine
        à suivre !!
         <==*/
        $autorise_arbo = false;
        
        if ($type == 'a')
        {
            $autorise_arbo = $access_fma;
        }
        
        if ($type == 'd')
        {
            $autorise_arbo = $dirlimit_fma[$dir];
        }
        
        if ($type == 'f')
        {
            if(array_key_exists($dir, $ficlimit_fma))
            {
                $autorise_arbo = $ficlimit_fma[$dir];
            }
        }  

        if (isset($autorise_arbo)) 
        {
            $auto_dir = '';
                
            if (($autorise_arbo == 'membre') and ($user))
            {
                $auto_dir = true;
            }
            elseif (($autorise_arbo == 'anonyme') and (!$user))
            {
                $auto_dir = true;
            }
            elseif (($autorise_arbo == 'admin') and ($admin))
            {
                $auto_dir = true;
            }
            elseif (($autorise_arbo != 'membre') and ($autorise_arbo != 'anonyme') and ($autorise_arbo != 'admin') and ($user)) 
            {
                 
                $tab_groupe = groupe::valid_group($user);
                if ($tab_groupe) 
                {
                    foreach($tab_groupe as $groupevalue) 
                    {
                        $tab_auto = explode(',', $autorise_arbo);
                        foreach($tab_auto as $gp) 
                        {
                            if ($gp > 0) 
                            {
                                if ($groupevalue == $gp) 
                                {
                                    $auto_dir = true;
                                    break;
                                }
                            } 
                            else 
                            {
                                $auto_dir = true;
                                if (-$groupevalue == $gp) 
                                {
                                    $auto_dir = false;
                                    break;
                                }
                            }
                        }

                        if ($auto_dir) 
                        {
                            break;
                        }
                    }
                }
            }
        } 
        else
        {
            $auto_dir = true;
        }
            
        if ($auto_dir != true) 
        {
            if ($type == 'd')
            {
                $dir_minuscptr++;
            }

            if ($type == 'f')
            {
                $fic_minuscptr++;
            }
        }

        return $auto_dir;
    }

    /**
     * [imagesize description]
     * @param  [type] $name      [description]
     * @param  [type] $Max_thumb [description]
     * @return [type]            [description]
     */
    public static function imagesize($name, $Max_thumb) 
    {
        $size = getimagesize($name);
       
        //hauteur
        $h_i = $size[1]; 
       
        //largeur
        $w_i = $size[0]; 

        if (($h_i > $Max_thumb) || ($w_i > $Max_thumb)) 
        {
            if ($h_i > $w_i) 
            {
                $convert = $Max_thumb/$h_i;
                $h_i = $Max_thumb;
                $w_i = ceil($w_i*$convert);
            } 
            else 
            {
                $convert = $Max_thumb/$w_i;
                $w_i = $Max_thumb;
                $h_i = ceil($h_i*$convert);
            }
        }

        $s_img['hauteur'][0] = $h_i;
        $s_img['hauteur'][1] = $size[1];
        $s_img['largeur'][0] = $w_i;
        $s_img['largeur'][1] = $size[0];
        
        return $s_img;
    }

    /**
     * [CreateThumb description]
     * @param [type] $Image       [description]
     * @param [type] $Source      [description]
     * @param [type] $Destination [description]
     * @param [type] $Max         [description]
     * @param [type] $ext         [description]
     */
    public static function CreateThumb($Image, $Source, $Destination, $Max, $ext) 
    {
        switch ($ext) 
        {
            case (preg_match('/jpeg|jpg/i', $ext) ? true : false) :
                if (function_exists('imagecreatefromjpeg'))
                {
                    $src = @imagecreatefromjpeg($Source.$Image);
                }
            break;

            case (preg_match('/gif/i', $ext) ? true : false) :
                if (function_exists('imagecreatefromgif'))
                {
                    $src = @imagecreatefromgif($Source.$Image);
                }
            break;

            case (preg_match('/png/i', $ext) ? true : false) :
                if (function_exists('imagecreatefrompng'))
                {
                    $src = @imagecreatefrompng($Source.$Image);
                }
            break;
        }

        $size = imagesize($Source.'/'.$Image, $Max);
        $h_i = $size['hauteur'][0]; //hauteur
        $w_i = $size['largeur'][0]; //largeur

        if ($src) 
        {
            if (function_exists('imagecreatetruecolor'))
            {
                $im = @imagecreatetruecolor($w_i, $h_i);
            }
            else
            {
                $im = @imagecreate($w_i, $h_i);
            }

            @imagecopyresized($im, $src, 0, 0, 0, 0, $w_i, $h_i, $size['largeur'][1], $size['hauteur'][1]);
            @imageinterlace ($im, 1);

            switch ($ext) 
            {
                case (preg_match('/jpeg|jpg/i', $ext) ? true : false) :
                    @imagejpeg($im, $Destination.$Image, 100);
                break;

                case (preg_match('/gif/i', $ext) ? true : false) :
                    @imagegif($im, $Destination.$Image);
                break;

                case (preg_match('/png/i', $ext) ? true : false) :
                    @imagepng($im, $Destination.$Image, 6);
                break;
            }

            @chmod($Dest.$Image, 0766);
            $size['gene-img'][0] = true;
        }

        return $size;
    }

}