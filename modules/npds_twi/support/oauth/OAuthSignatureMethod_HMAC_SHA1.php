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


/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104] 
 * where the Signature Base String is the text and the key is the concatenated values (each first 
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&' 
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {


    /**
     * [get_name description]
     * @return [type] [description]
     */
    public function get_name() 
    {
        return "HMAC-SHA1";
    }

    /**
     * [build_signature description]
     * @param  [type] $request  [description]
     * @param  [type] $consumer [description]
     * @param  [type] $token    [description]
     * @return [type]           [description]
     */
    public function build_signature($request, $consumer, $token) 
    {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);

        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
}