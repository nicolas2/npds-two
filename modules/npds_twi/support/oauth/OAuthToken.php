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
namespace modules\npds_twi\support\oauth;


class OAuthToken {

 
    /**
     * access tokens and request tokens
     * @var [type]
     */
    public $key;
    
    /**
     * [$secret description]
     * @var [type]
     */
    public $secret;


    /**
     * [__construct description]
     * @param [type] $key the token   
     * @param [type] $secret the token secret
     */
    public function __construct($key, $secret) 
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     * @return [type] [description]
     */
    public function to_string() 
    {
        return "oauth_token=" .
            OAuthUtil::urlencode_rfc3986($this->key) .
            "&oauth_token_secret=" .
            OAuthUtil::urlencode_rfc3986($this->secret);
    }

    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString() 
    {
        return $this->to_string();
    }

}
