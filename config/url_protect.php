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
 * Dont modified these lines if you dont know exactly what you have to do
 */
$bad_uri_content = array(
                  // To Filter "php WebWorm" and like Santy and other
                  "perl",
                  "chr(",

                  // To prevent SQL-injection
                  " union ",
                  " into ",
                  " select ",
                  " update ",
                  " from ",
                  " where ",
                  " insert ",
                  " drop ",
                  " delete ",
                  
                  // Comment inline SQL - shiney 2011
                  "/*",

                  // To prevent XSS
                  "outfile",
                  "/script",
                  "url(",
                  "/object",
                  "img dynsrc",
                  "img lowsrc",
                  "/applet",
                  "/style",
                  "/iframe",
                  "/frameset",
                  "document.cookie",
                  "document.location",
                  "msgbox(",
                  "alert(",
                  "expression(",
                  
                  // some HTML5 tags - dev 2012
                  "formaction",
                  "autofocus",
                  "onforminput",
                  "onformchange",
                  "history.pushstate("
                 );

return $bad_uri_content;
