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
namespace npds\utility;

use npds\error\access;
use npds\security\ip;
use npds\utility\crypt;


/*
 * spam
 */
class spam {


    /**
     * [boot description]
     * @return [type] [description]
     */
    public static function boot()
    {
        // First of all : Spam from IP 
        // |5 indicate that the same IP has passed 6 times with status 
        // KO in the anti_spambot function
        if (file_exists("storage/logs/spam.log")) 
        {
            $tab_spam = str_replace("\r\n", "", file("storage/logs/spam.log"));
        }

        if (is_array($tab_spam)) 
        {
            $ip = urldecode(ip::get());
          
            if (strstr($ip, ':')) 
            {
                $range = '6';
            }
            else 
            {
                $range = '4';
            }
          
            if (in_array($ip."|5", $tab_spam)) 
            {
                access::denied();
            }
          
            // nous pouvons bannir une plage d'adresse ip en V4 
            // dans l'admin IPban sous forme x.x.%|5 ou x.x.x.%|5
            if($range == '4') 
            {
                $ipv4 = explode('.', $ip);
             
                if (in_array($ipv4[0].'.'.$ipv4[1].'.%|5', $tab_spam)) 
                {
                    access::denied();
                }
             
                if (in_array($ipv4[0].'.'.$ipv4[1].'.'.$ipv4[2].'.%|5', $tab_spam)) 
                {
                    access::denied();
                }
            }
          
            // nous pouvons bannir une plage d'adresse ip en V6 
            // dans l'admin IPban sous forme x:x:%|5 ou x:x:x:%|5
            if($range == '6') 
            {
                $ipv6 = explode(':', $ip);
             
                if (in_array($ipv6[0].':'.$ipv6[1].':%|5', $tab_spam)) 
                {
                    access::denied();
                }
             
                if (in_array($ipv6[0].':'.$ipv6[1].':'.$ipv6[2].':%|5', $tab_spam)) 
                {
                    access::denied();
                }
            }
        }        
    }
  
    /**
     * valide le champ $asb_question avec la valeur de $asb_reponse 
     * (anti-spambot) et filtre le contenu de $message si nécessaire
     * @param [type] $asb_question [description]
     * @param [type] $asb_reponse  [description]
     * @param string $message      [description]
     */
    public static function R_spambot($asb_question, $asb_reponse, $message='') 
    {
        global $REQUEST_METHOD;
        
        if ($REQUEST_METHOD == "POST") 
        {
            $user = user();

            if ($user == '') 
            {
                if (($asb_reponse != '') 
                    and (is_numeric($asb_reponse)) 
                    and (strlen($asb_reponse) <=2 )) 
                {
                    $ibid = crypt::decrypt($asb_question);
                    $ibid = explode(',', $ibid);
                    $result = "\$arg=($ibid[0]);";
                    
                    // submit intervient en moins de 5 secondes (trop vite) 
                    // ou plus de 30 minutes (trop long)
                    $temp = time()-$ibid[1];
                    
                    if (($temp < 1800) and ($temp > 5)) 
                    {
                        eval($result);
                    } 
                    else 
                    {
                        $arg = uniqid(mt_rand());
                    }
                } 
                else 
                {
                    $arg = uniqid(mt_rand());
                }
                 
                if ($arg == $asb_reponse) 
                {
                    // plus de 2 http:// dans le texte
                    preg_match_all('#http://#', $message, $regs);
                    
                    if (count($regs[0]) > 2) 
                    {
                        static::L_spambot('', "false");
                        return false;
                    } 
                    else 
                    {
                        static::L_spambot('', "true");
                        return true;
                    }
                } 
                else 
                {
                    static::L_spambot('', "false");
                    return false;
                }
            } 
            else 
            {
                static::L_spambot('', "true");
                return true;
            }
        } 
        else 
        {
            static::L_spambot('', "false");
            return false;
        }
    }

