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
namespace npds\support;


/*
 * str
 */
class str {


    /**
     * [addslashes_GPC description]
     * @param  [type] &$arr [description]
     * @return [type]       [description]
     */
    public static function addslashes_GPC(&$arr) 
    {
        $arr = addslashes($arr);
    }

}
