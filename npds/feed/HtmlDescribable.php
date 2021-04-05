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
 * An HtmlDescribable is an item within a feed that can have a description that may
 * include HTML markup.
 */
class HtmlDescribable {
   /**
    * Indicates whether the description field should be rendered in HTML.
    */
   var $descriptionHtmlSyndicated;

   /**
    * Indicates whether and to how many characters a description should be truncated.
    */
   var $descriptionTruncSize;

   /**
    * Returns a formatted description field, depending on descriptionHtmlSyndicated and
    * $descriptionTruncSize properties
    * @return    string    the formatted description
    */
   function getDescription() {
      $descriptionField = new FeedHtmlField($this->description);
      $descriptionField->syndicateHtml = $this->descriptionHtmlSyndicated;
      $descriptionField->truncSize = $this->descriptionTruncSize;
      return $descriptionField->output();
   }
}