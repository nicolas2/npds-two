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
namespace npds\news;

use npds\news\compresse\zipfile;
use npds\news\compresse\gzfile;


/*
 * sendfile
 */
class sendfile {


    #autodoc send_file($line,$filename,$extension,$MSos) : compresse et t&eacute;l&eacute;charge un fichier / $line : le flux, $filename et $extension le fichier, $MSos (voir fonction get_os)
    public static function send_file($line, $filename, $extension, $MSos) 
    {
        $compressed = false;
        
        if (file_exists("npds/news/archive.php")) 
        {
            if (function_exists("gzcompress")) 
            {
                $compressed = true;
            }
        }

        if ($compressed) 
        {
            if ($MSos) 
            {
                $arc = new zipfile();
                $filez = $filename.".zip";
            } 
            else 
            {
                $arc = new gzfile();
                $filez = $filename.".gz";
            }

            $arc->addfile($line, $filename.".".$extension, "");
            $arc->arc_getdata();
            $arc->filedownload($filez);
        } 
        else 
        {
            if ($MSos) 
            {
                header("Content-Type: application/octetstream");
            } 
            else 
            {
                header("Content-Type: application/octet-stream");
            }

            header("Content-Disposition: attachment; filename=\"$filename."."$extension\"");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            echo $line;
        }
    }

    #autodoc send_tofile($line,$repertoire,$filename,$extension,$MSos) : compresse et enregistre un fichier / $line : le flux, $repertoire $filename et $extension le fichier, $MSos (voir fonction get_os)
    function send_tofile($line, $repertoire, $filename, $extension, $MSos) 
    {
        $compressed = false;
        
        if (file_exists("npds/news/archive.php")) 
        {
            if (function_exists("gzcompress")) 
            {
                $compressed = true;
            }
        }

        if ($compressed) 
        {
            if ($MSos) 
            {
                $arc = new zipfile();
                $filez = $filename.".zip";
            } 
            else 
            {
                $arc = new gzfile();
                $filez = $filename.".gz";
            }

            $arc->addfile($line, $filename.".".$extension, "");
            $arc->arc_getdata();
            
            if (file_exists($repertoire."/".$filez)) 
            {
                unlink($repertoire."/".$filez);
            }
            
            $arc->filewrite($repertoire."/".$filez, $perms=null);
        } 
        else 
        {
            if ($MSos) 
            {
                header("Content-Type: application/octetstream");
            } 
            else 
            {
                header("Content-Type: application/octet-stream");
            }

            header("Content-Disposition: attachment; filename=\"$filename."."$extension\"");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            echo $line;
        }
    }

}
