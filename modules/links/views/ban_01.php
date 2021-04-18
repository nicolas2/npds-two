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


// Le système de bannière
global $banners;
if (($banners) and function_exists("viewbanner")) 
{
    echo '<p class="text-center">';
    viewbanner();
    echo '</p>';
}
