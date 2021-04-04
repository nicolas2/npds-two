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
namespace npds\session;

use npds\security\hack;
use npds\security\ip;


/*
 * session
 */
class session {

     
    /**
     * Mise à jour la table session
     * @return [type] [description]
     */
    public static function manage() 
    {
        global $NPDS_Prefix, $cookie, $REQUEST_URI, $nuke_url;

        $guest = 0;
        $ip = ip::get();
        
        $username = isset($cookie[1]) ? $cookie[1] : $ip;
           
        if($username == $ip)
        {
            $guest = 1;
        }
              
        // mod_geoloc
        include("modules/geoloc/geoloc_conf.php");
              
        $file_path = array(
            'https://ipapi.co/'.$ip.'/json',
            'https://api.ipdata.co/'.$ip.'?api-key='.$api_key_ipdata,
            'https://extreme-ip-lookup.com/json/'.$ip,
            'http://ip-api.com/json/'.$ip
        );

        $file = file("modules/geoloc/geoloc_conf.php");
              
        if(strstr($file[25], 'geo_ip = 1')) 
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
                sql_query("UPDATE ".$NPDS_Prefix."ip_loc SET ip_visite= ip_visite +1 , ip_visi_pag = \"$ousursit\" WHERE ip_ip LIKE \"$ip\" ");
            }
            else 
            {
                $ibid = false;
                if(strstr($nuke_url, 'https')) 
                {
                    if(file_contents_exist($file_path[0])) 
                    {
                        $loc = file_get_contents($file_path[0]);
                        $loc_obj = json_decode($loc);
                          
                        if($loc_obj) 
                        {
                            if(!property_exists($loc_obj, "error")) 
                            {
                                $ibid = true;
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
                       
                    if($ibid == false) 
                    {
                        if(file_contents_exist($file_path[1])) 
                        {
                            $loc = file_get_contents($file_path[1]);
                            $loc_obj = json_decode($loc);
                             
                            if($loc_obj) 
                            {
                                if(!property_exists($loc_obj, "message")) 
                                {
                                    $ibid = true;
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
                                        $lat = (float)$loc_obj->latitude;}
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
                          
                        if($ibid == false) 
                        {
                            if(file_contents_exist($file_path[2])) 
                            {
                                $loc = file_get_contents($file_path[2]);
                                $loc_obj = json_decode($loc);
                                
                                if ($loc_obj->status == 'success') 
                                {
                                    $ibid = true;
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
                else if(strstr($nuke_url, 'http')) 
                {
                    if(file_contents_exist($file_path[3])) 
                    {
                        $loc = file_get_contents($file_path[3]);
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
        }
        // mod_geoloc

        $past = time()-300;
        
        sql_query("DELETE FROM ".$NPDS_Prefix."session WHERE time < '$past'");
        $result = sql_query("SELECT time FROM ".$NPDS_Prefix."session WHERE username='$username'");
           
        if ($row = sql_fetch_assoc($result)) 
        {
            if ($row['time'] < (time()-30)) 
            {
                sql_query("UPDATE ".$NPDS_Prefix."session SET username='$username', time='".time()."', host_addr='$ip', guest='$guest', uri='$REQUEST_URI', agent='".getenv("HTTP_USER_AGENT")."' WHERE username='$username'");
                
                if ($guest == 0) 
                {
                    global $gmt;
                    sql_query("UPDATE ".$NPDS_Prefix."users SET user_lastvisit='".(time()+(integer)$gmt*3600)."' WHERE uname='$username'");
                }
            }
        } 
        else 
        {
            sql_query("INSERT INTO ".$NPDS_Prefix."session (username, time, host_addr, guest, uri, agent) VALUES ('$username', '".time()."', '$ip', '$guest', '$REQUEST_URI', '".getenv("HTTP_USER_AGENT")."')");
        }
    }

}
