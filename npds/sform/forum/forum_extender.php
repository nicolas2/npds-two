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
use npds\sform\SformManager;


global $m;
$m = new SformManager();

$m->add_form_title("Bugs_Report");
$m->add_form_method("post");
$m->add_form_check("false");
$m->add_mess(" * d&eacute;signe un champ obligatoire ");
$m->add_submit_value("submitS");
$m->add_url("newtopic.php");

include("npds/sform/forum/$formulaire");

if (!$submitS)
{
   echo $m->print_form('');
}
else
{
   $message = $m->aff_response('', 'not_echo', '');
}
