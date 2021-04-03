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
namespace npds\security;

use npds\security\range\ipv4;
use npds\security\range\ipv6;


/*
 * ip
 */
class ip {


    /**
     * 
     */
    const REMOTE_ADDRESS_FALLBACK = '127.0.0.1';
    
    /**
     * [$trustedProxies description]
     * @var array
     */
    protected static $trustedProxies = [];

    /**
     * [$ip description]
     * @var [type]
     */
    protected static $ip;


    /**
     * [setTrustedProxies description]
     * @param array $trustedProxies [description]
     */
    public static function setTrustedProxies(array $trustedProxies)
    {
        static::$trustedProxies = $trustedProxies;
    }

    /**
     * [isTrustedProxy description]
     * @param  string  $ip [description]
     * @return boolean     [description]
     */
    protected static function isTrustedProxy(string $ip)
    {
        foreach(static::$trustedProxies as $trustedProxy)
        {
            if(static::inRange($ip, $trustedProxy))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * [getIp description]
     * @return [type] [description]
     */
    public static function get()
    {
        if(static::$ip === null)
        {
            $ip = $_SERVER['REMOTE_ADDR'];

            if($ip !== null && static::isTrustedProxy($ip))
            {
                $ips = $_SERVER['HTTP_X_FORWARDED_FOR'];

                if(!empty($ips))
                {
                    $ips = array_reverse(array_map('trim', explode(',', $ips)));

                    foreach($ips as $key => $value)
                    {
                        if(static::isTrustedProxy($value) === false)
                        {
                            break;
                        }

                        unset($ips[$key]);
                    }

                    $ip = current($ips);
                }
            }

            static::$ip = (filter_var($ip, FILTER_VALIDATE_IP) !== false) ? $ip : static::REMOTE_ADDRESS_FALLBACK;
        }

        return urlencode(static::$ip);
    }

    /**
     * [inRange description]
     * @param  string $ip    [description]
     * @param  string $range [description]
     * @return [type]        [description]
     */
    public static function inRange(string $ip, string $range)
    {
        return strpos($ip, '.') === false ? ipv6::in_range($ip, $range) : ipv4::in_range($ip, $range);
    }

}
