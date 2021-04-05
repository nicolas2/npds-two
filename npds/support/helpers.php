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
use npds\utility\crypt;
use npds\utility\code;


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
 * asset_url('images/npdstwo.png');
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

// get cookie user, user_language, admin

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

// Crypt

/**
 * keyED($txt, $encrypt_key)
 */
if (! function_exists('keyED'))
{ 
    /**
     * [keyED description]
     * @param  [type] $txt         [description]
     * @param  [type] $encrypt_key [description]
     * @return [type]              [description]
     */
    function keyED($txt, $encrypt_key) 
    {
        crypt::keyED($txt, $encrypt_key);
    }
}

/**
 * encrypt($txt)
 */
if (! function_exists('encrypt'))
{ 
    /**
     * [encrypt description]
     * @param  [type] $txt [description]
     * @return [type]      [description]
     */
    function encrypt($txt) 
    {
        crypt::encrypt($txt);
    }
}

/**
 * encryptK($txt, $C_key)
 */
if (! function_exists('encryptK'))
{ 
    /**
     * [encryptK description]
     * @param  [type] $txt   [description]
     * @param  [type] $C_key [description]
     * @return [type]        [description]
     */
    function encryptK($txt, $C_key) 
    {
        crypt::encryptK($txt, $C_key);
    }
}

/**
 * decrypt($txt)
 */
if (! function_exists('decrypt'))
{ 
    /**
     * [decrypt description]
     * @param  [type] $txt [description]
     * @return [type]      [description]
     */
    function decrypt($txt) 
    {
        crypt::decrypt($txt);
    }
}

/**
 * decryptK($txt, $C_key)
 */
if (! function_exists('decryptK'))
{ 
    /**
     * [decryptK description]
     * @param  [type] $txt   [description]
     * @param  [type] $C_key [description]
     * @return [type]        [description]
     */
    function decryptK($txt, $C_key) 
    {
        crypt::decryptK($txt, $C_key);
    }
}

// Code

/**
 * change_cod($r)
 */
if (! function_exists('change_cod'))
{ 
    /**
     * [change_cod description]
     * @param  [type] $r [description]
     * @return [type]    [description]
     */
    function change_cod($r) 
    {
        code::change_cod($r);
    }
}

/**
 * af_cod($ibid)
 */
if (! function_exists('af_cod'))
{ 
    /**
     * [af_cod description]
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    function af_cod($ibid) 
    {
        code::af_cod($ibid);
    }
}

/**
 * desaf_cod($ibid)
 */
if (! function_exists('desaf_cod'))
{ 
    /**
     * [desaf_cod description]
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    function desaf_cod($ibid) 
    {
        code::desaf_cod($ibid);
    }
}

/**
 * aff_code($ibid)
 */
if (! function_exists('aff_code'))
{ 
    /**
     * [aff_code description]
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    function aff_code($ibid) 
    {
        code::aff_code($ibid);
    }
}

/**
 * get_os()
 */
if (! function_exists('get_os'))
{
    /**
     * retourne true si l'OS de la station cliente est Windows sinon false
     * @return [type] [description]
     */
    function get_os() 
    {
        $client = getenv("HTTP_USER_AGENT");
        
        if (preg_match('#(\(|; )(Win)#', $client, $regs)) 
        {
            if ($regs[2] == "Win") 
            {
                $MSos = true;
            } 
            else 
            {
                $MSos = false;
            }
        } 
        else 
        {
            $MSos = false;
        }

        return $MSos;
    }
}

/**
 * check_install()
 */
if (! function_exists('check_install'))
{
    /**
     * [check_install description]
     * @return [type] [description]
     */
    function check_install() 
    {
        // Modification pour IZ-Xinstall - EBH - JPB & PHR
        if (file_exists("IZ-Xinstall.ok")) 
        {
            if (file_exists("install.php") OR is_dir("install")) 
            {
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <title>Npds Two IZ-Xinstall - Installation &amp; Configuration</title>
                </head>
                <body>
                    <div style="text-align: center; font-size: 20px; font-family: Arial; font-weight: bold; color: #000000"><br />
                    NPDS IZ-Xinstall - Installation &amp; Configuration
                    </div>
                    <div style="text-align: center; font-size: 20px; font-family: Arial; font-weight: bold; color: #ff0000"><br />
                        Vous devez supprimer le r&eacute;pertoire "install" ET le fichier "install.php" avant de poursuivre !<br />
                        You must remove the directory "install" as well as the file "install.php" before continuing!
                    </div>
                </body>
                </html>';
                die();
            }
        } 
        else 
        {
            if (file_exists("install.php") AND is_dir("install")) 
            {
                header("location: install.php");
            }
        }
    }
}
