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
use npds\groupes\groupe;
use npds\cache\cache;
use npds\views\theme;
use npds\error\access;
use npds\utility\crypt;
use npds\language\language; 
use npds\editeur\tiny;
use modules\fmanager\support\fmanager;
use modules\fmanager\support\navigator;


if (!stristr($_SERVER['PHP_SELF'], 'modules.php')) 
{
    die();
}

global $ModPath, $ModStart, $language, $Default_Theme, $Default_Skin, $NPDS_Key, $NPDS_Prefix;

if (file_exists("modules/$ModPath/lang/f-manager-$language.php"))
{
    include ("modules/$ModPath/lang/f-manager-$language.php");
}
else
{
    include ("modules/$ModPath/lang/f-manager-english.php");
}

// Lancement sur un Répertoire en fonction d'un fichier de conf particulier
if ($FmaRep) 
{
    if (filtre_module($FmaRep)) 
    {
        // Si je ne trouve pas de fichier - est-ce que l'utilisateur fait partie d'un groupe ?
        if (!file_exists("modules/$ModPath/config/users/conf/".strtolower($FmaRep).".php")) 
        {
            $tab_groupe = groupe::valid_group($user);
            if ($tab_groupe) 
            {
                // si j'ai au moins un groupe est ce que celui-ci dispose d'un fichier de configuration ?  - je m'arrête au premier groupe !
                foreach($tab_groupe as $gp) 
                {
                    $groupename = cache::Q_select("SELECT groupe_name FROM ".$NPDS_Prefix."groupes WHERE groupe_id='$gp' ORDER BY `groupe_id` ASC", 3600);
               
                    if (file_exists("modules/$ModPath/config/users/conf/".$groupename[0]['groupe_name'].".php")) 
                    {
                        $FmaRep = $groupename[0]['groupe_name'];
                        break;
                    }
                }
            }
        }

        if (file_exists("modules/$ModPath/config/users/conf/".strtolower($FmaRep).".php")) 
        {
            // Est ce que je doit récupérer le theme si un utilisateur est connecté ?
            if (isset($user)) 
            {
                $themelist = explode(' ', theme::list());
                $pos = array_search($cookie[9], $themelist);
                
                if ($pos !== false)
                {
                    $Default_Theme = $themelist[$pos];
                }
            }

            include("modules/$ModPath/config/users/conf/".strtolower($FmaRep).".php");
         
            if (fmanager::fma_autorise('a', '')) 
            {
                $theme_fma = $themeG_fma;
                $fic_minuscptr = 0;
                $dir_minuscptr = 0;
            } 
            else
            {
                access::error();
            }
        } 
        else
        {
            access::error();
        }
    } 
    else
    {
        access::error();
    }
} 
else
{
    access::error();
}

if (isset($browse)) 
{
    $ibid = rawurldecode(crypt::decrypt($browse));
   
    if (substr(@php_uname(), 0, 7) == 'Windows')
    {
        $ibid = preg_replace('#[\*\?"<>|]#i', '', $ibid);
    }
    else
    {
        $ibid = preg_replace('#[\:\*\?"<>|]#i', '', $ibid);
    }
   
    $ibid = str_replace('..', '', $ibid);
    // contraint à rester dans la zone de repertoire définie (CHROOT)
    $base = $basedir_fma.substr($ibid, strlen($basedir_fma));
} 
else 
{
    $browse = '';
    $base = $basedir_fma;
}

// initialisation de la classe
$obj = new navigator();
$obj->Extension = explode(' ', $extension_fma);

// traitements
$rename_dir = ''; 
$remove_dir = ''; 
$chmod_dir = '';
$remove_file = ''; 
$move_file = ''; 
$rename_file = ''; 
$chmod_file = ''; 
$edit_file = '';

if (substr(@php_uname(), 0, 7) == "Windows")
{
   $log_dir = str_replace($basedir_fma, '', $base);
}
else
{
   $log_dir = str_replace("\\", "/", str_replace($basedir_fma, '', $base));
}

