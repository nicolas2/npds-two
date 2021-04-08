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
namespace npds\banners;


/*
 * banner
 */
class banner {


	/**
	 * [IncorrectLogin description]
	 */
	public static function IncorrectLogin() 
	{
	    static::header_page();
	    
	    echo '<div class="alert alert-danger lead">'.translate("Identifiant incorrect !").'<br /><button class="btn btn-secondary mt-2" onclick="javascript:history.go(-1)" >'.translate("Retour en arrière").'</button></div>';
	    
	    static::footer_page();
	}

	/**
	 * [header_page description]
	 * @return [type] [description]
	 */
	public static function header_page() 
	{
	    global $Titlesitename, $Default_Theme, $language;
	    
	    include_once("modules/upload/upload.conf.php");
	    
	    include("config/meta.php");
	    
	    if ($url_upload_css) 
	    {
	        $url_upload_cssX = str_replace('style.css', $language.'-style.css', $url_upload_css);
	        
	        if (is_readable($url_upload.$url_upload_cssX))
	        {
	            $url_upload_css = $url_upload_cssX;
	        }
	        
	        print ("<link href=\"".$url_upload.$url_upload_css."\" title=\"default\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n");
	    }
	    
	    if(file_exists ('lib/include/header_head.inc'))
	    {
	        include('lib/include/header_head.inc');
	    }
	    
	    if(file_exists ('themes/'.$Default_Theme.'/include/header_head.inc'))
	    {
	        include('themes/'.$Default_Theme.'/include/header_head.inc');
	    }
	    
	    if(file_exists ('themes/'.$Default_Theme.'/style/style.css'))
	    {
	        echo '<link href="themes/'.$Default_Theme.'/style/style.css" rel="stylesheet" type=\"text/css\" media="all" />';
	    }

	    echo '
	    </head>
	    <body style="margin-top:64px;">
	        <div class="container-fluid">
	            <nav class="navbar fixed-top navbar-toggleable-md navbar-inverse bg-primary">
	                <a class="navbar-brand" href="index.php">Home</a>
	            </nav>
	            <h2 class="mt-4">'.translate("Bannières - Publicité").' @ '.$Titlesitename.'</h2>
	            <p align="center">';
	}

	/**
	 * [footer_page description]
	 * @return [type] [description]
	 */
	public static function footer_page() 
	{
	   echo '    </p>
	        </div>
	        <script type="text/javascript" src="assets/js/npds_adapt.js"></script>
	    </body>
	    </html>';
	}

}