    /**
     * Log spambot activity : 
     * $ip="" => getip of the current user OR $ip="x.x.x.x" 
     * $status = Op to do : 
     * true  => not log or suppress log - 
     * false => log+1 - 
     * ban   => Ban an IP 
     * @param [type] $ip     [description]
     * @param [type] $status [description]
     */
    public static function L_spambot($ip, $status) 
    {
        $cpt_sup = 0;
        $maj_fic = false;
        
        if ($ip == '')
        {
            $ip = ip::get();
        }

        $ip = urldecode($ip);
           
        if (file_exists("storage/logs/spam.log")) 
        {
            $tab_spam = str_replace("\r\n", '', file("storage/logs/spam.log"));
            
            if (in_array($ip.'|1', $tab_spam))
            {
                $cpt_sup = 2;
            }

            if (in_array($ip.'|2', $tab_spam))
            {
                $cpt_sup = 3;
            }

            if (in_array($ip.'|3', $tab_spam))
            {
                $cpt_sup = 4;
            }

            if (in_array($ip.'|4', $tab_spam))
            {
                $cpt_sup = 5;
            }
        }
        
        if ($cpt_sup) 
        {
            if ($status == "false") 
            {
                $tab_spam[array_search($ip.'|'.($cpt_sup-1), $tab_spam)] = $ip.'|'.$cpt_sup;
            } 
            elseif ($status == "ban") 
            {
                $tab_spam[array_search($ip.'|'.($cpt_sup-1), $tab_spam)] = $ip.'|5';
            } 
            else 
            {
                $tab_spam[array_search($ip.'|'.($cpt_sup-1), $tab_spam)] = '';
            }
            
            $maj_fic = true;
        } 
        else 
        {
            if ($status == "false") 
            {
                $tab_spam[] = $ip.'|1';
                $maj_fic = true;
            } 
            else if ($status == 'ban') 
            {
                if (!in_array($ip.'|5', $tab_spam)) 
                {
                    $tab_spam[] = $ip.'|5';
                    $maj_fic = true;
                }
            }
        }

        if ($maj_fic) 
        {
            $file = fopen("storage/logs/spam.log", "w");
            
            foreach($tab_spam as $key => $val) 
            {
                if ($val)
                {
                    fwrite($file, $val."\r\n");
                }
            }
            fclose($file);
        }
    }

