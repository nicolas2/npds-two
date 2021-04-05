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
 * A FeedItem is a part of a FeedCreator feed.
 *
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.3
 */
class FeedItem extends HtmlDescribable {
   /**
    * Mandatory attributes of an item.
    */
   var $title, $description, $link;

   /**
    * Optional attributes of an item.
    */
   var $author, $authorEmail, $image, $category, $comments, $guid, $source, $creator;

   /**
    * Publishing date of an item. May be in one of the following formats:
    *
    * RFC 822:
    * "Mon, 20 Jan 03 18:05:41 +0400"
    * "20 Jan 03 18:05:41 +0000"
    *
    * ISO 8601:
    * "2003-01-20T18:05:41+04:00"
    *
    * Unix:
    * 1043082341
    */
   var $date;

   /**
    * Any additional elements to include as an assiciated array. All $key => $value pairs
    * will be included unencoded in the feed item in the form
    *     <$key>$value</$key>
    * Again: No encoding will be used! This means you can invalidate or enhance the feed
    * if $value contains markup. This may be abused to embed tags not implemented by
    * the FeedCreator class used.
    */
   var $additionalElements = Array();
}