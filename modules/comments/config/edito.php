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


// Don't remove this line !
global $C_start;

// $file_name : racine du nom de ce fichier  (article, pollBoth, ...)
$file_name = 'edito';

// $forum : permet d'allouer un numéro de forum pour chaque 'type de commentaires' (article, sondage, ...) - le numéro de forum doit impérativement être NEGATIF
$forum = -99;

// $topic : permet d'allouer un numéro UNIQUE pour chaque publication sur laquelle un commentaire peut être réalisé (article numéro X, sondage numéro Y, ...)
$topic = 1;

// $url_ret : URL de retour lorsque la soumission du commentaire est OK
$url_ret = 'index.php?op=edito';

// $formulaire : Formulaire SFORM si vous souhaitez avoir une grille de saisie en lieu et place de l'interface standard de saisie - sinon ""
$formulair = '';

// $comments_per_page : Nombre de commentaire sur chaque page
$comments_per_page = 2;

// Mise à jour de champ d'une table externe à la table des commentaires
// $req_add = opération à effectuer lorsque je rajoute un commentaire
// $req_del = opération à effectuer lorsque je cache un commentaire
// $req_raz = opération à effectuer lorsque je supprime tous les commentaires
$comments_req_add = '';
$comments_req_del = '';
$comments_req_raz = '';
