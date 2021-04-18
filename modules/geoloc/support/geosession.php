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
namespace modules\geoloc\support;

use npds\security\hack;
use npds\utility\str;


/**
 * geosession
 */
class geosession {

    /**
     * [$ModPath description]
     * @var string
     */
    private static $ModPath = 'geoloc';

    /**
     * [$driver description]
     * @var array
     */
    private static $providers = array(
                            'https://ipapi.co',
                            'https://api.ipdata.co',
                            'https://extreme-ip-lookup.com',
                            'http://ip-api.com'       
                            );


    /**
     * [init description]
     * @return [type] [description]
     */
    public static function init($ip)
    { 
        global $NPDS_Prefix;

        $file = file('modules/'.static::$ModPath.'/config/geoloc.php');

        include('modules/'.static::$ModPath.'/config/geoloc.php');

        if(strstr($file['40'], 'geo_ip = 1')) 
        {
            $ousursit = '';
            
            global $ousursit;

            $resultat = sql_query("SELECT * FROM ".$NPDS_Prefix."ip_loc WHERE ip_ip LIKE \"$ip\"");
            $controle = sql_num_rows($resultat);
            while ($row = sql_fetch_array($resultat)) 
            {
                $ousursit = preg_replace("#/.*?/#", '', $_SERVER['PHP_SELF']);
            }

            if($controle != 0)
            {   
                sql_query("UPDATE ".$NPDS_Prefix."ip_loc SET ip_visite=ip_visite+1, ip_visi_pag=\"$ousursit\" WHERE ip_ip=\"$ip\"");
            }
            else 
            {
                // https://ipapi.co
                if ($provider_select == 0)
                {
                    static::ipapi_co_json($ip, $ousursit);
                }
                // https://api.ipdata.co
                elseif ($provider_select == 1)
                {
                    static::api_ipdata_co_json($ip, $ousursit);
                }
                // https://extreme-ip-lookup.com
                elseif ($provider_select == 2)
                {
                    static::extreme_ip_lookup_com_json($ip, $ousursit);
                }
                // http://ip-api.com
                elseif ($provider_select == 3)
                {
                    static::ip_api_com_json($ip, $ousursit);
                }                                                
            }
        }
    }

    /**
     * [ipapi_co_json description]
     * @param  [type] $ousursit [description]
     * @return [type]           [description]
     */
    public static function ipapi_co_json($ip, $ousursit)
    {
        global $NPDS_Prefix;

        $provider_uri = static::$providers[O].'/'.urldecode($ip).'/json';

        if(str::file_contents_exist($provider_uri)) 
        {
            $loc = file_get_contents($provider_uri);
            $loc_obj = json_decode($loc);
                          
            if($loc_obj) 
            {
                if(!property_exists($loc_obj, "error")) 
                {
                    if (!empty($loc_obj->country_name))
                    {
                        $pay = hack::remove($loc_obj->country_name);
                    }
                    else 
                    {
                        $pay = '';
                    }
                                
                    if (!empty($loc_obj->country))
                    {
                        $codepay = hack::remove($loc_obj->country);
                    }
                    else 
                    {
                        $codepay = '';
                    }
                                
                    if (!empty($loc_obj->city))
                    {
                        $vi = hack::remove($loc_obj->city);
                    }
                    else
                    {
                        $vi = '';
                    }
                                
                    if (!empty($loc_obj->latitude))
                    {
                        $lat = (float)$loc_obj->latitude;
                    }
                    else
                    {
                        $lat = '';
                    }
                                
                    if (!empty($loc_obj->longitude))
                    {
                        $long = (float)$loc_obj->longitude;
                    }
                    else
                    {
                        $long = '';
                    }
                                
                    sql_query("INSERT INTO ".$NPDS_Prefix."ip_loc (ip_long, ip_lat, ip_ip, ip_country, ip_code_country, ip_city) VALUES ('$long', '$lat', '$ip', '$pay', '$codepay', '$vi')");
                                
                    sql_query("UPDATE ".$NPDS_Prefix."ip_loc SET ip_visite= ip_visite +1, ip_visi_pag = \"$ousursit\" WHERE ip_ip LIKE \"$ip\" ");
                }
            }
        }
    } 

