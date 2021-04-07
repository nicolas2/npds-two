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
use npds\logs\logs;


if (stristr($_SERVER['PHP_SELF'], 'sitemap.php')) 
{
    die();
}

/**
 * [sitemapforum description]
 * @param  [type] $prio [description]
 * @return [type]       [description]
 */
function sitemapforum($prio)
{
    global $NPDS_Prefix, $nuke_url;

    $tmp = '';

    $result = sql_query("SELECT forum_id FROM " . $NPDS_Prefix . "forums WHERE forum_access='0' ORDER BY forum_id");
   
    while (list($forum_id) = sql_fetch_row($result)) 
    {
        // Forums
        $tmp .= "<url>\n";
        $tmp .= "<loc>$nuke_url/viewforum.php?forum=$forum_id</loc>\n";
        $tmp .= "<lastmod>" . date("Y-m-d", time()) . "</lastmod>\n";
        $tmp .= "<changefreq>hourly</changefreq>\n";
        $tmp .= "<priority>$prio</priority>\n";
        $tmp .= "</url>\n\n";
      
        $sub_result = sql_query("SELECT topic_id, topic_time FROM " . $NPDS_Prefix . "forumtopics WHERE forum_id='$forum_id' AND topic_status!='2' ORDER BY topic_id");
      
        while (list($topic_id, $topic_time) = sql_fetch_row($sub_result)) 
        {
            // Topics
            $tmp .= "<url>\n";
            $tmp .= "<loc>$nuke_url/viewtopic.php?topic=$topic_id&amp;forum=$forum_id</loc>\n";
            $tmp .= "<lastmod>" . substr($topic_time, 0, 10) . "</lastmod>\n";
            $tmp .= "<changefreq>hourly</changefreq>\n";
            $tmp .= "<priority>$prio</priority>\n";
            $tmp .= "</url>\n\n";
        }
    }

    return $tmp;
}

/**
 * [sitemaparticle description]
 * @param  [type] $prio [description]
 * @return [type]       [description]
 */
function sitemaparticle($prio)
{
    global $NPDS_Prefix, $nuke_url;

    $tmp = '';

    $result = sql_query("SELECT sid,time FROM " . $NPDS_Prefix . "stories WHERE ihome='0' AND archive='0' ORDER BY sid");
   
    while (list($sid, $time) = sql_fetch_row($result)) 
    {
        // Articles
        $tmp .= "<url>\n";
        $tmp .= "<loc>$nuke_url/article.php?sid=$sid</loc>\n";
        $tmp .= "<lastmod>" . substr($time, 0, 10) . "</lastmod>\n";
        $tmp .= "<changefreq>daily</changefreq>\n";
        $tmp .= "<priority>$prio</priority>\n";
        $tmp .= "</url>\n\n";
    }

    return $tmp;
}

/**
 * [sitemaprub description]
 * @param  [type] $prio [description]
 * @return [type]       [description]
 */
function sitemaprub($prio)
{
    global $NPDS_Prefix, $nuke_url;

    $tmp = '';

    // Sommaire des rubriques
    $tmp .= "<url>\n";
    $tmp .= "<loc>$nuke_url/sections.php</loc>\n";
    $tmp .= "<lastmod>" . date("Y-m-d", time()) . "</lastmod>\n";
    $tmp .= "<changefreq>weekly</changefreq>\n";
    $tmp .= "<priority>$prio</priority>\n";
    $tmp .= "</url>\n\n";

    $result = sql_query("SELECT artid, timestamp FROM " . $NPDS_Prefix . "seccont WHERE userlevel='0' ORDER BY artid");
    while (list($artid, $timestamp) = sql_fetch_row($result)) 
    {
        // Rubriques
        $tmp .= "<url>\n";
        $tmp .= "<loc>$nuke_url/sections.php?op=viewarticle&amp;artid=$artid</loc>\n";
        $tmp .= "<lastmod>" . date("Y-m-d", $timestamp) . "</lastmod>\n";
        $tmp .= "<changefreq>weekly</changefreq>\n";
        $tmp .= "<priority>$prio</priority>\n";
        $tmp .= "</url>\n\n";
    }

    return $tmp;
}

