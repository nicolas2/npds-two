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
use npds\sform\SformManager;


global $m;
$m = new SformManager();

$m->add_form_title("coolsus");
$m->add_form_method("post");
$m->add_form_check("false");
$m->add_mess("[french]* désigne un champ obligatoire[/french][english]* required field[/english]");
$m->add_submit_value("submitS");
$m->add_url("modules.php");

include("modules/comments/sform/$formulaire");

if(!isset($GLOBALS["submitS"])) 
{
  	echo language::aff_langue($m->print_form(''));
}
else
{
  	$message = language::aff_langue($m->aff_response('', "not_echo", ''));
}
