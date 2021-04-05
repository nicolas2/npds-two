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
namespace npds\mailler;

use npds\views\theme;
use npds\utility\spam;


/*
 * mailler
 */
class mailler {


    #autodoc Mess_Check_Mail($username) : Appel la fonction d'affichage du groupe check_mail (theme principal de NPDS) sans class
    public static function Mess_Check_Mail($username) 
    {
           static::Mess_Check_Mail_interface($username, '');
    }

    /**
     * Affiche le groupe check_mail (theme principal de Npds Two)
     * @param [type] $username [description]
     * @param [type] $class    [description]
     */
    public static function Mess_Check_Mail_interface($username, $class) 
    {
        global $anonymous;
           
        if ($ibid = theme::theme_image("fle_b.gif")) 
        {
            $imgtmp = $ibid;
        } 
        else 
        {
            $imgtmp = false;
        }
           
        if ($class != "") 
        {
            $class = "class=\"$class\"";
        }
           
        if ($username == $anonymous) 
        {
            if ($imgtmp) 
            {
                echo "<img alt=\"\" src=\"$imgtmp\" align=\"center\" />$username - <a href=\"user.php\" $class>".translate("Votre compte")."</a>";
            } 
            else 
            {
                echo "[$username - <a href=\"user.php\" $class>".translate("Votre compte")."</a>]";
            }
        } 
        else 
        {
            if ($imgtmp) 
            {
                echo "<a href=\"user.php\" $class><img alt=\"\" src=\"$imgtmp\" align=\"center\" />".translate("Votre compte")."</a>&nbsp;".static::Mess_Check_Mail_Sub($username, $class);
            } 
            else 
            {
                echo "[<a href=\"user.php\" $class>".translate("Votre compte")."</a>&nbsp;&middot;&nbsp;".static::Mess_Check_Mail_Sub($username, $class)."]";
            }
        }
    }

    /**
     * Affiche le groupe check_mail (theme principal de Npds Two) 
     * SOUS-Fonction
     * @param [type] $username [description]
     * @param [type] $class    [description]
     */
    public static function Mess_Check_Mail_Sub($username, $class) 
    {
        global $NPDS_Prefix, $user;
        
        if ($username) 
        {
            $userdata = explode(':', base64_decode($user));
            
            $total_messages = sql_num_rows(sql_query("SELECT msg_id FROM ".$NPDS_Prefix."priv_msgs WHERE to_userid = '$userdata[0]' AND type_msg='0'"));
            
            $new_messages = sql_num_rows(sql_query("SELECT msg_id FROM ".$NPDS_Prefix."priv_msgs WHERE to_userid = '$userdata[0]' AND read_msg='0' AND type_msg='0'"));
            
            if ($total_messages > 0) 
            {
                if ($new_messages > 0) 
                {
                    $Xcheck_Nmail = $new_messages;
                } 
                else 
                {
                    $Xcheck_Nmail = '0';
                }

                $Xcheck_mail = $total_messages;
            } 
            else 
            {
                $Xcheck_Nmail = '0';
                $Xcheck_mail = '0';
            }
        }

        $YNmail = "$Xcheck_Nmail";
        $Ymail = "$Xcheck_mail";
        $Mel = "<a href=\"viewpmsg.php\" $class>Mel</a>";
        
        if ($Xcheck_Nmail > 0) 
        {
            $YNmail = "<a href=\"viewpmsg.php\" $class>$Xcheck_Nmail</a>";
            $Mel = 'Mel';
        }

        if ($Xcheck_mail >0) 
        {
            $Ymail = "<a href=\"viewpmsg.php\" $class>$Xcheck_mail</a>";
            $Mel = 'Mel';
        }

        return ("$Mel : $YNmail / $Ymail");
    }

    /**
     * Pour copier un subject+message dans un email ($to_userid)
     * @param  [type] $to_userid [description]
     * @param  [type] $sujet     [description]
     * @param  [type] $message   [description]
     * @return [type]            [description]
     */
    public static function copy_to_email($to_userid, $sujet, $message) 
    {
        global $NPDS_Prefix;
           
        $result = sql_query("SELECT email,send_email FROM ".$NPDS_Prefix."users WHERE uid='$to_userid'");
        list($mail, $avertir_mail) = sql_fetch_row($result);
           
        if (($mail) and ($avertir_mail == 1)) 
        {
            static::send_email($mail, $sujet, $message, '', true, 'html');
        }
    }
 
