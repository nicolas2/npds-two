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
use npds\chat\chat;
use npds\views\theme;
use npds\utility\crypt;
use npds\auth\auth;
use npds\assets\css;
use npds\security\ip;
use npds\pixels\pixel;


if (!function_exists("Mysql_Connexion"))
{
    include ("boot/bootstrap.php");
}

// chatbox avec salon privatif 
// on utilise id pour filtrer les messages ->
// id = l'id du groupe au sens autorisation de Npds Two (-127,-1,0,1,2...126))
settype($id, 'integer');

if (unserialize(crypt::decrypt($auto)) != $id) 
{
    die();
}

// Savoir si le 'connecté' a le droit à ce chat ?
// le problème c'est que tous les groupes qui existent on le droit au chat ... 
// donc il faut trouver une solution pour pouvoir l'interdire
// soit on vient d'un bloc qui par définition autorise en fabricant l'interface
// soit on viens de WS et là ....

if (!auth::autorisation($id)) 
{
    die();
}

list($theme, $skin, $tmp_theme) = theme::getUsetOrDefaultThemeAndSkin();

$Titlesitename = 'Npds Two';

include("config/meta.php");

echo css::import_css($tmp_theme, $language, $skin, basename($_SERVER['PHP_SELF']), '');

include("assets/formhelp.java.php");

echo '</head>';

// cookie chat_info (1 par groupe)
echo '
<script type="text/javascript" src="assets/js/cookies.js"></script>';
echo "
    <body id=\"chat\" onload=\"setCookie('chat_info_$id', '1', '');\" onUnload=\"deleteCookie('chat_info_$id');\">";
echo '
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="assets/shared/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/shared/font-awesome/css/all.min.css">
    <form name="coolsus" action="chatinput.php" method="post">
    <input type="hidden" name="op" value="set" />
    <input type="hidden" name="id" value="'.$id.'" />
    <input type="hidden" name="auto" value="'.$auto.'" />';

if (!isset($cookie[1]))
{
    $pseudo = ((isset($name)) ? ($name) : urldecode(ip::get()));
}
else
{
    $pseudo = $cookie[1];
}

$xJava = 'name="message" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onfocus="storeForm(this)"';

echo translate("Vous êtes connecté en tant que :").' <strong>'.$pseudo.'</strong>&nbsp;';

echo '
    <input type="hidden" name="name" value="'.$pseudo.'" />
    <textarea id="chatarea" class="form-control my-3" type="text" rows="2" '.$xJava.' placeholder=""></textarea>
    <div class="float-right">';
    pixel::putitems("chatarea");

echo '
        </div>
        <input class="btn btn-primary btn-sm" type="submit" tabindex="1" value="'.translate("Valider").'" />
        </form>
        <script src="assets/js/npds_adapt.js"></script>
        <script type="text/javascript">
            //<![CDATA[
                document.coolsus.message.focus();
            //]]>
        </script>
    </body>
</html>';

settype($op, 'string');

switch ($op) 
{
    case 'set':
        if (!isset($cookie[1]) && isset($name)) 
        {
            $uname = $name;
            $dbname = 0;
        } 
        else 
        {
            $uname = $cookie[1];
            $dbname = 1;
        }
        chat::insertChat($uname, $message, $dbname, $id);
    break;
}
