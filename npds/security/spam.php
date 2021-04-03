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
use npds\security\ip;


/*
 * protect
 */
class spam {


    /**
     * [boot description]
     * @return [type] [description]
     */
    public static function boot()
    {
    // First of all : Spam from IP 
        // |5 indicate that the same IP has passed 6 times with status 
        // KO in the anti_spambot function
        if (file_exists("storage/logs/spam.log")) 
        {
            $tab_spam = str_replace("\r\n", "", file("storage/logs/spam.log"));
        }

        if (is_array($tab_spam)) 
        {
            $ip = urldecode(ip::get());
          
            if (strstr($ip, ':')) 
            {
                $range = '6';
            }
            else 
            {
                $range = '4';
            }
          
            if (in_array($ip."|5", $tab_spam)) 
            {
                access::denied();
            }
          
            // nous pouvons bannir une plage d'adresse ip en V4 
            // dans l'admin IPban sous forme x.x.%|5 ou x.x.x.%|5
            if($range == '4') 
            {
                $ipv4 = explode('.', $ip);
             
                if (in_array($ipv4[0].'.'.$ipv4[1].'.%|5', $tab_spam)) {
                    access::denied();
                }
             
                if (in_array($ipv4[0].'.'.$ipv4[1].'.'.$ipv4[2].'.%|5', $tab_spam)) {
                    access::denied();
                }
            }
          
            // nous pouvons bannir une plage d'adresse ip en V6 
            // dans l'admin IPban sous forme x:x:%|5 ou x:x:x:%|5
            if($range == '6') 
            {
                $ipv6 = explode(':', $ip);
             
                if (in_array($ipv6[0].':'.$ipv6[1].':%|5', $tab_spam)) {
                    access::denied();
                }
             
                if (in_array($ipv6[0].':'.$ipv6[1].':'.$ipv6[2].':%|5', $tab_spam)) {
                    access::denied();
                }
            }
        }        
    }
    
}
