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


$sql_nbREQ = 0;
 
/**
 * Escape string
 * @param [type] $arr [description]
 */
function SQL_escape_string($arr) 
{
    if (function_exists("mysql_real_escape_string"))
    {
        @mysql_real_escape_string($arr);
    }
    elseif (function_exists("mysql_escape_string"))
    {
        @mysql_escape_string($arr);
    }

    return $arr;
}
 
/**
 * Connexion
 * @return [type] [description]
 */
function sql_connect() 
{
    global $mysql_p, $dbhost, $dbuname, $dbpass, $dbname, $dblink;

    if (($mysql_p) or (!isset($mysql_p)))
    {
        $dblink = @mysql_pconnect($dbhost, $dbuname, $dbpass);
    }
    else
    {
        $dblink = @mysql_connect($dbhost, $dbuname, $dbpass);
    }

    if (!$dblink)
    {
        return false;
    }
    else 
    {
        if (!@mysql_select_db($dbname, $dblink))
        {
            return false;
        }
        else
        {
            return $dblink;
        }
    }
}
 
/**
 * Erreur survenue
 * @return [type] [description]
 */
function sql_error() 
{
    return @mysql_error();
}
 
/**
 * Exécution de requête
 * @param  [type] $sql [description]
 * @return [type]      [description]
 */
function sql_query($sql) 
{
    global $sql_nbREQ;
    $sql_nbREQ++;
    
    if (!$query_id = @mysql_query(SQL_escape_string($sql)))
    {
        return false;
    }
    else
    {
        return $query_id;
    }
}

/**
 * 
 * @param  string $q_id [description]
 * @return [type]       [description]
 */
function sql_fetch_array($q_id='') 
{
    if (empty($q_id)) 
    {
        global $query_id;
        $q_id = $query_id;
    }

    return @mysql_fetch_array($q_id);
}
 
/**
 * Tableau Associatif du résultat
 * @param  string $q_id [description]
 * @return [type]       [description]
 */
function sql_fetch_assoc($q_id="") 
{
    if (empty($q_id)) 
    {
        global $query_id;
        $q_id = $query_id;
    }

    return @mysql_fetch_assoc($q_id);
}
 
/**
 * Tableau Numérique du résultat
 * @param  string $q_id [description]
 * @return [type]       [description]
 */
function sql_fetch_row($q_id="") 
{
    if (empty($q_id)) 
    {
        global $query_id;
        $q_id = $query_id;
    }

    return @mysql_fetch_row($q_id);
}
 
/**
 * Resultat sous forme d'objet
 * @param  string $q_id [description]
 * @return [type]       [description]
 */
function sql_fetch_object($q_id="") 
{
    if (empty($q_id)) 
    {
        global $query_id;
        $q_id = $query_id;
    }

    return @mysql_fetch_object($q_id);
}
 
/**
 * Nombre de lignes d'un résultat
 * @param  string $q_id [description]
 * @return [type]       [description]
 */
function sql_num_rows($q_id="") 
{
    if (empty($q_id)) 
    {
        global $query_id;
        $q_id = $query_id;
    }

    return @mysql_num_rows($q_id);
}
 
/**
 * Nombre de champs d'une requête
 * @param  string $q_id [description]
 * @return [type]       [description]
 */
function sql_num_fields($q_id="") 
{
    if (empty($q_id)) 
    {
        global $query_id;
        $q_id = $query_id;
    }

    return @mysql_num_fields($q_id);
}
 
/**
 * Nombre de lignes affectées par les requêtes de type INSERT, UPDATE et DELETE
 * @return [type] [description]
 */
function sql_affected_rows() 
{
    return @mysql_affected_rows();
}
 
/**
 * Le dernier identifiant généré par un champ de type AUTO_INCREMENT
 * @return [type] [description]
 */
function sql_last_id() 
{
    return @mysql_insert_id();
}
 
/**
 * Lister les tables
 * @param  string $dbnom [description]
 * @return [type]        [description]
 */
function sql_list_tables($dbnom="") 
{
    if (empty($dbnom)) 
    {
        global $dbname;
        $dbnom = $dbname;
    }

    return @sql_query("SHOW TABLES FROM $dbnom");
}
 
/**
 * Controle
 * @return [type] [description]
 */
function sql_select_db() 
{
    global $dblink, $dbname;

    if (!@mysql_select_db($dblink, $dbname))
    {
        return false;
    }
    else
    {
        return true;
    }
}
 
/**
 * Libère toute la mémoire et les ressources utilisées par la requête $query_id
 * @param  string $q_id [description]
 * @return [type]       [description]
 */
function sql_free_result($q_id="") 
{
    return @mysql_free_result($q_id);
}

/**
 * Ferme la connexion avec la Base de données
 * @param  [type] $dblink [description]
 * @return [type]         [description]
 */
function sql_close($dblink) 
{
    return @mysql_close($dblink);
}

/**
 * [prefix description]
 * @param  string $tablename [description]
 * @return [type]            [description]
 */
function prefix($tablename="") 
{   
    global $NPDS_Prefix;
    
    if( !empty($tablename)) 
    { 
        return $NPDS_Prefix . $tablename;
    } 
    else 
    { 
        return $NPDS_Prefix;
    }
}
