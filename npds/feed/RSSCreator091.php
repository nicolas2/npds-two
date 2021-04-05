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
 * RSSCreator091 is a FeedCreator that implements RSS 0.91 Spec, revision 3.
 *
 * @see http://my.netscape.com/publish/formats/rss-spec-0.91.html
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class RSSCreator091 extends FeedCreator {

   /**
    * Stores this RSS feed's version number.
    * @access private
    */
   var $RSSVersion;

   public function __construct() {
      $this->_setRSSVersion("0.91");
      $this->contentType = "application/rss+xml";
   }
      public function RSSCreator091() {
      self::__construct();
   }

   /**
    * Sets this RSS feed's version number.
    * @access private
    */
   function _setRSSVersion($version) {
      $this->RSSVersion = $version;
   }

   /**
    * Builds the RSS feed's text. The feed will be compliant to RDF Site Summary (RSS) 1.0.
    * The feed will contain all items previously added in the same order.
    * @return    string    the feed's complete text
    */
   function createFeed() {
      $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
      $feed.= $this->_createGeneratorComment();
      // $feed.= $this->_createStylesheetReferences();
      $feed.= "<rss version=\"".$this->RSSVersion."\">\n";
      $feed.= "    <channel>\n";
      $feed.= "        <title>".FeedCreator::iTrunc(htmlspecialchars($this->title,ENT_COMPAT|ENT_HTML401,$this->encoding),100)."</title>\n";
      $this->descriptionTruncSize = 500;
      $feed.= "        <description>".$this->getDescription()."</description>\n";
      $feed.= "        <link>".$this->link."</link>\n";
      $now = new FeedDate();
      $feed.= "        <lastBuildDate>".htmlspecialchars($now->rfc822(),ENT_COMPAT|ENT_HTML401,$this->encoding)."</lastBuildDate>\n";
      $feed.= "        <generator>".FEEDCREATOR_VERSION."</generator>\n";

      if ($this->image!=null) {
         $feed.= "        <image>\n";
         $feed.= "            <url>".$this->image->url."</url>\n";
         $feed.= "            <title>".FeedCreator::iTrunc(htmlspecialchars($this->image->title,ENT_COMPAT|ENT_HTML401,$this->encoding),100)."</title>\n";
         $feed.= "            <link>".$this->image->link."</link>\n";
         if ($this->image->width!="") {
            $feed.= "            <width>".$this->image->width."</width>\n";
         }
         if ($this->image->height!="") {
            $feed.= "            <height>".$this->image->height."</height>\n";
         }
         if ($this->image->description!="") {
            $feed.= "            <description>".$this->image->getDescription()."</description>\n";
         }
         $feed.= "        </image>\n";
      }
      if ($this->language!="") {
         $feed.= "        <language>".$this->language."</language>\n";
      }
      if ($this->copyright!="") {
         $feed.= "        <copyright>".FeedCreator::iTrunc(htmlspecialchars($this->copyright,ENT_COMPAT|ENT_HTML401,$this->encoding),100)."</copyright>\n";
      }
      if ($this->editor!="") {
         $feed.= "        <managingEditor>".FeedCreator::iTrunc(htmlspecialchars($this->editor,ENT_COMPAT|ENT_HTML401,$this->encoding),100)."</managingEditor>\n";
      }
      if ($this->webmaster!="") {
         $feed.= "        <webMaster>".FeedCreator::iTrunc(htmlspecialchars($this->webmaster,ENT_COMPAT|ENT_HTML401,$this->encoding),100)."</webMaster>\n";
      }
      if ($this->pubDate!="") {
         $pubDate = new FeedDate($this->pubDate);
         $feed.= "        <pubDate>".htmlspecialchars($pubDate->rfc822(),ENT_COMPAT|ENT_HTML401,$this->encoding)."</pubDate>\n";
      }
      if ($this->category!="") {
         $feed.= "        <category>".htmlspecialchars($this->category,ENT_COMPAT|ENT_HTML401,$this->encoding)."</category>\n";
      }
      if ($this->docs!="") {
         $feed.= "        <docs>".FeedCreator::iTrunc(htmlspecialchars($this->docs,ENT_COMPAT|ENT_HTML401,$this->encoding),500)."</docs>\n";
      }
      if ($this->ttl!="") {
         $feed.= "        <ttl>".htmlspecialchars($this->ttl,ENT_COMPAT|ENT_HTML401,$this->encoding)."</ttl>\n";
      }
      if ($this->rating!="") {
         $feed.= "        <rating>".FeedCreator::iTrunc(htmlspecialchars($this->rating,ENT_COMPAT|ENT_HTML401,$this->encoding),500)."</rating>\n";
      }
      if ($this->skipHours!="") {
         $feed.= "        <skipHours>".htmlspecialchars($this->skipHours,ENT_COMPAT|ENT_HTML401,$this->encoding)."</skipHours>\n";
      }
      if ($this->skipDays!="") {
         $feed.= "        <skipDays>".htmlspecialchars($this->skipDays,ENT_COMPAT|ENT_HTML401,$this->encoding)."</skipDays>\n";
      }
      $feed.= $this->_createAdditionalElements($this->additionalElements, "    ");

      for ($i=0;$i<count($this->items);$i++) {
         $feed.= "        <item>\n";
         $feed.= "            <title>".FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title),ENT_COMPAT|ENT_HTML401,$this->encoding),100)."</title>\n";
         $feed.= "            <link>".htmlspecialchars($this->items[$i]->link,ENT_COMPAT|ENT_HTML401,$this->encoding)."</link>\n";
         $feed.= "            <description>".$this->items[$i]->getDescription()."</description>\n";

         if ($this->items[$i]->author!="") {
            $feed.= "            <author>".htmlspecialchars($this->items[$i]->author,ENT_COMPAT|ENT_HTML401,$this->encoding)."</author>\n";
         }
         if ($this->items[$i]->category!="") {
            $feed.= "            <category>".htmlspecialchars($this->items[$i]->category,ENT_COMPAT|ENT_HTML401,$this->encoding)."</category>\n";
         }
         if ($this->items[$i]->comments!="") {
            $feed.= "            <comments>".htmlspecialchars($this->items[$i]->comments,ENT_COMPAT|ENT_HTML401,$this->encoding)."</comments>\n";
         }
         if ($this->items[$i]->date!="") {
         $itemDate = new FeedDate($this->items[$i]->date);
            $feed.= "            <pubDate>".htmlspecialchars($itemDate->rfc822(),ENT_COMPAT|ENT_HTML401,$this->encoding)."</pubDate>\n";
         }
         if ($this->items[$i]->guid!="") {
            $feed.= "            <guid>".htmlspecialchars($this->items[$i]->guid,ENT_COMPAT|ENT_HTML401,$this->encoding)."</guid>\n";
         }
         $feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
         $feed.= "        </item>\n";
      }
      $feed.= "    </channel>\n";
      $feed.= "</rss>\n";
      return $feed;
   }
}