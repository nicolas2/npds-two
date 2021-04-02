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

/************************************************************************/
/* $proxy_url[x]="proxy.xxx.com";                                       */
/* $proxy_port[x]=80;                                                   */
/* $proxy_url[x]="proxy.xxx.com";                                       */
/* $proxy_port[x]=80;                                                   */
/* ...                                                                  */
/* Where x is the NPDS ID (numeric) of the headline (see Admin Module)  */
/* No Definition for headlines that not require proxy methodes,         */
/* intranet for exemple                                                 */
/************************************************************************/

// Def du proxy pour le grand titre du site de news dont l'ID est 998
$proxy_url[998] = "proxy-npds.org";
$proxy_port[998] = 80;

// Def du proxy pour le grand titre du site de news dont l'ID est 999
$proxy_url[999] = "proxy-npds.org";
$proxy_port[999] = 8080;

?>