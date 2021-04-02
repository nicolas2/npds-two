<?php

include 'lib/security/hack.php';

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
		return site_url(trim('modules.php?ModPath='.$ModPath.'&ModStart='.$url, '/'));
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