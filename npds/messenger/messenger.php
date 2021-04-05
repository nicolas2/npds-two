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
namespace npds\messenger;

use npds\security\hack;
use npds\error\error;
use npds\mailler\mailler;


/*
 * amessenger
 */
class amessenger {


	/**
	 * Ouvre la page d'envoi d'un MI (Message Interne)
	 * @param [type] $to_userid [description]
	 */
	public static function Form_instant_message($to_userid) 
	{
		include ("header.php");
		
		static::write_short_private_message(hack::remove($to_userid));
		
		include ("footer.php");
	}

	/**
	 * Insère un MI dans la base et le cas Èchéant envoi un mail
	 * @param  [type] $to_userid   [description]
	 * @param  [type] $image       [description]
	 * @param  [type] $subject     [description]
	 * @param  [type] $from_userid [description]
	 * @param  [type] $message     [description]
	 * @param  [type] $copie       [description]
	 * @return [type]              [description]
	 */
	public static function writeDB_private_message($to_userid, $image, $subject, $from_userid, $message, $copie) 
	{
		global $NPDS_Prefix;

		$res = sql_query("SELECT uid, user_langue FROM ".$NPDS_Prefix."users WHERE uname='$to_userid'");
		list($to_useridx, $user_languex) = sql_fetch_row($res);

		if ($to_useridx == '')
		{
		    error::forumerror('0016');
		}
		else 
		{
		    global $gmt;
		    
		    $time = date(translate("dateinternal"), time()+((integer)$gmt*3600));
		    
		    include_once("language/lang-multi.php");
		    
		    $subject = hack::remove($subject);
		    $message = str_replace("\n","<br />", $message);
		    $message = addslashes(hack::remove($message));
		    
		    $sql = "INSERT INTO ".$NPDS_Prefix."priv_msgs (msg_image, subject, from_userid, to_userid, msg_time, msg_text) ";
		    $sql .= "VALUES ('$image', '$subject', '$from_userid', '$to_useridx', '$time', '$message')";
		    
		    if (!$result = sql_query($sql))
		    {
		        error::forumerror('0020');
		    }
		    
		    if ($copie) 
		    {
		        $sql = "INSERT INTO ".$NPDS_Prefix."priv_msgs (msg_image, subject, from_userid, to_userid, msg_time, msg_text, type_msg, read_msg) ";
		        $sql .= "VALUES ('$image', '$subject', '$from_userid', '$to_useridx', '$time', '$message', '1', '1')";
		        
		        if (!$result = sql_query($sql))
		        {
		            error::forumerror('0020');
		        }
		    }

		    global $subscribe, $nuke_url, $sitename;
		    if ($subscribe) 
		    {
		        $sujet = translate_ml($user_languex, "Notification message privé.").'['.$from_userid.'] / '.$sitename;
		        
		        $message = $time.'<br />'.translate_ml($user_languex, "Bonjour").'<br />'.translate_ml($user_languex, "Vous avez un nouveau message.").'<br /><br /><b>'.$subject.'</b><br /><br /><a href="'.$nuke_url.'/viewpmsg.php">'.translate_ml($user_languex, "Cliquez ici pour lire votre nouveau message.").'</a><br />';
		        
		        include("signat.php");
		        
		        mailler::copy_to_email($to_useridx, $sujet, stripslashes($message));
		    }
		}
	}

	/**
	 * Formulaire d'&eacute;criture d'un MI
	 * @param  [type] $to_userid [description]
	 * @return [type]            [description]
	 */
	public static function write_short_private_message($to_userid) 
	{
		echo '
		<h2>'.translate("Message à un membre").'</h2>
		<h3><i class="fa fa-at mr-1"></i>'.$to_userid.'</h3>
		<form id="sh_priv_mess" action="powerpack.php" method="post">
		    <div class="form-group row">
		        <label class="col-form-label col-sm-12" for="subject" >'.translate("Sujet").'</label>
		        <div class="col-sm-12">
		            <input class="form-control" type="text" id="subject" name="subject" maxlength="100" />
		        </div>
		    </div>
		    <div class="form-group row">
		        <label class="col-form-label col-sm-12" for="message" >'.translate("Message").'</label>
		        <div class="col-sm-12">
		            <textarea class="form-control"  id="message" name="message" rows="10"></textarea>
		        </div>
		    </div>
		    <div class="form-group row">
		        <div class="col-sm-12">
		            <div class="custom-control custom-checkbox" >
		               <input class="custom-control-input" type="checkbox" id="copie" name="copie" />
		               <label class="custom-control-label" for="copie">'.translate("Conserver une copie").'</label>
		            </div>
		        </div>
		    </div>
		    <div class="form-group row">
		        <input type="hidden" name="to_userid" value="'.$to_userid.'" />
		        <input type="hidden" name="op" value="write_instant_message" />
		        <div class="col-sm-12">
		            <input class="btn btn-primary" type="submit" name="submit" value="'.translate("Valider").'" accesskey="s" />&nbsp;
		            <button class="btn btn-secondary" type="reset">'.translate("Annuler").'</button>
		        </div>
		    </div>
		</form>';
	}

}
