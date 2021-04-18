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


/**
 * font
 */
class font {


	/**
	 * [svg description]
	 * @return [type] [description]
	 */
	public static function svg()
	{
	    $fonts_svg = array(
	        ['user','uf007','Utilisateur'],
	        ['userCircle','uf2bd','Utilisateur en cercle'],
	        ['userCircle','uf2be','Utilisateur en cercle'],
	        ['users','uf0c0','Utilisateurs'],
	        ['heart','uf004','Coeur'],
	        ['thumbtack','uf08d','Punaise'],
	        ['circle','uf111','Cercle'],
	        ['camera','uf030','Appareil photo'],
	        ['anchor','uf13d','Ancre'],
	        ['mapMarker','uf041','Marqueur carte'],
	        ['plane','uf072','Avion'],
	        ['star','uf005','Etoile'],
	        ['home','uf015','Maison'],
	        ['flag','uf024','Drapeau'],
	        ['crosshairs','uf05b','Croix'],
	        ['asterisk','uf069','Astérisque'],
	        ['fire','uf06d','Flamme'],
	        ['comment','uf075','Commentaire']
	    );

	    return $fonts_svg;		
	}

	/**
	 * [provider description]
	 * @return [type] [description]
	 */
	public static function provider()
	{
		$fond_provider = array(
		    ['OSM', geoloc_translate("Plan").' (OpenStreetMap)'],
		    ['toner', geoloc_translate("Noir et blanc").' (Stamen)'],
		    ['watercolor', geoloc_translate("Dessin").' (Stamen)'],
		    ['terrain', geoloc_translate("Relief").' (Stamen)'],
		    ['modisterra', geoloc_translate("Satellite").' (NASA)'],
		    ['natural-earth-hypso-bathy', geoloc_translate("Relief").' (mapbox)'],
		    ['geography-class', geoloc_translate("Carte").' (mapbox)'],
		    ['Road', geoloc_translate("Plan").' (Bing maps)'],
		    ['Aerial', geoloc_translate("Satellite").' (Bing maps)'],
		    ['AerialWithLabels', geoloc_translate("Satellite").' et label (Bing maps)'],
		    ['sat-google', geoloc_translate("Satellite").' (Google maps)']
		);

		return $fond_provider;
	}

}
