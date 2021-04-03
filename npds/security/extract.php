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
        if (!empty($_COOKIE)) {
            extract($_COOKIE, EXTR_OVERWRITE);
        }

        if (isset($user)) {
            $ibid = explode(':', base64_decode($user));
            array_walk($ibid, [protect::class, 'url']);
            return base64_encode(str_replace("%3A", ":", urlencode(base64_decode($user))));
        }

        return;
    }
    
    /**
     * [user_language description]
     * @return [type] [description]
     */
    public static function user_language() 
    {
        if (!empty($_COOKIE)) {
            extract($_COOKIE, EXTR_OVERWRITE);
        }

        if (isset($user_language)) {
            $ibid = explode(':', $user_language);
            array_walk($ibid, [protect::class, 'url']);
            return str_replace("%3A", ":", urlencode($user_language));
        }

        return;
    }

    /**
     * [admin description]
     * @return [type] [description]
     */
    public static function admin() 
    {
        if (!empty($_COOKIE)) {
            extract($_COOKIE, EXTR_OVERWRITE);
        }

        if (isset($admin)) {
            $ibid = explode(':', base64_decode($admin));
            array_walk($ibid, [protect::class, 'url']);
            return base64_encode(str_replace('%3A', ':', urlencode(base64_decode($admin))));
        }

        return;
    }

}
