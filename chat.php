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

// Pour le lancement du Chat : 
// chat.php?id=gp_id&auto=token_de_securite
// gp_id=ID du groupe au sens Npds Two du terme => 
// 0 : tous 
// -127 : Admin 
// -1 : Anonyme 
// 1 : membre  
// 2 ... 126 : groupe de membre
// token_de_securite = encrypt(serialize(gp_id)) 
// Permet d'éviter le lancement du Chat sans autorisation

if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

$Titlesitename = 'Npds Two';
$nuke_url = '';
$meta_op = '';

$meta_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset///EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">";

include('config/meta.php');

echo '
<link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />
</head>
    <frameset rows="1%,60%,240">
        <frame src="chatrafraich.php?repere=0&amp;aff_entetes=1&amp;connectes=-1&amp;id='.$id.'&amp;auto='.$auto.'" frameborder="0" scrolling="no" noresize="noresize" name="rafraich">
        <frame src="chattop.php" frameborder="0" scrolling="yes" noresize="noresize" name="haut">
        <frame src="chatinput.php?id='.$id.'&amp;auto='.$auto.'" frameborder="0" scrolling="yes" noresize="noresize" name="bas">
    </frameset>
</html>';
