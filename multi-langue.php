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
use npds\cookie\cookie;


// Multi-language
$local_path = '';

settype($user_language, 'string');

if (isset($module_mark))
{
    $local_path = '../../';
}

if (file_exists($local_path.'storage/cache/language.php'))
{
    include ($local_path.'storage/cache/language.php');
}
else
{
    include ($local_path.'admin/manuels/list.php');
}

if (isset($choice_user_language)) 
{
    if ($choice_user_language != '') 
    {
        if ($user_cook_duration <= 0) 
        {
            $user_cook_duration = 1;
        }

        $timeX = time()+(3600*$user_cook_duration);
      
        if ((stristr($languageslist, $choice_user_language)) and ($choice_user_language != ' ')) 
        {
            cookie::set('user_language', $choice_user_language, $timeX);
            $user_language = $choice_user_language;
        }
    }
}

if ($multi_langue) 
{
    if (($user_language != '') and ($user_language != " ")) 
    {
        $tmpML = stristr($languageslist, $user_language);
        $tmpML = explode(' ', $tmpML);
      
        if ($tmpML[0])
        {
            $language = $tmpML[0];
        }
    }
}
// Multi-language
