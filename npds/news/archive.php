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


/*
 * archive
 */
class archive {

    /**
     * [$overwrite description]
     * @var integer
     */
    public $overwrite = 0;
    
    /**
     * [$defaultperms description]
     * @var integer
     */
    public $defaultperms   = 0644;


    /**
     * [__construct description]
     * @param array $flags [description]
     */
    public function __construct($flags=array()) 
    {
        if(isset($flags['overwrite'])) 
        { 
            $this->overwrite = $flags['overwrite']; 
        }

        if(isset($flags['defaultperms']))
        { 
            $this->defaultperms = $flags['defaultperms']; 
        }
    }

    /**
     * [adddirectories description]
     * @param  [type] $dirlist [description]
     * @return [type]          [description]
     */
    public function adddirectories($dirlist) 
    {
        $pwd = getcwd();
        @chdir($this->cwd);
        $filelist = array();
        
        foreach($dirlist as $current) 
        {
            if(@is_dir($current)) 
            {
                $temp = $this->parsedirectories($current);
                foreach($temp as $filename) 
                { 
                    $filelist[] = $filename; 
                }
            }
            elseif(@file_exists($current)) 
            {
                $filelist[] = $current;
            }
        }
        @chdir($pwd);
        $this->addfiles($filelist);
    }
       
    /**
     * [parsedirectories description]
     * @param  [type] $dirname [description]
     * @return [type]          [description]
     */
    public function parsedirectories($dirname) 
    {
        $filelist = array();
        $dir = @opendir($dirname);
        
        while(false !== ($file = readdir($dir))) 
        {
            if($file == "." 
                || $file == ".." 
                || $file == "default.html" 
                || $file == "index.html")
            {
                 continue;
            }
            elseif(@is_dir($dirname."/".$file)) 
            {
                if($this->recursesd != 1) 
                { 
                    continue; 
                }

                $temp = $this->parsedirectories($dirname."/".$file);
                
                foreach($temp as $file2) 
                {
                   $filelist[] = $file2;
                }
            }
            elseif(@file_exists($dirname."/".$file))
            {
                $filelist[] = $dirname."/".$file;
            }
        }
        @closedir($dir);
          
        return $filelist;
    }

    /**
     * [filewrite description]
     * @param  [type] $filename [description]
     * @param  [type] $perms    [description]
     * @return [type]           [description]
     */
    public function filewrite($filename, $perms=null) 
    {
        if($this->overwrite != 1 && @file_exists($filename)) 
        { 
            return $this->error("Le fichier $filename existe déjà."); 
        }

        if(@file_exists($filename)) 
        { 
            @unlink($filename); 
        }
        
        $fp = @fopen($filename, "wb");
        
        if(!fwrite($fp, $this->arc_getdata()))
        {
              return $this->error("Impossible d'écrire les données dans le fichier $filename.");
        }
        
        @fclose($fp);
        
        if(!isset($perms))
        {
              $perms = $this->defaultperms;
        }
        
        @chmod($filename, $perms);
    }

    /**
     * [extractfile description]
     * @param  [type] $filename [description]
     * @return [type]           [description]
     */
    public function extractfile($filename) 
    {
        if($fp = @fopen($filename, "rb")) 
        {
            if (filesize($filename) > 0)
            {
                 return $this->extract(fread($fp, filesize($filename)));
            }
            else
            {
                 return $this->error("Fichier $filename vide.");
            }
            @fclose($fp);
        }
        else
        {
              return $this->error("Impossible d'ouvrir le fichier $filename.");
        }
    }
       
    /**
     * [error description]
     * @param  [type] $error [description]
     * @return [type]        [description]
     */
    public function error($error) 
    {
        $this->errors[] = $error;
        return 0;
    }

}
