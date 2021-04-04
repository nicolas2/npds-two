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
namespace npds\logs;

use npds\security\ip;


/*
 * logs
 */
class logs {

 
    /**
     * Pour &eacute;crire dans un log 
     * Exemple : security.log
     * @param [type] $fic_log [description]
     * @param [type] $req_log [description]
     * @param [type] $mot_log [description]
     */
    public static function Ecr_Log($fic_log, $req_log, $mot_log) 
    {
        // $Fic_log= the file name :
        //  => "security" for security maters
        //  => ""
        // $req_log= a phrase describe the infos
        //
        // $mot_log= if "" the Ip is recorded, else extend status infos
           
        $logfile = "storage/logs/$fic_log.log";
        $fp = fopen($logfile, 'a');
           
        flock($fp, 2);
        fseek($fp, filesize($logfile));
           
        if ($mot_log == "") 
        {
            $mot_log = "IP=>".ip:get();
        }
          
        $ibid = sprintf(
            "%-10s %-60s %-10s\r\n",
            date("m/d/Y H:i:s", time()),
            basename($_SERVER['PHP_SELF'])."=>".strip_tags(urldecode($req_log)), 
            strip_tags(urldecode($mot_log)));
           
        fwrite($fp, $ibid);
        flock($fp, 3);
        fclose($fp);
    }

}
