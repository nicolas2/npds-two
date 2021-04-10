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
use npds\error\access;


if (!stristr($_SERVER['PHP_SELF'], 'admin.php')) 
{
	access::error();
}

include ("header.php");

if ($ModPath != '') 
{
    if (file_exists("modules/$ModPath/$ModStart.php"))
    {
        include("modules/$ModPath/$ModStart.php");
    }
} 
else
{
    redirect_url(urldecode($ModStart));
}
