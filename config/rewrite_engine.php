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

/*
 * Note :
 * Ce fichier permet de demander à SuperCache de procéder à une modif
 * sur les pages dont il assure le cache. ce traitement peut opérer
 * des modifications dans le résultat HTML et doit agir sur
 * la variable $output
 * 
 * par exemple : 
 * $output=preg_replace('class="noir"', "", $output)
 */

?>