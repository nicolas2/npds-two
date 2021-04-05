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
 * UniversalFeedCreator lets you choose during runtime which
 * format to build.
 * For general usage of a feed class, see the FeedCreator class
 * below or the example above.
 *
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class UniversalFeedCreator extends FeedCreator {
   var $_feed;

   function _setFormat($format) {
      switch (strtoupper($format)) {

         case "2.0":
            // fall through
         case "RSS2.0":
            $this->_feed = new RSSCreator20();
            break;

         case "1.0":
            // fall through
         case "RSS1.0":
            $this->_feed = new RSSCreator10();
            break;

         case "0.91":
            // fall through
         case "RSS0.91":
            $this->_feed = new RSSCreator091();
            break;

         case "MBOX":
            $this->_feed = new MBOXCreator();
            break;

         case "OPML":
            $this->_feed = new OPMLCreator();
            break;

         case "ATOM":
            $this->_feed = new AtomCreator03();
            break;

         default:
            $this->_feed = new RSSCreator091();
            break;
      }

      $vars = get_object_vars($this);
      foreach ($vars as $key => $value) {
         // prevent overwriting of properties "contentType", "encoding"; do not copy "_feed" itself
         if (!in_array($key, array("_feed", "contentType", "encoding"))) {
            $this->_feed->{$key} = $this->{$key};
         }
      }
   }

   /**
    * Creates a syndication feed based on the items previously added.
    *
    * @see      FeedCreator::addItem()
    * @return   string    the contents of the feed.
    */
   function createFeed($format = "RSS0.91") {
      $this->_setFormat($format);
      return $this->_feed->createFeed();
   }

   /**
    * Saves this feed as a file on the local disk. After the file is saved, an HTTP redirect
    * header may be sent to redirect the use to the newly created file.
    * @since 1.4
    *
    * @param   string   filename optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()).
    * @param   boolean  displayContents   optional send the content of the file or not. If true, the file will be sent in the body of the response.
    */
   function saveFeed($format="RSS0.91", $filename="", $displayContents=true) {
      $this->_setFormat($format);
      $this->_feed->saveFeed($filename, $displayContents);
   }

   /**
    * Turns on caching and checks if there is a recent version of this feed in the cache.
    * If there is, an HTTP redirect header is sent.
    * To effectively use caching, you should create the FeedCreator object and call this method
    * before anything else, especially before you do the time consuming task to build the feed
    * (web fetching, for example).
    *
    * @param filename   string   optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()).
    * @param timeout int      optional the timeout in seconds before a cached version is refreshed (defaults to 3600 = 1 hour)
    */
   function useCached($format="RSS0.91", $filename="", $timeout=3600) {
      $this->_setFormat($format);
      $this->_feed->useCached($filename, $timeout);
   }
}