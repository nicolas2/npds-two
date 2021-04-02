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