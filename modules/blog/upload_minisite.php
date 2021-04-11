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


/**
 * [win_upload description]
 * @param  [type] $typeL [description]
 * @return [type]        [description]
 */
function win_upload($typeL) 
{
    if ($typeL == 'win') 
    {
        echo "
        <script type=\"text/javascript\">
            //<![CDATA[
                window.open('modules.php?ModPath=f-manager&ModStart=f-manager&FmaRep=minisite-ges','wtmpMinisite', 'menubar=no,location=no,directories=no,status=no,copyhistory=no,toolbar=no,scrollbars=yes,resizable=yes, width=780, height=500');
            //]]>
        </script>";
    }
    else 
    {
        return ("'modules.php?ModPath=f-manager&ModStart=f-manager&FmaRep=minisite-ges','wtmpMinisite', 'menubar=no,location=no,directories=no,status=no,copyhistory=no,toolbar=no,scrollbars=yes,resizable=yes, width=780, height=500'");
    }
}
