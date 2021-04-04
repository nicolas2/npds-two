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

// SuperCache

/**
 * Q_Select
 */
if (! function_exists('Q_Select'))
{ 
    /**
     * [Q_Select description]
     * @param [type]  $Xquery    [description]
     * @param integer $retention [description]
     */
    function Q_Select($Xquery, $retention=3600) 
    {
        global $SuperCache, $cache_obj;
       
        if (($SuperCache) and ($cache_obj)) 
        {
            $row = $cache_obj->CachingQuery($Xquery, $retention);
          
            return ($row);
        } 
        else 
        {
            $result = @sql_query($Xquery);
            $tab_tmp = array();
          
            while($row = sql_fetch_assoc($result)) 
            {
                $tab_tmp[] = $row;
            }
          
            return ($tab_tmp);
        }
    }
}

/**
 * PG_clean
 */
if (! function_exists('PG_clean'))
{ 
    /**
     * [PG_clean description]
     * @param [type] $request [description]
     */
    function PG_clean($request) 
    {
        global $CACHE_CONFIG;
       
        $page = md5($request);
        $dh = opendir($CACHE_CONFIG['data_dir']);
        
        while(false !== ($filename = readdir($dh))) 
        {
            if ($filename === '.' 
                OR $filename === '..' 
                OR (strpos($filename, $page) === FALSE)) 
            {
                continue;
            }
              
            unlink($CACHE_CONFIG['data_dir'].$filename);
        }
        closedir($dh);
    }
}

/**
 * Q_Clean
 */
if (! function_exists('Q_Clean'))
{ 
    /**
     * [Q_Clean description]
     */
    function Q_Clean() 
    {
        global $CACHE_CONFIG;
        
        $dh = opendir($CACHE_CONFIG['data_dir']."sql");
        
        while(false !== ($filename = readdir($dh))) 
        {
            if ($filename === '.' 
                OR $filename === '..') 
            {
                continue;
            }
              
            if (is_file($CACHE_CONFIG['data_dir']."sql/".$filename))
            {
                unlink($CACHE_CONFIG['data_dir']."sql/".$filename);
            }
        }

        closedir($dh);
        $fp = fopen($CACHE_CONFIG['data_dir']."sql/.htaccess", 'w');
        @fputs($fp, "Deny from All");
        fclose($fp);
    }
}

/**
 * SC_clean
 */
if (! function_exists('SC_clean'))
{ 
    /**
     * [SC_clean description]
     */
    function SC_clean() 
    {
        global $CACHE_CONFIG;
       
        $dh = opendir($CACHE_CONFIG['data_dir']);
       
        while (false !== ($filename = readdir($dh))) 
        {
            if ($filename === '.' 
                OR $filename === '..' 
                OR $filename === 'ultramode.txt' 
                OR $filename === 'net2zone.txt' 
                OR $filename === 'sql' 
                OR $filename === 'index.html')
            { 
                continue;
            }
             
            if (is_file($CACHE_CONFIG['data_dir'].$filename))
            {
                unlink($CACHE_CONFIG['data_dir'].$filename);
            }
        }
        closedir($dh);
        Q_Clean();
    }
}

/**
 * SC_infos()
 */
if (! function_exists('SC_infos'))
{ 
    /**
     * Indique le status de SuperCache
     */
    function SC_infos() 
    {
        global $SuperCache, $npds_sc;
        
        if ($SuperCache) 
        {
            if ($npds_sc) 
            {
                return '<span class="small">'.translate(".:Page << Super-Cache:.").'</span>';
            } 
            else 
            {
                return '<span class="small">'.translate(".:Page >> Super-Cache:.").'</span>';
            }
        }
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
