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
 * access
 */
class access {


    /**
     * [access_denied description]
     * @return [type] [description]
     */
    public static function denied() 
    {
        include('admin/die.php');
    }

    /**
     * [error description]
     * @return [type] [description]
     */
    public static function error() 
    {
        include('admin/die.php');
    }

}
