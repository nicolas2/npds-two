<?php

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

define('BASEPATH', dirname(__DIR__) .DS);

define('APPPATH', BASEPATH);

define('SYSPATH', BASEPATH .'system' .DS);


//var_dump(BASEPATH, APPPATH, SYSPATH);

//
include(APPPATH."lib/grab_globals.php");

//
include(APPPATH."config/config.php");

//
include(APPPATH."lib/multi-langue.php");

//
include(APPPATH."language/$language/lang-$language.php");

//
include(APPPATH."lib/cache/cache.class.php");

//
if ($mysql_i==1)
   include(APPPATH."lib/database/mysqli.php");
else 
   include(APPPATH."lib/database/mysql.php");

//
include(APPPATH."lib/metalang/metalang.php");