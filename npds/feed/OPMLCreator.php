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
 * OPMLCreator is a FeedCreator that implements OPML 1.0.
 *
 * @see http://opml.scripting.com/spec
 * @author Dirk Clemens, Kai Blankenhorn
 * @since 1.5
 */
class OPMLCreator extends FeedCreator {

   public function __construct() {
      $this->encoding = "utf-8";
   }
   public function OPMLCreator() {
      self::__construct();
   }

   function createFeed() {
      $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
      $feed.= $this->_createGeneratorComment();
      $feed.= "<opml xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n";
      $feed.= "    <head>\n";
      $feed.= "        <title>".htmlspecialchars($this->title,ENT_COMPAT|ENT_HTML401,$this->encoding)."</title>\n";
      if ($this->pubDate!="") {
         $date = new FeedDate($this->pubDate);
         $feed.= "         <dateCreated>".$date->rfc822()."</dateCreated>\n";
      }
      if ($this->lastBuildDate!="") {
         $date = new FeedDate($this->lastBuildDate);
         $feed.= "         <dateModified>".$date->rfc822()."</dateModified>\n";
      }
      if ($this->editor!="") {
         $feed.= "         <ownerName>".$this->editor."</ownerName>\n";
      }
      if ($this->editorEmail!="") {
         $feed.= "         <ownerEmail>".$this->editorEmail."</ownerEmail>\n";
      }
      $feed.= "    </head>\n";
      $feed.= "    <body>\n";
      for ($i=0;$i<count($this->items);$i++) {
         $feed.= "    <outline type=\"rss\" ";
         $title = htmlspecialchars(strip_tags(strtr($this->items[$i]->title,"\n\r","  ")),ENT_COMPAT|ENT_HTML401,$this->encoding);
         $feed.= " title=\"".$title."\"";
         $feed.= " text=\"".$title."\"";
         $feed.= " url=\"".htmlspecialchars($this->items[$i]->link,ENT_COMPAT|ENT_HTML401,$this->encoding)."\"";
         $feed.= "/>\n";
      }
      $feed.= "    </body>\n";
      $feed.= "</opml>\n";
      return $feed;
   }
}