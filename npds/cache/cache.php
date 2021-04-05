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
namespace npds\cache;


/*
 * cache
 */
class cache {


    /**
     * [Q_Select description]
     * @param [type]  $Xquery    [description]
     * @param integer $retention [description]
     */
    public static function Q_Select($Xquery, $retention=3600) 
    {
        global $SuperCache, $cache_obj;
       
        if (($SuperCache) and ($cache_obj)) 
        {
            $row = $cache_obj->CachingQuery($Xquery, $retention);
          
            return ($row);
        } 
        else 
        {
            $result = @sql_query($Xquery);
            $tab_tmp = array();
          
            while($row = sql_fetch_assoc($result)) 
            {
                $tab_tmp[] = $row;
            }
          
            return ($tab_tmp);
        }
    }

    /**
     * [PG_clean description]
     * @param [type] $request [description]
     */
    public static function PG_clean($request) 
    {
        global $CACHE_CONFIG;
       
        $page = md5($request);
        $dh = opendir($CACHE_CONFIG['data_dir']);
        
        while(false !== ($filename = readdir($dh))) 
        {
            if ($filename === '.' 
                OR $filename === '..' 
                OR (strpos($filename, $page) === FALSE)) 
            {
                continue;
            }
              
            unlink($CACHE_CONFIG['data_dir'].$filename);
        }
        closedir($dh);
    }

    /**
     * [Q_Clean description]
     */
    public static function Q_Clean() 
    {
        global $CACHE_CONFIG;
        
        $dh = opendir($CACHE_CONFIG['data_dir']."sql");
        
        while(false !== ($filename = readdir($dh))) 
        {
            if ($filename === '.' 
                OR $filename === '..') 
            {
                continue;
            }
              
            if (is_file($CACHE_CONFIG['data_dir']."sql/".$filename))
            {
                unlink($CACHE_CONFIG['data_dir']."sql/".$filename);
            }
        }

        closedir($dh);
        $fp = fopen($CACHE_CONFIG['data_dir']."sql/.htaccess", 'w');
        @fputs($fp, "Deny from All");
        fclose($fp);
    }

    /**
     * [SC_clean description]
     */
    public static function SC_clean() 
    {
        global $CACHE_CONFIG;
       
        $dh = opendir($CACHE_CONFIG['data_dir']);
       
        while (false !== ($filename = readdir($dh))) 
        {
            if ($filename === '.' 
                OR $filename === '..' 
                OR $filename === 'ultramode.txt' 
                OR $filename === 'net2zone.txt' 
                OR $filename === 'sql' 
                OR $filename === 'index.html')
            { 
                continue;
            }
             
            if (is_file($CACHE_CONFIG['data_dir'].$filename))
            {
                unlink($CACHE_CONFIG['data_dir'].$filename);
            }
        }
        closedir($dh);
        static::Q_Clean();
    }

    /**
     * Indique le status de SuperCache
     */
    public static function SC_infos() 
    {
        global $SuperCache, $npds_sc;
        
        if ($SuperCache) 
        {
            if ($npds_sc) 
            {
                return '<span class="small">'.translate(".:Page << Super-Cache:.").'</span>';
            } 
            else 
            {
                return '<span class="small">'.translate(".:Page >> Super-Cache:.").'</span>';
            }
        }
    }

}
