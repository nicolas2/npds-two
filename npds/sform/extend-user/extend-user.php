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

$m->add_form_title('Register');
$m->add_form_id('register');
$m->add_form_method('post');
$m->add_form_check('false');
$m->add_url('user.php');

include('npds/sform/extend-user/formulaire.php');

echo $m->print_form('');
