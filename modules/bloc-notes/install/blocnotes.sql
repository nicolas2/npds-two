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

/* Attention le cas échéant au prefix de cette table si 
vous avez plusieurs Npds Two dans la même DB */

CREATE TABLE blocnotes (
  bnid tinytext NOT NULL,
  texte text,
  PRIMARY KEY  (bnid(32))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO metalang VALUES ('!blocnote!', 'function MM_blocnote($arg) {\r\n      global $REQUEST_URI;\r\n      if (!stristr($REQUEST_URI,"admin.php")) {\r\n         return(@oneblock($arg,"RB"));\r\n      } else {\r\n         return("");\r\n      }\r\n}', 'meta', '-', NULL, '[french]Fabrique un blocnote contextuel en lieu et place du meta-mot / syntaxe : !blocnote!ID - ID = Id du bloc de droite dans le gestionnaire de bloc de NPDS[/french]', '0');
