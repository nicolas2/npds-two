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
namespace npds\feed;


/**
 * FeedDate is an internal class that stores a date for a feed or feed item.
 * Usually, you won't need to use this.
 */
class FeedDate {
   var $unix;

   /**
    * Creates a new instance of FeedDate representing a given date.
    * Accepts RFC 822, ISO 8601 date formats as well as unix time stamps.
    * @param mixed $dateString optional the date this FeedDate will represent. If not specified, the current date and time is used.
    */

   public function __construct($dateString='') {
      if ($dateString=="") $dateString = date("r");

      if (is_integer($dateString)) {
         $this->unix = $dateString;
         return;
      }
      if (preg_match("~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~",$dateString,$matches)) {
         $months = Array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
         $this->unix = mktime($matches[4],$matches[5],$matches[6],$months[$matches[2]],$matches[1],$matches[3]);
         if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
            $tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
         } else {
            if (strlen($matches[7])==1) {
               $oneHour = 3600;
               $ord = ord($matches[7]);
               if ($ord < ord("M")) {
                  $tzOffset = (ord("A") - $ord - 1) * $oneHour;
               } elseif ($ord >= ord("M") AND $matches[7]!="Z") {
                  $tzOffset = ($ord - ord("M")) * $oneHour;
               } elseif ($matches[7]=="Z") {
                  $tzOffset = 0;
               }
            }
            switch ($matches[7]) {
               case "UT":
               case "GMT": $tzOffset = 0;
            }
         }
         $this->unix += $tzOffset;
         return;
      }
      if (preg_match("~(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(.*)~",$dateString,$matches)) {
         $this->unix = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
         if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
            $tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
         } else {
            if ($matches[7]=="Z") {
               $tzOffset = 0;
            }
         }
         $this->unix += $tzOffset;
         return;
      }
      $this->unix = 0;
   }
   public function FeedDate($dateString='') {
      self::__construct($dateString='');
   }

   /**
    * Gets the date stored in this FeedDate as an RFC 822 date.
    *
    * @return a date in RFC 822 format
    */
   function rfc822() {
      global $gmt;
      //return gmdate("r",$this->unix);
      $date = gmdate("D, d M Y H:i:s", $this->unix+(3600*(integer)$gmt));
      if (TIME_ZONE!="") $date .= " ".str_replace(":","",TIME_ZONE);
      return $date;
   }

   /**
    * Gets the date stored in this FeedDate as an ISO 8601 date.
    *
    * @return a date in ISO 8601 format
    */
   function iso8601() {
      global $gmt;
      $date = gmdate("Y-m-d\TH:i:sO",$this->unix+(3600*(integer)$gmt));
      $date = substr($date,0,22) . ':' . substr($date,-2);
      if (TIME_ZONE!="") $date = str_replace("+00:00",TIME_ZONE,$date);
      return $date;
   }

   /**
    * Gets the date stored in this FeedDate as unix time stamp.
    *
    * @return a date as a unix time stamp
    */
   function unix() {
      global $gmt;
      return $this->unix+(3600*(integer)$gmt);
   }
}