include_once("modules/upload/upload.conf.php");

include_once("modules/fmanager/support/action.php");

// Construction de la Classe
if ($obj->File_Navigator($base, $tri_fma['tri'], $tri_fma['sens'], $dirsize_fma)) 
{
   // Current PWD and Url_back / match by OS determination
   if (substr(@php_uname(), 0, 7) == "Windows") 
   {
      $cur_nav = str_replace("\\", "/", $obj->Pwd());
      $cur_nav_back = dirname($base);
   } 
   else 
   {
      $cur_nav = $obj->Pwd();
      $cur_nav_back = str_replace("\\", "/", dirname($base));
   }

   // contraint à rester dans la zone de répertoire définie (CHROOT)
   $cur_nav = $base.substr($cur_nav,strlen($base));

   $home = '/'.basename($basedir_fma);
   $cur_nav_href_back = "<a href=\"modules.php?ModPath=$ModPath&amp;ModStart=$ModStart&amp;FmaRep=$FmaRep&amp;browse=".rawurlencode(crypt::encrypt($cur_nav_back))."$urlext_fma\">".str_replace(dirname($basedir_fma), "", $cur_nav_back)."</a>/".basename($cur_nav);
   
   if ($home_fma != '') 
   {
      $cur_nav_href_back = str_replace($home, $home_fma, $cur_nav_href_back);
   }

   $cur_nav_encrypt = rawurlencode(crypt::encrypt($cur_nav));
} 
else 
{
   // le répertoire ou sous répertoire est protégé (ex : chmod)
   redirect_url("modules.php?ModPath=$ModPath&amp;ModStart=$ModStart&amp;FmaRep=$FmaRep&amp;browse=".rawurlencode(crypt::encrypt(dirname($base))));
}

// gestion des types d'extension de fichiers
$handle = opendir("$racine_fma/assets/images/upload/file_types");
while (false !== ($file = readdir($handle))) 
{
   if ($file != '.' && $file != '..') 
   {
      $prefix = strtoLower(substr($file, 0, strpos($file, '.')));
      $att_icons[$prefix] = '<img src="assets/images/upload/file_types/'.$file.'" alt="" />'; // no more used keep if we back
      $att_icons[$prefix] = '
      <span class="fa-stack">
        <i class="fa fa-file fa-stack-2x text-muted"></i>
        <span class="fa-stack-1x filetype-text ">'.$prefix.'</span>
      </span>';
   }
}

closedir($handle);

$att_icon_default = '
      <span class="fa-stack">
        <i class="fa fa-file fa-stack-2x text-muted"></i>
        <span class="fa-stack-1x filetype-text ">?</span>
      </span>';
$att_icon_multiple = "<img src=\"assets/images/upload/file_types/multiple.gif\" alt=\"\" />";
$att_icon_dir = '<i class="fa fa-folder fa-lg"></i>';
$att_icon_search = '<i class="fa fa-search fa-lg"></i>';

$suppM = fma_translate("Supprimer");
$renaM = fma_translate("Renommer");
$chmoM = fma_translate("Chmoder");
$editM = fma_translate("Editer");
$moveM = fma_translate("Déplacer / Copier");
$pictM = fma_translate("Autoriser Pic-Manager");

// Répertoires
$subdirs = ''; 
$sizeofDir = 0;

settype($tab_search, 'array');