    /**
     * forge un champ de formulaire 
     * (champ de saisie : $asb_reponse champ hidden : asb_question) 
     * permettant de déployer une fonction anti-spambot
     */
    public static function Q_spambot() 
    {
        $asb_question = array (
            '4 - (3 / 1)'   => 1,
            '7 - 5 - 0'     => 2,
            '2 + (1 / 1)'   => 3,
            '2 + (1 + 1)'   => 4,
            '3 + (0) + 2'   => 5,
            '3 + (9 / 3)'   => 6,
            '4 + 3 - 0'     => 7,
            '6 + (0) + 2'   => 8,
            '8 + (5 - 4)'   => 9,
            '0 + (6 + 4)'   => 10,
            '(5 * 2) + 1'   => 11,
            '6 + (3 + 3)'   => 12,
            '1 + (6 * 2)'   => 13,
            '(8 / 1) + 6 '  => 14,
            '6 + (5 + 4)'   => 15,
            '8 + (4 * 2)'   => 16,
            '1 + (8 * 2)'   => 17,
            '9 + (3 + 6)'   => 18,
            '(7 * 2) + 5'   => 19,
            '(8 * 3) - 4'   => 20,
            '7 + (2 * 7)'   => 21,
            '9 + 5 + 8'     => 22,
            '(5 * 4) + 3'   => 23,
            '0 + (8 * 3)'   => 24,
            '1 + (4 * 6)'   => 25,
            '(6 * 5) - 4'   => 26,
            '3 * (9 + 0)'   => 27,
            '4 + (3 * 8)'   => 28,
            '(6 * 4) + 5'   => 29,
            '0 + (6 * 5)'   => 30);
           
        // START ALEA
        mt_srand((double)microtime()*1000000);
        
        // choix de la question
        $asb_index = mt_rand(0, count($asb_question)-1);
        $ibid = array_keys($asb_question);
        $aff = $ibid[$asb_index];

        // translate
        $tab = explode(' ', str_replace(')', '', str_replace('(', '', $aff))); 
        $al1 = mt_rand(0, count($tab)-1);
        
        if (function_exists("imagepng"))
        {
            $aff = str_replace($tab[$al1], html_entity_decode(translate($tab[$al1]), ENT_QUOTES | ENT_HTML401, 'UTF-8'), $aff);
        }
        else
        {
            $aff = str_replace($tab[$al1], translate($tab[$al1]), $aff);
        }
              
        // mis en majuscule
        if ($asb_index%2)
        {
            $aff = ucfirst($aff);
        }
        
        // END ALEA

        // Captcha - si GD
        if (function_exists("imagepng"))
        {
            $aff = "<img src=\"getfile.php?att_id=".rawurlencode(crypt::encrypt($aff." = "))."&amp;apli=captcha\" style=\"vertical-align: middle;\" />";
        }
        else
        {
            $aff = "".static::anti_spam($aff." = ", 0)."";
        }

        $tmp = '';
        
        $user = user();

        if ($user == '') 
        {
            $tmp = '
            <div class="form-group row">
                <div class="col-sm-9 text-right">
                    <label class="form-control-label text-danger" for="asb_reponse">'.translate("Anti-Spam / Merci de répondre à la question suivante : ").'&nbsp;'.$aff.'</label>
                </div>
                <div class="col-sm-3 text-right">
                    <input class="form-control" type="text" id="asb_reponse" name="asb_reponse" maxlength="2" onclick="this.value" />
                    <input type="hidden" name="asb_question" value="'.crypt::encrypt($ibid[$asb_index].','.time()).'" />
                </div>
            </div>';
        } 
        else 
        {
            $tmp = '
            <input type="hidden" name="asb_question" value="" />
            <input type="hidden" name="asb_reponse" value="" />';
        }
        
        return $tmp;
    }

    /**
     * Encode une chaine en mélangeant caractères normaux, codes décimaux et hexa. 
     * Si $highcode == 1, utilise également le codage ASCII 
     * (compatible uniquement avec des mailto et des URL, pas pour affichage)
     * @param  [type]  $str      [description]
     * @param  integer $highcode [description]
     * @return [type]            [description]
     */
    public static function anti_spam($str, $highcode = 0) 
    {
        $str_encoded = "";  
        mt_srand((double)microtime()*1000000);
           
        for($i = 0; $i < strlen($str); $i++) 
        {
            if ($highcode == 1) 
            {
                $alea = mt_rand(1, 400);
                $modulo = 4;
            } 
            else 
            { 
                $alea = mt_rand(1, 300);
                $modulo = 3;
            }
            
            switch (($alea % $modulo)) 
            {
                case 0: 
                    $str_encoded .= $str[$i];
                break;

                case 1: 
                    $str_encoded .= "&#".ord($str[$i]).";";
                break;

                case 2: 
                    $str_encoded .= "&#x".bin2hex($str[$i]).";";
                break;

                case 3: 
                    $str_encoded .= "%".bin2hex($str[$i])."";
                break;

                default: 
                    $str_encoded = "Error";
                break;  
            }  
        }
        
        return $str_encoded;
    }

    /**
     * Permet l'utilisation de la fonction anti_spam via preg_replace
     * @param  [type] $ibid [description]
     * @return [type]       [description]
     */
    public static function preg_anti_spam($ibid) 
    {
        return ("<a href=\"mailto:".static::anti_spam($ibid, 1)."\" target=\"_blank\">".static::anti_spam($ibid, 0)."</a>");  
    } 

}
