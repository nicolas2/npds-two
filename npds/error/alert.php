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
namespace npds\error;

use npds\logs\logs;
use npds\cookie\cookie;


/*
 * alert
 */
class alert {


    /**
     * [Admin_alert description]
     * @param [type] $motif [description]
     */
    function admin($motif) 
    {
        global $admin;

        cookie::destroy('admin');
        unset($admin);

        logs::Ecr_Log('security', 'admin/auth.inc.php/Admin_alert : '.$motif, '');
       
        $Titlesitename = 'NPDS';
       
        if (file_exists("config/meta.php"))
        {
            include("config/meta.php");
        }
       
        echo '
            </head>
            <body>
                <br /><br /><br />
                <p style="font-size: 24px; font-family: Tahoma, Arial; color: red; text-align:center;"><strong>.: '.translate("Votre adresse Ip est enregistrée").' :.</strong></p>
            </body>
            </html>';
        die();
    }

}
