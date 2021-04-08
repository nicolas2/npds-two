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
use npds\language\language;


// OBJET : Ce fichier vous permet de personnaliser la page de Login et le plan du site
   echo '
   <p class="text-center my-3">
      <a class="btn btn-outline-primary btn-sm" href="static.php?op=charte.html&amp;npds=1&amp;metalang=1">'.language::aff_langue("[french]Charte d'utilisation du site[/french][english]Terms of use[/english]").'</a>
   </p>';
?>