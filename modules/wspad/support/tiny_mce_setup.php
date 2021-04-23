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


global $surlignage, $font_size, $auteur, $groupe;

$tmp .= "
theme_advanced_path : false,
toolbar : 'image | tablecontrols | npds_img npds_gperso npds_gmns npds_gupl',
tiny_mce_groupe : '&groupe=$groupe',

setup: function (ed) {
    ed.on('keydown',function(e) {
        // faisons une 'static' en javascript
        if ( typeof this.counter == 'undefined' ) this.counter = 0;

        // On capte les touches de directions
        if (e.keyCode >= 37 && e.keyCode <= 40) {
            this.counter=0;
            return true;
        }
        // On capte la touche backspace
        if ((e.keyCode == 8) || (e.keyCode == 13)) {
            this.counter=0;
            return true;
        }

        //console.log('key : ' + e.keyCode);//<==debug

        if (this.counter==0) {
            tinymce.activeEditor.formatter.register('wspadformat', {
                inline     : 'span',
                styles     : {'background-color' : '$surlignage', 'font-size' : '$font_size'},
                classes    : '$auteur'
            });
            tinymce.activeEditor.formatter.apply('wspadformat');
            this.counter=1;
        }
    });

    // déplacement dans le RTE via la sourie
    ed.on('mousedown',function(ed, e) {
        this.counter=0;
    });
},";
