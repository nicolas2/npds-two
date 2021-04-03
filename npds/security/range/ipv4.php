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
namespace npds\security\range;


/**
 * ipv4
 */
class ipv4
{

	
	/**
	 * [inRange description]
	 * @param  string $ip    [description]
	 * @param  string $range [description]
	 * @return [type]        [description]
	 */
	public static function inRange(string $ip, string $range)
	{
		if(strpos($range, '/') === false)
		{
			$netmask = 32;
		}
		else
		{
			[$range, $netmask] = explode('/', $range, 2);

			if($netmask < 0 || $netmask > 32)
			{
				return false;
			}
		}

		if(($ip2Long = ip2long($ip)) === false || ($range2Long = ip2long($range)) === false)
		{
			return false;
		}

		$netmaskDecimal = ~ ((2 ** (32 - $netmask)) - 1);

		return ($ip2Long & $netmaskDecimal) === ($range2Long & $netmaskDecimal);
	}
}