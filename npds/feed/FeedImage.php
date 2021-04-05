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
 * An FeedImage may be added to a FeedCreator feed.
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.3
 */
class FeedImage extends HtmlDescribable {
   /**
    * Mandatory attributes of an image.
    */
   var $title, $url, $link;

   /**
    * Optional attributes of an image.
    */
   var $width, $height, $description;
}