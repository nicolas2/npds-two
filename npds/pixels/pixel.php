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
namespace npds\pixel;

use npds\views\theme;


/*
 * pixel
 */
class pixel {


    /**
     * [smilie description]
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    public static function smilie($message) 
    {
        // Tranforme un :-) en IMG
        global $theme;
        if ($ibid = theme::theme_image("forum/smilies/smilies.php")) 
        {
            $imgtmp = "themes/$theme/images/forum/smilies/";
        } 
        else 
        {
            $imgtmp = "assets/images/forum/smilies/";
        }
           
        if (file_exists($imgtmp."smilies.php")) 
        {
            include ($imgtmp."smilies.php");
            
            foreach ($smilies AS $tab_smilies) 
            {
                $suffix = strtoLower(substr(strrchr($tab_smilies[1], '.'),1));
                
                if (($suffix == "gif") or ($suffix == "png"))
                {
                    $message = str_replace($tab_smilies[0], "<img class='n-smil' src='".$imgtmp.$tab_smilies[1]."' />", $message);
                }
                else
                {
                    $message = str_replace($tab_smilies[0], $tab_smilies[1], $message);
                }
            }
        }
           
        if ($ibid = theme::theme_image("forum/smilies/more/smilies.php")) 
        {
            $imgtmp = "themes/$theme/images/forum/smilies/more/";
        } 
        else 
        {
            $imgtmp = "assets/images/forum/smilies/more/";
        }
           
        if (file_exists($imgtmp."smilies.php")) 
        {
            include ($imgtmp."smilies.php");

            foreach ($smilies AS $tab_smilies) 
            {
                $message = str_replace($tab_smilies[0], "<img class='n-smil' src='".$imgtmp.$tab_smilies[1]."' />", $message);
            }
        }
        
        return $message;
    }

    /**
     * [smile description]
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    public static function smile($message) 
    {
        // Tranforme une IMG en :-)
        global $theme;
           
        if ($ibid = theme::theme_image("forum/smilies/smilies.php")) 
        {
            $imgtmp = "themes/$theme/images/forum/smilies/";
        } 
        else 
        {
            $imgtmp = "assets/images/forum/smilies/";
        }
        
        if (file_exists($imgtmp."smilies.php")) 
        {
            include ($imgtmp."smilies.php");
            
            foreach ($smilies AS $tab_smilies) 
            {
                $message = str_replace("<img class='n-smil' src='".$imgtmp.$tab_smilies[1]."' />", $tab_smilies[0], $message);
            }
        }
           
        if ($ibid = theme::theme_image("forum/smilies/more/smilies.php")) 
        {
            $imgtmp = "themes/$theme/images/forum/smilies/more/";
        } 
        else 
        {
            $imgtmp = "assets/images/forum/smilies/more/";
        }
           
        if (file_exists($imgtmp."smilies.php")) 
        {
            include ($imgtmp."smilies.php");
            
            foreach ($smilies AS $tab_smilies) 
            {
                $message = str_replace("<img class='n-smil' src='".$imgtmp.$tab_smilies[1]."' />", $tab_smilies[0],  $message);
            }
        }
           
        return $message;
    }

    /**
     * Note a revoir 
     * ne fonctionne pas dans tous les contextes car on a pas la variable du theme !?
     * @return [type] [description]
     */
    public static function putitems_more() 
    {
        global $theme, $tmp_theme;
           
        if (stristr($_SERVER['PHP_SELF'], "more_emoticon.php")) 
        {
            $theme = $tmp_theme;
        }
           
        echo '<p align="center">'.translate("Cliquez pour insérer des émoticons dans votre message").'</p>';
        
        if ($ibid = theme::theme_image("forum/smilies/more/smilies.php"))
        {
            $imgtmp = "themes/$theme/images/forum/smilies/more/";
        } 
        else 
        {
            $imgtmp = "assets/images/forum/smilies/more/";
        }
           
        if (file_exists($imgtmp."smilies.php")) 
        {
            include ($imgtmp."smilies.php");
            
            echo '
            <div>';
            
            foreach ($smilies AS $tab_smilies) 
            {
                if ($tab_smilies[3]) 
                {
                    echo '
                    <span class ="d-inline-block m-2"><a href="#" onclick="javascript: DoAdd(\'true\',\'message\',\' '.$tab_smilies[0]. '\');"><img src="'.$imgtmp.$tab_smilies[1].'" width="32" height="32" alt="'.$tab_smilies[2];
                    
                    if ($tab_smilies[2]) 
                    {
                        echo ' => ';
                    }

                    echo $tab_smilies[0].'" /></a></span>';
                }
            }

            echo '
            </div>';
        }
    }

    /**
     * appel un popover pour la saisie des emoji (Unicode v13) 
     * dans un textarea défini par $targetarea
     * @param  [type] $targetarea [description]
     * @return [type]             [description]
     */
    public static function putitems($targetarea) 
    {
        global $theme;
          
        echo '
        <div title="'.translate("Cliquez pour insérer des emoji dans votre message").'" data-toggle="tooltip">
            <button class="btn btn-link pl-0" type="button" id="button-textOne" data-toggle="emojiPopper" data-target="#'.$targetarea.'">
                <i class="far fa-smile fa-lg" aria-hidden="true"></i>
            </button>
        </div>
        <script src="assets/shared/emojipopper/js/emojiPopper.min.js"></script>
        <script type="text/javascript">
            //<![CDATA[
                $(function () {
                    "use strict"
                    var emojiPopper = $(\'[data-toggle="emojiPopper"]\').emojiPopper({
                        url: "assets/shared/emojipopper/php/emojicontroller.php",
                        title:"Choisir un emoji"
                    });
                });
            //]]>
        </script>';
    }

    /**
     * [emotion_add description]
     * @param  [type] $image_subject [description]
     * @return [type]                [description]
     */
    public static function emotion_add($image_subject) 
    {
        global $theme;

        if ($ibid = theme::theme_image('forum/subject/index.html')) 
        {
            $imgtmp = "themes/$theme/images/forum/subject";
        } 
        else 
        {
            $imgtmp = 'assets/images/forum/subject';
        }
        
        $handle = opendir($imgtmp);
        
        while (false !== ($file = readdir($handle))) 
        {
            $filelist[] = $file;
        }
           
        asort($filelist);
        
        $temp = ''; 
        $j = 0;
           
        foreach($filelist as $key => $file ) 
        {
            if (!preg_match('#\.gif|\.jpg|\.png$#i', $file)) 
            {
                continue;
            }

            $temp .= '
            <div class="custom-control custom-radio custom-control-inline mb-3">';
              
            if ($image_subject != '') 
            {
                if ($file == $image_subject)
                {
                    $temp .= '
                    <input type="radio" value="'.$file.'" id="image_subject'.$j.'" name="image_subject" class="custom-control-input" checked="checked" />';
                }
                else
                {
                    $temp .= '
                    <input type="radio" value="'.$file.'" id="image_subject'.$j.'" name="image_subject" class="custom-control-input" />';
                }
            } 
            else 
            {
                $temp .= '
                <input type="radio" value="'.$file.'" id="image_subject'.$j.'" name="image_subject" class="custom-control-input" checked="checked" />';
                $image_subject = 'no image';
            }
              
            $temp .= '<label class="custom-control-label" for="image_subject'.$j.'" ><img class="n-smil d-block" src="'.$imgtmp.'/'.$file.'" alt="" /></label>
                 </div>';
            $j++;
        }

        return $temp;
    }

}
