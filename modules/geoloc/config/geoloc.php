<?php 
/**
* Npds Two
*
* Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier
*
*
* module geoloc version 4.0
* geoloc.php file 2008-2021 by Jean Pierre Barbary (jpb)
*
* @author Nicolas2
* @version 1.0
* @date 02/04/2021
*/


// clef api bing maps
$api_key_bing = "";

// clef api mapbox
$api_key_mapbox = "";

// Champ lat dans sql
$ch_lat = "C7";

// Champ long dans sql
$ch_lon = "C8";

// interface carte

// Type de carte
$cartyp = "OSM";

// Coordinates Units
$co_unit = "dms";

// Chemin des images
$ch_img = "modules/geoloc/assets/images/";

// Autorisation de géolocalisation des IP
$geo_ip = 1;

// Clef API pour provider IP
$api_key_ipdata = "e7521a7e1698251b796ff03f7c87961a368582ed6d3ea91947b31550";

// Nom fichier image anonyme géoréférencé en ligne
$nm_img_acg = "acg.png";

// Nom fichier image membre géoréférencé en ligne
$nm_img_mbcg = "mbcg.png";

// Nom fichier image membre géoréférencé
$nm_img_mbg = "mbg.png";

// Type de marker
$mark_typ = 0;

// Largeur icone des markers
$w_ico = "28";

// Hauteur icone des markers
$h_ico = "28";

// Font SVG
$f_mbg = "user";

// Echelle du Font SVG du membre
$mbg_sc = "24";

// Epaisseur trait Font SVG du membre
$mbg_t_ep = "1";

// Couleur trait SVG du membre
$mbg_t_co = "rgb(241, 13, 13)";

// Opacité trait SVG du membre
$mbg_t_op = "1";

// Couleur fond SVG du membre
$mbg_f_co = "rgba(237, 68, 151, 0.89)";

// Opacité fond SVG du membre
$mbg_f_op = "1";

// Echelle du Font SVG du membre géoréférencé
$mbgc_sc = "32";

// Epaisseur trait Font SVG du membre géoréférencé
$mbgc_t_ep = "1";

// Couleur trait SVG du membre géoréférencé
$mbgc_t_co = "rgb(255, 255, 255)"; 

// Opacité trait SVG du membre géoréférencé
$mbgc_t_op = "1";

// Couleur fond SVG du membre géoréférencé
$mbgc_f_co = "rgba(225, 29, 75, 0.87)";

// Opacité fond SVG du membre géoréférencé
$mbgc_f_op = "1";

// Echelle du Font SVG pour anonyme en ligne
$acg_sc = "24";

// Epaisseur trait Font SVG pour anonyme en ligne
$acg_t_ep = "1";

// Couleur trait SVG pour anonyme en ligne
$acg_t_co = "rgb(255, 250, 247)";

// Opacité trait SVG pour anonyme en ligne
$acg_t_op = "1";

// Couleur fond SVG pour anonyme en ligne
$acg_f_co = "rgba(32, 32, 26, 0.89)";

// Opacité fond SVG pour anonyme en ligne
$acg_f_op = "1";

// interface bloc 

// Type de carte pour le bloc
$cartyp_b = "sat-google";

// Nom fichier image membre géoréférencé pour le bloc
$img_mbgb = "mbcg.png";

// Largeur icone marker dans le bloc
$w_ico_b = "28";

// Hauteur icone marker dans le bloc
$h_ico_b = "28";

// hauteur carte dans bloc
$h_b = "240";

// facteur zoom carte dans bloc
$z_b = "4";

// Provider Ip : http://ip-api.com
$provider_select = "3";
