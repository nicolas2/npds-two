<?php
/**
 * Npds Two
 *
 * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier
 * 
 * 
 * This module is to configure the main options for your site
 * 
 * @author Nicolas2
 * @version 1.0
 * @date 02/04/2021
 */

#$CACHE_TIMINGS['index.php'] = 300;  // 5 minutes
#$CACHE_QUERYS['index.php']  = "^";  // Query_String for this page : "" = All

#$CACHE_QUERYS['leprog.php']  = "^opc=(visite|modification|commentaire)"\;
#$CACHE_QUERYS['section.php'] = "^offset=(10|20|30)&cat=[0-9]{1,2}"\;
#$CACHE_QUERYS['news.php']    = "^idn=[0-9]{1,2}"\;

$SuperCache = false;

$CACHE_TIMINGS['index.php'] = 300;
$CACHE_QUERYS['index.php'] = "^";

$CACHE_TIMINGS['article.php'] = 300;
$CACHE_QUERYS['article.php'] = "^";

$CACHE_TIMINGS['sections.php'] = 300;
$CACHE_QUERYS['sections.php'] = "^op";

$CACHE_TIMINGS['faq.php'] = 86400;
$CACHE_QUERYS['faq.php'] = "^myfaq";

$CACHE_TIMINGS['links.php'] = 28800;
$CACHE_QUERYS['links.php'] = "^";

$CACHE_TIMINGS['forum.php'] = 3600;
$CACHE_QUERYS['forum.php'] = "^";

$CACHE_TIMINGS['memberslist.php'] = 1800;
$CACHE_QUERYS['memberslist.php'] = "^";

$CACHE_TIMINGS['modules.php'] = 3600;
$CACHE_QUERYS['modules.php'] = "^";

?>