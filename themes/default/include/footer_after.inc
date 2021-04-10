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
use npds\language\language;


// core required dont remove
echo '
    <script type="text/javascript" src="assets/shared/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="assets/shared/bootstrap-table/dist/bootstrap-table.min.js"></script>
    <script type="text/javascript" src="assets/shared/bootstrap-table/dist/locale/bootstrap-table-'.language::language_iso(1, "-", 1).'.min.js" async="async"></script>
    <script type="text/javascript" src="assets/shared/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.min.js" async="async"></script>
    <script type="text/javascript" src="assets/shared/bootstrap-table/dist/extensions/export/bootstrap-table-export.min.js" async="async"></script>
    <script type="text/javascript" src="assets/shared/tableExport.jquery.plugin-master/tableExport.js" async="async"></script>
    <script type="text/javascript" src="assets/js/js.cookie.js" async="async"></script>
    <script type="text/javascript" src="assets/js/jquery-ui.min.js" ></script>
    <script type="text/javascript" src="assets/js/bootbox.min.js" async="async"></script>
    <script type="text/javascript" src="assets/js/prism.js"></script>
    <script type="text/javascript">
        //<![CDATA[
            (tarteaucitron.job = tarteaucitron.job || []).push("vimeo");
            (tarteaucitron.job = tarteaucitron.job || []).push("youtube");
            (tarteaucitron.job = tarteaucitron.job || []).push("dailymotion");
            //tarteaucitron.user.gtagUa = "";
            tarteaucitron.user.gtagMore = function () { /* add here your optionnal gtag() */ };
            (tarteaucitron.job = tarteaucitron.job || []).push("gtag");
        //]]
    </script>';
// core

//If you want to show the Time used to generate each page uncomment those lines

$time_end = microtime(true);
$time = round($time_end - TIMESTART,3);
echo '
    <span class="small" id="build_time">'.language::aff_langue('[french]Temps :[/french][english]Time:[/english][german]Zeit in Sekunden[/german][spanish]Tiempo en segundos :[/spanish][chinese]&#x5728;&#x51E0;&#x79D2;&#x949F;&#x7684;&#x65F6;&#x95F4; :[/chinese] '.$time.' [french]seconde(s)[/french][english]second(s)[/english]').'</span>';

//

echo '
    </footer>
</div>
<script type="text/javascript" src="assets/js/npds_adapt.js"></script>';
