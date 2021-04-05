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


/*
 * filemanager
 */
class filemanager {

	/**
	 * [$units description]
	 * @var array
	 */
	public $units = array('B', 'KB', 'MB', 'GB', 'TB');
	  

	/**
	 * [file_size_format description]
	 * @param  [type] $fileName  [description]
	 * @param  [type] $precision [description]
	 * @return [type]            [description]
	 */
	public function file_size_format($fileName, $precision) 
	{
	    $bytes = $fileName;
	    $bytes = max($bytes, 0);
	    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	    $pow = min($pow, count($this->units) - 1);
	    $bytes /= pow(1024, $pow);
	    $retValue = round($bytes, $precision) . ' ' . $this->units[$pow];
	      
	    return $retValue;
	}

	/**
	 * [file_size_auto description]
	 * @param  [type] $fileName  [description]
	 * @param  [type] $precision [description]
	 * @return [type]            [description]
	 */
	public function file_size_auto($fileName, $precision) 
	{
	    $bytes= @filesize($fileName);
	    $bytes= max($bytes, 0);
	    $pow= floor(($bytes ? log($bytes) : 0) / log(1024));
	    $pow= min($pow, count($this->units) - 1);
	    $bytes /= pow(1024, $pow);
	    $retValue = round($bytes, $precision) . ' ' . $this->units[$pow];
	    
	    return $retValue;
	}

	/**
	 * [file_size_option description]
	 * @param  [type] $fileName [description]
	 * @param  [type] $unitType [description]
	 * @return [type]           [description]
	 */
	public function file_size_option($fileName, $unitType) 
	{
	    switch($unitType) 
	    {
	        case $this->units[0]: 
	            $fileSize = number_format((filesize(trim($fileName))), 1) ; 
	        break;

	        case $this->units[1]: 
	            $fileSize = number_format((filesize(trim($fileName))/1024), 1) ; 
	        break;

	        case $this->units[2]: 
	            $fileSize = number_format((filesize(trim($fileName))/1024/1024), 1) ; 
	        break;

	        case $this->units[3]: 
	            $fileSize = number_format((filesize(trim($fileName))/1024/1024/1024), 1) ; 
	        break;

	        case $this->units[4]: 
	            $fileSize = number_format((filesize(trim($fileName))/1024/1024/1024/1024), 1) ; 
	        break;
	    }
	    
	    $retValue = $fileSize. ' ' .$unitType;
	    
	    return $retValue;
	}

}
