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

// Nombre de Stories par page 
$maxcount = 10;

// Les news en ligne (0=0;) ou les archives (0=1;) ? 
$arch = 0;

// Titre de la liste des news (par exemple : "<h2>Les Archives</h2>") / si $arch_titre est vide rien ne sera affiché 
$arch_titre = "<h2>Les Nouvelles</h2>";

// Temps de rétention en secondes
$retcache = 83000;
