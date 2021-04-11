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
use modules\fmanager\support\fmanager;
use npds\logs\logs;
use npds\security\ip;
use npds\utility\crypt;
use npds\editeur\tiny;


settype($op, 'string');

switch ($op) 
{
    case 'upload':
        if ($ficcmd_fma[0]) 
        {
            if ($userfile != 'none') 
            {
                global $language;

                include_once("modules/upload/lang/upload.lang-$language.php");
                include_once("modules/upload/clsUpload.php");
                
                $upload = new Upload();
                $filename = trim($upload->getFileName("userfile"));
                
                if ($filename) 
                {
                    $upload->maxupload_size = $max_size;
                    $auto = fmanager::fma_filter('f', $filename, $obj->Extension);
                    
                    if ($auto[0]) 
                    {
                        if (!$upload->saveAs($auto[2], $base.'/', 'userfile', true))
                        {
                            $Err = $upload->errors;
                        }
                        else
                        {
                            logs::Ecr_Log('security', 'Upload File', $log_dir.'/'.$filename.' IP=>'.ip::get());
                        }
                    } 
                    else
                    {
                        $Err = $auto[1];
                    }
                }
            }
        }
    break;

    // Répertoires
    case 'createdir':
        if ($dircmd_fma[0]) 
        {
            $auto = fmanager::fma_filter('d', $userdir, $obj->Extension);
            if ($auto[0]) 
            {
                if (!$obj->Create('d',$base.'/'.$auto[2]))
                {
                    $Err = $obj->Errors;
                }
                else 
                {
                    logs::Ecr_Log('security', 'Create Directory', $log_dir.'/'.$userdir.' IP=>'.ip::get());
                    $fp = fopen($base.'/'.$auto[2].'/.htaccess', 'w');
                    fputs($fp, 'Deny from All');
                    fclose($fp);
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
    break;

    case 'renamedir':
        if ($dircmd_fma[1]) 
        {
            $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    $theme_fma = $themeC_fma;
                   
                    $cmd = '<span class="text-muted"><i class="fa fa-folder fa-2x mr-2 align-middle"></i></span>'.fma_translate("Renommer un répertoire");
                   
                    $rename_dir = '
                    <form method="post" action="modules.php">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <input type="hidden" name="op" value="renamedir-save" />
                    <div class="form-group">
                        <label><code> '.fmanager::extend_ascii($auto[2]).'</code></label>
                        <input class="form-control" type="text" name="renamefile" value="'.fmanager::extend_ascii($auto[2]).'" />
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" name="ok">'.fma_translate("Ok").'</button>
                    </div>
                    </form>';
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
    break;

   case 'renamedir-save':
        if ($dircmd_fma[1]) 
        {
            // origine
            $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                // destination
                $autoD = fmanager::fma_filter('d', $renamefile, $obj->Extension);
                
                if ($autoD[0]) 
                {
                    $auto[3] = crypt::decrypt($browse);
                   
                    if (!$obj->Rename($auto[3].'/'.$auto[2],$auto[3].'/'.$autoD[2])){
                        $Err = $obj->Errors;
                    }
                    else
                    {
                        logs::Ecr_Log('security', 'Rename Directory', $log_dir.'/'.$autoD[2].' IP=>'.ip::get());
                    }
                } 
                else
                {
                    $Err = $autoD[1];
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'removedir':
        if ($dircmd_fma[2]) 
        {
            $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
             
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    $theme_fma = $themeC_fma;
                   
                    $cmd = '<span class="text-muted"><i class="fa fa-folder fa-2x mr-2 text-danger align-middle"></i></span><span class="text-danger">'.fma_translate("Supprimer un répertoire").'</span>';
                   
                    $remove_dir = '
                    <form method="post" action="modules.php">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <input type="hidden" name="op" value="removedir-save" />
                    <div class="form-group">
                        '.fma_translate("Confirmez-vous la suppression de").' <code>'.fmanager::extend_ascii($auto[2]).'</code>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-danger" type="submit" name="ok">'.fma_translate("Ok").'</button>
                    </div>
                    </form>';
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'removedir-save':
        if ($dircmd_fma[2]) 
        {
            $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                @unlink($auto[3].'/'.$auto[2].'/.htaccess');
                @unlink($auto[3].'/'.$auto[2].'/pic-manager.txt');
                
                if (!$obj->RemoveDir($auto[3].'/'.$auto[2])) 
                {
                    $Err = $obj->Errors;
                } 
                else
                {
                    logs::Ecr_Log('security','Delete Directory', $log_dir.'/'.$auto[2].' IP=>'.ip::get());
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'chmoddir':
        if ($dircmd_fma[3]) 
        {
            $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
             
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    $theme_fma = $themeC_fma;
                   
                    $cmd = '<span class="text-muted"><i class="fa fa-folder fa-2x mr-2 align-middle"></i></span>'.fma_translate("Changer les droits d'un répertoire");
                   
                    $chmod_dir = '
                    <form method="post" action="modules.php">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <input type="hidden" name="op" value="chmoddir-save" />
                    <div class="form-group">
                        <label class="form-control-label" for="chmoddir" ><code>'.fmanager::extend_ascii($auto[2]).'</code></label>
                        <select class="custom-select form-control" id="chmoddir" name="chmoddir">
                            '.fmanager::chmod_pres($obj->GetPerms($auto[3].'/'.$auto[2]),'chmoddir').'
                    </div>
                    <div class="form-group">
                        <input class="btn btn-primary" type="submit" name="ok" value="'.fma_translate("Ok").'" />
                    </div>
                    </form>';
                }
            }
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'chmoddir-save':
        if ($dircmd_fma[3]) 
        {
            $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);

                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    settype($chmoddir, 'integer');
                   
                    if (!$obj->ChgPerms($auto[3].'/'.$auto[2],$chmoddir))
                    {
                        $Err = $obj->Errors;
                    }
                    else
                    {
                        logs::Ecr_Log('security', 'Chmod Directory', $log_dir.'/'.$auto[2].' IP=>'.ip::get());
                    }
                }
            }
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   // Fichiers
   case 'createfile':
        if ($ficcmd_fma[0]) 
        {
            $auto = fmanager::fma_filter('f', $userfile, $obj->Extension);
            if ($auto[0])
            {
                if (!$obj->Create('f',$base.'/'.$auto[2])){
                    $Err = $obj->Errors;
                }
                else
                {
                    logs::Ecr_Log('security', 'Create File', $log_dir.'/'.$userfile.' IP=>'.ip::get());
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'renamefile':
        if ($ficcmd_fma[1]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);

                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    $theme_fma = $themeC_fma;
                   
                    $cmd = '<span class="text-muted"><i class="fa fa-file fa-2x mr-2 align-middle"></i></span>'.fma_translate("Renommer un fichier");
                   
                    $rename_file = '
                    <form method="post" action="modules.php">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <input type="hidden" name="op" value="renamefile-save" />
                    <div class="form-group">
                        <label class="form-control-label" for="renamefile"><code>'.fmanager::extend_ascii($auto[2]).'</code></label>
                        <input class="form-control" type="text" size="60" id="renamefile" name="renamefile" value="'.fmanager::extend_ascii($auto[2]).'" />
                    </div>
                    <div class="form-group">
                        <input class="btn btn-primary" type="submit" name="ok" value="'.fma_translate("Ok").'" />
                    </div>
                    </form>';
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'renamefile-save':
        if ($ficcmd_fma[1]) 
        {
            // origine
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                // destination
                $autoD = fmanager::fma_filter('f', $renamefile, $obj->Extension);
                if ($autoD[0]) 
                {
                    $auto[3] = crypt::decrypt($browse);
                    if (!$obj->Rename($auto[3].'/'.$auto[2], $auto[3].'/'.$autoD[2]))
                    {
                        $Err = $obj->Errors;
                    }
                    else
                    {
                        logs::Ecr_Log('security', 'Rename File', $log_dir.'/'.$autoD[2].' IP=>'.ip::get());
                    }
                } 
                else
                {
                    $Err = $autoD[1];
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'movefile':
        if ($ficcmd_fma[1]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists($auto[3].'/'.$auto[2]))
                {
                    $theme_fma = $themeC_fma;
                   
                    $cmd = '<span class="text-muted"><i class="fa fa-file fa-2x mr-2 align-middle"></i></span>'.fma_translate("Déplacer / Copier un fichier");
                   
                    $move_file = '
                    <form method="post" action="modules.php">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <div class="form-group">
                        <select class="custom-select mr-2" name="op">
                            <option value="movefile-save" selected="selected"> '.fma_translate("Déplacer").'</option>
                            <option value="copyfile-save">'.fma_translate("Copier").'</option>
                        </select>
                        <code>'.fmanager::extend_ascii($auto[2]).'</code>
                    </div>
                    <div class="form-group">
                        <select class="custom-select form-control" name="movefile">';
                      
                    $move_file .= '
                            <option value="">/</option>';
                      
                    $arb = explode('|', $obj->GetDirArbo($basedir_fma));
                      
                    foreach($arb as $rep) 
                    {
                        if ($rep != '') 
                        {
                            $rep2 = str_replace($basedir_fma, '', $rep);
                            
                            if (fmanager::fma_autorise('d', basename($rep)))
                            {
                                $move_file .= '
                                <option value="'.$rep2.'">'.str_replace('/',' / ',$rep2).'</option>';
                            }
                        }
                    }

                    $move_file .= '
                            </select>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit" name="ok">'.fma_translate("Ok").'</button>
                        </div>
                    </form>';
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'movefile-save':
        if ($ficcmd_fma[1]) 
        {
            // origine
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                // destination
                $auto[3] = crypt::decrypt($browse);
                
                if (!$obj->Move($auto[3].'/'.$auto[2], $basedir_fma.$movefile."/".$auto[2]))
                {
                    $Err = $obj->Errors;
                }
                else{
                    logs::Ecr_Log('security','Move File', $log_dir.'/'.$auto[2].' TO '.$movefile.'/'.$auto[2].' IP=>'.ip::get());
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'copyfile-save':
        if ($ficcmd_fma[1]) 
        {
            // origine
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                // destination
                $auto[3] = crypt::decrypt($browse);
                
                if (!$obj->Copy($auto[3].'/'.$auto[2],$basedir_fma.$movefile.'/'.$auto[2]))
                {
                    $Err = $obj->Errors;
                }
                else
                {
                    logs::Ecr_Log('security', 'Copy File', $log_dir.'/'.$auto[2].' TO '.$movefile.'/'.$auto[2].' IP=>'.ip::get());
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'removefile':
        if ($ficcmd_fma[2]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists("$auto[3]/$auto[2]")) 
                {
                    $theme_fma = $themeC_fma;
                   
                    $cmd = '<span class="text-muted"><i class="fa fa-file fa-2x mr-2 text-danger align-middle"></i></span><span class="text-danger">'.fma_translate("Supprimer un fichier").'</span>';
                   
                    $remove_file = '
                    <form method="post" action="modules.php">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <input type="hidden" name="op" value="removefile-save" />
                    <div class="form-group lead">
                         '.fma_translate("Confirmez-vous la suppression de").' <code>'.fmanager::extend_ascii($auto[2]).'</code>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-danger" type="submit" name="ok">Ok</button>
                    </div>
                    </form>';
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'removefile-save':
        if ($ficcmd_fma[2]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                
                if (!$obj->Remove($auto[3].'/'.$auto[2]))
                {
                    $Err = $obj->Errors;
                }
                else
                {
                    logs::Ecr_Log('security', 'Delete File', $log_dir.'/'.$auto[2].' IP=>'.ip::get());
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'chmodfile':
        if ($ficcmd_fma[3]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    $theme_fma = $themeC_fma;
                   
                    $cmd = '<span class="text-muted"><i class="fa fa-folder fa-2x mr-2 align-middle"></i></span>'.fma_translate("Changer les droits d'un fichier").'</span>';
                   
                    $chmod_file = '
                    <form method="post" action="modules.php">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <input type="hidden" name="op" value="chmodfile-save" />
                    <div class="form-group">
                        <label class="form-control-label" for="chmodfile"><code>'.fmanager::extend_ascii($auto[2]).'</code></label>
                        <select class="custom-select form-control" id="chmodfile" name="chmodfile">
                            '.fmanager::chmod_pres($obj->GetPerms($auto[3].'/'.$auto[2]),"chmodfile").'
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" name="ok">'.fma_translate("Ok").'</button>
                    </div>
                    </form>';
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'chmodfile-save':
        if ($ficcmd_fma[3]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    settype($chmodfile,"integer");
                   
                    if (!$obj->ChgPerms($auto[3].'/'.$auto[2], $chmodfile))
                    {
                        $Err = $obj->Errors;
                    }
                    else{
                        logs::Ecr_Log('security', 'Chmod File', $log_dir.'/'.$auto[2].' IP=>'.ip::get());
                    }
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
        $op = '';
   break;

   case 'editfile':
        if ($ficcmd_fma[4]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
             
            if ($auto[0]) 
            {
                $auto[3] = crypt::decrypt($browse);
                if (file_exists($auto[3].'/'.$auto[2])) 
                {
                    $theme_fma = $themeC_fma;
                    $cmd = '<span class="text-muted"><i class="fa fa-file fa-2x mr-2 align-middle"></i></span>'.fma_translate("Editer un fichier").'</span>';
                    $fp = fopen($auto[3].'/'.$auto[2],'r');
                   
                    if (filesize($auto[3].'/'.$auto[2])>0)
                    {
                        $Fcontent = fread($fp,filesize($auto[3].'/'.$auto[2]));
                    }
                   
                    fclose($fp);
                   
                    $edit_file = '
                    <form method="post" action="modules.php" name="adminForm">
                    <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                    <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                    <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                    <input type="hidden" name="browse" value="'.$browse.'" />
                    <input type="hidden" name="att_name" value="'.$att_name.'" />
                    <input type="hidden" name="op" value="editfile-save" />
                    <div class="form-group row">
                        <label class="form-control-label col-12" for="editfile"><code>'.fmanager::extend_ascii($auto[2]).'</code></label>';
                   
                    settype($Fcontent, 'string');
                   
                    $edit_file .= '
                        <div class="col-12">
                            <textarea class="tin form-control" id="editfile" name="editfile" rows="18">'.htmlspecialchars($Fcontent, ENT_COMPAT|ENT_HTML401, cur_charset).'</textarea>
                        </div>
                    </div>';
                   
                    $tabW = explode(' ', $extension_Wysiwyg_fma);
                    $suffix = strtoLower(substr(strrchr( $att_name, '.' ), 1 ));
                   
                    if (in_array($suffix, $tabW))
                    {
                        $edit_file .= tiny::aff_editeur('editfile', 'true');
                    }
                   
                    $edit_file .= '
                        <button class="btn btn-primary" type="submit" name="ok">'.fma_translate("Ok").'</button>
                    </form>';
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
   break;

   case 'editfile-save':
        if ($ficcmd_fma[4]) 
        {
            $auto = fmanager::fma_filter('f', $att_name, $obj->Extension);
            if ($auto[0]) 
            {
                $tabW = explode(' ', $extension_Edit_fma);
                $suffix = strtoLower(substr(strrchr( $att_name, '.' ), 1 ));
                
                if (in_array($suffix,$tabW)) 
                {
                    $auto[3] = crypt::decrypt($browse);
                    if (file_exists($auto[3].'/'.$auto[2])) 
                    {
                        $fp = fopen($auto[3].'/'.$auto[2],'w');
                        fputs($fp, stripslashes($editfile));
                        fclose($fp);
                        logs::Ecr_Log('security','Edit File', $log_dir.'/'.$auto[2].' IP=>'.ip::get());
                    }
                } 
                else
                {
                    logs::Ecr_Log('security','Edit File forbidden', $log_dir.'/'.$auto[2].' IP=>'.ip::get());
                }
            } 
            else
            {
                $Err = $auto[1];
            }
        }
        $op = '';
   break;

   case 'pict':
        $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
        if ($auto[0]) 
        {
            $auto[3] = crypt::decrypt($browse);
            if (file_exists($auto[3].'/'.$auto[2])) 
            {
                $theme_fma = $themeC_fma;
                
                $cmd = '<span class="text-muted"><i class="fa fa-image fa-2x mr-2 align-middle"></i></span>'.fma_translate("Autoriser Pic-Manager").' >> '.$auto[2];
                
                $pict_dir = '
                <form method="post" action="modules.php">
                <input type="hidden" name="ModPath" value="'.$ModPath.'" />
                <input type="hidden" name="ModStart" value="'.$ModStart.'" />
                <input type="hidden" name="FmaRep" value="'.$FmaRep.'" />
                <input type="hidden" name="browse" value="'.$browse.'" />
                <input type="hidden" name="att_name" value="'.$att_name.'" />
                <input type="hidden" name="op" value="pict-save" />
                <div class="form-group">
                    <label class="form-control-label" for="maxthumb">'.fma_translate("Taille maximum (pixel) de l'imagette").'</label>';
                
                $fp = @file($auto[3].'/'.$auto[2].'/pic-manager.txt');
                
                // La première ligne du tableau est un commentaire
                settype($fp[1], 'integer');
                $Max_thumb = $fp[1];
                
                if ($Max_thumb == 0)
                {
                    $Max_thumb = 150;
                }
                
                settype($fp[2], 'integer');
                $refresh = $fp[2];
                
                if ($refresh == 0)
                {
                    $refresh = 3600;
                }
                
                $pict_dir .= '
                        <input class="form-control" type="number" id="maxthumb" name="maxthumb" size="4" value="'.$Max_thumb.'" />
                    </div>
                    <div class="form-group">
                        <label class="form-control-label" for="refresh">'.fma_translate("Temps de cache (en seconde) des imagettes").'</label> 
                        <input class="form-control" type="number" id="refresh" name="refresh" size="6" value="'.$refresh.'" />
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" name="ok">'.fma_translate("Ok").'</button>
                    </div>
                </form>';
            }
        } 
        else
        {
            $Err = $auto[1];
        }
   break;

   case 'pict-save':
        $auto = fmanager::fma_filter('d', $att_name, $obj->Extension);
        if ($auto[0]) 
        {
            $auto[3] = crypt::decrypt($browse);
            $fp = fopen($auto[3].'/'.$auto[2].'/pic-manager.txt', 'w');
            settype($maxthumb, 'integer');
            fputs($fp, "Enable and customize pic-manager / to remove pic-manager : just remove pic-manager.txt\n");
            fputs($fp, $maxthumb."\n");
            fputs($fp, $refresh."\n");
            fclose($fp);
            logs::Ecr_Log('security', 'Pic-Manager', $log_dir.'/'.$auto[2].' IP=>'.ip::get());
        }
        else
        {
            $Err = $auto[1];
        }
   break;

   case 'searchfile':
        $resp = $obj->SearchFile($base,$filesearch);
        if ($resp) 
        {
            $resp = explode('|', $resp);
            array_pop($resp);
            $cpt = 0;

            foreach($resp as $fic_resp) 
            {
                // on limite le retour au niveau immédiatement inférieur au rep courant
                $rep_niv1 = explode('/', str_replace($base, '', $fic_resp));
                if (count($rep_niv1) < 4) 
                {
                    $dir_search = basename(dirname($fic_resp));
                    $fic_search = basename($fic_resp);
                    if (fmanager::fma_autorise('d', $dir_search)) 
                    {
                        if (fmanager::fma_autorise('f', $fic_search)) 
                        {
                            $tab_search[$cpt][0] = $dir_search;
                            $tab_search[$cpt][1] = $fic_search;
                            $cpt++;
                        }
                    }
                }
            }
            $fic_minuscptr = 0;
        }
    break;
   
    default:
    break;
}
