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


class OAuthDataStore {


    /**
     * [lookup_consumer description]
     * @param  [type] $consumer_key [description]
     * @return [type]               [description]
     */
    public function lookup_consumer($consumer_key) 
    {
        // implement me
    }

    /**
     * [lookup_token description]
     * @param  [type] $consumer   [description]
     * @param  [type] $token_type [description]
     * @param  [type] $token      [description]
     * @return [type]             [description]
     */
    public function lookup_token($consumer, $token_type, $token) 
    {
        // implement me
    }

    /**
     * [lookup_nonce description]
     * @param  [type] $consumer  [description]
     * @param  [type] $token     [description]
     * @param  [type] $nonce     [description]
     * @param  [type] $timestamp [description]
     * @return [type]            [description]
     */
    public function lookup_nonce($consumer, $token, $nonce, $timestamp) 
    {
        // implement me
    }

    /**
     * [new_request_token description]
     * @param  [type] $consumer [description]
     * @param  [type] $callback [description]
     * @return [type]           [description]
     */
    public function new_request_token($consumer, $callback = null) 
    {
        // return a new token attached to this consumer
    }

    /**
     * [new_access_token description]
     * @param  [type] $token    [description]
     * @param  [type] $consumer [description]
     * @param  [type] $verifier [description]
     * @return [type]           [description]
     */
    public function new_access_token($token, $consumer, $verifier = null) 
    {
        // return a new access token attached to this consumer
        // for the user associated with this token if the request token
        // is authorized
        // should also invalidate the request token
    }

}