while ($obj->NextDir()) 
{
    if (fmanager::fma_autorise('d', $obj->FieldName)) 
    {
        $sizeofDir = 0;
         $subdirs .= '
        <tr>';
      
        $clik_url = "<a href=\"modules.php?ModPath=$ModPath&amp;ModStart=$ModStart&amp;FmaRep=$FmaRep&amp;browse=".rawurlencode(crypt::encrypt("$base/$obj->FieldName"))."$urlext_fma\">";
      
        if ($dirpres_fma[0])
        {
            $subdirs .= '<td width="3%" align="center">'.$clik_url.$att_icon_dir.'</a></td>';
        }
      
        if ($dirpres_fma[1])
        {
            $subdirs .= '<td nowrap="nowrap">'.$clik_url.fmanager::extend_ascii($obj->FieldName).'</a></td>';
        }
      
        if ($dirpres_fma[2])
        {
            $subdirs.='<td><small>'.$obj->FieldDate.'</small></td>';
        }
      
        if ($dirpres_fma[3]) 
        {
            $sizeofD = $obj->FieldSize;
            $sizeofDir = $sizeofDir+(integer)$sizeofD;
            $subdirs .= '<td class="d-none d-sm-table-cell"><small>'.$obj->ConvertSize($sizeofDir).'</small></td>';
        }
        else
        {
            $subdirs .= '
            <td class="d-none d-sm-table-cell"><small>#NA#</small></td>';
        }
      
        if ($dirpres_fma[4])
        {
            $subdirs .= '<td class="d-none d-sm-table-cell"><small>'.$obj->FieldPerms.'</small></td>';
        }
      
        // Traitements
        $obj->FieldName = rawurlencode($obj->FieldName);
        $subdirs .= '
            <td>';
      
        if ($dircmd_fma[1])
        {
            $subdirs .= '<a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=renamedir&amp;att_name='.$obj->FieldName.'"><i class="fa fa-edit fa-lg" title="'.$renaM.'" data-toggle="tooltip"></i></a>';
        }
      
        if ($dircmd_fma[3])
        {
            $subdirs .= ' <a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=chmoddir&amp;att_name='.$obj->FieldName.'"><i class="fas fa-pencil-alt fa-lg ml-2" title="'.$chmoM.'" data-toggle="tooltip"></i><small>7..</small></a>';
        }
      
        if ($dirpres_fma[5])
        {
            $subdirs .= ' <a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=pict&amp;att_name='.$obj->FieldName.'"><i class="fa fa-image fa-lg ml-2" title="'.$pictM.'" data-toggle="tooltip"></i></a>';
        }
      
        if ($dircmd_fma[2])
        {
            $subdirs .= ' <a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=removedir&amp;att_name='.$obj->FieldName.'"><i class="far fa-trash-alt fa-lg text-danger ml-2" title="'.$suppM.'" data-toggle="tooltip"></i></a>';
        }
      
        $subdirs .= '</td>
        </tr>';

        // Search Result for sub-directories
        if ($tab_search) 
        {
            reset($tab_search);

            foreach($tab_search as $l => $fic_resp) 
            {
                if ($fic_resp[0] == $obj->FieldName) 
                {
                    $ibid = rawurlencode(crypt::encrypt(rawurldecode(encrypt($cur_nav.'/'.$fic_resp[0])).'#fma#'.crypt::encrypt($fic_resp[1])));
               
                    $subdirs .= '
                    <tr>
                        <td width="3%"></td>
                    <td>';
               
                    $pop = "'getfile.php?att_id=$ibid&amp;apli=f-manager'";
                    $target = "target=\"_blank\"";
               
                    if (!$wopen_fma) 
                    {
                        $subdirs .= "$att_icon_search <a href=$pop $target>".fmanager::extend_ascii($fic_resp[1])."</a></td></tr>\n";
                    } 
                    else 
                    {
                        if (!isset($wopenH_fma)) 
                        {
                            $wopenH_fma = 500;
                        }

                        if (!isset($wopenW_fma)) 
                        {
                            $wopenW_fma = 400;
                        }

                        $PopUp = "$pop,'FManager','menubar=no,location=no,directories=no,status=no,copyhistory=no,height=$wopenH_fma,width=$wopenW_fma,toolbar=no,scrollbars=yes,resizable=yes'";
                        $subdirs .= "$att_icon_search <a href=\"javascript:void(0);\" onclick=\"popup=window.open($PopUp); popup.focus();\">".fmanager::extend_ascii($fic_resp[1])."</a></td></tr>\n";
                    }
                    array_splice($tab_search, $l, 1);
                }
            }
        }
    }
}

