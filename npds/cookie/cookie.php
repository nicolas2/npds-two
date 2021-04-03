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