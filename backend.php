<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/* ===========================                                          */
/*                                                                      */
/* Based on PhpNuke 4.x source code                                     */
/*                                                                      */
/* NPDS Copyright (c) 2002-2020 by Philippe Brunier                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
include("mainfile.php");

include('functions.php');

function fab_feed($type,$filename,$timeout) {
   global $sitename,$slogan,$backend_image,$backend_title,$backend_width,$backend_height,$backend_language,$storyhome;
   include("lib/feed/feedcreator.class.php");

   $rss=new UniversalFeedCreator();
   $rss->useCached($type,$filename,$timeout);

   $rss->title=$sitename;
   $rss->description=$slogan;
   $rss->descriptionTruncSize=250;
   $rss->descriptionHtmlSyndicated=true;

   $rss->link=$nuke_url;
   $rss->syndicationURL=site_url('backend.php?op='.$type);

   $image=new FeedImage();
   $image->title=$sitename;
   $image->url=$backend_image;
   $image->link=$nuke_url;
   $image->description=$backend_title;
   $image->width=$backend_width;
   $image->height=$backend_height;
   $rss->image = $image;

   $xtab=news_aff('index',"WHERE ihome='0' AND archive='0'",$storyhome,'');
   $story_limit=0;
   while (($story_limit<$storyhome) and ($story_limit<sizeof($xtab))) {
      list($sid, $catid, $aid, $title, $time, $hometext, $bodytext, $comments, $counter, $topic, $informant, $notes) = $xtab[$story_limit];
      $story_limit++;
      $item = new FeedItem();
      $item->title = preview_local_langue($backend_language, str_replace('&quot;','\"',$title));
      $item->link = site_url('article.php?sid='.$sid);
      $item->description = meta_lang(preview_local_langue($backend_language, $hometext));
      $item->descriptionHtmlSyndicated = true;
      $item->date = convertdateTOtimestamp($time)+((integer)$gmt*3600);
      $item->source = $nuke_url;
      $item->author = $aid;

      $rss->addItem($item);
   }
   echo $rss->saveFeed($type, $filename);
}

// Format : RSS0.91, RSS1.0, RSS2.0, MBOX, OPML, ATOM
settype($op,'string');
$op=strtoupper($op);
switch ($op) {
    case 'MBOX':
        fab_feed('MBOX','storage/cache/MBOX-feed',3600);
        break;
    case 'OPML':
        fab_feed('OPML','storage/cache/OPML-feed.xml',3600);
        break;
    case 'ATOM':
        fab_feed('ATOM','storage/cache/ATOM-feed.xml',3600);
        break;
    case 'RSS1.0':
        fab_feed('RSS1.0','storage/cache/RSS1.0-feed.xml',3600);
        break;
    case 'RSS2.0':
        fab_feed('RSS2.0','storage/cache/RSS2.0-feed.xml',3600);
        break;
    case 'RSS0.91':
        fab_feed('RSS0.91','storage/cache/RSS0.91-feed.xml',3600);
        break;
    default:
        fab_feed('RSS1.0','storage/cache/RSS1.0-feed.xml',3600);
        break;
}
?>