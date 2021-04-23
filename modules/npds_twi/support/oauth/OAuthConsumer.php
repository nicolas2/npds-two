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


class OAuthConsumer {

    /**
     * [$key description]
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
     * @param [type] $key          [description]
     * @param [type] $secret       [description]
     * @param [type] $callback_url [description]
     */
    public function __construct($key, $secret, $callback_url=NULL) 
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->callback_url = $callback_url;
    }

    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString() 
    {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}
