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
use npds\views\theme;
use npds\language\metalang;
use npds\cache\cache;


/**
 * Cette fonction est utilisée pour intégrer des smilies et comme service pour theme_img()
 * @param [type] $ibid [description]
 */
function MM_img($ibid) 
{
    $ibid = metalang::arg_filter($ibid);
    $ibidX = theme::theme_image($ibid);
       
    if ($ibidX)
    {
        $ret = "<img src=\"$ibidX\" border=\"0\" alt=\"\" />";
    }
    else 
    {
        if (@file_exists("assets/$ibid"))
        {
            $ret = "<img src=\"assets/images/$ibid\" border=\"0\" alt=\"\" />";
        }
        else
        {
            $ret = false;
        }
    }

    return $ret;
}

/**
 * [SC_infos description]
 */
function SC_infos()
{
    cache::SC_infos();
}
