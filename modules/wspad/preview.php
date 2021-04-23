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


// For More security
if (!stristr($_SERVER['PHP_SELF'], 'modules.php'))
{ 
    die();
}

if (strstr($ModPath, '..') 
    || strstr($ModStart, '..') 
    || stristr($ModPath, 'script') 
    || stristr($ModPath, 'cookie') 
    || stristr($ModPath, 'iframe') 
    || stristr($ModPath, 'applet') 
    || stristr($ModPath, 'object') 
    || stristr($ModPath, 'meta') 
    || stristr($ModStart, 'script') 
    || stristr($ModStart, 'cookie') 
    || stristr($ModStart, 'iframe') 
    || stristr($ModStart, 'applet') 
    || stristr($ModStart, 'object') 
    || stristr($ModStart, 'meta')) 
{
    die();
}

global $language, $NPDS_Prefix;
include_once("modules/$ModPath/lang/$language.php");
// For More security

if (isset($user)) 
{
    if ($cookie[9] == "") 
    {
        $cookie[9] = $Default_Theme;
    }

    if (isset($theme)) 
    {
        $cookie[9] = $theme;
    }

    $tmp_theme = $cookie[9];
      
    if (!$file = @opendir("themes/$cookie[9]"))
    {
        $tmp_theme = $Default_Theme;
    }
} 
else
{
    $tmp_theme = $Default_Theme;
}
   
$Titlesitename = "NPDS wspad";
   
include("config/meta.php");
   
echo '<link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />';
   
global $site_font;
   
echo import_css($tmp_theme, $language, $site_font, '','');
   
echo '
</head>
<body style="padding: 10px; background:#ffffff;">';
      
$wspad = rawurldecode(decrypt($pad));
$wspad = explode("#wspad#", $wspad);
$row = sql_fetch_assoc(sql_query("SELECT content, modtime, editedby, ranq  FROM ".$NPDS_Prefix."wspad WHERE page='".$wspad[0]."' AND member='".$wspad[1]."' AND ranq='".$wspad[2]."'"));
      
echo '
    <h2>'.$wspad[0].'</h2>
    <span class="">[ '.wspad_trans("révision").' : '.$row['ranq'].' - '.$row['editedby']." / ".date(translate("dateinternal"), $row['modtime']+((integer)$gmt*3600)).' ]</span>
    <hr />
    '.aff_langue($row['content']).'
</body>
</html>';
