<!--
/**
 * Npds Two
 *
 * Based on NPDS Copyright (c) 2002-2020 by Philippe Brunier
 * 
 * @author Nicolas2
 * @version 1.0
 * @date 02/04/2021
 */

/* the url of the web site where the push module is
 * + /push.php - for ex : "http://www.npds.org/modules/push/push.php"
 * var url = "http://www.npds.org/modules/push/push.php";
 */
var url = "http://www.npds.org/modules/push/push.php";

/* options : value : 1=true / 0=False
 * ordering => News,Faq,Poll,Members,Links
 * var options="11111";
 */
var options = "11111";

function getCookie(Name) 
{
    var offset;
    
    var end;
    
    var search = Name + "=";
    
    if (document.cookie.length > 0) 
    {
        offset = document.cookie.indexOf(search);

        if (offset != -1) 
        {
            offset += search.length;
            end = document.cookie.indexOf(";", offset);
            
            if (end == -1)
            {
                end = document.cookie.length;
            }
            
            return unescape(document.cookie.substring(offset, end));
        }
    }
}

function setCookie(name, value, expire) 
{
    document.cookie = name + "=" + escape(value)+((expire == null) ? "" : ("; expires=" + expire.toGMTString()));
}

function expiration(temp) 
{
    var today = new Date();
    
    var expires = new Date();
    
    expires.setTime(today.getTime() + temp);
    
    return (expires);
}

function register(name, Xvalue) 
{
    setCookie(name, Xvalue, expiration(5000));
    history.go(0);
}

var Xtmp = getCookie("npds-push");

if (Xtmp) 
{
    setCookie("npds-push", Xtmp, expiration(5));
    document.write('<script src="'+url+"?options="+options+"&"+Xtmp+'"></script>');
} 
else 
{
    document.write('<script src="'+url+"?options="+options+'"></script>');
}
//-->