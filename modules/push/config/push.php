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


// Width of Menu block (ex : 120 pixel or "90%")
$push_largeur = "100%";

// After the Faq Menu put a html tag (for ex : "<br />")
$push_br = "";

// Width of "seconf page" block (ex : 200 pixel or "90%")
$push_largeur_suite = "100%";

// Number of news show
$push_news_limit = 10;
 
// Number of columm for member's list (1 to n)
$push_member_col = 3;

// Number of member show on each first page (default max=29)
$push_member_limit = 9;

// Title of the block ("Npds Push Addon")
$push_titre = "-: Npds Two Push :-";

// Logo (gif or jpg)
$push_logo = "modules/push/assets/images/pushlogo.gif";

// Number of Web links per page (2 or more)
$push_view_perpage = 6;

// ASCendind or DESCending orderby trigger for Web links ("ASC" or "DESC")
$push_orderby = "ASC";

// Follow <a Href ... Link in this module (True or False)
$follow_links = true;


// For NPDS SuperCache Config (or other SuperCache implementation)

// default 4*3600 secondes = 4 Hours
$CACHE_TIMINGS['push.php'] = 4*3600; 

// Don't modify this line !
$CACHE_QUERYS['push.php'] = "^";
