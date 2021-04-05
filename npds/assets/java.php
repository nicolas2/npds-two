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
namespace npds\assets;


/*
 * java
 */
class java {


	/**
	 * Personnalise une ouverture de fenêtre (popup)
	 * @param [type] $F [description]
	 * @param [type] $T [description]
	 * @param [type] $W [description]
	 * @param [type] $H [description]
	 */
	public static function JavaPopUp($F, $T, $W, $H) 
	{
	    if ($T == "") 
	    {
	    	$T = "@ ".time()." ";
	    }
	    
	    $PopUp = "'$F','$T','menubar=no,location=no,directories=no,status=no,copyhistory=no,height=$H,width=$W,toolbar=no,scrollbars=yes,resizable=yes'";
	    
	    return $PopUp;
	}

}
