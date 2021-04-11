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
use npds\assets\css;
use npds\cache\cache;


if (!stristr($_SERVER['PHP_SELF'], 'admin.php')) 
{
   access::error();
}

$f_meta_nom = 'setban';

admindroits($aid, $f_meta_nom);

include ("modules/$ModPath/lang/ipban.lang-$language.php");

$f_titre = ipban_translate("Administration de l'IpBan");

settype($hlpfile,'string');

$hlpfile = 'admin/manuels/'.$language.'/ipban.html';

/**
 * [ConfigureBan description]
 * @param [type] $ModPath  [description]
 * @param [type] $ModStart [description]
 */
function ConfigureBan($ModPath, $ModStart) 
{
    global $f_meta_nom, $f_titre, $adminimg,$language, $hlpfile;
    
    settype($ip_ban, 'string');
       
    if (file_exists('storage/logs/spam.log')) 
    {
        $fd = fopen ('storage/logs/spam.log', 'r');
        while (!feof($fd)) 
        {
            $ip_ban.= fgets($fd, 4096);
        }
        fclose ($fd);
    }

    GraphicAdmin($hlpfile);
    adminhead ($f_meta_nom, $f_titre, $adminimg);
    
    echo '
        <hr />
            <div class="card card-body mb-3">
                '.ipban_translate("Chaque ligne ne doit contenir qu'une adresse IP (v4 ou v6) de forme : a.b.c.d|X (ex. v4 : 168.192.1.1|5) ; a:b:c:d:e:f:g:h|X (ex. v6 : 2001:0db8:0000:85a3:0000:0000:ac1f:8001|5).").'<br />
                <span class="text-danger lead">'.ipban_translate("Si X >= 5 alors l'accès sera refusé !").'</span><br />
                '.ipban_translate("Ce fichier est mis à jour automatiquement par l'anti-spam de NPDS.").'
            </div>
            <form id="ipban_mod" action="admin.php" method="post">
                <div class="form-group row">
                    <label class="col-form-label col-sm-12" for="ip_ban">'.ipban_translate("Liste des IP").'</label>
                    <div class="col-sm-12">
                        <textarea id="ip_ban" class="form-control" name="ipban" rows="15">'.$ip_ban.'</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-12">
                        <button class="btn btn-primary" type="submit">'.adm_translate("Sauver les modifications").'</button>
                        <input type="hidden" name="op" value="Extend-Admin-SubModule" />
                        <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                        <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                        <input type="hidden" name="subop" value="SaveSetBan" />
                    </div>
                </div>
            </form>';
    
    css::adminfoot('', '', '', '');
}

/**
 * [SaveSetBan description]
 * @param [type] $Xip_ban [description]
 */
function SaveSetBan($Xip_ban) 
{
    $file = fopen('storage/logs/spam.log', 'w');
    fwrite($file, $Xip_ban);
    fclose($file);
    cache::SC_clean();
}

settype($subop, 'string');

switch ($subop) {

    case 'SaveSetBan':
        SaveSetBan($ipban);
    break;

    default:
        ConfigureBan($ModPath, $ModStart);
    break;
}
