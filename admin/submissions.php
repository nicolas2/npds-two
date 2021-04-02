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

if (!stristr($_SERVER['PHP_SELF'], 'admin.php')) 
   Access_Error();

$f_meta_nom = 'submissions';
$f_titre = adm_translate('Article en attente de validation');

//==> controle droit
admindroits($aid, $f_meta_nom);
//<== controle droit

global $language;
$hlpfile = "admin/manuels/$language/submissions.html";

function submissions() 
{
   global $hlpfile, $NPDS_Prefix, $aid, $radminsuper, $f_meta_nom, $f_titre, $adminimg;
   
   $dummy = 0;
   
   include ("header.php");
   
   GraphicAdmin($hlpfile);
   adminhead($f_meta_nom, $f_titre, $adminimg);
   
   $result = sql_query("SELECT qid, subject, timestamp, topic, uname FROM ".$NPDS_Prefix."queue ORDER BY timestamp");
   
   if (sql_num_rows($result) == 0)
      echo '
   <hr />
   <h3>'.adm_translate("Pas de nouveaux Articles postés").'</h3>';
   else {
      echo '
   <hr />
   <h3>'.adm_translate("Nouveaux Articles postés").'<span class="badge badge-danger float-right">'.sql_num_rows($result).'</span></h3>
   <table id="tad_subm" data-toggle="table" data-striped="true" data-show-toggle="true" data-search="true" data-mobile-responsive="true" data-buttons-class="outline-secondary" data-icons="icons" data-icons-prefix="fa">
      <thead>
         <tr>
            <th data-halign="center"><i class="fa fa-user fa-lg"></i></th>
            <th data-sortable="true" data-sorter="htmlSorter" data-halign="center">'.adm_translate("Sujet").'</th>
            <th data-sortable="true" data-sorter="htmlSorter" data-halign="center">'.adm_translate("Titre").'</th>
            <th data-halign="center" data-align="right">'.adm_translate("Date").'</th>
            <th class="n-t-col-xs-2" data-halign="center" data-align="center">'.adm_translate("Fonctions").'</th>
         </tr>
      </thead>
      <tbody>';
      
      while (list($qid, $subject, $timestamp, $topic, $uname) = sql_fetch_row($result)) {
         if ($topic < 1) 
            $topic = 1;
         
         $affiche = false;
         $result2 = sql_query("SELECT topicadmin, topictext, topicimage FROM ".$NPDS_Prefix."topics WHERE topicid='$topic'");
         list ($topicadmin, $topictext, $topicimage) = sql_fetch_row($result2);
         
         if ($radminsuper)
            $affiche = true;
         else {
            $topicadminX = explode(',', $topicadmin);
            for ($i = 0; $i < count($topicadminX); $i++) {
               if (trim($topicadminX[$i]) == $aid) 
                  $affiche = true;
            }
         }

         echo '
         <tr>
            <td>'.userpopover($uname, '40').' '.$uname.'</td>
            <td>';
         
         if ($subject == '') 
            $subject = adm_translate("Aucun Sujet");
         
         $subject = aff_langue($subject);
         
         if ($affiche)
            echo '<img class=" " src="assets/images/topics/'.$topicimage.'" height="30" width="30" alt="avatar" />&nbsp;<a href="admin.php?op=topicedit&amp;topicid='.$topic.'" class="adm_tooltip">'.aff_langue($topictext).'</a></td>
             <td align="left"><a href="admin.php?op=DisplayStory&amp;qid='.$qid.'">'.ucfirst($subject).'</a></td>';
         else
            echo aff_langue($topictext).'</td>
            <td><i>'.ucfirst($subject).'</i></td>';
         
         echo '
             <td class="small">'.formatTimestamp($timestamp).'</td>';
         
         if ($affiche)
            echo '
             <td><a class="" href="admin.php?op=DisplayStory&amp;qid='.$qid.'"><i class="fa fa-edit fa-lg" title="'.adm_translate("Editer").'" data-toggle="tooltip" ></i></a><a class="text-danger" href="admin.php?op=DeleteStory&amp;qid='.$qid.'"><i class="far fa-trash-alt fa-lg ml-3" title="'.adm_translate("Effacer").'" data-toggle="tooltip" ></i></a></td>
         </tr>';
         else
            echo '
            <td>&nbsp;</td>
         </tr>';
         
         $dummy++;
      }

      if ($dummy < 1)
         echo '<h3>'.adm_translate("Pas de nouveaux Articles postés").'</h3>';
      else
         echo '
      </tbody>
   </table>';
   }

   adminfoot('', '', '', '');
}

switch ($op) {

   default:
      submissions();
   break;
}
?>