// Fichiers
$files = ''; 
$sizeofFic = 0;
while ($obj->NextFile()) 
{
    if (fmanager::fma_autorise('f', $obj->FieldName)) 
    {
        $ibid = rawurlencode(crypt::encrypt($cur_nav_encrypt."#fma#".crypt::encrypt($obj->FieldName)));
        $files.= '
        <tr>';
      
        if ($ficpres_fma[0]) 
        {
            $ico_search = false;
            $files .= '
            <td width="3%" align="center">';
         
            if ($tab_search) 
            {
                reset($tab_search);

                // notice pour le each ...
                while ( (list($l, $fic_resp) = each($tab_search)) and (!$ico_search)) 
                {
                    if ($fic_resp[1] == $obj->FieldName) 
                    {
                        array_splice($tab_search, $l, 1);
                        $files .= $att_icon_search;
                        $ico_search = true;
                    }
                }
            }

            if (!$ico_search) 
            {
                if (($obj->FieldView == 'jpg') 
                    or ($obj->FieldView == 'jpeg') 
                    or ($obj->FieldView == 'gif') 
                    or ($obj->FieldView == 'png'))
                {
                    $files .= "<img src=\"getfile.php?att_id=$ibid&amp;apli=f-manager\" width=\"32\" height=\"32\" />";
                }
                else 
                {
                    if (isset($att_icons[$obj->FieldView]))
                    {
                        $files .= $att_icons[$obj->FieldView];
                    }
                    else
                    {
                        $files .= $att_icon_default;
                    }
                }
            }
            $files .= '</td>';
        }

        if ($ficpres_fma[1]) 
        {
            if ($url_fma_modifier) 
            {
                include("$racine_fma/modules/$ModPath/config/users/mod/$FmaRep.php");
                
                $pop = $url_modifier;
                $target = '';
            } 
            else 
            {
                $pop = "'getfile.php?att_id=$ibid&amp;apli=f-manager'";
                $target = 'target="_blank"';
            }

            if (!$wopen_fma) 
            {
                $files .= "
                <td nowrap=\"nowrap\" width=\"50%\"><a href=$pop $target>".fmanager::extend_ascii($obj->FieldName)."</a></td>";
            } 
            else 
            {
                if (!isset($wopenH_fma))
                { 
                    $wopenH_fma = 500;
                }

                if (!isset($wopenW_fma)) 
                {
                    $wopenW_fma = 400;
                }

                $PopUp = "$pop,'FManager','menubar=no,location=no,directories=no,status=no,copyhistory=no,height=$wopenH_fma,width=$wopenW_fma,toolbar=no,scrollbars=yes,resizable=yes'";
            
                if (stristr($PopUp, "window.opener"))
                {
                    $files .= "
                    <td><a href=\"javascript:void(0);\" $PopUp popup.focus();\">".fmanager::extend_ascii($obj->FieldName)."</a></td>";
                }
                else
                {
                    $files .= "
                    <td><a href=\"javascript:void(0);\" onclick=\"popup=window.open($PopUp); popup.focus();\">".fmanager::extend_ascii($obj->FieldName)."</a></td>";}
            }
        }

        if ($ficpres_fma[2])
        {
            $files .= '<td><small>'.$obj->FieldDate.'</small></td>';
        }
      
        if ($ficpres_fma[3]) 
        {
            $sizeofF = $obj->FieldSize;
            $sizeofFic = $sizeofFic+$sizeofF;
            $files .= '<td><small>'.$obj->ConvertSize($sizeofF).'</small></td>';
        }  
        else 
        {
            $files .= '<td><small>#NA#</small></td>';
        }

        if ($ficpres_fma[4]) 
        {
            $files .= '<td><small>'.$obj->FieldPerms.'</small></td>'; 
        }
        else 
        {
            $files .= "<td><small>#NA#</small></td>";
        }

        // Traitements
        $obj->FieldName = rawurlencode($obj->FieldName);
        $cmd_ibid = '';

        if ($ficcmd_fma[1])
        {
            $cmd_ibid .= '<a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=renamefile&amp;att_name='.$obj->FieldName.'"><i class="fa fa-edit fa-lg ml-2" title="'.$renaM.'" data-toggle="tooltip"></i></a>';
        }
      
        if ($ficcmd_fma[4]) 
        {
            $tabW = explode(' ',$extension_Edit_fma);
            $suffix = strtoLower(substr(strrchr( $obj->FieldName, '.' ), 1 ));
         
            if (in_array($suffix, $tabW))
            {
                $cmd_ibid .= '<a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=editfile&amp;att_name='.$obj->FieldName.'"><i class="fas fa-pencil-alt fa-lg ml-2" title="'.$editM.'" data-toggle="tooltip"></i></a>';
            }
        }
      
        if ($ficcmd_fma[5])
        {
            $cmd_ibid .= '<a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=movefile&amp;att_name='.$obj->FieldName.'"><i class="far fa-share-square fa-lg ml-2" title="'.$moveM.'" data-toggle="tooltip"></i></a>';
        }
      
        if ($ficcmd_fma[3])
        {
            $cmd_ibid .= '<a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=chmodfile&amp;att_name='.$obj->FieldName.'"><i class="fas fa-pencil-alt fa-lg ml-2" title="'.$chmoM.'" data-toggle="tooltip"></i><small>7..</small></a>';
        }
      
        if ($ficcmd_fma[2])
        {
            $cmd_ibid .= '<a href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.$cur_nav_encrypt.'&amp;op=removefile&amp;att_name='.$obj->FieldName.'"><i class="far fa-trash-alt fa-lg text-danger ml-2" title="'.$suppM.'" data-toggle="tooltip"></i></a>';
        }

        if ($cmd_ibid) 
        {
            $files .= '
            <td>'.$cmd_ibid.'</td>';
        }

        $files .= '
        </tr>';
    }
}

