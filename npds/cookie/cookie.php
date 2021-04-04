<?php
/**
 * 
 *
 * @author 
 * @version 
 * @date 
 */
namespace npds\cookie;


/**
 * cookie
 */
class cookie
{

    /**
     *
     */
    const FOURYEARS = 126144000;


    /**
     * Décode le cookie membre et vérifie certaines choses (password)
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public static function decode($user) 
    {
        global $NPDS_Prefix, $language;
        
        $stop = false;

        if (array_key_exists("user", $_GET)) 
        {
            if ($_GET['user'] != '') 
            { 
                $stop = true; 
                $user = "BAD-GET";
            }
        }
        else if (isset($HTTP_GET_VARS)) 
        {
            if (array_key_exists("user", $HTTP_GET_VARS) and ($HTTP_GET_VAR['user'] != '')) 
            { 
                $stop = true; 
                $user = "BAD-GET";
            }
        }

        if ($user) 
        {
            $cookie = explode(':', base64_decode($user));
            
            settype($cookie[0], "integer");
            
            if (trim($cookie[1]) != '') 
            {
                $result = sql_query("SELECT pass, user_langue FROM ".$NPDS_Prefix."users WHERE uname='$cookie[1]'");
                
                if (sql_num_rows($result) == 1) 
                {
                    list($pass, $user_langue) = sql_fetch_row($result);
                    if (($cookie[2] == md5($pass)) AND ($pass != '')) 
                    {
                        if ($language != $user_langue) 
                        {
                            sql_query("UPDATE ".$NPDS_Prefix."users SET user_langue='$language' WHERE uname='$cookie[1]'");
                        }
                        return $cookie;
                    } 
                    else 
                    {
                        $stop = true;
                    }
                } 
                else 
                {
                    $stop = true;
                }
            } 
            else 
            {
                $stop = true;
            }

            if ($stop) 
            {
                static::destroy('user');
                
                unset($user);
                unset($cookie);
                
                header("Location: index.php");
            }
        }
    }

    /**
     * Le cookie avec clé existe-t-il?
     *
     * @param string $key
     * @return bool
     */
    public static function exists($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * Définissez la valeur du cookie et les options: expiration, chemin et domaine.
     *
     * @param string $key
     * @param mixed $value
     * @param int $expiry
     * @param string $path
     * @param bool $domain
     * @return bool
     */
    public static function set($key, $value, $expiry = self::FOURYEARS, $path = '/', $domain = false)
    {
        $retval = false;

        $domain = static::getDomain($domain);

        if (! headers_sent()) 
        {
            if ($expiry === -1) 
            {
                // Durée de vie = 2030-01-01 00:00:00
                $expiry = 1893456000;  
            } 
            else if (is_numeric($expiry)) 
            {
                $expiry += time();
            } 
            else 
            {
                $expiry = strtotime($expiry);
            }

            $retval = @setcookie($key, $value, $expiry, $path, $domain);

            if ($retval) 
            {
                $_COOKIE[$key] = $value;
            }
        }

        return $retval;
    }

    /**
     * Obtenir la valeur du cookie
     *
     * @param $key
     * @param string $default
     * @return string|mixed
     */
    public static function get($key, $default = '')
    {
        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default);
    }

    /**
     * Obtenez le tableau de cookies
     * @return array
     */
    public static function display()
    {
        return $_COOKIE;
    }

    /**
     * Détruire l'entrée de cookie
     * @param string $key
     * @param string $path Optional
     * @param string $domain Optional
     */
    public static function destroy($key, $path = '/', $domain = false)
    { 
        $domain = static::getDomain($domain);

        if (! headers_sent()) 
        {
            unset($_COOKIE[$key]);

            // Pour supprimer le cookie, nous définissons son expiration quatre ans après.
            @setcookie($key, '', time() - self::FOURYEARS, $path, $domain);
        }
    }
    
    /**
     * Assurez-vous d'avoir un domaine valide.
     * @return 
     */
    private static function getDomain($domain)
    {
        return ($domain !== false) ? $domain : $_SERVER['HTTP_HOST'];
    }

}
