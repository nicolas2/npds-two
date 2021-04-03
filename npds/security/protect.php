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

use npds\error\access;


/*
 * protect
 */
class protect {


    /**
     * [url_protect description]
     * @param  [type] $arr [description]
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function url($arr, $key) 
    {
        $bad_uri_content = include ("config/url_protect.php");

        $arr = rawurldecode($arr);
        $RQ_tmp = strtolower($arr);
        $RQ_tmp_large = strtolower($key)."=".$RQ_tmp;
      
        if(in_array($RQ_tmp, $bad_uri_content) 
            OR in_array($RQ_tmp_large, $bad_uri_content)) 
        {
                unset($bad_uri_content);
                access::denied();
        }
    }

}
