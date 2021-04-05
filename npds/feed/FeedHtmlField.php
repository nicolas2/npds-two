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
 * An FeedHtmlField describes and generates
 * a feed, item or image html field (probably a description). Output is
 * generated based on $truncSize, $syndicateHtml properties.
 * @author Pascal Van Hecke <feedcreator.class.php@vanhecke.info>
 * @version 1.6
 */
class FeedHtmlField {
   /**
    * Mandatory attributes of a FeedHtmlField.
    */
   var $rawFieldContent;

   /**
    * Optional attributes of a FeedHtmlField.
    *
    */
   var $truncSize, $syndicateHtml;

   /**
    * Creates a new instance of FeedHtmlField.
    * @param  $string: if given, sets the rawFieldContent property
    */

   public function __construct($parFieldContent) {
      if ($parFieldContent)
         $this->rawFieldContent = $parFieldContent;
   }
   public function FeedHtmlField($parFieldContent) {
      self::__construct($parFieldContent);
   }

   /**
    * Creates the right output, depending on $truncSize, $syndicateHtml properties.
    * @return string the formatted field
    */
   function output() {
      // when field available and syndicated in html we assume
      // - valid html in $rawFieldContent and we enclose in CDATA tags
      // - no truncation (truncating risks producing invalid html)
      if (!$this->rawFieldContent) {
         $result = "";
      }  elseif ($this->syndicateHtml) {
         $result = "<![CDATA[".$this->rawFieldContent."]]>";
      } else {
         if ($this->truncSize and is_int($this->truncSize)) {
            $result = FeedCreator::iTrunc(htmlspecialchars($this->rawFieldContent,ENT_COMPAT|ENT_HTML401,cur_charset),$this->truncSize);
         } else {
            $result = htmlspecialchars($this->rawFieldContent,ENT_COMPAT|ENT_HTML401,cur_charset);
         }
      }
      return $result;
   }
}