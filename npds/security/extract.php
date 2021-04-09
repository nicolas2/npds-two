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

use npds\security\protect;
use npds\cookie\cookie;


/*
 * extract
 */
class extract {

    /**
     * [user description]
     * @return [type] [description]
     */
    public static function user() 
    {
        $user = cookie::get('user', null);

        if (isset($user)) 
        {
            $ibid = explode(':', base64_decode($user));
            array_walk($ibid, [protect::class, 'url']);
            $user = base64_encode(str_replace("%3A", ":", urlencode(base64_decode($user))));
        
            return $user;
        }

        return null;
    }
    
    /**
     * [user_language description]
     * @return [type] [description]
     */
    public static function user_language() 
    {
        $user_language = cookie::get('user_language', null);

        if (isset($user_language)) 
        {
            $ibid = explode(':', $user_language);
            array_walk($ibid, [protect::class, 'url']);
            $user_language = str_replace("%3A", ":", urlencode($user_language));
        
            return $user_language;
        }

        return null;
    }

    /**
     * [admin description]
     * @return [type] [description]
     */
    public static function admin() 
    {
        $admin = cookie::get('admin', null);

        if (isset($admin)) 
        {
            $ibid = explode(':', base64_decode($admin));
            array_walk($ibid, [protect::class, 'url']);
            $admin = base64_encode(str_replace('%3A', ':', urlencode(base64_decode($admin))));
        
            return $damin;
        }

        return null;
    }

}
