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
use npds\security\hack;
use npds\security\ip;
use npds\security\extract;

// Url

/**
 * site_url('index.php?op=index');
 */
if (! function_exists('site_url'))
{
    /**
     * [site_url description]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    function site_url($url)
    {
        global $nuke_url;

        return $nuke_url.'/'.trim($url, '/');
    }
}

/**
 * module_url('links', 'links');
 */
if (! function_exists('module_url'))
{
    /**
     * [module_url description]
     * @param  [type] $url     [description]
     * @param  [type] $ModPath [description]
     * @return [type]          [description]
     */
    function module_url($url, $ModPath)
    {
        return site_url('modules.php?ModPath='.$ModPath.'&ModStart='.trim($url, '/'));
    }
}

/**
 * asset_url('links', 'links');
 */
if (! function_exists('asset_url'))
{
    /**
     * [module_url description]
     * @param  [type] $url     [description]
     * @param  [type] $ModPath [description]
     * @return [type]          [description]
     */
    function asset_url($url)
    {
        return site_url('assets/'.trim($url, '/'));
    }
}

/**
 * redirect_url($urlx)
 */
if (! function_exists('redirect_url'))
{
    /**
     * Permet une redirection javascript / en lieu et place de header("location: ...");
     * @param  [type] $url     [description]
     * @param  [type] $ModPath [description]
     * @return [type]          [description]
     */ 
    function redirect_url($url) 
    {
        echo "<script type=\"text/javascript\">\n";
        echo "//<![CDATA[\n";
        echo "document.location.href='".site_url($url)."';\n";
        echo "//]]>\n";
        echo "</script>";
    }
}

// Security

/**
 * removeHack($string)
 */
if (! function_exists('removeHack'))
{
    /**
     * [removeHack description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    function removeHack($string)
    {
        return hack::remove($string);
    }
}

/**
 * getip()
 */
if (! function_exists('getip'))
{
    /**
     * [getip description]
     * @return [type] [description]
     */
    function getip()
    {
        return ip::get();
    }
}

/**
 * cookie user
 */
if (! function_exists('user'))
{
    /**
     * [user description]
     * @return [type] [description]
     */
    function user()
    {
        return extract::user();
    }
}

/**
 * cookie user_laguage
 */
if (! function_exists('user_laguage'))
{
    /**
     * [user_lnguage description]
     * @return [type] [description]
     */
    function user_laguage()
    {
        return extract::user_laguage();
    }
}

/**
 * cookie user
 */
if (! function_exists('admin'))
{
    /**
     * [admin description]
     * @return [type] [description]
     */
    function admin()
    {
        return extract::admin();
    }
}
