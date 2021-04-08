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
use npds\stats\stat;
use npds\utility\str;
use npds\error\error;
use npds\assets\css;
use npds\forum\forumtopics;
use npds\forum\forumposts;


if (!stristr($_SERVER['PHP_SELF'], 'admin.php'))
{
    include('admin/die.php');
}

if (!function_exists('Mysql_Connexion'))
{
    include ('boot/bootstrap.php');
}

include('auth.php');

$f_meta_nom = 'abla';
$f_titre = translate("Tableau de bord");

admindroits($aid, $f_meta_nom);

global $language;
$hlpfile = '';

global $admin;
if ($admin) 
{
    include ('header.php');
   
    GraphicAdmin($hlpfile);
    adminhead($f_meta_nom, $f_titre, $adminimg);

    global $startdate;
    list($membres, $totala, $totalb, $totalc, $totald, $totalz) = stat::req_stat();
   
    //LNL Email in outside table
    $result = sql_query("SELECT email FROM ".$NPDS_Prefix."lnl_outside_users");
    if ($result) 
    {
        $totalnl = sql_num_rows($result);
    }
    else 
    {
        $totalnl = "0";
    }

    include("storage/logs/abla.log.php");
   
    $timex = time()-$xdate;
    if ($timex >= 86400)
    {
        $timex = round($timex/86400).' '.translate("Jour(s)");
    }
    elseif ($timex >= 3600)
    {
        $timex = round($timex/3600).' '.translate("Heure(s)");
    }
    elseif ($timex >= 60)
    {
        $timex = round($timex/60).' '.translate("Minute(s)");
    }
    else
    {
        $timex = $timex.' '.translate("Seconde(s)");
    }
   
    echo '
    <hr />
    <p class="lead mb-3">'.translate("Statistiques générales").' - '.translate("Dernières stats").' : '.$timex.' </p>
    <table class="mb-2" data-toggle="table" data-classes="table mb-2">
        <thead class="collapse thead-default">
            <tr>
                <th class="n-t-col-xs-9"></th>
                <th class="text-right"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>'.translate("Nb. pages vues").' : </td>
                <td>'.str::wrh($totalz).' (';
   
    if ($totalz > $xtotalz)
    {
        echo '<span class="text-success">+';
    }
    elseif ($totalz < $xtotalz)
    {
        echo '<span class="text-danger">';
    }
    else
    {
        echo '<span>';
    }
       
    echo str::wrh($totalz-$xtotalz).'</span>)</td>
            </tr>
            <tr>
                <td>'.translate("Nb. de membres").' : </td>
                <td>'.str::wrh($membres).' (';
       
    if ($membres>$xmembres)
    {
        echo '<span class="text-success">+';
    }
    elseif ($membres<$xmembres)
    {
        echo '<span class="text-danger">';
    }
    else
    {
        echo '<span>';
    }
       
    echo str::wrh($membres-$xmembres).'</span>)</td>
            </tr>
            <tr>
                <td>'.translate("Nb. d'articles").' : </td>
                <td>'.str::wrh($totala).' (';
       
    if ($totala > $xtotala)
    {
        echo '<span class="text-success">+';
    }
    elseif ($totala < $xtotala)
    {
        echo '<span class="text-danger">';
    }
    else
    {
        echo '<span>';
    }
       
    echo str::wrh($totala-$xtotala).'</span>)</td>
            </tr>
            <tr>
                <td>'.translate("Nb. de forums").' : </td>
                <td>'.str::wrh($totalc).' (';
       
    if ($totalc > $xtotalc)
    {
        echo '<span class="text-success">+';
    }
    elseif ($totalc < $xtotalc)
    {
        echo '<span class="text-danger">';
    }
    else
    {
        echo '<span>';
    }
       
    echo str::wrh($totalc-$xtotalc).'</span>)</td>
            </tr>
            <tr>
                <td>'.translate("Nb. de sujets").' : </td>
                <td>'.str::wrh($totald).' (';
       
    if ($totald > $xtotald)
    {
        echo '<span class="text-success">+';
    }
    elseif ($totald < $xtotald)
    {
        echo '<span class="text-danger">';
    }
    else
    {
        echo '<span>';
    }
       
    echo str::wrh($totald-$xtotald).'</span>)</td>
            </tr>
            <tr>
                <td>'.translate("Nb. de critiques").' : </td>
                <td>'.str::wrh($totalb).' (';
       
    if ($totalb > $xtotalb)
    {
        echo '<span class="text-success">+';
    }
    elseif ($totalb < $xtotalb)
    {
        echo '<span class="text-danger">';
    }
    else
    {
        echo '<span>';
    }
       
    echo str::wrh($totalb-$xtotalb).'</span>)</td>
            </tr>
            <tr>
                <td>'.translate("Nb abonnés à lettre infos").' : </td>
                <td>'.str::wrh($totalnl).' (';
       
    if ($totalnl > $xtotalnl)
    {
        echo '<span class="text-success">+';
    }
    elseif ($totalnl < $xtotalnl)
    {
        echo '<span class="text-danger">';
    }
    else
    {
        echo '<span>';
    }
       
    echo str::wrh($totalnl-$xtotalnl).'</span>)</td>
            </tr>';

    $xfile = "<?php\n";
    $xfile .= "\$xdate = ".time().";\n";
    $xfile .= "\$xtotalz = $totalz;\n";
    $xfile .= "\$xmembres = $membres;\n";
    $xfile .= "\$xtotala = $totala;\n";
    $xfile .= "\$xtotalc = $totalc;\n";
    $xfile .= "\$xtotald = $totald;\n";
    $xfile .= "\$xtotalb = $totalb;\n";
    $xfile .= "\$xtotalnl = $totalnl;\n";
       
    echo '
        </tbody>
    </table>
    <p class="lead my-3">'.translate("Statistiques des chargements").'</p>
    <table data-toggle="table" data-classes="table">
        <thead class=" thead-default">
            <tr>
                <th class="n-t-col-xs-9"></th>
                <th class="text-right"></th>
            </tr>
        </thead>
        <tbody>';
       
    $num_dow = 0;
    $result = sql_query("SELECT dcounter, dfilename FROM ".$NPDS_Prefix."downloads");
       
    settype($xdownload, 'array');
       
    while(list($dcounter, $dfilename) = sql_fetch_row($result)) 
    {
        $num_dow++;
        echo '
            <tr>
                <td><span class="text-danger">';
          
        if (array_key_exists($num_dow, $xdownload))
        {
            echo $xdownload[$num_dow][1];
        }
          
        echo '</span> -/- '.$dfilename.'</td>
            <td><span class="text-danger">';
          
        if (array_key_exists($num_dow, $xdownload))
        {
            echo $xdownload[$num_dow][2];
        }
          
        echo '</span> -/- '.$dcounter.'</td>
            </tr>';
          
        $xfile .= "\$xdownload[$num_dow][1] = \"$dfilename\";\n";
        $xfile .= "\$xdownload[$num_dow][2] = \"$dcounter\";\n";
    }

    echo '
          </tbody>
    </table>
    <p class="lead my-3">Forums</p>
    <table class="table table-bordered table-sm" data-classes="table">
        <thead class="">
            <tr>
                <th>'.translate("Forum").'</th>
                <th class="n-t-col-xs-2 text-center">'.translate("Sujets").'</th>
                <th class="n-t-col-xs-2 text-center">'.translate("Contributions").'</th>
                <th class="n-t-col-xs-3 text-right">'.translate("Dernières contributions").'</th>
            </tr>
        </thead>';
       
    $result = sql_query("SELECT * FROM ".$NPDS_Prefix."catagories ORDER BY cat_id");
    $num_for = 0;
    
    while (list($cat_id, $cat_title) = sql_fetch_row($result)) 
    {
        $sub_sql = "SELECT f.*, u.uname FROM ".$NPDS_Prefix."forums f, ".$NPDS_Prefix."users u WHERE f.cat_id = '$cat_id' AND f.forum_moderator = u.uid ORDER BY forum_index,forum_id";
          
        if (!$sub_result = sql_query($sub_sql)) 
        {
            error::forumerror('0022');
        }

        if ($myrow = sql_fetch_assoc($sub_result)) 
        {
            echo '
            <tbody>
                <tr>
                   <td class="table-active" colspan="4">'.stripslashes($cat_title).'</td>
                </tr>';
            do 
            {
                $num_for++;
                $last_post = forumtopics::get_last_post($myrow['forum_id'], 'forum', 'infos', true);
                
                echo '
                <tr>';
                
                $total_topics = forumtopics::get_total_topics($myrow['forum_id']);
                $name = stripslashes($myrow['forum_name']);
                
                $xfile .= "\$xforum[$num_for][1] = \"$name\";\n";
                $xfile .= "\$xforum[$num_for][2] = $total_topics;\n";
                
                $desc = stripslashes($myrow['forum_desc']);
                
                echo '
                <td><a tabindex="0" role="button" data-trigger="focus" data-toggle="popover" data-placement="right" data-content="'.$desc.'"><i class="far fa-lg fa-file-alt mr-2"></i></a><a href="'.site_url('viewforum.php?forum='.$myrow['forum_id']).'" ><span class="text-danger">';
                
                if (array_key_exists($num_for, $xforum))
                {
                    echo $xforum[$num_for][1];
                }
                
                echo '</span> -/- '.$name.' </a></td>
                    <td class="text-center"><span class="text-danger">';
                
                if (array_key_exists($num_for, $xforum))
                {
                    echo $xforum[$num_for][2];
                }
                
                echo '</span> -/- '.$total_topics.'</td>';
                
                $total_posts = forumposts::get_total_posts($myrow['forum_id'], "", "forum", false);
                $xfile .= "\$xforum[$num_for][3] = $total_posts;\n";
                
                echo '
                <td class="text-center"><span class="text-danger">';
                
                if (array_key_exists($num_for, $xforum))
                {
                    echo $xforum[$num_for][3];
                }
                
                echo '</span> -/- '.$total_posts.'</td>
                <td class="text-right small">'.$last_post.'</td>';
            } while($myrow = sql_fetch_assoc($sub_result));
        }
    }

    echo '
            </tr>
        </tbody>
    </table>';

    $file = fopen("storage/logs/abla.log.php", "w");
    $xfile .= "?>\n";
    fwrite($file, $xfile);
    fclose($file);

    css::adminfoot('', '', '', '');
} 
else
{
    redirect_url('index.php');
}