    /**
     * Pour envoyer un mail en texte ou html via les fonctions mail ou email
     * $mime = 'text', 'html' 'html-nobr'-(sans application de nl2br) ou 'mixed'-(piece jointe)
     * @param  [type]  $email    [description]
     * @param  [type]  $subject  [description]
     * @param  [type]  $message  [description]
     * @param  string  $from     [description]
     * @param  boolean $priority [description]
     * @param  string  $mime     [description]
     * @return [type]            [description]
     */
    public static function send_email($email, $subject, $message, $from="", $priority=false, $mime="text") 
    {
        global $mail_fonction, $adminmail;
        
        $advance = '';
        
        if ($priority) 
        {
            $advance = "X-Priority: 2\n";
        }

        if ($mime == 'mixed') 
        {
            // dans $message se trouve le nom du fichier à joindre 
            // (voir le module session-log pour un exemple)
            $boundary = '_'.md5(uniqid(mt_rand()));
            $attached_file = file_get_contents($message);
            $attached_file = chunk_split(base64_encode($attached_file));
            
            $message = "\n\n". "--" .$boundary . "\nContent-Type: application; name=\"".basename($message)."\" charset=".cur_charset."\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"".basename($message)."\"\r\n\n".$attached_file . "--" . $boundary . "--";
            $advance .= "MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        }

        if ($mime == 'text') 
        {
            $advance .= "Content-Type: text/plain; charset=".cur_charset."\n";
        }

        if (($mime == 'html') or ($mime == 'html-nobr')) 
        {
            $advance .= "Content-Type: text/html; charset=".cur_charset."\n";
            
            if ($mime != 'html-nobr')
            {
                $message = nl2br($message);
            }
            else
            {
                $mime = 'html';
            }
            
            $css = "<html>\n<head>\n<style type='text/css'>\nbody {\nbackground: #FFFFFF;\nfont-family: Tahoma, Calibri, Arial;\nfont-size: 1 rem;\ncolor: #000000;\n}\na, a:visited, a:link, a:hover {\ntext-decoration: underline;\n}\n</style>\n</head>\n<body>\n";
            $message = $css.$message."\n</body>\n</html>";
        }

        if (($mail_fonction == 1) or ($mail_fonction == "")) 
        {
            if ($from != '') 
            {
                $From_email = $from;
            } 
            else 
            {
                $From_email = $adminmail;
            }
            
            if (preg_match('#^[_\.0-9a-z-]+@[0-9a-z-\.]+\.+[a-z]{2,4}$#i', $From_email)) 
            {
                $result = mail($email, $subject, $message, "From: $From_email\nReturn-Path: $From_email\nX-Mailer: NPDS\n$advance");
            }
        } 
        else 
        {
            $pos = strpos($adminmail, '@');
            $tomail = substr($adminmail, 0, $pos);
            $result = email($tomail, $email, $subject, $message, $tomail, "Return-Path:\nX-Mailer: NPDS\n$advance");
        }   
        
        if ($result) 
        {
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /**
     * [fakedmail description]
     * @param  [type] $r [description]
     * @return [type]    [description]
     */
    public static function fakedmail($r) 
    { 
        return spam::preg_anti_spam($r[1]);
    }

    /**
     * Controle si le domaine existe et si il dispose d'un serveur de mail
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public static function checkdnsmail($email) 
    {
        $ibid = explode('@', $email);
        
        if(!checkdnsrr($ibid[1],'MX'))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    /**
     * utilisateur dans le fichier des mails incorrect true or false 
     * @param  [type] $utilisateur [description]
     * @return [type]              [description]
     */
    public static function isbadmailuser($utilisateur) 
    {
        $contents = '';
        $filename = "storage/users_private/usersbadmail.txt";
        $handle = fopen($filename, "r");
        
        if(filesize($filename) > 0)
        {
            $contents = fread($handle, filesize($filename));
        }
        fclose($handle);
        
        if(strstr($contents, '#'.$utilisateur.'|'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
