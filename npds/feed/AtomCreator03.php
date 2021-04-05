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
 * AtomCreator03 is a FeedCreator that implements the atom specification,
 * as in http://www.intertwingly.net/wiki/pie/FrontPage.
 * Please note that just by using AtomCreator03 you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * Some elements have not been implemented yet. These are (incomplete list):
 * author URL, item author's email and URL, item contents, alternate links,
 * other link content types than text/html. Some of them may be created with
 * AtomCreator03::additionalElements.
 *
 * @see FeedCreator#additionalElements
 * @since 1.6
 * @author Kai Blankenhorn <kaib@bitfolge.de>, Scott Reynen <scott@randomchaos.com>
 */
class AtomCreator03 extends FeedCreator {

   public function __construct() {
      $this->contentType = "application/atom+xml";
      $this->encoding = "utf-8";
   }
   public function AtomCreator03() {
      self::__construct();
   }

   function createFeed() {
      $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
      $feed.= $this->_createGeneratorComment();
      $feed.= "<feed xmlns=\"http://www.w3.org/2005/Atom\"";
      if ($this->language!="") {
         $feed.= " xml:lang=\"".$this->language."\"";
      }
      $feed.= ">\n";
      $feed.= "    <title type=\"html\">".htmlspecialchars($this->title,ENT_COMPAT|ENT_HTML401,$this->encoding)."</title>\n";
      $feed.= "    <tagline>".htmlspecialchars($this->description,ENT_COMPAT|ENT_HTML401,$this->encoding)."</tagline>\n";
      $feed.= "    <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->link,ENT_COMPAT|ENT_HTML401,$this->encoding)."\"/>\n";
      $feed.= "    <id>".htmlspecialchars($this->link,ENT_COMPAT|ENT_HTML401,$this->encoding)."</id>\n";
      $now = new FeedDate();
      $feed.= "    <updated>".htmlspecialchars($now->iso8601(),ENT_COMPAT|ENT_HTML401,$this->encoding)."</updated>\n";
      if ($this->editor!="") {
         $feed.= "    <author>\n";
         $feed.= "        <name>".$this->editor."</name>\n";
         if ($this->editorEmail!="") {
            $feed.= "        <email>".$this->editorEmail."</email>\n";
         }
         $feed.= "    </author>\n";
      }
      $feed.= "    <generator>".FEEDCREATOR_VERSION."</generator>\n";
      $feed.= $this->_createAdditionalElements($this->additionalElements, "    ");
      for ($i=0;$i<count($this->items);$i++) {
         $feed.= "    <entry>\n";
         $feed.= "        <title>".htmlspecialchars(strip_tags($this->items[$i]->title),ENT_COMPAT|ENT_HTML401,$this->encoding)."</title>\n";
         $feed.= "        <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->items[$i]->link,ENT_COMPAT|ENT_HTML401,$this->encoding)."\"/>\n";
         if ($this->items[$i]->date=="") {
            $this->items[$i]->date = time();
         }
         $itemDate = new FeedDate($this->items[$i]->date);
         $feed.= "        <created>".htmlspecialchars($itemDate->iso8601(),ENT_COMPAT|ENT_HTML401,$this->encoding)."</created>\n";
         $feed.= "        <issued>".htmlspecialchars($itemDate->iso8601(),ENT_COMPAT|ENT_HTML401,$this->encoding)."</issued>\n";
         $feed.= "        <updated>".htmlspecialchars($itemDate->iso8601(),ENT_COMPAT|ENT_HTML401,$this->encoding)."</updated>\n";
         $feed.= "        <id>".htmlspecialchars($this->items[$i]->link,ENT_COMPAT|ENT_HTML401,$this->encoding)."</id>\n";
         $feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
         if ($this->items[$i]->author!="") {
            $feed.= "        <author>\n";
            $feed.= "            <name>".htmlspecialchars($this->items[$i]->author,ENT_COMPAT|ENT_HTML401,$this->encoding)."</name>\n";
            $feed.= "        </author>\n";
         }
         if ($this->items[$i]->description!="") {
            $feed.= "        <summary type=\"html\">".htmlspecialchars($this->items[$i]->description,ENT_COMPAT|ENT_HTML401,$this->encoding)."</summary>\n";
         }
         $feed.= "    </entry>\n";
      }
      $feed.= "</feed>\n";
      return $feed;
   }
}