    /**
     * [api_ipdata_co_json description]
     * @param  [type] $ibid     [description]
     * @param  [type] $ousursit [description]
     * @return [type]           [description]
     */
    public static function api_ipdata_co_json($ip, $ousursit)
    {
        global $NPDS_Prefix;

        include('modules/'.static::$ModPath.'/config/geoloc.php');

        $provider_uri = static::$providers[1].'/'.urldecode($ip).'?api-key='.$api_key_ipdata;

        if(str::file_contents_exist($provider_uri)) 
        {
            $loc = file_get_contents($provider_uri);
            $loc_obj = json_decode($loc);
                             
            if($loc_obj) 
            {
                if(!property_exists($loc_obj, "message")) 
                {
                    if (!empty($loc_obj->country_name))
                    {
                        $pay = hack::remove($loc_obj->country_name);
                    }
                    else 
                    {
                        $pay = '';
                    }

                    if (!empty($loc_obj->country_code))
                    {
                        $codepay = hack::remove($loc_obj->country_code);
                    }
                    else 
                    {
                        $codepay = '';
                    }

                    if (!empty($loc_obj->city))
                    {
                        $vi = hack::remove($loc_obj->city);
                    }
                    else
                    {
                        $vi = '';
                    }

                    if (!empty($loc_obj->latitude))
                    {
                        $lat = (float)$loc_obj->latitude;
                    }
                    else
                    {
                        $lat = '';
                    }

                    if (!empty($loc_obj->longitude))
                    {
                        $long = (float)$loc_obj->longitude;
                    }
                    else
                    {
                        $long = '';
                    }

                    sql_query("INSERT INTO ".$NPDS_Prefix."ip_loc (ip_long, ip_lat, ip_ip, ip_country, ip_code_country, ip_city) VALUES ('$long', '$lat', '$ip', '$pay', '$codepay', '$vi')");
                                    
                    sql_query("UPDATE ".$NPDS_Prefix."ip_loc SET ip_visite=ip_visite +1, ip_visi_pag = \"$ousursit\" WHERE ip_ip LIKE \"$ip\" ");
                }
            }
        }                
    }

    /**
     * [extreme_ip_lookup_com_json description]
     * @param  [type] $ousursit [description]
     * @return [type]           [description]
     */
    public static function extreme_ip_lookup_com_json($ip, $ousursit)
    {
        global $NPDS_Prefix;

        $provider_uri = static::$providers[2].'/json/'.urldecode($ip);
                            
        if(str::file_contents_exist($provider_uri)) 
        {
            $loc = file_get_contents($provider_uri);
            $loc_obj = json_decode($loc);
                                
            if ($loc_obj->status == 'success') 
            {
                if (!empty($loc_obj->country))
                {
                    $pay = hack::remove($loc_obj->country);
                }
                else 
                {
                    $pay = '';
                }
                                   
                if (!empty($loc_obj->countryCode))
                {
                    $codepay = hack::remove($loc_obj->countryCode);
                }
                else 
                {
                    $codepay = '';
                }
                                   
                if (!empty($loc_obj->city))
                {
                    $vi = hack::remove($loc_obj->city);
                }
                else
                {
                    $vi = '';
                }
                                   
                if (!empty($loc_obj->lat))
                {
                    $lat = (float)$loc_obj->lat;
                }
                else
                {
                    $lat = '';
                }
                                   
                if (!empty($loc_obj->lon))
                {
                    $long = (float)$loc_obj->lon;
                }
                else
                {
                    $long = '';
                }
                                    
                sql_query("INSERT INTO ".$NPDS_Prefix."ip_loc (ip_long, ip_lat, ip_ip, ip_country, ip_code_country, ip_city) VALUES ('$long', '$lat', '$ip', '$pay', '$codepay', '$vi')");
                                    
                sql_query("UPDATE ".$NPDS_Prefix."ip_loc SET ip_visite= ip_visite +1, ip_visi_pag = \"$ousursit\" WHERE ip_ip LIKE \"$ip\" ");
            }
        }                
    }

    /**
     * [ip_api_com_json description]
     * @param  [type] $ousursit [description]
     * @return [type]           [description]
     */
    public static function ip_api_com_json($ip, $ousursit)
    {   
        global $NPDS_Prefix;

        $provider_uri = static::$providers[3].'/json/'.urldecode($ip);

        if(str::file_contents_exist($provider_uri)) 
        {
            $loc = file_get_contents($provider_uri);
            $loc_obj = json_decode($loc);
                     
            if($loc_obj) 
            {
                if ($loc_obj->status == 'success') 
                {
                    if (!empty($loc_obj->country))
                    {
                        $pay = hack::remove($loc_obj->country);
                    }
                    else 
                    {
                        $pay = '';
                    }

                    if (!empty($loc_obj->countryCode))
                    {
                        $codepay = hack::remove($loc_obj->countryCode);
                    }
                    else 
                    {
                        $codepay = '';
                    }

                    if (!empty($loc_obj->city))
                    {
                        $vi = hack::remove($loc_obj->city);
                    }
                    else
                    {
                        $vi = '';
                    }

                    if (!empty($loc_obj->lat))
                    {
                        $lat = (float)$loc_obj->lat;
                    }
                    else
                    {
                        $lat = '';
                    }

                    if (!empty($loc_obj->lon))
                    {
                        $long = (float)$loc_obj->lon;
                    }
                    else
                    {
                        $long = '';
                    }

                    sql_query("INSERT INTO ".$NPDS_Prefix."ip_loc (ip_long, ip_lat, ip_ip, ip_country, ip_code_country, ip_city) VALUES ('$long', '$lat', '$ip', '$pay', '$codepay', '$vi')");
                                
                    sql_query("UPDATE ".$NPDS_Prefix."ip_loc SET ip_visite= ip_visite +1, ip_visi_pag = \"$ousursit\" WHERE ip_ip LIKE \"$ip\" ");
                }
            }
        }        
    }

}

