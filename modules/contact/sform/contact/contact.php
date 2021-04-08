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
use npds\assets\css;
use npds\logs\logs;
use npds\utility\spam;
use npds\language\language;
use npds\mailler\mailler;


global $ModPath, $ModStart;

global $m;
$m = new SformManager();

$m->add_form_title('contact');
$m->add_form_id('formcontact');
$m->add_form_method('post');
$m->add_form_check('false');
$m->add_url('modules.php');
$m->add_field('ModStart', '', $ModStart, 'hidden', false);
$m->add_field('ModPath', '', $ModPath, 'hidden', false);
$m->add_submit_value('subok');
$m->add_field('subok', '', 'Submit', 'hidden', false);

include('modules/'.$ModPath.'/sform/contact/formulaire.php');

css::adminfoot('fv', '', 'var formulid = ["'.$m->form_id.'"];', '1');

// Manage the <form>
switch($subok) 
{
    
    case 'Submit':
        settype($message, 'string');
        settype($sformret, 'string');
        
        if (!$sformret) 
        {
            $m->make_response();
            
            //anti_spambot
            if (!spam::R_spambot($asb_question, $asb_reponse, $message)) 
            {
                logs::Ecr_Log('security', 'Contact', '');
                $subok = '';
            } 
            else 
            {
                $message = $m->aff_response('', 'not_echo', '');
                
                global $notify_email;
                mailler::send_email($notify_email, "Contact site", language::aff_langue($message), '', '', "html");
                
                echo '
                <div class="alert alert-success">
                '.language::aff_langue("[french]Votre demande est prise en compte. Nous y r&eacute;pondrons au plus vite[/french][english]Your request is taken into account. We will answer it as fast as possible.[/english][chinese]&#24744;&#30340;&#35831;&#27714;&#24050;&#34987;&#32771;&#34385;&#22312;&#20869;&#12290; &#25105;&#20204;&#20250;&#23613;&#24555;&#22238;&#22797;[/chinese][spanish]Su solicitud es tenida en cuenta. Le responderemos lo m&aacute;s r&aacute;pido posible.[/spanish][german]Ihre Anfrage wird ber&uuml;cksichtigt. Wir werden so schnell wie m&ouml;glich antworten[/german]").'
                </div>';
                break;
            }
        } 
        else
        {
            $subok = '';
        }

    default:
        echo language::aff_langue($m->print_form(''));
    break;
}
