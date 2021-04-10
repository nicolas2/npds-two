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
namespace npds\news\compress;

use npds\news\archive;


/*
 * zipfile
 */
class zipfile extends archive {

    /**
     * [$cwd description]
     * @var string
     */
    public  $cwd = "./";

    /**
     * [$comment description]
     * @var string
     */
    public $comment = "";

    /**
     * [$level description]
     * @var integer
     */
    public $level = 9;

    /**
     * [$offset description]
     * @var integer
     */
    public $offset = 0;

    /**
     * [$recursesd description]
     * @var integer
     */
    public $recursesd = 1;

    /**
     * [$storepath description]
     * @var integer
     */
    public $storepath = 1;

    /**
     * [$replacetime description]
     * @var integer
     */
    public $replacetime = 0;

    /**
     * [$central description]
     * @var array
     */
    public $central = array();

    /**
     * [$zipdata description]
     * @var array
     */
    public $zipdata = array();


    /**
     * [__construct description]
     * @param string $cwd   [description]
     * @param array  $flags [description]
     */
    public function __construct($cwd="./", $flags=array()) 
    {
        $this->cwd = $cwd;
        
        if(isset($flags['time'])) 
        { 
            $this->replacetime = $flags['time']; 
        }
        
        if(isset($flags['recursesd'])) 
        { 
            $this->recursesd = $flags['recursesd']; 
        }
        
        if(isset($flags['storepath'])) 
        { 
            $this->storepath = $flags['storepath']; 
        }
        
        if(isset($flags['level'])) 
        { 
            $this->level = $flags['level']; 
        }
        
        if(isset($flags['comment'])) 
        { 
            $this->comment = $flags['comment']; 
        }
        
        parent::__construct($flags);
    }
       
    /**
     * [addfile description]
     * @param  [type] $data     [description]
     * @param  [type] $filename [description]
     * @param  array  $flags    [description]
     * @return [type]           [description]
     */
    public function addfile($data, $filename, $flags=array()) 
    {
        if($this->storepath != 1) 
        { 
            $filename = strstr($filename, "/")? substr($filename, strrpos($filename, "/")+1) : $filename; 
        }
        else 
        { 
            $filename = preg_replace("/^(\.{1,2}(\/|\\\))+/", "", $filename); 
        }
        
        $mtime = !empty($this->replacetime)? getdate($this->replacetime) : (isset($flags['time'])? getdate($flags['time']) : getdate());
        
        $mtime = preg_replace("/(..){1}(..){1}(..){1}(..){1}/","\\x\\4\\x\\3\\x\\2\\x\\1",dechex(($mtime['year']-1980<<25)|($mtime['mon']<<21)|($mtime['mday']<<16)|($mtime['hours']<<11)|($mtime['minutes']<<5)|($mtime['seconds']>>1)));
        
        eval('$mtime = "'.$mtime.'";');
        
        $crc32 = crc32($data);
        $normlength = strlen($data);
        $data = gzcompress($data, $this->level);
        $data = substr($data, 2, strlen($data)-6);
        $complength = strlen($data);
        
        $this->zipdata[] = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00".$mtime.pack("VVVvv", $crc32, $complength, $normlength, strlen($filename), 0x00).$filename.$data.pack("VVV", $crc32, $complength, $normlength);
        
        $this->central[] = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00".$mtime.pack("VVVvvvvvVV", $crc32, $complength, $normlength, strlen($filename), 0x00, 0x00, 0x00, 0x00, 0x0000, $this->offset).$filename;
        
        $this->offset = strlen(implode("", $this->zipdata));
    }
     
    /**
     * [addfiles description]
     * @param  [type] $filelist [description]
     * @return [type]           [description]
     */
    public function addfiles($filelist) 
    {
        $pwd = getcwd();
        @chdir($this->cwd);
        
        foreach($filelist as $current) 
        {
            if(!@file_exists($current)) 
            { 
                continue; 
            }

            $stat = stat($current);
            
            if($fp = @fopen($current, "rb"))
            {
                if ($stat[7] > 0)
                {
                    $data = fread($fp, $stat[7]);
                }
                fclose($fp);
            }
            else
            {
                $data = "";
            }
            
            $flags = array('time' => $stat[9]);
            $this->addfile($data, $current, $flags);
        }

        @chdir($pwd);
    }

    /**
     * [arc_getdata description]
     * @return [type] [description]
     */
    public function arc_getdata() 
    {
        $central = implode("", $this->central);
        $zipdata = implode("", $this->zipdata);
        
        return $zipdata.$central."\x50\x4b\x05\x06\x00\x00\x00\x00".pack("vvVVv", sizeof($this->central), sizeof($this->central), strlen($central), strlen($zipdata), strlen($this->comment)).$this->comment;
    }
     
    /**
     * [filedownload description]
     * @param  [type] $filename [description]
     * @return [type]           [description]
     */
    public function filedownload($filename) 
    {
        @header("Content-Type: application/zip; name=\"$filename\"");
        @header("Content-Disposition: attachment; filename=\"$filename\"");
        @header("Pragma: no-cache");
        @header("Expires: 0");
        
        print($this->arc_getdata());
    }

}
