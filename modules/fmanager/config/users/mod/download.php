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

// cette variable fonctionne si $url_fma_modifier=true;
// $url_modifier permet de modifier le comportement du lien (a href ....) 
// se trouvant sur les fichiers affichÈs par FMA
$repw = str_replace($basedir_fma, "", $cur_nav);

if ($repw != "") 
{
   	if (substr($repw, 0, 1) == "/")
   	{
      	$repw = substr($repw, 1)."/".$obj->FieldName;
   	}
} 
else
{
   	$repw = $obj->FieldName;
}

$url_modifier = "\"#\" onclick=\"javascript:window.opener.document.adminForm.durl.value='".$repw."'; window.opener.document.adminForm.dfilename.value='".fmanager::extend_ascii($obj->FieldName)."';\"";

