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
namespace modules\geoloc\support;

use npds\language\language;


/**
 * box
 */
class box {

    /**
     * [$ModPath description]
     * @var string
     */
    private static $ModPath = 'geoloc';

      
    /**
     * [geoloc description]
     * @return [type] [description]
     */
    public static function geoloc()
    {
        $content = '';
        
        include('modules/'.static::$ModPath.'/config/geoloc.php');
        //$source_fond = '';
        
        switch ($cartyp_b) 
        {
            case 'OSM':
                $source_fond = 'new ol.source.OSM()';
            break;

            case 'sat-google':
                $source_fond = ' new ol.source.XYZ({url: "https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}",crossOrigin: "Anonymous", attributions: " &middot; <a href=\"https://www.google.at/permissions/geoguidelines/attr-guide.html\">Map data ©2015 Google</a>"})';
            break;
            case 'Road':
            case 'Aerial':
            case 'AerialWithLabels':
                $source_fond = 'new ol.source.BingMaps({key: "'.$api_key_bing.'",imagerySet: "'.$cartyp_b.'"})';
            break;

            case 'natural-earth-hypso-bathy': 
            case 'geography-class':
                $source_fond = ' new ol.source.TileJSON({url: "https://api.tiles.mapbox.com/v4/mapbox.'.$cartyp_b.'.json?access_token='.$api_key_mapbox.'"})';
            break;

            case 'terrain':
            case 'toner':
            case 'watercolor':
                $source_fond = 'new ol.source.Stamen({layer:"'.$cartyp_b.'"})';
            break;

            default:
                $source_fond = 'new ol.source.OSM()';
        }

        $content .='
        <div class="mb-2" id="map_bloc_ol" tabindex="200" style=" min-height:'.$h_b.'px;" lang="'.language::language_iso(1, 0, 0).'"></div>
            <script type="text/javascript">
                //<![CDATA[
                    if (!$("link[href=\'/assets/shared/ol/ol.css\']").length)
                        $("head link[rel=\'stylesheet\']").last().after("<link rel=\'stylesheet\' href=\'/assets/shared/ol/ol.css\' type=\'text/css\' media=\'screen\'>");
                    $("head link[rel=\'stylesheet\']").last().after("<link rel=\'stylesheet\' href=\'/modules/'.static::$ModPath.'/assets/css/geoloc_bloc.css\' type=\'text/css\' media=\'screen\'>");
                    if (typeof ol=="undefined")
                        $("head").append($("<script />").attr({"type":"text/javascript","src":"assets/shared/ol/ol.js"}));
                    $(function(){
                        var
                        georefUser_icon = new ol.style.Style({
                            image: new ol.style.Icon({
                                src: "'.$ch_img.$img_mbgb.'",
                                imgSize:['.$w_ico_b.','.$h_ico_b.']
                            })
                        }),
                        georeferencedUsers = new ol.layer.Vector({
                            source: new ol.source.Vector({
                                url: "modules/'.static::$ModPath.'/storage/json/user.json",
                                format: new ol.format.GeoJSON()
                            }),
                            style: georefUser_icon
                        }),
                        attribution = new ol.control.Attribution({collapsible: true}),
                        fullscreen = new ol.control.FullScreen();
                        var map = new ol.Map({
                            interactions: new ol.interaction.defaults({
                                constrainResolution: true, onFocusOnly: true
                            }),
                            controls: new ol.control.defaults({attribution: false}).extend([attribution, fullscreen]),
                            target: document.getElementById("map_bloc_ol"),
                            layers: [
                                new ol.layer.Tile({
                                    source: '.$source_fond.'
                                }),
                                georeferencedUsers
                            ],
                            view: new ol.View({
                                center: ol.proj.fromLonLat([0, 45]),
                                zoom: '.$z_b.'
                            })
                        });
                        function checkSize() {
                            var small = map.getSize()[0] < 600;
                            attribution.setCollapsible(small);
                            attribution.setCollapsed(small);
                        }
                        window.addEventListener("resize", checkSize);
                        checkSize();';

        $content .= file_get_contents('modules/'.static::$ModPath.'/assets/js/ol-dico.js');
        $content .= '
                        const targ = map.getTarget();
                        const lang = targ.lang;
                        for (var i in dic) {
                            if (dic.hasOwnProperty(i)) {
                                $("#map_bloc_ol "+dic[i].cla).prop("title", dic[i][lang]);
                            }
                        }
                        fullscreen.on("enterfullscreen",function(){
                            $(dic.olfullscreentrue.cla).attr("data-original-title", dic["olfullscreentrue"][lang]);
                        })
                        fullscreen.on("leavefullscreen",function(){
                            $(dic.olfullscreenfalse.cla).attr("data-original-title", dic["olfullscreenfalse"][lang]);
                        })
                        $("#map_bloc_ol .ol-zoom-in, #map_bloc_ol .ol-zoom-out").tooltip({placement: "right", container: "#map_bloc_ol",});
                        $("#map_bloc_ol .ol-full-screen-false, #map_bloc_ol .ol-rotate-reset, #map_bloc_ol .ol-attribution button[title]").tooltip({placement: "left", container: "#map_bloc_ol",});
                    });
                //]]>
            </script>';

        $content .= '<div class="mt-1"><a href="modules.php?ModPath='.static::$ModPath.'&amp;ModStart=geoloc"><i class="fa fa-globe fa-lg mr-1"></i>[french]Carte[/french][english]Map[/english][chinese]&#x5730;&#x56FE;[/chinese][spanish]Mapa[/spanish][german]Karte[/german]</a>';

        if(admin())
        {
            $content .= '<a href="admin.php?op=Extend-Admin-SubModule&amp;ModPath='.static::$ModPath.'&amp;ModStart=admin/geoloc_set"><i class="fa fa-cogs fa-lg ml-1"></i>&nbsp;[french]Admin[/french][english]Admin[/english][chinese]Admin[/chinese][spanish]Admin[/spanish][german]Admin[/german]</a>';
        }

        $content .= '</div>';
        $content = language::aff_langue($content);

        return $content;
    } 
    
}

// Init géoloc block
$content = box::geoloc();