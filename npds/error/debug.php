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
namespace npds\error;


/*
 * error
 */
class debug {


    /**
     * Modify the report level of PHP
     * @return [type] [description]
     */
    public static function reporting()
    {
        $debug = include ('config/debug.php');

        // report NO ERROR
        if ($debug['noerror'] === true) 
        {
            error_reporting(0);
        }

        // Devel report
        if ($debug['devel'] === true) 
        {
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        }
 
        // standard ERROR report
        if ($debug['standard'] === true) 
        {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
        } 

        // report All error
        if ($debug['all'] === true) 
        {
            error_reporting(E_ALL);
        }
    }

}
