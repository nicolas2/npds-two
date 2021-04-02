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

   $Titlesitename = 'NPDS';
   
   if (file_exists("config/meta.php"))
   include ("config/meta.php");
   
   echo '
   </head>
   <body>
      <br />
      <br />
      <p style="text-align:center">
         <span style="font-size: 24px; font-family: Courier New, Courier, Liberation Mono, monospace; font-weight: bold; color: red;">
            Acc&egrave;s Refus&eacute; ! <br />
            Access Denied ! <br />
            Zugriff verweigert ! <br />
            &#x901A;&#x5165;&#x88AB;&#x5426;&#x8BA4; ! <br />
            Acceso denegado ! <br />
         </span>
         <br />
         <br />
         <span style="font-size: 18px; font-family: Courier New, Courier, Liberation Mono, monospace; font-weight: bold; color: black;">
            NPDS - Portal System
         </span>
      </p>
   </body>
</html>';
   die();
?>