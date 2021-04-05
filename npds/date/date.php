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
namespace npds\date;


/*
 * date
 */
class date {


    /**
     * [convertdateTOtimestamp description]
     * @param  [type] $myrow [description]
     * @return [type]        [description]
     */
    public static function convertdateTOtimestamp($myrow) 
    {
        if (substr($myrow, 2, 1) == "-") 
        {
            $day = substr($myrow, 0, 2);
            $month = substr($myrow, 3, 2);
            $year = substr($myrow, 6, 4);
        } 
        else 
        {
            $day = substr($myrow, 8, 2);
            $month = substr($myrow, 5, 2);
            $year = substr($myrow, 0, 4);
        }
           
        $hour = substr($myrow, 11, 2);
        $mns = substr($myrow, 14, 2);
        $sec = substr($myrow, 17, 2);
        $tmst = mktime($hour, $mns, $sec, $month, $day, $year);
           
        return $tmst;
    }

    /**
     * [post_convertdate description]
     * @param  [type] $tmst [description]
     * @return [type]       [description]
     */
    public static function post_convertdate($tmst) 
    {
        if ($tmst > 0)
        {
            $val = date(translate("dateinternal"), $tmst);
        }
        else
        {
            $val = '';
        }
          
        return $val;
    }

    /**
     * [convertdate description]
     * @param  [type] $myrow [description]
     * @return [type]        [description]
     */
    public static function convertdate($myrow) 
    {
        $tmst = static::convertdateTOtimestamp($myrow);
        $val = static::post_convertdate($tmst);
        
        return $val;
    }

}