if (file_exists($infos_fma))
{
   $infos = language::aff_langue(join('', file($infos_fma)));
}

// Form
$upload_file = '
<form id="uploadfichier" enctype="multipart/form-data" method="post" action="modules.php" lang="'.language::language_iso(1, '', '').'">
    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
    <input type="hidden" name="browse" value="'.$browse.'" />
    <input type="hidden" name="op" value="upload" />
    <div class="form-group">
        <span class="help-block">'.fma_translate("Extensions autorisées : ").'<span class="text-success">'.$extension_fma.'</span></span>
        <div class="input-group mb-2 mr-sm-2">
            <div class="input-group-prepend" onclick="reset2($(\'#userfile\'),\'\');">
                <div class="input-group-text"><i class="fas fa-sync"></i></div>
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" name="userfile" id="userfile" />
                <label id="lab" class="custom-file-label" for="userfile">'.fma_translate("Sélectionner votre fichier").'</label>
            </div>
        </div>
    </div>
    <button class="btn btn-primary" type="submit" name="ok" >'.fma_translate("Ok").'</button>
</form>
<script type="text/javascript">
    //<![CDATA[
        $(".custom-file-input").on("change",function(){
            $(this).next(".custom-file-label").addClass("selected").html($(this).val().split(\'\\\\\').pop());
        });
        window.reset2 = function (e,f) {
            e.wrap("<form>").closest("form").get(0).reset();
            e.unwrap();
            event.preventDefault();
            $("#lab"+f).html("'.fma_translate("Sélectionner votre fichier").'")
        };
    //]]>
</script>';

$create_dir = '
<form method="post" action="modules.php">
    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
    <input type="hidden" name="browse" value="'.$browse.'" />
    <input type="hidden" name="op" value="createdir" />
    <div class="form-group">
        <input class="form-control" name="userdir" type="text" value="" />
    </div>
    <input class="btn btn-primary" type="submit" name="ok" value="'.fma_translate("Ok").'" />
</form>';

$create_file = '
<form method="post" action="modules.php">
    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
    <input type="hidden" name="browse" value="'.$browse.'" />
    <input type="hidden" name="op" value="createfile" />
    <div class="form-group">
        <input class="form-control" name="userfile" type="text" value="" />
    </div>
    <input class="btn btn-primary" type="submit" name="ok" value="'.fma_translate("Ok").'" />
</form>';

$search_file = '
<form method="post" action="modules.php">
    <input type="hidden" name="ModPath" value="'.$ModPath.'">
    <input type="hidden" name="ModStart" value="'.$ModStart.'">
    <input type="hidden" name="FmaRep" value="'.$FmaRep.'">
    <input type="hidden" name="browse" value="'.$browse.'">
    <input type="hidden" name="op" value="searchfile">
    <div class="form-group">
        <input class="form-control" name="filesearch" type="text" size="50" value="">
    </div>
    <input class="btn btn-primary" type="submit" name="ok" value="'.fma_translate("Ok").'">
</form>';

chdir("$racine_fma/");

// Génération de l'interface
$inclusion = false;
if (file_exists("themes/$Default_Theme/views/modules/f-manager/$theme_fma"))
{
   $inclusion = "themes/$Default_Theme/views/modules/f-manager/$theme_fma";
}
elseif (file_exists("themes/default/views/modules/f-manager/$theme_fma"))
{
   $inclusion = "themes/default/views/modules/f-manager/$theme_fma";
}
else 
{
   echo "views/modules/f-manager/$theme_fma manquant / not find !";
}

if ($inclusion) 
{
    $Xcontent = join('', file($inclusion));
   
    if($FmaRep == 'minisite-ges') 
    {
        if ($user) 
        {
            $userdata = explode(':', base64_decode($user));
            $Xcontent = str_replace('_home','<a class="nav-link" href="minisite.php?op='.$userdata[1].'" target="_blank"><i class="fa fa-desktop fa-lg"></i></a>', $Xcontent);
        }
    }
    else
    {
        $Xcontent = str_replace('_home','<a class="nav-link" href="index.php" target="_blank"><i class="fa fa-home fa-lg"></i></a>', $Xcontent);
    }
   
    $Xcontent = str_replace('_back', fmanager::extend_ascii($cur_nav_href_back), $Xcontent);
    $Xcontent = str_replace('_refresh', '<a class="nav-link" href="modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart.'&amp;FmaRep='.$FmaRep.'&amp;browse='.rawurlencode($browse).$urlext_fma.'"><span class="d-sm-none"><i class="fas fa-sync la-lg fa-spin"></i></span><span class="d-none d-sm-inline">'.fma_translate("Rafraîchir").'</span></a>', $Xcontent);

    //if ($dirsize_fma) 
    //{
        $Xcontent = str_replace('_size', $obj->ConvertSize($obj->GetDirSize($cur_nav)), $Xcontent);
    //}
    //else 
    //{
    //   $Xcontent=str_replace("_size",'-',$Xcontent);
    //}
   
    $Xcontent = str_replace('_nb_subdir', ($obj->Count("d")-$dir_minuscptr), $Xcontent);
   
    if(($obj->Count("d")-$dir_minuscptr) == 0)
    {
        $Xcontent = str_replace('_tabdirclassempty','collapse', $Xcontent);
    }
   
    $Xcontent = str_replace('_subdirs', $subdirs,$Xcontent);
    $Xcontent = str_replace('_nb_file', ($obj->Count("f")-$fic_minuscptr), $Xcontent);
    $Xcontent = str_replace('_files', $files, $Xcontent);

    if (isset($cmd))
    {
        $Xcontent = str_replace('_cmd', $cmd, $Xcontent);
    }
    else
    {
        $Xcontent = str_replace('_cmd', '', $Xcontent);
    }

    if ($dircmd_fma[0])
    {
        $Xcontent = str_replace('_cre_dir', $create_dir, $Xcontent);
    }
    else 
    {
        $Xcontent = str_replace('_classcredirno', 'collapse', $Xcontent);
        $Xcontent = str_replace('<div id="cre_dir">', '<div id="cre_dir" style="display: none;">', $Xcontent);
        $Xcontent = str_replace('_cre_dir', '', $Xcontent);
    }

    $Xcontent = str_replace('_del_dir', $remove_dir, $Xcontent);
    $Xcontent = str_replace('_ren_dir', $rename_dir, $Xcontent);
    $Xcontent = str_replace('_chm_dir', $chmod_dir, $Xcontent);

    if (isset($pict_dir))
    {
        $Xcontent = str_replace('_pic_dir', $pict_dir, $Xcontent);
    }
    else
    {
        $Xcontent = str_replace("_pic_dir", '', $Xcontent);
    }

    if ($ficcmd_fma[0]) 
    {
        $Xcontent = str_replace('_upl_file', $upload_file, $Xcontent);
        $Xcontent = str_replace('_cre_file', $create_file, $Xcontent);
    } 
    else 
    {
        $Xcontent = str_replace('_classuplfileno','collapse', $Xcontent);
        $Xcontent = str_replace('<div id="upl_file">', '<div id="upl_file" style="display: none;">', $Xcontent);
        $Xcontent = str_replace('_classcrefileno', 'collapse', $Xcontent);
        $Xcontent = str_replace('<div id="cre_file">', '<div id="cre_file" style="display: none;">', $Xcontent);
        $Xcontent = str_replace('_upl_file','',$Xcontent);
        $Xcontent = str_replace('_cre_file','',$Xcontent);
    }

    $Xcontent = str_replace('_sea_file', $search_file, $Xcontent);
    $Xcontent = str_replace('_del_file', $remove_file, $Xcontent);
    $Xcontent = str_replace('_chm_file', $chmod_file, $Xcontent);
    $Xcontent = str_replace('_ren_file', $rename_file, $Xcontent);
    $Xcontent = str_replace('_mov_file', $move_file, $Xcontent);

    if (isset($Err))
    {
        $Xcontent = str_replace('_error', '<div class="alert alert-danger alert-dismissible fade show" role="alert">'.$Err.'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>', $Xcontent);
    }
    else
    {
        $Xcontent = str_replace('_error', '', $Xcontent);
    }

    if (isset($infos))
    {
        $Xcontent = str_replace('_infos', $infos, $Xcontent);
    }
    else
    {
        $Xcontent = str_replace('_infos', '', $Xcontent);
    }
   
    if ($dirpres_fma[5]) 
    {
        if ($uniq_fma)
        {
            $Xcontent = str_replace('_picM', '<a class="nav-link" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=pic-manager&amp;FmaRep='.$FmaRep.'&amp;browse='.rawurlencode($browse).'"><span class="d-sm-none"><i class="fa fa-image fa-lg" title="'.fma_translate("Images manager").'" data-toggle="tooltip" data-placement="bottom"></i></span><span class="d-none d-sm-inline">'.fma_translate("Images manager").'</span></a>', $Xcontent);
        }
        else
        {
            $Xcontent = str_replace('_picM', '<a class="nav-link" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=pic-manager&amp;FmaRep='.$FmaRep.'&amp;browse='.rawurlencode($browse).'" target="_blank"><span class="d-sm-none"><i class="fa fa-image fa-lg"></i></span><span class="d-none d-sm-inline">'.fma_translate("Images manager").'</span></a>', $Xcontent);
        }
    } 
    else
    {
        $Xcontent = str_replace('_picM', '', $Xcontent);
    }

    $Xcontent = str_replace('_quota', $obj->ConvertSize($sizeofDir+$sizeofFic).' || '.fma_translate("Taille maximum d'un fichier : ").$obj->ConvertSize($max_size), $Xcontent);

    if (!$NPDS_fma) 
    {
        // utilisation de pages.php
        settype($PAGES, 'array');
      
        list($theme, $skin, $tmp_theme) = theme::getUsetOrDefaultThemeAndSkin();

        require_once("themes/".$tmp_theme."/pages.php");
      
        $Titlesitename = language::aff_langue($PAGES["modules.php?ModPath=$ModPath&ModStart=$ModStart*"]['title']);

        include("config/meta.php");
      
        echo '
        <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" href="assets/shared/font-awesome/css/all.min.css" />
        <link rel="stylesheet" id="fw_css" href="themes/_skins/'.$skin.'/bootstrap.min.css" />
        <link rel="stylesheet" href="assets/shared/bootstrap-table/dist/bootstrap-table.css" />
        <link rel="stylesheet" id="fw_css_extra" href="themes/_skins/'.$skin.'/extra.css" />
        <link rel="stylesheet" href="'.$css_fma.'" title="default" type="text/css" media="all" />';

        global $tiny_mce;
        if ($tiny_mce)
        {
            $tiny_mce_init = $PAGES["modules.php?ModPath=$ModPath&ModStart=$ModStart*"]['TinyMce'];
            if ($tiny_mce_init) 
            {
                $tiny_mce_theme = $PAGES["modules.php?ModPath=$ModPath&ModStart=$ModStart*"]['TinyMce-theme'];
                echo tiny::aff_editeur("tiny_mce", "begin");
            }
        }

        echo '
        <script type="text/javascript" src="assets/js/jquery.min.js"></script>
        </head>
        <body class="p-3">';
    } 
    else
    {
        include ("header.php");
    }

    // Head banner de présentation fmanager
    if (file_exists("themes/$Default_Theme/views/modules/f-manager/head.html")) 
    {
        echo "\n";
        include ("themes/$Default_Theme/views/modules/f-manager/head.html");
        echo "\n";
    }
    else if (file_exists("themes/default/views/modules/f-manager/head.html")) 
    {
        echo "\n";
        include ("themes/default/views/modules/f-manager/head.html");
        echo "\n";
    }

    ?>
    <script type="text/javascript">
        //<![CDATA[
            function previewImage(fileInfo) 
            {
                var filename = '';
                filename = fileInfo;

                //create the popup
                popup = window.open('', 'imagePreview', 'width=600,height=450,left=100,top=75,screenX=100,screenY=75,scrollbars,location,menubar,status,toolbar,resizable=1');

                //start writing in the html code
                popup.document.writeln("<html><body style='background-color: #FFFFFF;'>");
                popup.document.writeln("<img src='" + filename + "'></body></html>");
            }
        //]]>
    </script>
    <?php

    // l'insertion de la FORM d'édition doit intervenir à la fin du calcul de l'interface ... sinon on modifie le contenu
    // Meta_lang n'est pas chargé car trop lent pour une utilisation sur de gros répertoires
    $Xcontent = language::aff_langue($Xcontent);
    $Xconten = str_replace('_edt_file', $edit_file, $Xcontent);
    echo $Xcontent;

    // Foot banner de présentation fmanager
    if (file_exists("themes/$Default_Theme/views/modules/f-manager/foot.html")) 
    {
        echo "\n";
        include ("themes/$Default_Theme/views/modules/f-manager/foot.html");
        echo "\n";
    }
    else if (file_exists("themes/default/views/modules/f-manager/foot.html")) 
    {
        echo "\n";
        include ("themes/default/views/modules/f-manager/foot.html");
        echo "\n";
    }

    if (!$NPDS_fma) 
    {
        echo '
                </body>
            </html>';
        
        if ($tiny_mce)
        {
            if ($tiny_mce_init)
            {
                echo tiny::aff_editeur("tiny_mce", "end");
            }
        }
    } 
    else
    {
        include ("footer.php");
    }
}
