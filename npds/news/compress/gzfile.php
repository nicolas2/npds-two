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
 * gzfile
 */
class gzfile extends archive {

    /**
     * [$gzdata description]
     * @var string
     */
    public  $gzdata = "";
       

    /**
     * [addfile description]
     * @param  [type] $data     [description]
     * @param  [type] $filename [description]
     * @param  [type] $comment  [description]
     * @return [type]           [description]
     */
    public function addfile($data, $filename=null, $comment=null) 
    {
        $flags = bindec("000".(!empty($comment)? "1" : "0").(!empty($filename)? "1" : "0")."000");
        $this->gzdata .= pack("C1C1C1C1VC1C1", 0x1f, 0x8b, 8, $flags, time(), 2, 0xFF);
        
        if(!empty($filename)) 
        { 
            $this->gzdata .= "$filename\0"; 
        }
        
        if(!empty($comment)) 
        { 
            $this->gzdata .= "$comment\0"; 
        }
        
        $this->gzdata .= gzdeflate($data);
        $this->gzdata .= pack("VV", crc32($data), strlen($data));
    }
       
    /**
     * [extract description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function extract($data) 
    {
        $id = unpack("H2id1/H2id2", substr($data, 0, 2));
          
        if($id['id1'] != "1f" || $id['id2'] != "8b") 
        { 
            return $this->error("Données non valide."); 
        }
          
        $temp = unpack("Cflags",substr($data,2,1));
        $temp = decbin($temp['flags']);
          
        if($temp & 0x8) 
        { 
            $flags['name'] = 1; 
        }

        if($temp & 0x4) 
        { 
            $flags['comment'] = 1; 
        }
          
        $offset = 10;
        $filename = "";

        while(!empty($flags['name'])) 
        {
            $char = substr($data, $offset, 1);
            $offset++;
            
            if($char == "\0") 
            { 
                break; 
            }

            $filename .= $char;
        }

        if($filename == "") 
        { 
            $filename = "file"; 
        }

        $comment = "";
        
        while(!empty($flags['comment'])) 
        {
            $char = substr($data, $offset, 1);
            $offset++;
            
            if($char == "\0") 
            { 
                break; 
            }

            $comment .= $char;
        }

        $temp = unpack("Vcrc32/Visize", substr($data, strlen($data)-8, 8));
        $crc32 = $temp['crc32'];
        $isize = $temp['isize'];
        $data = gzinflate(substr($data, $offset, strlen($data)-8-$offset));
        
        if($crc32 != crc32($data)) 
        { 
            return $this->error("Erreur de contrôle"); 
        }
        
        return array('filename' => $filename, 'comment' => $comment, 'size' => $isize, 'data' => $data);
    }
       
    /**
     * [arc_getdata description]
     * @return [type] [description]
     */
    public function arc_getdata() 
    {
        return $this->gzdata;
    }
       
    /**
     * [filedownload description]
     * @param  [type] $filename [description]
     * @return [type]           [description]
     */
    public function filedownload($filename) 
    {
        @header("Content-Type: application/x-gzip; name=\"$filename\"");
        @header("Content-Disposition: attachment; filename=\"$filename\"");
        @header("Pragma: no-cache");
        @header("Expires: 0");
        
        print($this->arc_getdata());
    }

}
