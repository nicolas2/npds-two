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
namespace npds\files;

use npds\views\theme;


/*
 * file
 */
class file {


    /**
     * [$Url description]
     * @var string
     */
    public $Url = '';
       
    /**
     * [$Extention description]
     * @var string
     */
    public $Extention = '';
       
    /**
     * [$Size description]
     * @var integer
     */
    public $Size = 0;


    /**
     * [__construct description]
     * @param [type] $Url [description]
     */
    public function __construct($Url) 
    {
        $this->Url = $Url;
    }

    /**
     * [Size description]
     */
    public function Size() 
    {
        $this->Size = @filesize($this->Url);
    }

    /**
     * [Extention description]
     */
    public function Extention() 
    {
        $extension = strtolower(substr(strrchr($this->Url, '.'), 1));
        $this->Extention = $extension;
    }

    /**
     * [Affiche_Size description]
     * @param string $Format [description]
     */
    public function Affiche_Size($Format="CONVERTI") 
    {
        $this->Size();
        
        if (!$this->Size)
        { 
            return '<span class="text-danger"><strong>?</strong></span>';
        }

        switch ($Format) 
        {
            // en kilo/mega ou giga
            case "CONVERTI": 
                //return ($this->pretty_Size($this->Size));
                return '!!bug!!';
            break;

            case "NORMAL": // en octet
                return $this->Size;
            break;
        }
    }

    /**
     * [Affiche_Extention description]
     * @param [type] $Format [description]
     */
    public function Affiche_Extention($Format) 
    {
        $this->Extention();

        switch ($Format) 
        {
            case "IMG":
                if ($ibid = theme::theme_image("upload/file_types/".$this->Extention.".gif")) 
                {
                    $imgtmp = $ibid;
                } 
                else 
                {
                    $imgtmp = "assets/images/upload/file_types/".$this->Extention.".gif";
                }
                
                if (@file_exists($imgtmp)) 
                {
                    return '<img src="'.$imgtmp.'" />'; 
                }
                else 
                {
                    return '<img src="assets/images/upload/file_types/unknown.gif" />';
                }
            break;
            
            case "webfont":
                return '
                <span class="fa-stack">
                    <i class="fa fa-file fa-stack-2x"></i>
                    <span class="fa-stack-1x filetype-text">'.$this->Extention.'</span>
                </span>';
            break;
        }
    }

}
