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
namespace npds\language;


/*
 * utf8
 */
class utf8 {


    /**
     * Encode une chaine UF8 au format javascript
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function utf8_java($ibid) 
    {
        // UTF8 = &#x4EB4;&#x6B63;&#7578;
        // javascript = \u4EB4\u6B63\u.dechex(7578)
        $tmp = explode ('&#', $ibid);
        
        foreach($tmp as $bidon) 
        {
            if ($bidon) 
            {
                $bidon = substr($bidon, 0, strpos($bidon, ";"));
                $hex = strpos($bidon, 'x');
                
                if ($hex === false)
                {
                    $ibid = str_replace('&#'.$bidon.';', '\\u'.dechex($bidon), $ibid);
                }
                else
                {
                    $ibid = str_replace('&#'.$bidon.';', '\\u'.substr($bidon, 1), $ibid);
                }
            }
        }

        return $ibid;
    }

}
