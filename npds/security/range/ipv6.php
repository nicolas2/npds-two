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
 * ipv6
 */
class ipv6
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
            $netmask = 128;
        }
        else
        {
            [$range, $netmask] = explode('/', $range, 2);

            if($netmask < 1 || $netmask > 128)
            {
                return false;
            }
        }

        $binNetmask = str_repeat('f', $netmask / 4);

        switch($netmask % 4)
        {
            case 1:
                $binNetmask .= '8';
                break;
            case 2:
                $binNetmask .= 'c';
                break;
            case 3:
                $binNetmask .= 'e';
                break;
        }

        $binNetmask = pack('H*', str_pad($binNetmask, 32, '0'));

        try
        {
            return (inet_pton($ip) & $binNetmask) === inet_pton($range);
        }
        catch(Throwable $e)
        {
            return false;
        }
    }
}
