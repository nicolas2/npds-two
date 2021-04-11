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
use npds\security\hack;


if ($uriBlocNote) 
{
    if ($typeBlocNote == "shared")
    {
        $bnid = md5($nomBlocNote);
    }
    elseif ($typeBlocNote == "context") 
    {
        if ($nomBlocNote == "\$username") 
        {
            global $cookie, $admin;
            $nomBlocNote = $cookie[1];
            $cur_admin = explode(':', base64_decode($admin));
         
            if ($cur_admin)
            {
                $nomBlocNote = $cur_admin[0];
            }
        }

        if (stristr(urldecode($uriBlocNote), "article.php"))
        {
            $bnid = md5($nomBlocNote.substr(urldecode($uriBlocNote), 0, strpos(urldecode($uriBlocNote),"&")));
        }
        else
        {
            $bnid = md5($nomBlocNote.urldecode($uriBlocNote));
        }
    } 
    else
    {
        $bnid = '';
    }

    if ($bnid) 
    {
        if ($supBlocNote == 'RAZ')
        {
            sql_query("DELETE FROM ".$NPDS_Prefix."blocnotes WHERE bnid='$bnid'");
        }
        else 
        {
            sql_query("LOCK TABLES ".$NPDS_Prefix."blocnotes WRITE");
            $result = sql_query("SELECT texte FROM ".$NPDS_Prefix."blocnotes WHERE bnid='$bnid'");
         
            if (sql_num_rows($result) > 0) 
            {
                if ($texteBlocNote != '')
                {
                    sql_query("UPDATE ".$NPDS_Prefix."blocnotes SET texte='".hack::remove($texteBlocNote)."' WHERE bnid='$bnid'");
                }
                else
                {
                    sql_query("DELETE FROM ".$NPDS_Prefix."blocnotes WHERE bnid='$bnid'");
                }
            } 
            else 
            {
                if ($texteBlocNote != '')
                {
                    sql_query("INSERT INTO ".$NPDS_Prefix."blocnotes (bnid, texte) VALUES ('$bnid', '".hack::remove($texteBlocNote)."')");
                }
            }
            sql_query("UNLOCK TABLES");
        }
    }
    header ("location: ".urldecode($uriBlocNote));
}
else
{
    header ("location: index.php");
}
