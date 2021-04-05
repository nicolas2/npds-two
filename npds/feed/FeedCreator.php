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
 * FeedCreator is the abstract base implementation for concrete
 * implementations that implement a specific format of syndication.
 *
 * @abstract
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.4
 */
class FeedCreator extends HtmlDescribable {
   /**
    * Mandatory attributes of a feed.
    */
   var $title, $description, $link;

   /**
    * Optional attributes of a feed.
    */
   var $syndicationURL, $image, $language, $copyright, $pubDate, $lastBuildDate, $editor, $editorEmail, $webmaster, $category, $docs, $ttl, $rating, $skipHours, $skipDays;

   /**
   * The url of the external xsl css stylesheet used to format the naked rss feed.
   * Ignored in the output when empty.
   */
   var $xslStyleSheet = "";
   var $cssStyleSheet = "http://www.w3.org/2000/08/w3c-synd/style.css";

   /**
    * @access private
    */
   var $items = Array();

   /**
    * This feed's MIME content type.
    * @since 1.4
    * @access private
    */
   var $contentType = "application/xml";

   /**
    * This feed's character encoding.
    * @since 1.6.1
    **/
   var $encoding = cur_charset;

   /**
    * Any additional elements to include as an assiciated array. All $key => $value pairs
    * will be included unencoded in the feed in the form
    *     <$key>$value</$key>
    * Again: No encoding will be used! This means you can invalidate or enhance the feed
    * if $value contains markup. This may be abused to embed tags not implemented by
    * the FeedCreator class used.
    */
   var $additionalElements = Array();

   /**
    * Adds an FeedItem to the feed.
    *
    * @param object FeedItem $item The FeedItem to add to the feed.
    * @access public
    */
   function addItem($item) {
      $this->items[] = $item;
   }

   /**
    * Truncates a string to a certain length at the most sensible point.
    * First, if there's a '.' character near the end of the string, the string is truncated after this character.
    * If there is no '.', the string is truncated after the last ' ' character.
    * If the string is truncated, " ..." is appended.
    * If the string is already shorter than $length, it is returned unchanged.
    *
    * @static
    * @param string    string A string to be truncated.
    * @param int        length the maximum length the string should be truncated to
    * @return string    the truncated string
    */
   function iTrunc($string, $length) {
      if (strlen($string)<=$length) {
         return $string;
      }

      $pos = strrpos($string,".");
      if ($pos>=$length-4) {
         $string = substr($string,0,$length-4);
         $pos = strrpos($string,".");
      }
      if ($pos>=$length*0.4) {
         return substr($string,0,$pos+1)." ...";
      }

      $pos = strrpos($string," ");
      if ($pos>=$length-4) {
         $string = substr($string,0,$length-4);
         $pos = strrpos($string," ");
      }
      if ($pos>=$length*0.4) {
         return substr($string,0,$pos)." ...";
      }

      return substr($string,0,$length-4)." ...";

   }

   /**
    * Creates a comment indicating the generator of this feed.
    * The format of this comment seems to be recognized by
    * Syndic8.com.
    */
   function _createGeneratorComment() {
      return "<!-- generator=\"".FEEDCREATOR_VERSION."\" -->\n";
   }

   /**
    * Creates a string containing all additional elements specified in
    * $additionalElements.
    * @param   elements array an associative array containing key => value pairs
    * @param indentString  string   a string that will be inserted before every generated line
    * @return    string    the XML tags corresponding to $additionalElements
    */
   function _createAdditionalElements($elements, $indentString="") {
      $ae = "";
      if (is_array($elements)) {
         foreach($elements AS $key => $value) {
            $ae.= $indentString."<$key>$value</$key>\n";
         }
      }
      return $ae;
   }

   function _createStylesheetReferences() {
      $xml = "";
      if ($this->cssStyleSheet) $xml .= "<?xml-stylesheet href=\"".$this->cssStyleSheet."\" type=\"text/css\"?>\n";
      if ($this->xslStyleSheet) $xml .= "<?xml-stylesheet href=\"".$this->xslStyleSheet."\" type=\"text/xsl\"?>\n";
      return $xml;
   }

   /**
    * Builds the feed's text.
    * @abstract
    * @return    string    the feed's complete text
    */
   function createFeed() {
   }

   /**
    * Generate a filename for the feed cache file. The result will be $_SERVER["PHP_SELF"] with the extension changed to .xml.
    * @return string the feed cache filename
    * @since 1.4
    * @access private
    */
   function _generateFilename() {
      $fileInfo = pathinfo($_SERVER['PHP_SELF']);
      return substr($fileInfo["basename"],0,-(strlen($fileInfo["extension"])+1)).".xml";
   }

   /**
    * @since 1.4
    * @access private
    */
   function _redirect($filename) {
      Header("Content-Type: ".$this->contentType."; filename=".basename($filename));
      Header("Content-Disposition: inline; filename=".basename($filename));
      readfile($filename, "r");
      die();
   }

   /**
    * Turns on caching and checks if there is a recent version of this feed in the cache.
    * If there is, an HTTP redirect header is sent.
    * To effectively use caching, you should create the FeedCreator object and call this method
    * before anything else, especially before you do the time consuming task to build the feed
    * (web fetching, for example).
    * @since 1.4
    * @param filename   string   optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()).
    * @param timeout int      optional the timeout in seconds before a cached version is refreshed (defaults to 3600 = 1 hour)
    */
   function useCached($filename="", $timeout=3600) {
      if ($filename=="") {
         $filename = $this->_generateFilename();
      }
      if (file_exists($filename) AND (time()-filemtime($filename) < $timeout)) {
         $this->_redirect($filename);
      }
   }

   /**
    * Saves this feed as a file on the local disk. After the file is saved, a redirect
    * header may be sent to redirect the user to the newly created file.
    * @since 1.4
    *
    * @param filename   string   optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()).
    * @param redirect   boolean  optional send an HTTP redirect header or not. If true, the user will be automatically redirected to the created file.
    */
   function saveFeed($filename="", $displayContents=true) {
      if ($filename=="") {
         $filename = $this->_generateFilename();
      }
      $feedFile = fopen($filename, "w+");
      if ($feedFile) {
         fputs($feedFile,$this->createFeed());
         fclose($feedFile);
         if ($displayContents) {
            $this->_redirect($filename);
         }
      } else {
         echo "<br /><b>Erreur de création du fichier de canal / Error creating feed file</b><br />";
      }
   }
}