/**
 * [sitemapdown description]
 * @param  [type] $prio [description]
 * @return [type]       [description]
 */
function sitemapdown($prio)
{
    global $NPDS_Prefix, $nuke_url;

    $tmp = '';

    // Sommaire des downloads
    $tmp .= "<url>\n";
    $tmp .= "<loc>$nuke_url/download.php</loc>\n";
    $tmp .= "<lastmod>" . date("Y-m-d", time()) . "</lastmod>\n";
    $tmp .= "<changefreq>weekly</changefreq>\n";
    $tmp .= "<priority>$prio</priority>\n";
    $tmp .= "</url>\n\n";

    $result = sql_query("SELECT did, ddate FROM " . $NPDS_Prefix . "downloads WHERE perms='0' ORDER BY did");
    while (list($did, $ddate) = sql_fetch_row($result)) 
    {
        $tmp .= "<url>\n";
        $tmp .= "<loc>$nuke_url/download.php?op=geninfo&amp;did=$did</loc>\n";
        $tmp .= "<lastmod>$ddate</lastmod>\n";
        $tmp .= "<changefreq>weekly</changefreq>\n";
        $tmp .= "<priority>$prio</priority>\n";
        $tmp .= "</url>\n\n";
    }

    return $tmp;
}

/**
 * [sitemapothers description]
 * @param  [type] $PAGES [description]
 * @return [type]        [description]
 */
function sitemapothers($PAGES)
{
    global $nuke_url;

    $tmp = '';
    foreach ($PAGES as $name => $loc) 
    {
        if (array_key_exists('sitemap', $PAGES[$name])) 
        {
            if (($PAGES[$name]['run'] == "yes") 
                and ($name != "article.php") 
                and ($name != "forum.php") 
                and ($name != "sections.php") 
                and ($name != "download.php")) 
            {
                $tmp .= "<url>\n";
                $tmp .= "<loc>$nuke_url/" . str_replace("&", "&amp;", $name) . "</loc>\n";
                $tmp .= "<lastmod>" . date("Y-m-d", time()) . "</lastmod>\n";
                $tmp .= "<changefreq>daily</changefreq>\n";
                $tmp .= "<priority>" . $PAGES[$name]['sitemap'] . "</priority>\n";
                $tmp .= "</url>\n\n";
            }
        }
    }

    return $tmp;
}

/**
 * [sitemap_create description]
 * @param  [type] $PAGES    [description]
 * @param  [type] $filename [description]
 * @return [type]           [description]
 */
function sitemap_create($PAGES, $filename)
{

    $ibid  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $ibid .= "<urlset\n";
    $ibid .= "xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
    $ibid .= "xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
    $ibid .= "xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n\n";

    if (array_key_exists('sitemap', $PAGES['article.php']))
    {
        $ibid .= sitemaparticle($PAGES['article.php']['sitemap']);
    }

    if (array_key_exists('sitemap', $PAGES['forum.php']))
    {
        $ibid .= sitemapforum($PAGES['forum.php']['sitemap']);
    }

    if (array_key_exists('sitemap', $PAGES['sections.php']))
    {
        $ibid .= sitemaprub($PAGES['sections.php']['sitemap']);
    }

    if (array_key_exists('sitemap', $PAGES['download.php']))
    {
        $ibid .= sitemapdown($PAGES['download.php']['sitemap']);
    }

    $ibid .= sitemapothers($PAGES);
    $ibid .= "</urlset>";

    $file = fopen($filename, "w");
    fwrite($file, $ibid);
    fclose($file);

    logs::Ecr_Log("sitemap", "sitemap generated : " . date("H:i:s", time()), "");
}

// http://www.example.com/storage/cache/sitemap.xml 
$filename = "storage/cache/sitemap.xml";

// delais = 6 heures (21600 secondes)
$refresh = 21600;

if (file_exists($filename)) 
{
    if (time() - filemtime($filename) - $refresh > 0) 
    {
        sitemap_create($PAGES, $filename);
    }
} 
else
{
   sitemap_create($PAGES, $filename);
}
