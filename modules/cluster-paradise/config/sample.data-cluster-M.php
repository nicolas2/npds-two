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


// Maitre (vous) 
$part[0] = array(
    "WWW"         => "www.npds.org",
    "KEY"         => "la_clef_de_npds.org",
);

// Esclave N°1
$part[1] = array(
    "WWW"         => "www.esclave-un.net",
    "SUBSCRIBE"   => "NEWS",
    "OP"          => "EXPORT",
    "FROMTOPICID" => "5",
    "TOTOPIC"     => "GNU / GPL",
    "FROMCATID"   => "",
    "TOCATEG"     => "",
    "AUTHOR"      => "NPDS-Cluster",
    "MEMBER"      => "NPDS"
);

// Esclave N°2
$part[2] = array(
    "WWW"         => "www.esclave-deux.net",
    "SUBSCRIBE"   => "NEWS",
    "OP"          => "EXPORT",
    "FROMTOPICID" => "",
    "TOTOPIC"     => "",
    "FROMCATID"   => "",
    "TOCATEG"     => "",
    "AUTHOR"      => "Npds-Two-Cluster",
    "MEMBER"      => "Npds-Two"
);
