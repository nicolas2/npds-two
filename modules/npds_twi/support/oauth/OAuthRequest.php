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


class OAuthRequest {


    /**
     * [$parameters description]
     * @var [type]
     */
    private $parameters;

    /**
     * [$http_method description]
     * @var [type]
     */
    private $http_method;

    /**
     * [$http_url description]
     * @var [type]
     */
    private $http_url;

    /**
     * for debug purposes
     * @var [type]
     */
    public $base_string;

    /**
     * [$version description]
     * @var string
     */
    public static $version = '1.0';

    /**
     * [$POST_INPUT description]
     * @var string
     */
    public static $POST_INPUT = 'php://input';


    /**
     * [__construct description]
     * @param [type] $http_method [description]
     * @param [type] $http_url    [description]
     * @param [type] $parameters  [description]
     */
    public function __construct($http_method, $http_url, $parameters=NULL) 
    {
        @$parameters or $parameters = array();
        
        $parameters = array_merge( OAuthUtil::parse_parameters(parse_url($http_url, PHP_URL_QUERY)), $parameters);
        
        $this->parameters = $parameters;
        $this->http_method = $http_method;
        $this->http_url = $http_url;
    }

    /**
     * attempt to build up a request from what was passed to the server
     * @param  [type] $http_method [description]
     * @param  [type] $http_url    [description]
     * @param  [type] $parameters  [description]
     * @return [type]              [description]
     */
    public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) 
    {
        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
                  ? 'http'
                  : 'https';
        
        @$http_url or $http_url = $scheme .
                                  '://' . $_SERVER['HTTP_HOST'] .
                                  ':' .
                                  $_SERVER['SERVER_PORT'] .
                                  $_SERVER['REQUEST_URI'];
        
        @$http_method or $http_method = $_SERVER['REQUEST_METHOD'];

        // We weren't handed any parameters, so let's find the ones relevant to
        // this request.
        // If you run XML-RPC or similar you should use this to provide your own
        // parsed parameter-list
        if (!$parameters) 
        {
            // Find request headers
            $request_headers = OAuthUtil::get_headers();

            // Parse the query-string to find GET parameters
            $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

            // It's a POST request of the proper content-type, so parse POST
          // parameters and add those overriding any duplicates from GET
            if ($http_method == "POST"
                && @strstr($request_headers["Content-Type"],
                         "application/x-www-form-urlencoded")) 
            {
                $post_data = OAuthUtil::parse_parameters(
                    file_get_contents(self::$POST_INPUT)
                );
                
                $parameters = array_merge($parameters, $post_data);
            }

            // We have a Authorization-header with OAuth data. Parse the header
            // and add those overriding any duplicates from GET or POST
            if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") 
            {
                $header_parameters = OAuthUtil::split_header(
                    $request_headers['Authorization']
                );

                $parameters = array_merge($parameters, $header_parameters);
            }
        }

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    /**
     * pretty much a helper function to set up the request
     * @param  [type] $consumer    [description]
     * @param  [type] $token       [description]
     * @param  [type] $http_method [description]
     * @param  [type] $http_url    [description]
     * @param  [type] $parameters  [description]
     * @return [type]              [description]
     */
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) 
    {
        @$parameters or $parameters = array();
        
        $defaults = array("oauth_version" => OAuthRequest::$version,
                          "oauth_nonce" => OAuthRequest::generate_nonce(),
                          "oauth_timestamp" => OAuthRequest::generate_timestamp(),
                          "oauth_consumer_key" => $consumer->key);
        if ($token)
        {
            $defaults['oauth_token'] = $token->key;
        }

        $parameters = array_merge($defaults, $parameters);

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    /**
     * [set_parameter description]
     * @param [type]  $name             [description]
     * @param [type]  $value            [description]
     * @param boolean $allow_duplicates [description]
     */
    public function set_parameter($name, $value, $allow_duplicates = true) 
    {
        if ($allow_duplicates && isset($this->parameters[$name])) 
        {
            // We have already added parameter(s) with this name, so add to the list
            if (is_scalar($this->parameters[$name])) 
            {
                // This is the first duplicate, so transform scalar (string)
                // into an array so we can add the duplicates
                $this->parameters[$name] = array($this->parameters[$name]);
            }

            $this->parameters[$name][] = $value;
        } 
        else 
        {
            $this->parameters[$name] = $value;
        }
    }

    /**
     * [get_parameter description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function get_parameter($name) 
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * [get_parameters description]
     * @return [type] [description]
     */
    public function get_parameters() 
    {
        return $this->parameters;
    }

    /**
     * [unset_parameter description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function unset_parameter($name) 
    {
        unset($this->parameters[$name]);
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     * @return string
     */
    public function get_signable_parameters() 
    {
        // Grab all parameters
        $params = $this->parameters;

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) 
        {
            unset($params['oauth_signature']);
        }

        return OAuthUtil::build_http_query($params);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function get_signature_base_string() 
    {
        $parts = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );

        $parts = OAuthUtil::urlencode_rfc3986($parts);

        return implode('&', $parts);
    }

    /**
     * just uppercases the http method
     * @return [type] [description]
     */
    public function get_normalized_http_method() 
    {
        return strtoupper($this->http_method);
    }

    /**
     * parses the url and rebuilds it to be
     * @return [type] [description]
     */
    public function get_normalized_http_url() 
    {
        $parts = parse_url($this->http_url);

        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
            || ($scheme == 'http' && $port != '80')) 
        {
            $host = "$host:$port";
        }

        return "$scheme://$host$path";
    }

    /**
     * builds a url usable for a GET request
     * @return [type] [description]
     */
    public function to_url() 
    {
        $post_data = $this->to_postdata();
        $out = $this->get_normalized_http_url();
        
        if ($post_data) 
        {
            $out .= '?'.$post_data;
        }
        
        return $out;
    }

    /**
     * builds the data one would send in a POST request
     * @return [type] [description]
     */
    public function to_postdata() 
    {
        return OAuthUtil::build_http_query($this->parameters);
    }

    /**
     * builds the Authorization: header
     * @param  [type] $realm [description]
     * @return [type]        [description]
     */
    public function to_header($realm=null) 
    {
        $first = true;
    	
        if($realm) 
        {
            $out = 'Authorization: OAuth realm="' . OAuthUtil::urlencode_rfc3986($realm) . '"';
            $first = false;
        } 
        else
        {
            $out = 'Authorization: OAuth';
        }

        $total = array();
        
        foreach ($this->parameters as $k => $v) 
        {
            if (substr($k, 0, 5) != "oauth") 
            {
                continue;
            }
          
            if (is_array($v)) 
            {
                throw new OAuthException('Arrays not supported in headers');
            }

            $out .= ($first) ? ' ' : ',';
            $out .= OAuthUtil::urlencode_rfc3986($k) .
                    '="' .
                    OAuthUtil::urlencode_rfc3986($v) .
                    '"';
            $first = false;
        }

        return $out;
    }

    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString() 
    {
        return $this->to_url();
    }

    /**
     * [sign_request description]
     * @param  [type] $signature_method [description]
     * @param  [type] $consumer         [description]
     * @param  [type] $token            [description]
     * @return [type]                   [description]
     */
    public function sign_request($signature_method, $consumer, $token) 
    {
        $this->set_parameter(
            "oauth_signature_method",
            $signature_method->get_name(),
            false
        );
        
        $signature = $this->build_signature($signature_method, $consumer, $token);
        $this->set_parameter("oauth_signature", $signature, false);
    }

    /**
     * [build_signature description]
     * @param  [type] $signature_method [description]
     * @param  [type] $consumer         [description]
     * @param  [type] $token            [description]
     * @return [type]                   [description]
     */
    public function build_signature($signature_method, $consumer, $token) 
    {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        
        return $signature;
    }

    /**
     * util function: current timestamp
     * @return [type] [description]
     */
    private static function generate_timestamp() 
    {
        return time();
    }

    /**
     * util function: current nonce
     * @return [type] [description]
     */
    private static function generate_nonce() 
    {
        $mt = microtime();
        $rand = mt_rand();

        // md5s look nicer than numbers
        return md5($mt . $rand); 
    }

}