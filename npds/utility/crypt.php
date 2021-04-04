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
namespace npds\utility;


/*
 * protect
 */
class crypt {


    /**
     * Composant des fonctions encrypt et decrypt
     * @param  [type] $txt         [description]
     * @param  [type] $encrypt_key [description]
     * @return [type]              [description]
     */
    public static function keyED($txt, $encrypt_key) 
    {
        $encrypt_key = md5($encrypt_key);
        
        $ctr = 0;
        $tmp = '';
        
        for ($i = 0; $i < strlen($txt); $i++) 
        {
            if ($ctr == strlen($encrypt_key)) 
            {
                $ctr = 0;
            }

            $tmp .= substr($txt, $i, 1) ^ substr($encrypt_key, $ctr, 1);
            $ctr++;
        }
        
        return $tmp;
    }
    
    /**
     * retourne une chaine encryptée en utilisant la valeur de $NPDS_Key
     * @param  [type] $txt [description]
     * @return [type]      [description]
     */
    public static function encrypt($txt) 
    {
        global $NPDS_Key;
        
        return static::encryptK($txt, $NPDS_Key);
    }
    
    /**
     * retourne une chaine encryptée en utilisant la clef : $C_key
     * @param  [type] $txt   [description]
     * @param  [type] $C_key [description]
     * @return [type]        [description]
     */
    public static function encryptK($txt, $C_key) 
    {
        srand( (double)microtime()*1000000);  
        $encrypt_key = md5(rand(0, 32000));
        
        $ctr = 0;
        $tmp = '';
           
        for ($i = 0; $i < strlen($txt); $i++) 
        {
            if ($ctr == strlen($encrypt_key)) 
            {
                $ctr = 0;
            }
               
            $tmp .= substr($encrypt_key, $ctr, 1) . (substr($txt, $i, 1) ^ substr($encrypt_key, $ctr, 1));
            $ctr++;
        }
        
        return base64_encode(static::keyED($tmp, $C_key));
    }
    
    /**
     * retourne une chaine décryptée en utilisant la valeur de $NPDS_Key
     * @param  [type] $txt [description]
     * @return [type]      [description]
     */
    public static function decrypt($txt) 
    {
        global $NPDS_Key;
        
        return static::decryptK($txt, $NPDS_Key);
    }
    
    /**
     * retourne une décryptée en utilisant la clef de $C_Key
     * @param  [type] $txt   [description]
     * @param  [type] $C_key [description]
     * @return [type]        [description]
     */
    public static function decryptK($txt, $C_key) 
    {
        $txt = static::keyED(base64_decode($txt), $C_key);
        $tmp = '';
           
        for ($i = 0; $i < strlen($txt); $i++) 
        {
            $md5 = substr($txt, $i, 1);
            $i++;
            $tmp .= (substr($txt, $i, 1) ^ $md5);
        }
           
        return $tmp;
    }

}
