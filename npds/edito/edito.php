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
namespace npds\edito;

use npds\time\time;
use npds\language\metalang;
use npds\language\language;


/*
 * edito
 */
class edito {


    /**
     * Construit l'edito
     * @return [type] [description]
     */
    public static function fab_edito() 
    {
        global $cookie;
           
        if (isset($cookie[3])) 
        {
            if (file_exists("storage/static/edito_membres.txt")) 
            {
                $fp = fopen("storage/static/edito_membres.txt", "r");
                if (filesize("storage/static/edito_membres.txt") > 0)
                {
                    $Xcontents = fread($fp, filesize("storage/static/edito_membres.txt"));
                }
                fclose($fp);
            } 
            else 
            {
                if (file_exists("storage/static/edito.txt")) 
                {
                    $fp = fopen("storage/static/edito.txt", "r");
                    if (filesize("storage/static/edito.txt") > 0)
                    {
                        $Xcontents = fread($fp, filesize("storage/static/edito.txt"));
                    }
                    fclose($fp);
                }
            }
        } 
        else 
        {
            if (file_exists("storage/static/edito.txt")) 
            {
                $fp = fopen("storage/static/edito.txt", "r");
                if (filesize("storage/static/edito.txt") > 0)
                {
                    $Xcontents = fread($fp, filesize("storage/static/edito.txt"));
                }
                fclose($fp);
            }
        }

        $affich = false;
        $Xibid = strstr($Xcontents, 'aff_jours');
        
        if ($Xibid) 
        {
            parse_str($Xibid, $Xibidout);
            
            if (($Xibidout['aff_date']+($Xibidout['aff_jours']*86400))-time()>0) 
            {
                $affichJ = false; 
                $affichN = false;
                
                if ((time::NightDay() == 'Jour') and ($Xibidout['aff_jour'] == 'checked')) 
                {
                    $affichJ = true;
                }

                if ((time::NightDay() == 'Nuit') and ($Xibidout['aff_nuit'] == 'checked')) 
                {
                    $affichN = true;
                }
            }

            $XcontentsT = substr($Xcontents, 0, strpos($Xcontents, 'aff_jours'));
            
            $contentJ = substr($XcontentsT, strpos($XcontentsT, "[jour]")+6, strpos($XcontentsT, "[/jour]")-6);
            $contentN = substr($XcontentsT, strpos($XcontentsT, "[nuit]")+6, strpos($XcontentsT, "[/nuit]")-19-strlen($contentJ));
            
            $Xcontents = '';
            
            if (isset($affichJ) and $affichJ === true)
            {
                $Xcontents = $contentJ;
            }
            
            if (isset($affichN) and $affichN === true) 
            {
                if ($contentN != '')
                {
                    $Xcontents = $contentN;
                }
                else
                {
                    $Xcontents = $contentJ;
                }
            }
            
            if ($Xcontents != '') 
            {
                $affich = true;
            }
        } 
        else
        {
            $affich = true;
        }
        
        $Xcontents = metalang::meta_lang(language::aff_langue($Xcontents));
        
        return array($affich, $Xcontents);
    }

    /**
     * [aff_edito description]
     * @return [type] [description]
     */
    public static function aff_edito() 
    {
        list($affich, $Xcontents) = static::fab_edito();
        
        if (($affich) and ($Xcontents != '')) 
        {
            $notitle = false;
            if (strstr($Xcontents, '!edito-notitle!')) 
            {
                $notitle = 'notitle';
                $Xcontents = str_replace('!edito-notitle!', '', $Xcontents);
            }

            $ret = false;
           
            if (function_exists("themedito")) 
            {
                $ret = themedito($Xcontents);
            } 
            else 
            {
                if (function_exists("theme_centre_box")) 
                {
                    if (!$notitle) 
                    {
                        $title = translate("EDITO");
                    } 
                    else 
                    {
                        $title = '';
                    }
                    
                    theme_centre_box($title, $Xcontents);
                    $ret = true;
                }
            }
            
            if ($ret == false) 
            {
                if (!$notitle)
                {
                    echo '<span class="edito">'.translate("EDITO").'</span>';
                }
                echo $Xcontents;
                echo '<br />';
            }
        }
    }

}
