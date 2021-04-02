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

$sform_path='lib/sform/';
include_once($sform_path.'sform.php');

global $m;
$m=new form_handler();
//********************
$m->add_form_title('Register');
$m->add_form_method('post');
$m->add_form_check('false');
$m->add_url('user.php');

/************************************************/
include($sform_path.'extend-user/aff_formulaire.php');
/************************************************/
echo $m->aff_response('');
?>